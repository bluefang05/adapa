import 'package:adapa/core/models/activity_content.dart';
import 'package:adapa/core/models/activity_family.dart';
import 'package:adapa/features/activity/renderers/ordering_activity_renderer.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('ordering supports repeated visible tokens without duplicate keys', (
    tester,
  ) async {
    final harness = await RuntimeHarness.create();
    addTearDown(harness.dispose);

    final activity = ActivityContent(
      id: 'ordering_duplicate_token_test',
      type: 'word_order',
      family: ActivityFamily.ordering,
      scoreMode: 'auto',
      normalization: const {},
      hints: const [],
      feedback: const {},
      capabilities: const {},
      payload: const {
        'tokens': ['가', '나', '가'],
        'correct_order': ['가', '나', '가'],
      },
    );

    await tester.pumpWidget(
      harness.wrap(OrderingActivityRenderer(activity: activity)),
    );
    await tester.pumpAndSettle();

    expect(find.text('가'), findsNWidgets(2));
    expect(tester.takeException(), isNull);
  });
}
