# ADAPA AI Agent Guide

This file is for AI coding assistants working on this repository.

## Project Snapshot

ADAPA is a Flutter learning app. The first course is Spanish-language Korean from zero level, A0-A1. The app is offline-first: course content, images, stroke guides, progress, and local settings live on device.

Current navigation model:

`course -> unit -> lesson -> theory/activity`

Production content lives under `assets/content/`. Avoid hardcoding Korean lesson data in Dart unless there is already a clear local pattern for that exact behavior.

## High-Signal Paths

- `lib/app.dart`: app bootstrap, runtime wiring, theme, repository, TTS service.
- `lib/core/runtime/`: inherited runtime object shared by screens.
- `lib/core/content/`: asset-backed course loading and content reference resolution.
- `lib/core/models/`: normalized course, unit, lesson, activity, and romanization models.
- `lib/core/services/tts_service.dart`: Korean pronunciation/TTS abstraction and Flutter TTS implementation.
- `lib/core/settings/app_settings_controller.dart`: local appearance and TTS speed settings.
- `lib/core/progress/`: progress snapshot, rules, persistence coordination.
- `lib/features/activity/renderers/`: real activity UI grouped by activity family.
- `lib/features/lesson/widgets/lesson_content_block.dart`: theory block rendering and many lesson-level pronunciation buttons.
- `assets/content/units/`: unit JSON files.
- `assets/content/resources/`: shared catalogs for strokes, visual vocabulary, dialogues, readings, and assessment policy.
- `test/content_completeness_audit_test.dart`: structural audit for all production content.
- `test/all_activity_renderers_smoke_test.dart`: pumps all production activities through their real renderers.

## Content Rules

- Treat `assets/content` as the source of truth for curriculum data.
- Keep JSON valid UTF-8. Hangul corruption/mojibake is a release blocker.
- Preserve the pedagogical romanization policy:
  - early units may show romanization;
  - later units hide or disable it;
  - do not add a global romanization override casually.
- Activity IDs are persisted. Renaming IDs can break saved progress unless handled by a migration.
- When adding assets, declare their directories in `pubspec.yaml`.
- Use structured JSON parsing or existing model loaders. Avoid ad hoc string rewrites across content files.

## Pronunciation / TTS

ADAPA uses device TTS through `flutter_tts`; it does not ship recorded Korean voice clips for course pronunciation.

Important behavior:

- Default requested locale is `ko-KR`.
- `FlutterTtsService` accepts compatible Korean locale variants reported by devices, including `ko-KR`, `ko_KR`, `ko`, and extended Korean locale tags.
- It must not fall back to non-Korean voices such as `en-US` or `ja-JP`.
- If no Korean voice is available, UI should show a user-facing Spanish message instead of failing silently.
- TTS speed is controlled by `AppSettingsController` and exposed in Settings.

When debugging pronunciation complaints, check:

1. The device has a Korean TTS voice installed.
2. `Settings -> Voz coreana -> Probar voz coreana` works.
3. The relevant activity sends Hangul text, not Spanish text or romanization, to `TtsControls`.
4. `test/tts_service_test.dart` still passes.
5. `test/content_completeness_audit_test.dart` reports no blocking content errors.

## Verification Commands

Use these from the repository root.

```powershell
flutter test test\tts_service_test.dart
flutter test test\content_completeness_audit_test.dart test\all_activity_renderers_smoke_test.dart test\mobile_navigation_smoke_test.dart
```

For a broad regression pass without regenerating store screenshots:

```powershell
$tests = Get-ChildItem test -Filter *.dart | Where-Object { $_.Name -ne 'generate_play_store_screenshots_test.dart' } | ForEach-Object { $_.FullName }
flutter test $tests
```

For analysis:

```powershell
dart analyze
```

Known current analyzer notes may include unrelated cleanup warnings in screenshot/ad files. Do not mix those cleanups into a pronunciation fix unless the user asks.

The full `flutter test` may run screenshot generation and can take longer or stall depending on the local graphics/test environment. Prefer the broad pass above when screenshots are not part of the requested change.

## Change Discipline

- Keep changes scoped.
- Do not reformat unrelated files.
- Do not regenerate release artifacts unless explicitly requested.
- Do not modify `release/` assets, screenshots, or app bundles unless the task is release packaging.
- Existing content audit warnings about one-character answers are not automatically bugs; inspect the lesson context before changing content.
- If Android behavior is involved, check `android/app/src/main/AndroidManifest.xml` and `ANDROID_TTS_SETUP.md`.

## Release-Sensitive Areas

- Android package ID and signing files.
- AdMob IDs and test-mode behavior.
- Progress persistence schema and activity IDs.
- `pubspec.yaml` dependency pins and asset declarations.
- Korean curriculum JSON and shared catalogs.

