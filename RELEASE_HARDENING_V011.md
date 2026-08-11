# ADAPA Flutter v0.11 — release hardening

This iteration prepares publication artifacts without pretending that the
unexecuted Flutter build has passed.

## Security / privacy changes
- release no longer falls back to the debug signing key;
- release signing reads `android/key.properties` only when present;
- Android cloud backup is disabled;
- cleartext traffic is disabled;
- production manifest still has no INTERNET permission;
- in-app Privacy & data screen added;
- local progress deletion is exposed to the user.

## Google Play preparation
- privacy policy (Markdown + hostable HTML);
- Spanish store listing draft;
- Data safety worksheet;
- release checklist;
- 512×512 Play icon;
- 1024×500 feature graphic;
- upload-key generation/configuration scripts;
- release gate that builds signed APK + AAB only after analyze/tests.

## Current target API
The Android Gradle file keeps `targetSdk = flutter.targetSdkVersion`.
Flutter 3.44.7 defines this as API 36, matching the Google Play requirement
that starts 31 August 2026.

## Intentionally pending
- real compile/analyze/test results;
- support email;
- public privacy-policy URL;
- real screenshots;
- final version bump to 1.0.0+1;
- Play Console forms.
