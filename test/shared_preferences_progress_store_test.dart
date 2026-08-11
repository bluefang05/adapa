import 'package:adapa/core/persistence/key_value_store.dart';
import 'package:adapa/core/persistence/shared_preferences_progress_store.dart';
import 'package:adapa/core/progress/activity_progress.dart';
import 'package:adapa/core/progress/progress_snapshot.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('progress JSON survives an async key/value round trip', () async {
    final preferences = MemoryKeyValueStore();
    final store = SharedPreferencesProgressStore(preferences: preferences);

    final snapshot = ProgressSnapshot(
      courseId: 'ko_a0_a1',
      contentVersion: '1.0',
      activities: const {
        'u01l01_a01': ActivityProgress(
          activityId: 'u01l01_a01',
          attempts: 2,
          completed: true,
          bestScore: 1,
        ),
      },
      sessionData: const {
        'u07l04_a01': {'text': '저는 학생이에요.', 'complete': true},
      },
    );

    await store.save(snapshot);
    final loaded = await store.load('ko_a0_a1');

    expect(loaded, isNotNull);
    expect(loaded!.activities['u01l01_a01']!.attempts, 2);
    expect(loaded.sessionData['u07l04_a01']?['text'], '저는 학생이에요.');
  });

  test('corrupt persisted progress is ignored safely', () async {
    final preferences = MemoryKeyValueStore({
      'adapa.progress.v1.ko_a0_a1': '{not valid json',
    });
    final store = SharedPreferencesProgressStore(preferences: preferences);

    expect(await store.load('ko_a0_a1'), isNull);
  });
}
