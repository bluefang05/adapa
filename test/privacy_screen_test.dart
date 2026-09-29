import 'package:adapa/features/settings/privacy_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('privacy screen states local/offline/TTS behavior', (
    tester,
  ) async {
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

  testWidgets('failed reset keeps progress and can be retried', (tester) async {
    await tester.binding.setSurfaceSize(const Size(412, 1800));
    addTearDown(() => tester.binding.setSurfaceSize(null));
    final store = _FailOnceClearStore();
    final harness = await RuntimeHarness.create(store: store);
    addTearDown(harness.dispose);
    harness.session.write('u01l01_a01', {
      'complete': true,
      'record_attempt': true,
    });
    await harness.progress.flush();
    await tester.pumpWidget(harness.wrap(const PrivacyScreen()));

    await tester.tap(find.text('Reiniciar mi progreso'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Reiniciar'));
    await tester.pumpAndSettle();

    expect(
      find.text('No se pudo reiniciar el progreso. Inténtalo de nuevo.'),
      findsOneWidget,
    );
    expect(find.text('Progreso reiniciado.'), findsNothing);
    expect(harness.progress.completedActivityCount, 1);
    expect(harness.session.read('u01l01_a01'), isNotNull);
    expect(store.value!.activities, isNotEmpty);
    expect(tester.takeException(), isNull);

    await tester.tap(find.text('Reiniciar mi progreso'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Reiniciar'));
    await tester.pumpAndSettle();
    expect(harness.progress.completedActivityCount, 0);
    expect(harness.session.read('u01l01_a01'), isNull);
    expect(store.value, isNull);
    expect(tester.takeException(), isNull);
  });
}

class _FailOnceClearStore extends MemoryProgressStore {
  bool _fail = true;

  @override
  Future<void> clear(String courseId) async {
    if (_fail) {
      _fail = false;
      throw StateError('simulated reset failure');
    }
    await super.clear(courseId);
  }
}
