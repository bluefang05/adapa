#!/usr/bin/env python3
from pathlib import Path
import re
import sys

ROOT = Path(__file__).resolve().parents[1]
pubspec = (ROOT / "pubspec.yaml").read_text(encoding="utf-8")
settings = (ROOT / "android/settings.gradle.kts").read_text(encoding="utf-8")
wrapper = (ROOT / "android/gradle/wrapper/gradle-wrapper.properties").read_text(encoding="utf-8")
app_gradle = (ROOT / "android/app/build.gradle.kts").read_text(encoding="utf-8")
gradle_props = (ROOT / "android/gradle.properties").read_text(encoding="utf-8")
dart_sources = "\n".join(
    p.read_text(encoding="utf-8")
    for root in (ROOT / "lib", ROOT / "test", ROOT / "integration_test")
    for p in root.rglob("*.dart")
)

errors = []

def require(condition, message):
    if not condition:
        errors.append(message)

# Flutter 3.44.7 template/tooling contract.
require("sdk: '>=3.12.0 <4.0.0'" in pubspec, "Dart lower bound is not 3.12")
require("flutter: '>=3.44.0'" in pubspec, "Flutter lower bound is not 3.44")
require('version "9.0.1"' in settings, "AGP is not 9.0.1")
require('version "2.3.20"' in settings, "KGP is not 2.3.20")
require("gradle-9.1.0-all.zip" in wrapper, "Gradle wrapper is not 9.1.0")
require("JavaVersion.VERSION_17" in app_gradle, "Java 17 contract missing")
require("minSdk = 24" in app_gradle, "minSdk is not 24")
require("targetSdk = flutter.targetSdkVersion" in app_gradle, "targetSdk must follow Flutter")
require("android.builtInKotlin=false" in gradle_props, "Flutter 3.44 KGP compatibility flag missing")
require("android.newDsl=false" in gradle_props, "Flutter 3.44 legacy DSL compatibility flag missing")

# Exact package pins until the first real `pubspec.lock` is generated.
for spec in (
    "flutter_tts: 4.2.5",
    "unorm_dart: 0.3.2",
    "shared_preferences: 2.5.5",
    "flutter_lints: 6.0.0",
):
    require(spec in pubspec, f"Dependency is not exactly pinned: {spec}")

# Legacy preferences API must be absent from app/test source.
require(
    "SharedPreferences.getInstance()" not in dart_sources,
    "Legacy SharedPreferences.getInstance() is still used",
)
require(
    "SharedPreferences.setMockInitialValues" not in dart_sources,
    "Legacy SharedPreferences test mock is still used",
)
require(
    "SharedPreferencesAsync" in dart_sources,
    "Modern SharedPreferencesAsync API is not used",
)

print(f"ADAPA dependency contract audit: {len(errors)} error(s)")
for error in errors:
    print("ERROR:", error)

sys.exit(1 if errors else 0)
