# ADAPA Flutter v0.9 — QA preflight

This iteration is deliberately a **pre-compilation correction pass**.

## Build blockers corrected

### 1. Kotlin Android plugin

`android/gradle.properties` explicitly has:

- `android.builtInKotlin=false`
- `android.newDsl=false`

That means ADAPA is deliberately on Flutter's temporary legacy KGP path for
AGP 9 compatibility. The app module must therefore apply `kotlin-android`.

v0.8 declared the plugin version in `settings.gradle.kts` but did not apply
the plugin in `android/app/build.gradle.kts`. v0.9 fixes the plugin order to:

1. `com.android.application`
2. `kotlin-android`
3. `dev.flutter.flutter-gradle-plugin`

### 2. Nested stroke assets

The 111 stroke PNGs live in 29 leaf directories such as:

`assets/strokes/vowels/u314f/`

Flutter asset directory declarations include only files directly inside the
declared directory, except resolution variants. Declaring only
`assets/strokes/` was therefore insufficient.

v0.9 explicitly declares all 29 stroke leaf directories in `pubspec.yaml`.

## New executable tests

Once Flutter is available:

- `asset_bundle_integrity_test.dart` loads all 131 production image assets;
- `production_content_smoke_test.dart` loads 8 units / 36 lessons / 171 activities;
- `android_contract_test.dart` checks package, minSdk, Kotlin plugin and TTS query.

## Preflight scripts

Windows:

```powershell
powershell -ExecutionPolicy Bypass -File .\tools\preflight.ps1
```

Linux/macOS:

```bash
bash tools/preflight.sh
```

The scripts run:

1. `flutter --version`
2. `flutter doctor -v`
3. static Python audit
4. `flutter clean`
5. `flutter pub get`
6. `flutter analyze`
7. `flutter test`
8. debug APK, if a complete Gradle wrapper is present

## Gradle wrapper runtime

The archived project may omit `gradlew`, `gradlew.bat` and `gradle-wrapper.jar`. Flutter's Android build tooling injects missing wrapper files from its SDK cache before the Gradle build runs. Use `flutter build apk --debug`; do not fetch an arbitrary wrapper JAR manually.

