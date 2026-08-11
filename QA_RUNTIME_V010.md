# ADAPA Flutter v0.10 — Runtime QA harness

v0.10 does not add course content or new educational features. It turns the
existing test plan into executable Flutter tests so the first real run produces
useful failures instead of manual guesswork.

## Important correction from v0.9

A missing `gradlew`, `gradlew.bat`, or `gradle-wrapper.jar` in the ZIP is not a
reason to skip `flutter build apk`.

Flutter's Android Gradle utility calls `injectGradleWrapperIfNeeded()` before it
uses the wrapper and copies missing wrapper runtime files from the Flutter SDK
cache. The preflight now relies on Flutter to do that job.

## Automated coverage

### `flutter test`

In addition to the existing logic tests, v0.10 adds:

- production asset bundle test: 131 image assets;
- production content test: 8 units, 36 lessons, 171 activities, 9 families;
- **all 171 real activity renderers are pumped as widgets**;
- mobile navigation test at **320×568**;
- settings test at **412×915**;
- Android package/minSdk/TTS contract.

### Real device / emulator

`integration_test/app_navigation_test.dart` verifies an actual application flow:

ADAPA → Descubre el Hangul → Vocales básicas → first activity → Settings.

Run it with:

```powershell
flutter test integration_test/app_navigation_test.dart -d DEVICE_ID
```

Or use:

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\device_qa.ps1
```

## Main preflight

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\preflight.ps1
```

It now performs:

1. Flutter version
2. Flutter doctor
3. static project audit
4. flutter clean
5. flutter pub get
6. flutter analyze --suggestions
7. flutter analyze
8. flutter test
9. flutter build apk --debug
10. flutter devices

If all nine executable checks pass, the app has crossed the first real technical
QA gate and is ready for device-focused behavioral testing.

## Manual checks after automated QA

On an Android phone, verify:

- app starts offline;
- progress remains after force-close/reopen;
- Korean TTS works when ko-KR is installed;
- absence of ko-KR shows a message and does not crash;
- normal and slow TTS speeds are noticeably different;
- all vowel/consonant stroke sequences render;
- Unit 4 visual vocabulary renders;
- Hangul keyboard input does not hide the active field;
- long dialogue turns do not overflow;
- reorder activities remain usable on a narrow phone;
- free writing survives app restart;
- locked units cannot be entered from the learning path;
- finishing prerequisites unlocks the next unit;
- final challenge never exposes romanization.

## Still not claimed

No `flutter analyze`, `flutter test`, APK build or device run has been executed
inside the artifact-generation environment, because Flutter is not installed
there. v0.10 makes those checks one-command reproducible on the development PC.
