import 'package:adapa/features/settings/privacy_screen.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('privacy screen states local/offline/TTS behavior', (tester) async {
    final harness = await RuntimeHarness.create();
    addTearDown(harness.dispose);

    await tester.pumpWidget(harness.wrap(const PrivacyScreen()));
    await tester.pumpAndSettle();

    expect(find.text('Privacidad y datos'), findsOneWidget);
    expect(find.text('Sin nube propia'), findsOneWidget);
    expect(find.text('Texto a voz del dispositivo'), findsOneWidget);
    expect(find.text('Backup de la aplicación desactivado'), findsOneWidget);
    expect(find.text('Borrar mi progreso local'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });
}
