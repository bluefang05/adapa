import 'package:adapa/features/settings/privacy_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('privacy screen states local/offline/TTS behavior', (tester) async {
    await tester.binding.setSurfaceSize(const Size(412, 1800));
    addTearDown(() => tester.binding.setSurfaceSize(null));

    final harness = await RuntimeHarness.create(loadCourse: false);
    addTearDown(harness.dispose);

    await tester.pumpWidget(harness.wrap(const PrivacyScreen()));
    await tester.pump();

    expect(find.text('Privacidad y datos'), findsOneWidget);
    expect(find.text('Sin nube propia'), findsOneWidget);
    expect(find.text('Texto a voz del dispositivo'), findsOneWidget);
    expect(find.text('Backup de la aplicación desactivado'), findsOneWidget);
    expect(find.text('Publicidad (Google AdMob)'), findsOneWidget);
    expect(find.text('enmandom@gmail.com'), findsOneWidget);
    expect(find.text('Reiniciar mi progreso'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });
}
