import 'package:adapa/features/course/course_home_screen.dart';
import 'package:adapa/features/settings/settings_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  Future<void> setPhoneSize(WidgetTester tester, Size size) async {
    await tester.binding.setSurfaceSize(size);
    tester.view.devicePixelRatio = 1.0;
  }

  testWidgets(
      '320x568: course -> unit -> lesson -> activity has no build exception',
      (tester) async {
    await setPhoneSize(tester, const Size(320, 568));
    addTearDown(() async {
      tester.view.resetDevicePixelRatio();
      await tester.binding.setSurfaceSize(null);
    });

    final harness = await RuntimeHarness.create();
    addTearDown(harness.dispose);

    await tester.pumpWidget(
      harness.wrap(CourseHomeScreen(repository: harness.repository)),
    );
    await tester.pumpAndSettle();

    expect(find.text('ADAPA'), findsOneWidget);
    expect(tester.takeException(), isNull);

    final unitFinder = find.text('Descubre el Hangul');
    await tester.scrollUntilVisible(unitFinder, 220);
    await tester.tap(unitFinder);
    await tester.pumpAndSettle();

    expect(find.text('Vocales básicas'), findsOneWidget);
    expect(tester.takeException(), isNull);

    await tester.tap(find.text('Vocales básicas'));
    await tester.pumpAndSettle();

    expect(find.text('Aprende'), findsOneWidget);
    expect(find.text('Practica'), findsOneWidget);
    expect(tester.takeException(), isNull);

    final activityFinder =
        find.text('¿Cuál de estas vocales corresponde a «a»?');
    await tester.scrollUntilVisible(activityFinder, 260);
    await tester.tap(activityFinder);
    await tester.pumpAndSettle();

    expect(find.text('Actividad 1 de 5'), findsOneWidget);
    expect(find.text('Siguiente'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });

  testWidgets('412x915: settings screen fits and exposes Korean TTS controls',
      (tester) async {
    await setPhoneSize(tester, const Size(412, 915));
    addTearDown(() async {
      tester.view.resetDevicePixelRatio();
      await tester.binding.setSurfaceSize(null);
    });

    final harness = await RuntimeHarness.create();
    addTearDown(harness.dispose);

    await tester.pumpWidget(harness.wrap(const SettingsScreen()));
    await tester.pumpAndSettle();

    expect(find.text('Voz coreana'), findsOneWidget);
    expect(find.text('Comprobar ko-KR'), findsOneWidget);
    expect(find.text('Velocidad'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });
}
