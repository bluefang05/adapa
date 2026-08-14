import 'package:adapa/core/models/activity_content.dart';
import 'package:adapa/core/models/activity_family.dart';
import 'package:adapa/features/activity/renderers/matching_activity_renderer.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('matching accepts duplicate right labels as interchangeable visible answers', (
    tester,
  ) async {
    final harness = await RuntimeHarness.create();
    addTearDown(harness.dispose);

    final activity = ActivityContent(
      id: 'matching_duplicate_label_test',
      type: 'matching',
      family: ActivityFamily.matching,
      scoreMode: 'auto',
      normalization: const {},
      hints: const [],
      feedback: const {},
      capabilities: const {},
      payload: const {
        'pairs': [
          {'left': '학생', 'right': 'persona'},
          {'left': '선생님', 'right': 'persona'},
        ],
      },
    );

    await tester.pumpWidget(
      harness.wrap(MatchingActivityRenderer(activity: activity)),
    );
    await tester.pumpAndSettle();

    final chips = find.widgetWithText(ActionChip, 'persona');
    expect(chips, findsNWidgets(2));

    await tester.tap(find.text('학생'));
    await tester.tap(chips.first);
    await tester.pump();
    expect(find.text('1/2 parejas resueltas'), findsOneWidget);

    await tester.tap(find.text('선생님'));
    final remaining = find.byWidgetPredicate(
      (widget) =>
          widget is ActionChip &&
          widget.label is Text &&
          (widget.label as Text).data == 'persona' &&
          widget.onPressed != null,
    );
    expect(remaining, findsOneWidget);
    await tester.tap(remaining);
    await tester.pump();

    expect(find.text('2/2 parejas resueltas'), findsOneWidget);
    expect(harness.session.read(activity.id)?['complete'], isTrue);
    expect(tester.takeException(), isNull);
  });
}
