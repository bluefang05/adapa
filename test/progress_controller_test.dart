import 'package:flutter_test/flutter_test.dart';

import 'package:adapa/core/content/course_repository.dart';
import 'package:adapa/core/models/activity_content.dart';
import 'package:adapa/core/models/activity_family.dart';
import 'package:adapa/core/models/course_manifest.dart';
import 'package:adapa/core/models/lesson_content.dart';
import 'package:adapa/core/models/unit_content.dart';
import 'package:adapa/core/persistence/progress_store.dart';
import 'package:adapa/core/progress/progress_controller.dart';
import 'package:adapa/core/progress/progress_snapshot.dart';

void main() {
  group('ProgressController', () {
    test('75% threshold completes a lesson after all required activities are attempted', () async {
      final fixture = _Fixture();
      final controller = ProgressController(repository: fixture.repository, store: fixture.store);
      await controller.initialize();

      await controller.handleSessionWrite('a1', {'complete': true, 'record_attempt': true});
      await controller.handleSessionWrite('a2', {'complete': true, 'record_attempt': true});
      await controller.handleSessionWrite('a3', {'complete': true, 'record_attempt': true});
      await controller.handleSessionWrite('a4', {'complete': false, 'record_attempt': true});

      final lesson = controller.lessonProgress('l1');
      expect(lesson.score, 0.75);
      expect(lesson.completed, isTrue);
      expect(controller.isUnitComplete('u1'), isTrue);
      expect(controller.isUnitUnlocked('u2'), isTrue);
    });

    test('best result survives a failed retry and attempts accumulate', () async {
      final fixture = _Fixture();
      final controller = ProgressController(repository: fixture.repository, store: fixture.store);
      await controller.initialize();

      await controller.handleSessionWrite('a1', {'complete': false, 'record_attempt': true});
      await controller.handleSessionWrite('a1', {'complete': true, 'record_attempt': true});
      await controller.handleSessionWrite('a1', {'complete': false, 'record_attempt': true});

      final progress = controller.activityProgress('a1');
      expect(progress.attempts, 3);
      expect(progress.bestScore, 1.0);
      expect(progress.completed, isTrue);
    });

    test('session data is persisted and can hydrate a new controller', () async {
      final fixture = _Fixture();
      final first = ProgressController(repository: fixture.repository, store: fixture.store);
      await first.initialize();
      await first.handleSessionWrite('open1', {
        'text': '저는 학생이에요.',
        'complete': true,
        'record_attempt': true,
      });
      await first.flush();

      final second = ProgressController(repository: fixture.repository, store: fixture.store);
      await second.initialize();
      expect(second.persistedSessionData['open1']?['text'], '저는 학생이에요.');
      expect(second.activityProgress('open1').completed, isTrue);
    });


    test('save failure is exposed and retry clears the persistence error', () async {
      final fixture = _Fixture();
      final store = _FailOnceProgressStore();
      final controller = ProgressController(repository: fixture.repository, store: store);
      await controller.initialize();

      await controller.handleSessionWrite('a1', {
        'complete': true,
        'record_attempt': true,
      });
      await controller.flush();

      expect(controller.hasPersistenceError, isTrue);
      expect(controller.persistenceError, isNotNull);

      await controller.retrySave();

      expect(controller.hasPersistenceError, isFalse);
      expect(store.value, isNotNull);
    });
  });
}

class _Fixture {
  _Fixture() {
    final a1 = _activity('a1');
    final a2 = _activity('a2');
    final a3 = _activity('a3');
    final a4 = _activity('a4');
    final open1 = _activity('open1', scoreMode: 'none');

    final l1 = LessonContent(
      id: 'l1',
      title: 'Lección 1',
      theory: const [],
      activities: [a1, a2, a3, a4],
      completion: const {
        'required_activity_ids': ['a1', 'a2', 'a3', 'a4'],
        'minimum_score': 0.75,
      },
      sourcePages: const [],
      dialogueRefs: const [],
    );
    final l2 = LessonContent(
      id: 'l2',
      title: 'Lección abierta',
      theory: const [],
      activities: [open1],
      completion: const {
        'required_activity_ids': ['open1'],
        'rule': 'complete_required_open_activity',
      },
      sourcePages: const [],
      dialogueRefs: const [],
    );

    final u1 = UnitContent(
      id: 'u1',
      title: 'U1',
      prerequisites: const [],
      resourceRefs: const {},
      lessons: [l1],
      completion: const {'required_lessons': ['l1']},
      source: const {},
    );
    final u2 = UnitContent(
      id: 'u2',
      title: 'U2',
      prerequisites: const ['u1'],
      resourceRefs: const {},
      lessons: [l2],
      completion: const {'required_lessons': ['l2']},
      source: const {},
    );

    final manifest = CourseManifest(
      id: 'course',
      title: 'Curso',
      subtitle: 'Prueba',
      level: 'A0',
      ttsLocale: 'ko-KR',
      offlineFirst: true,
      contentVersion: '1',
      units: const [
        UnitSummary(id: 'u1', title: 'U1', lessonCount: 1, activityCount: 4, asset: 'u1', prerequisites: []),
        UnitSummary(id: 'u2', title: 'U2', lessonCount: 1, activityCount: 1, asset: 'u2', prerequisites: ['u1']),
      ],
      resources: const {},
      summary: const {},
    );
    repository = _FakeRepository(manifest, {'u1': u1, 'u2': u2});
  }

  late final _FakeRepository repository;
  final _MemoryProgressStore store = _MemoryProgressStore();

  ActivityContent _activity(String id, {String scoreMode = 'auto'}) => ActivityContent(
        id: id,
        type: scoreMode == 'none' ? 'free_writing' : 'multiple_choice',
        family: scoreMode == 'none' ? ActivityFamily.textInput : ActivityFamily.choice,
        scoreMode: scoreMode,
        normalization: const {},
        hints: const [],
        feedback: const {},
        capabilities: const {},
        payload: const {},
      );
}

class _FakeRepository implements CourseRepository {
  _FakeRepository(this.manifest, this.units);
  final CourseManifest manifest;
  final Map<String, UnitContent> units;

  @override
  Future<CourseManifest> loadManifest() async => manifest;

  @override
  Future<UnitContent> loadUnit(UnitSummary unit) async => units[unit.id]!;

  @override
  Future<Map<String, dynamic>> loadResource(String assetPath) async => const {};
}

class _MemoryProgressStore implements ProgressStore {
  ProgressSnapshot? value;

  @override
  Future<void> clear(String courseId) async => value = null;

  @override
  Future<ProgressSnapshot?> load(String courseId) async => value;

  @override
  Future<void> save(ProgressSnapshot snapshot) async => value = ProgressSnapshot.fromJson(snapshot.toJson());
}


class _FailOnceProgressStore implements ProgressStore {
  bool _shouldFail = true;
  ProgressSnapshot? value;

  @override
  Future<void> clear(String courseId) async => value = null;

  @override
  Future<ProgressSnapshot?> load(String courseId) async => value;

  @override
  Future<void> save(ProgressSnapshot snapshot) async {
    if (_shouldFail) {
      _shouldFail = false;
      throw StateError('simulated storage failure');
    }
    value = ProgressSnapshot.fromJson(snapshot.toJson());
  }
}
