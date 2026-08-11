# ADAPA v0.7 — Android platform decision

## Decision

The production branch now targets **Android minSdk 24**.

Earlier ADAPA iterations attempted to preserve API 21. That is no longer a
sound baseline for the current stable Flutter toolchain: Flutter 3.44.x lists
Android 24+ as supported and Android 23 or earlier as unsupported.

For a publishable, maintainable v1, ADAPA follows the supported Flutter
baseline instead of forcing an unsupported minSdk.

## Toolchain baseline

- Flutter stable: 3.44.x-compatible project files
- Gradle: 9.1.0
- Android Gradle Plugin: 9.0.1
- Kotlin Gradle Plugin declared by the Flutter template: 2.3.20
- Java/JVM target: 17
- compileSdk: supplied by Flutter 3.44.x (36)
- targetSdk: supplied by Flutter 3.44.x (36)
- minSdk: **24**, explicit in `android/app/build.gradle.kts`

## Why explicit minSdk?

`minSdk = 24` is intentionally written into the project rather than silently
inherited. That makes a future support-baseline change visible during review.

## TTS

The production Android manifest declares:

`android.intent.action.TTS_SERVICE`

inside `<queries>`, required for discovery of TTS engines on Android 11+.

The release manifest does **not** request INTERNET. ADAPA's course remains
offline-first. Debug/profile manifests request INTERNET only for Flutter
development tooling / VM-service communication.

## API 21 legacy branch

If API 21 becomes a hard business requirement later, it should be handled as a
separate compatibility branch pinned to an older Flutter toolchain. It should
not constrain the publishable main branch.
