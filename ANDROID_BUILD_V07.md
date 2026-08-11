# ADAPA v0.7 — first Android build checklist

Run these commands from the project root on a machine with Flutter installed.

## 1. Verify toolchain

```powershell
flutter --version
flutter doctor -v
```

Use Flutter stable 3.44.x for this project baseline.

## 2. Resolve packages

```powershell
flutter clean
flutter pub get
```

Flutter will create/update `android/local.properties` and inject Gradle wrapper
files when needed.

## 3. Static checks

```powershell
flutter analyze
flutter test
```

Do not continue to Play packaging if either command fails.

## 4. Run on device

```powershell
flutter devices
flutter run
```

Verify at minimum:

- course opens offline;
- progress survives app restart;
- Korean TTS works when `ko-KR` is installed;
- missing Korean TTS is handled without crashing;
- stroke PNGs display;
- Unit 4 vocabulary images display;
- final challenge remains Hangul-only.

## 5. Release-mode smoke test

```powershell
flutter run --release
```

The v0.7 Gradle file still uses the debug signing key for local release-mode
testing. **Do not upload that artifact to Play.**

## 6. Build an AAB only after signing is configured

Later, when the upload keystore is connected:

```powershell
flutter build appbundle --release
```

Expected output:

`build\app\outputs\bundle\release\app-release.aab`
