import 'package:adapa/core/content/asset_course_repository.dart';
import 'package:adapa/core/models/activity_family.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  test('scored text-input activities never silently accept arbitrary text', () async {
    final repository = AssetCourseRepository();
    final manifest = await repository.loadManifest();

    final problems = <String>[];
    for (final summary in manifest.units) {
      final unit = await repository.loadUnit(summary);
      for (final lesson in unit.lessons) {
        for (final activity in lesson.activities) {
          if (activity.family != ActivityFamily.textInput) continue;
          if (activity.scoreMode == 'none' || activity.scoreMode == 'structural') {
            continue;
          }
          final answers = activity.payload['accepted_answers'] as List? ?? const [];
          if (answers.isEmpty) {
            problems.add('${activity.id} (${activity.type})');
          }
        }
      }
    }

    expect(
      problems,
      isEmpty,
      reason: 'A scored text activity without accepted_answers would make the '
          'generic evaluator accept any non-empty answer: ${problems.join(', ')}',
    );
  });

  test('playback activities point to an existing compatible source activity', () async {
    final repository = AssetCourseRepository();
    final manifest = await repository.loadManifest();
    final activities = <String, dynamic>{};

    for (final summary in manifest.units) {
      final unit = await repository.loadUnit(summary);
      for (final lesson in unit.lessons) {
        for (final activity in lesson.activities) {
          activities[activity.id] = activity;
        }
      }
    }

    const playbackSources = {
      'dialogue',
      'dialogue_roleplay',
      'final_guided_conversation',
    };

    for (final activity in activities.values) {
      if (activity.type != 'tts_dialogue_playback') continue;
      final sourceId = activity.payload['source_activity']?.toString();
      expect(sourceId, isNotNull, reason: '${activity.id} has no source_activity');
      final source = activities[sourceId];
      expect(source, isNotNull, reason: '${activity.id} points to missing $sourceId');
      expect(
        playbackSources,
        contains(source.type),
        reason: '${activity.id} points to ${source.type}, which does not '
            'currently persist playback_lines.',
      );
    }
  });
}
