import 'package:adapa/core/content/asset_course_repository.dart';
import 'package:adapa/core/models/activity_family.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  test('production course loads end to end', () async {
    final repository = AssetCourseRepository();
    final manifest = await repository.loadManifest();

    expect(manifest.id, 'ko_a0_a1');
    expect(manifest.units.length, 8);

    var lessons = 0;
    var activities = 0;
    final families = <ActivityFamily>{};
    final ids = <String>{};

    for (final summary in manifest.units) {
      final unit = await repository.loadUnit(summary);
      expect(unit.id, summary.id);
      expect(unit.lessons.length, summary.lessonCount);

      lessons += unit.lessons.length;
      for (final lesson in unit.lessons) {
        for (final activity in lesson.activities) {
          expect(ids.add(activity.id), isTrue,
              reason: 'Duplicate activity id: ${activity.id}');
          activities += 1;
          families.add(activity.family);
        }
      }
    }

    expect(lessons, 36);
    expect(activities, 171);
    expect(families.length, 9);
  });
}
