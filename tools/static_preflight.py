#!/usr/bin/env python3
from __future__ import annotations

import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

failures: list[str] = []
warnings: list[str] = []

def ok(condition: bool, message: str) -> None:
    if not condition:
        failures.append(message)

def read_json(path: Path):
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except Exception as exc:
        failures.append(f"Invalid JSON {path.relative_to(ROOT)}: {exc}")
        return None

# 1. Core files.
required = [
    ROOT / "pubspec.yaml",
    ROOT / "lib" / "main.dart",
    ROOT / "lib" / "app.dart",
    ROOT / "android" / "app" / "build.gradle.kts",
    ROOT / "android" / "app" / "src" / "main" / "AndroidManifest.xml",
    ROOT / "assets" / "content" / "course_manifest.json",
]
for path in required:
    ok(path.exists(), f"Missing required file: {path.relative_to(ROOT)}")

# 2. Android Gradle contract.
gradle_path = ROOT / "android" / "app" / "build.gradle.kts"
if gradle_path.exists():
    gradle = gradle_path.read_text(encoding="utf-8")
    ok('id("com.android.application")' in gradle, "Android application plugin missing")
    ok('id("kotlin-android")' in gradle, "Kotlin Android plugin missing")
    ok(
        gradle.find('id("kotlin-android")') <
        gradle.find('id("dev.flutter.flutter-gradle-plugin")'),
        "Flutter Gradle Plugin must come after Kotlin plugin",
    )
    ok('minSdk = 24' in gradle, "minSdk 24 contract changed")
    ok(
        'applicationId = "com.enmanuelapps.adapa"' in gradle,
        "Unexpected Android applicationId",
    )

# 3. JSON assets and uniqueness.
json_files = list((ROOT / "assets" / "content").rglob("*.json"))
for path in json_files:
    read_json(path)

manifest = read_json(ROOT / "assets" / "content" / "course_manifest.json")
activity_ids: set[str] = set()
lesson_ids: set[str] = set()
unit_ids: set[str] = set()
activity_count = 0
lesson_count = 0

if manifest:
    units = manifest.get("units", [])
    ok(len(units) == 8, f"Expected 8 units, found {len(units)}")
    for summary in units:
        unit_path = ROOT / summary["asset"]
        unit = read_json(unit_path)
        if not unit:
            continue
        uid = unit["id"]
        ok(uid not in unit_ids, f"Duplicate unit id: {uid}")
        unit_ids.add(uid)
        for lesson in unit.get("lessons", []):
            lesson_count += 1
            lid = lesson["id"]
            ok(lid not in lesson_ids, f"Duplicate lesson id: {lid}")
            lesson_ids.add(lid)
            for activity in lesson.get("activities", []):
                activity_count += 1
                aid = activity["id"]
                ok(aid not in activity_ids, f"Duplicate activity id: {aid}")
                activity_ids.add(aid)

ok(lesson_count == 36, f"Expected 36 lessons, found {lesson_count}")
ok(activity_count == 171, f"Expected 171 activities, found {activity_count}")

# 4. Every required activity reference resolves.
if manifest:
    for summary in manifest.get("units", []):
        unit = read_json(ROOT / summary["asset"])
        if not unit:
            continue
        for lesson in unit.get("lessons", []):
            required_ids = lesson.get("completion", {}).get(
                "required_activity_ids", []
            )
            for aid in required_ids:
                ok(
                    aid in activity_ids,
                    f"Broken required activity reference: {aid}",
                )

# 5. Referenced image files exist physically.
asset_refs: set[str] = set()
stroke_catalog = read_json(
    ROOT / "assets" / "content" / "resources" /
    "unit_01_stroke_asset_catalog_v1.0.json"
)
if stroke_catalog:
    for item in stroke_catalog.get("sets", {}).values():
        asset_refs.update(map(str, item.get("steps", [])))
        if item.get("final"):
            asset_refs.add(str(item["final"]))

visual_catalog = read_json(
    ROOT / "assets" / "content" / "resources" /
    "unit_04_visual_asset_catalog_v1.0.json"
)
if visual_catalog:
    for item in visual_catalog.get("assets", []):
        asset_refs.add(str(item["asset"]))

for rel in sorted(asset_refs):
    ok((ROOT / rel).exists(), f"Missing physical asset: {rel}")

ok(len(asset_refs) == 131, f"Expected 131 production image refs, found {len(asset_refs)}")

# 6. pubspec must declare each leaf stroke directory.
pubspec = (ROOT / "pubspec.yaml").read_text(encoding="utf-8")
stroke_leafs = {
    path.parent.relative_to(ROOT).as_posix() + "/"
    for path in (ROOT / "assets" / "strokes").rglob("*.png")
}
for leaf in sorted(stroke_leafs):
    ok(f"- {leaf}" in pubspec, f"Stroke directory not bundled in pubspec: {leaf}")
ok(len(stroke_leafs) == 29, f"Expected 29 stroke asset directories, found {len(stroke_leafs)}")

# 7. Local Dart imports resolve.
dart_files = list((ROOT / "lib").rglob("*.dart")) + list((ROOT / "test").rglob("*.dart"))
for dart_file in dart_files:
    text = dart_file.read_text(encoding="utf-8")
    for rel in re.findall(r"import\s+'([^']+)';", text):
        if rel.startswith(("package:", "dart:")):
            continue
        target = (dart_file.parent / rel).resolve()
        ok(
            target.exists(),
            f"Broken local import in {dart_file.relative_to(ROOT)}: {rel}",
        )


# 7b. Content grading and dialogue playback contracts.
text_input_contract_issues = []
playback_contract_issues = []
activity_by_id = {}
if manifest:
    for summary in manifest.get("units", []):
        unit = read_json(ROOT / summary["asset"])
        if not unit:
            continue
        for lesson in unit.get("lessons", []):
            for activity in lesson.get("activities", []):
                activity_by_id[activity["id"]] = activity
                if activity.get("family") == "text_input":
                    score_mode = activity.get("scoreMode", "auto")
                    if score_mode not in ("none", "structural"):
                        answers = activity.get("payload", {}).get("accepted_answers", [])
                        if not answers:
                            text_input_contract_issues.append(activity["id"])

for activity in activity_by_id.values():
    if activity.get("type") != "tts_dialogue_playback":
        continue
    source_id = activity.get("payload", {}).get("source_activity")
    source = activity_by_id.get(source_id)
    if source is None:
        playback_contract_issues.append(
            f"{activity['id']} -> missing {source_id}"
        )
    elif source.get("type") not in {
        "dialogue", "dialogue_roleplay", "final_guided_conversation"
    }:
        playback_contract_issues.append(
            f"{activity['id']} -> incompatible {source.get('type')}"
        )

ok(
    not text_input_contract_issues,
    "Scored text activities without accepted_answers: "
    + ", ".join(text_input_contract_issues),
)
ok(
    not playback_contract_issues,
    "Broken dialogue playback source contracts: "
    + ", ".join(playback_contract_issues),
)

# 7c. Flutter 3.44+ reorder API contract.
ordering_renderer = (ROOT / "lib/features/activity/renderers/ordering_activity_renderer.dart").read_text(encoding="utf-8")
ok(
    "onReorderItem:" in ordering_renderer,
    "Ordering renderer is not using onReorderItem",
)
ok(
    "if (newIndex > oldIndex) newIndex -= 1;" not in ordering_renderer,
    "Ordering renderer still manually adjusts newIndex",
)

# 8. Wrapper runtime may be absent in the archive.
# Flutter's GradleUtils.injectGradleWrapperIfNeeded() supplies missing wrapper
# runtime files when a Flutter Android build is started, so this is informational.
wrapper_candidates = [
    ROOT / "android" / "gradlew",
    ROOT / "android" / "gradlew.bat",
    ROOT / "android" / "gradle" / "wrapper" / "gradle-wrapper.jar",
]
missing_wrapper = [p for p in wrapper_candidates if not p.exists()]
if missing_wrapper:
    print(
        "INFO: Gradle wrapper runtime is not packaged; Flutter will inject "
        "missing wrapper files when an Android build starts."
    )

print(f"ADAPA static preflight: {len(failures)} failure(s), {len(warnings)} warning(s)")
for item in failures:
    print(f"FAIL: {item}")
for item in warnings:
    print(f"WARN: {item}")

if failures:
    sys.exit(1)
