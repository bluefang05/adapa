import 'package:adapa/core/models/activity_content.dart';
import 'package:adapa/core/models/activity_family.dart';
import 'package:adapa/features/practice/practice_session_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets(
    'choice with multiple correct answers requires the complete set and retries',
    (tester) async {
      tester.view.physicalSize = const Size(1080, 2400);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      final harness = await RuntimeHarness.create(loadCourse: false);
      addTearDown(harness.dispose);

      final activity = ActivityContent(
        id: 'multi_choice_real_test',
        type: 'multiple_choice',
        family: ActivityFamily.choice,
        scoreMode: 'auto',
        prompt: 'Selecciona A y B',
        normalization: const {},
        hints: const [],
        feedback: const {
          'correct': 'Conjunto correcto.',
          'wrong': {'default': 'Ese conjunto no es correcto.'},
        },
        capabilities: const {},
        payload: const {
          'options': ['A', 'B', 'C', 'D'],
          'correct': ['A', 'B'],
          'selection_mode': 'multiple',
        },
      );

      await tester.pumpWidget(
        harness.wrap(
          PracticeSessionScreen(
            activities: [activity],
            title: 'Práctica',
          ),
        ),
      );
      await tester.pumpAndSettle();

      expect(
        find.text('Selecciona 2 respuestas. Se comprueban automáticamente.'),
        findsOneWidget,
      );

      await tester.tap(find.text('A'));
      await tester.pump();
      expect(find.text('Práctica completada'), findsNothing);

      await tester.tap(find.text('C'));
      await tester.pump(const Duration(milliseconds: 80));
      expect(find.text('Ese conjunto no es correcto.'), findsOneWidget);
      expect(find.text('Práctica completada'), findsNothing);

      await tester.pump(const Duration(milliseconds: 420));
      await tester.tap(find.text('A'));
      await tester.pump();
      await tester.tap(find.text('B'));
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 500));

      expect(find.text('Conjunto correcto.'), findsOneWidget);
      expect(find.text('Práctica completada'), findsOneWidget);
      expect(harness.session.read(activity.id), isNull);
    },
  );

  testWidgets(
    'multiple correct values remain alternative single-tap answers unless explicitly multi-select',
    (tester) async {
      final harness = await RuntimeHarness.create(loadCourse: false);
      addTearDown(harness.dispose);

      final activity = ActivityContent(
        id: 'alternative_correct_values_test',
        type: 'multiple_choice',
        family: ActivityFamily.choice,
        scoreMode: 'auto',
        prompt: 'Elige una respuesta válida',
        normalization: const {},
        hints: const [],
        feedback: const {'correct': 'Bien.'},
        capabilities: const {},
        payload: const {
          'options': ['A', 'B', 'C'],
          'correct': ['B', 'C'],
        },
      );

      await tester.pumpWidget(
        harness.wrap(
          PracticeSessionScreen(
            activities: [activity],
            title: 'Práctica',
          ),
        ),
      );
      await tester.pumpAndSettle();

      expect(
        find.text('Toca una respuesta. Si no es, prueba otra vez.'),
        findsOneWidget,
      );
      await tester.tap(find.text('B'));
      await tester.pump();
      await tester.pump(const Duration(milliseconds: 500));

      expect(find.text('Bien.'), findsOneWidget);
      expect(find.text('Práctica completada'), findsOneWidget);
      expect(harness.session.read(activity.id), isNull);
    },
  );

}
