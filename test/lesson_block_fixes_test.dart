import 'package:adapa/core/models/activity_content.dart';
import 'package:adapa/core/models/activity_family.dart';
import 'package:adapa/core/models/content_block.dart';
import 'package:adapa/core/models/romanization_policy.dart';
import 'package:adapa/features/activity/renderers/ordering_activity_renderer.dart';
import 'package:adapa/features/lesson/widgets/lesson_content_block.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('ComparisonPairs renders polite and informal text with badges', (
    tester,
  ) async {
    final harness = await RuntimeHarness.create();
    addTearDown(harness.dispose);

    final block = ContentBlock(
      id: 'test_comparison',
      type: 'comparison',
      title: 'Educado e informal',
      text: null,
      payload: const {
        'pairs': [
          {
            'polite': '감사합니다',
            'informal': '고마워',
            'meaning_es': 'Gracias',
          },
          {
            'polite': '죄송해요',
            'informal': '미안해',
            'meaning_es': 'Disculpa / lo siento',
          },
        ],
      },
    );

    await tester.pumpWidget(
      harness.wrap(
        Scaffold(
          body: LessonContentBlockCard(
            block: block,
            romanizationPolicy: const RomanizationPolicy(
              mode: RomanizationMode.visible,
              canReveal: true,
            ),
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('감사합니다'), findsOneWidget);
    expect(find.text('고마워'), findsOneWidget);
    expect(find.text('죄송해요'), findsOneWidget);
    expect(find.text('미안해'), findsOneWidget);
    expect(find.text('Educado'), findsNWidgets(2));
    expect(find.text('Informal'), findsNWidgets(2));
    expect(find.text('Gracias'), findsOneWidget);
    expect(find.text('Disculpa / lo siento'), findsOneWidget);
  });

  testWidgets('OrderingActivityRenderer moves items via arrow buttons', (
    tester,
  ) async {
    final harness = await RuntimeHarness.create();
    addTearDown(harness.dispose);

    final activity = ActivityContent(
      id: 'test_ordering_arrows',
      type: 'reorder',
      family: ActivityFamily.ordering,
      scoreMode: 'auto',
      normalization: const {},
      hints: const [],
      feedback: const {
        'correct': 'Bien hecho.',
        'wrong': {'default': 'Incorrecto.'},
      },
      capabilities: const {},
      payload: const {
        'tokens': ['둘', '셋', '다섯', '넷', '하나'],
        'correct_order': ['하나', '둘', '셋', '넷', '다섯'],
      },
    );

    await tester.pumpWidget(
      harness.wrap(
        Scaffold(
          body: SingleChildScrollView(
            child: OrderingActivityRenderer(activity: activity),
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.byIcon(Icons.keyboard_arrow_up), findsWidgets);
    expect(find.byIcon(Icons.keyboard_arrow_down), findsWidgets);
    expect(find.byIcon(Icons.drag_handle), findsWidgets);

    // Tap arrow down on first element
    final downButtons = find.byIcon(Icons.keyboard_arrow_down);
    await tester.tap(downButtons.first);
    await tester.pumpAndSettle();

    expect(tester.takeException(), isNull);
  });
}
