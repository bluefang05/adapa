import 'package:adapa/core/models/activity_content.dart';
import 'package:adapa/features/activity/activity_renderer_host.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets(
    'all 171 production activities build through their real renderer',
    (tester) async {
      await tester.binding.setSurfaceSize(const Size(412, 915));
      addTearDown(() => tester.binding.setSurfaceSize(null));

      final harness = await RuntimeHarness.create();
      addTearDown(harness.dispose);

      final manifest = await harness.repository.loadManifest();
      final activities = <ActivityContent>[];

      for (final summary in manifest.units) {
        final unit = await harness.repository.loadUnit(summary);
        for (final lesson in unit.lessons) {
          activities.addAll(lesson.activities);
        }
      }

      expect(activities.length, 171);

      for (final activity in activities) {
        await tester.pumpWidget(
          harness.wrap(
            SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: ActivityRendererHost(activity: activity),
            ),
          ),
        );

        // Resolve FutureBuilders used by dialogues, readings and asset catalogs.
        await tester.pump();
        await tester.pump(const Duration(milliseconds: 50));
        await tester.pumpAndSettle(const Duration(milliseconds: 20));

        final exception = tester.takeException();
        expect(
          exception,
          isNull,
          reason:
              'Renderer failed for ${activity.id} '
              '(${activity.type}/${activity.family.name})',
        );
      }
    },
  );
}
