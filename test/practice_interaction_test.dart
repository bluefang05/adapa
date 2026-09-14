import 'package:adapa/core/models/activity_content.dart';
import 'package:adapa/core/models/activity_family.dart';
import 'package:adapa/features/activity/renderers/matching_activity_renderer.dart';
import 'package:adapa/features/practice/practice_session_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets(
    'unit practice retries a wrong choice and does not write to course session',
    (tester) async {
      final harness = await RuntimeHarness.create();
      addTearDown(harness.dispose);

      final activity = ActivityContent(
        id: 'practice_choice_test',
        type: 'multiple_choice',
        family: ActivityFamily.choice,
        scoreMode: 'auto',
        prompt: 'Elige B',
        normalization: const {},
        hints: const [],
        feedback: const {
          'correct': 'Bien.',
          'wrong': {'default': 'Prueba otra vez.'},
        },
        capabilities: const {},
        payload: const {
          'options': ['A', 'B'],
          'correct': ['B'],
        },
      );

      await tester.pumpWidget(
        harness.wrap(
          PracticeSessionScreen(activities: [activity], title: 'Práctica'),
        ),
      );
      await tester.pumpAndSettle();

      await tester.tap(find.text('A'));
      await tester.pump(const Duration(milliseconds: 80));
      expect(find.text('Prueba otra vez.'), findsOneWidget);
      expect(harness.session.read(activity.id), isNull);

      await tester.pump(const Duration(milliseconds: 400));
      await tester.tap(find.text('B'));
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 50));

      expect(find.text('Práctica completada'), findsOneWidget);
      expect(find.text('Practicar otra vez'), findsOneWidget);
      expect(harness.session.read(activity.id), isNull);

      await tester.tap(find.text('Practicar otra vez'));
      await tester.pump();
      expect(find.text('Práctica completada'), findsNothing);
    },
  );

  testWidgets('matching rejects a wrong pair immediately and keeps retrying', (
    tester,
  ) async {
    final harness = await RuntimeHarness.create();
    addTearDown(harness.dispose);

    final activity = ActivityContent(
      id: 'matching_retry_test',
      type: 'matching',
      family: ActivityFamily.matching,
      scoreMode: 'auto',
      normalization: const {},
      hints: const [],
      feedback: const {
        'correct': 'Parejas completas.',
        'wrong': {'default': 'No coincide.'},
      },
      capabilities: const {},
      payload: const {
        'pairs': [
          {'left': 'A', 'right': '1'},
          {'left': 'B', 'right': '2'},
        ],
      },
    );

    await tester.pumpWidget(
      harness.wrap(MatchingActivityRenderer(activity: activity)),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('A'));
    await tester.pump();
    await tester.tap(find.widgetWithText(ActionChip, '2'));
    await tester.pump(const Duration(milliseconds: 80));
    expect(find.text('No coincide.'), findsOneWidget);
    expect(find.text('0/2 parejas resueltas'), findsOneWidget);

    await tester.pump(const Duration(milliseconds: 400));
    await tester.tap(find.text('A'));
    await tester.pump();
    await tester.tap(find.widgetWithText(ActionChip, '1'));
    await tester.pump();
    expect(find.text('1/2 parejas resueltas'), findsOneWidget);

    await tester.tap(find.text('B'));
    await tester.pump();
    await tester.tap(find.widgetWithText(ActionChip, '2'));
    await tester.pump();
    expect(find.text('2/2 parejas resueltas'), findsOneWidget);
    expect(harness.session.read(activity.id)?['complete'], isTrue);
  });

  testWidgets('matching shows answer options before cards', (tester) async {
    final harness = await RuntimeHarness.create();
    addTearDown(harness.dispose);

    final activity = ActivityContent(
      id: 'matching_options_visible_test',
      type: 'matching',
      family: ActivityFamily.matching,
      scoreMode: 'auto',
      normalization: const {},
      hints: const [],
      feedback: const {},
      capabilities: const {},
      payload: const {
        'pairs': [
          {'left': 'ㄱ', 'right': 'g/k'},
          {'left': 'ㄴ', 'right': 'n'},
          {'left': 'ㅁ', 'right': 'm'},
          {'left': 'ㅅ', 'right': 's'},
        ],
      },
    );

    await tester.pumpWidget(
      harness.wrap(MatchingActivityRenderer(activity: activity)),
    );
    await tester.pumpAndSettle();

    expect(find.text('Opciones'), findsOneWidget);
    expect(find.text('Tarjetas'), findsOneWidget);
    expect(
      tester.getTopLeft(find.text('Opciones')).dy,
      lessThan(tester.getTopLeft(find.text('Tarjetas')).dy),
    );
    expect(find.widgetWithText(ActionChip, 'g/k'), findsOneWidget);
  });
}
