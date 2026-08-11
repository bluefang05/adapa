#!/usr/bin/env python3
from pathlib import Path
import re
import sys

ROOT = Path(__file__).resolve().parents[1]
errors = []
warnings = []

def require(condition, message):
    if not condition:
        errors.append(message)

gradle = (ROOT / "android/app/build.gradle.kts").read_text(encoding="utf-8")
manifest = (ROOT / "android/app/src/main/AndroidManifest.xml").read_text(encoding="utf-8")
gitignore = (ROOT / "android/.gitignore").read_text(encoding="utf-8")
privacy = (ROOT / "release/privacy_policy_es.md").read_text(encoding="utf-8")
listing = (ROOT / "release/play_store/listing_es.md").read_text(encoding="utf-8")

require('applicationId = "com.enmanuelapps.adapa"' in gradle, "Unexpected applicationId")
require('minSdk = 24' in gradle, "minSdk is not 24")
require('targetSdk = flutter.targetSdkVersion' in gradle, "targetSdk no longer follows Flutter")
require('signingConfigs.getByName("debug")' not in gradle, "Release still uses debug signing")
require('signingConfigs.getByName("release")' in gradle, "Release signing config missing")
require('android:allowBackup="false"' in manifest, "Cloud backup not disabled")
require('android:usesCleartextTraffic="false"' in manifest, "Cleartext traffic not disabled")
require('android.permission.INTERNET' not in manifest, "Production manifest requests INTERNET")
require('key.properties' in gitignore, "key.properties is not ignored")
require('*.jks' in gitignore, "JKS files are not ignored")
require((ROOT / "release/privacy_policy_es.html").exists(), "Privacy HTML missing")
require((ROOT / "tools/release_gate.ps1").exists(), "Release gate script missing")

if '[PENDIENTE_CORREO_DE_SOPORTE]' in privacy:
    warnings.append("Support email still pending in privacy policy")
if '[PENDIENTE_URL_PUBLICA_DE_PRIVACIDAD]' in listing:
    warnings.append("Public privacy URL still pending in Play listing")

print(f"ADAPA release static audit: {len(errors)} error(s), {len(warnings)} warning(s)")
for e in errors:
    print("ERROR:", e)
for w in warnings:
    print("WARN:", w)

sys.exit(1 if errors else 0)
