# ADAPA Flutter v0.12 — Technical release candidate

This iteration deliberately makes no pedagogical changes.

## Persistence modernization

`shared_preferences` 2.5.5 documents the original `SharedPreferences` API as
legacy and recommends `SharedPreferencesAsync` or `SharedPreferencesWithCache`
for new work.

ADAPA now uses `SharedPreferencesAsync` behind an internal `KeyValueStore`
interface. This gives us:

- no process-local preferences cache;
- Android's modern DataStore Preferences backend by default;
- deterministic unit/widget tests without depending on plugin mock globals;
- one injectable persistence boundary for both progress and TTS settings.

Because ADAPA has not shipped publicly yet, there is no installed-user legacy
preferences migration to preserve.

## Dependency freeze before first real build

Until Flutter generates the first `pubspec.lock`, dependencies are exact:

- flutter_tts 4.2.5
- shared_preferences 2.5.5
- unorm_dart 0.3.2
- flutter_lints 6.0.0

The app still declares Flutter >=3.44.0 / Dart >=3.12.0.

## Flutter 3.44.7 Android contract

The official Flutter 3.44.7 tooling source defines:

- Gradle 9.1.0
- Android Gradle Plugin 9.0.1
- Kotlin Gradle Plugin 2.3.20
- compileSdk 36
- minSdk 24
- targetSdk 36
- Java minimum 17

ADAPA's Android project is pinned/aligned to those values.

## Next gate

No additional feature work should be added before the first real command run.

Run on the development PC:

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\preflight.ps1
```

Then fix only failures proven by Flutter/analyzer/tests/build.
