import 'dart:io';

import 'package:flutter_test/flutter_test.dart';

void main() {
  test('Android app contract is release-safe', () {
    final gradle = File('android/app/build.gradle.kts').readAsStringSync();
    final manifest =
        File('android/app/src/main/AndroidManifest.xml').readAsStringSync();

    expect(gradle, contains('applicationId = "com.enmanuelapps.adapa"'));
    expect(gradle, contains('minSdk = 24'));
    expect(gradle, contains('targetSdk = flutter.targetSdkVersion'));
    expect(gradle, contains('id("kotlin-android")'));
    expect(gradle, contains('id("dev.flutter.flutter-gradle-plugin")'));

    expect(
      gradle,
      isNot(contains('signingConfig = signingConfigs.getByName("debug")')),
      reason: 'Release artifacts must never use the debug signing key.',
    );
    expect(gradle, contains('hasReleaseSigning'));
    expect(gradle, contains('signingConfigs.getByName("release")'));

    expect(manifest, contains('android.intent.action.TTS_SERVICE'));
    expect(manifest, contains('android:allowBackup="false"'));
    expect(manifest, contains('android:usesCleartextTraffic="false"'));
    expect(manifest, isNot(contains('android.permission.INTERNET')));
  });
}
