import 'dart:async';

import 'package:flutter/foundation.dart';

import '../content/course_repository.dart';
import '../models/activity_content.dart';
import '../models/course_manifest.dart';
import '../models/lesson_content.dart';
import '../models/unit_content.dart';
import '../persistence/progress_store.dart';
import 'activity_progress.dart';
import 'progress_snapshot.dart';
import 'resume_pointer.dart';

class LessonProgressView {
  const LessonProgressView({
    required this.completed,
    required this.attemptedRequired,
    required this.requiredCount,
    required this.score,
    required this.minimumScore,
    required this.completedActivities,
    required this.activityCount,
  });

  final bool completed;
  final int attemptedRequired;
  final int requiredCount;
  final double score;
  final double? minimumScore;
  final int completedActivities;
  final int activityCount;

  double get activityFraction =>
      activityCount == 0 ? 0 : completedActivities / activityCount;
}

class UnitProgressView {
  const UnitProgressView({
    required this.completed,
    required this.unlocked,
    required this.completedLessons,
    required this.lessonCount,
    required this.completedActivities,
    required this.activityCount,
  });

  final bool completed;
  final bool unlocked;
  final int completedLessons;
  final int lessonCount;
  final int completedActivities;
  final int activityCount;

  double get fraction =>
      activityCount == 0 ? 0 : completedActivities / activityCount;
}

typedef NowProvider = DateTime Function();

class ProgressController extends ChangeNotifier {
  ProgressController({
    required this.repository,
    required this.store,
    NowProvider? now,
  }) : _now = now ?? DateTime.now;

  final CourseRepository repository;
  final ProgressStore store;
  final NowProvider _now;

  CourseManifest? _manifest;
  final Map<String, UnitContent> _units = {};
  final Map<String, LessonContent> _lessons = {};
  final Map<String, ActivityContent> _activities = {};
  final Map<String, String> _activityToLesson = {};
  final Map<String, String> _lessonToUnit = {};
  final List<String> _activityOrder = [];

  ProgressSnapshot? _snapshot;
  Future<void> _saveQueue = Future<void>.value();
  String? _persistenceError;

  bool get initialized => _manifest != null && _snapshot != null;
  CourseManifest get manifest => _manifest!;
  ResumePointer? get resume => _snapshot?.resume;
  int get currentStreak => _snapshot?.currentStreak ?? 0;
  int get longestStreak => _snapshot?.longestStreak ?? 0;
  DateTime? get lastStudyDate => _snapshot?.lastStudyDate;
  String? get persistenceError => _persistenceError;
  bool get hasPersistenceError => _persistenceError != null;
  Map<String, Map<String, dynamic>> get persistedSessionData =>
      Map<String, Map<String, dynamic>>.from(
        _snapshot?.sessionData ?? const {},
      );

  Future<void> initialize() async {
    final manifest = await repository.loadManifest();
    _manifest = manifest;
    for (final summary in manifest.units) {
      final unit = await repository.loadUnit(summary);
      _units[unit.id] = unit;
      for (final lesson in unit.lessons) {
        _lessons[lesson.id] = lesson;
        _lessonToUnit[lesson.id] = unit.id;
        for (final activity in lesson.activities) {
          _activities[activity.id] = activity;
          _activityToLesson[activity.id] = lesson.id;
          _activityOrder.add(activity.id);
        }
      }
    }

    final loaded = await store.load(manifest.id);
    _snapshot = _sanitize(
      loaded ??
          ProgressSnapshot.empty(
            courseId: manifest.id,
            contentVersion: manifest.contentVersion,
          ),
      manifest,
    );
    notifyListeners();
  }

  ProgressSnapshot _sanitize(ProgressSnapshot input, CourseManifest manifest) {
    final activities = <String, ActivityProgress>{};
    final sessions = <String, Map<String, dynamic>>{};
    for (final entry in input.activities.entries) {
      if (_activities.containsKey(entry.key)) {
        activities[entry.key] = entry.value;
      }
    }
    for (final entry in input.sessionData.entries) {
      if (_activities.containsKey(entry.key)) sessions[entry.key] = entry.value;
    }
    final oldResume = input.resume;
    final resume =
        oldResume != null && _activities.containsKey(oldResume.activityId)
        ? oldResume
        : null;
    return ProgressSnapshot(
      courseId: manifest.id,
      contentVersion: manifest.contentVersion,
      activities: activities,
      sessionData: sessions,
      resume: resume,
      updatedAt: input.updatedAt,
      currentStreak: input.currentStreak,
      longestStreak: input.longestStreak,
      lastStudyDate: input.lastStudyDate,
    );
  }

  ActivityProgress activityProgress(String activityId) =>
      _snapshot?.activities[activityId] ??
      ActivityProgress(activityId: activityId);

  bool isActivityCompleted(String activityId) =>
      activityProgress(activityId).completed;

  LessonProgressView lessonProgress(String lessonId) {
    final lesson = _lessons[lessonId];
    if (lesson == null) {
      return const LessonProgressView(
        completed: false,
        attemptedRequired: 0,
        requiredCount: 0,
        score: 0,
        minimumScore: null,
        completedActivities: 0,
        activityCount: 0,
      );
    }
    final required =
        (lesson.completion['required_activity_ids'] as List? ?? const [])
            .map((e) => e.toString())
            .toList(growable: false);
    final minScore = (lesson.completion['minimum_score'] as num?)?.toDouble();
    final specialRule = lesson.completion['rule']?.toString();

    final requiredIds = required.isEmpty
        ? lesson.activities.map((a) => a.id).toList(growable: false)
        : required;
    var attemptedRequired = 0;
    var unscoredRequiredComplete = true;
    final scored = <double>[];

    for (final id in requiredIds) {
      final activity = _activities[id];
      final progress = activityProgress(id);
      if (progress.attempted) attemptedRequired += 1;
      if (activity == null) continue;
      if (activity.scoreMode == 'none') {
        if (!progress.completed) unscoredRequiredComplete = false;
      } else {
        scored.add(progress.bestScore ?? 0.0);
      }
    }

    final score = scored.isEmpty
        ? (unscoredRequiredComplete ? 1.0 : 0.0)
        : scored.reduce((a, b) => a + b) / scored.length;
    final allRequiredTouched = requiredIds.every((id) {
      final activity = _activities[id];
      final p = activityProgress(id);
      return activity?.scoreMode == 'none' ? p.completed : p.attempted;
    });

    final usesRequiredCompletionOnly =
        specialRule == 'complete_required_open_activity' ||
        specialRule == 'complete_final_challenge' ||
        specialRule == 'complete_required_review';
    final completed = usesRequiredCompletionOnly
        ? requiredIds.every(isActivityCompleted)
        : allRequiredTouched &&
              unscoredRequiredComplete &&
              score >= (minScore ?? 1.0);

    return LessonProgressView(
      completed: completed,
      attemptedRequired: attemptedRequired,
      requiredCount: requiredIds.length,
      score: score,
      minimumScore: minScore,
      completedActivities: lesson.activities
          .where((a) => isActivityCompleted(a.id))
          .length,
      activityCount: lesson.activities.length,
    );
  }

  bool isLessonComplete(String lessonId) => lessonProgress(lessonId).completed;

  UnitProgressView unitProgress(String unitId) {
    final unit = _units[unitId];
    if (unit == null) {
      return const UnitProgressView(
        completed: false,
        unlocked: false,
        completedLessons: 0,
        lessonCount: 0,
        completedActivities: 0,
        activityCount: 0,
      );
    }
    final requiredLessons =
        (unit.completion['required_lessons'] as List? ?? const [])
            .map((e) => e.toString())
            .toList(growable: false);
    final lessonIds = requiredLessons.isEmpty
        ? unit.lessons.map((l) => l.id).toList(growable: false)
        : requiredLessons;
    final completeLessons = lessonIds.where(isLessonComplete).length;
    final activities = unit.lessons
        .expand((l) => l.activities)
        .toList(growable: false);
    return UnitProgressView(
      completed: lessonIds.isNotEmpty && completeLessons == lessonIds.length,
      unlocked: isUnitUnlocked(unitId),
      completedLessons: completeLessons,
      lessonCount: lessonIds.length,
      completedActivities: activities
          .where((a) => isActivityCompleted(a.id))
          .length,
      activityCount: activities.length,
    );
  }

  bool isUnitComplete(String unitId) => unitProgress(unitId).completed;

  bool isUnitUnlocked(String unitId) {
    final unit = _units[unitId];
    if (unit == null) return false;
    if (unit.prerequisites.isEmpty) return true;
    return unit.prerequisites.every(isUnitComplete);
  }

  bool get courseComplete {
    final manifest = _manifest;
    if (manifest == null || manifest.units.isEmpty) return false;
    return manifest.units.every((u) => isUnitComplete(u.id));
  }

  double get courseFraction {
    final all = _activities.keys.toList(growable: false);
    if (all.isEmpty) return 0;
    return all.where(isActivityCompleted).length / all.length;
  }

  int get completedActivityCount =>
      _activities.keys.where(isActivityCompleted).length;
  int get activityCount => _activities.length;
  int get completedUnitCount => _units.keys.where(isUnitComplete).length;

  String? lessonIdForActivity(String activityId) =>
      _activityToLesson[activityId];
  String? unitIdForActivity(String activityId) {
    final lessonId = _activityToLesson[activityId];
    return lessonId == null ? null : _lessonToUnit[lessonId];
  }

  ActivityContent? activityById(String id) => _activities[id];
  LessonContent? lessonById(String id) => _lessons[id];
  UnitContent? unitById(String id) => _units[id];

  int activityIndexInLesson(String activityId) {
    final lessonId = lessonIdForActivity(activityId);
    final lesson = lessonId == null ? null : _lessons[lessonId];
    if (lesson == null) return -1;
    return lesson.activities.indexWhere((a) => a.id == activityId);
  }

  ActivityContent? previousActivityInLesson(String activityId) {
    final lessonId = lessonIdForActivity(activityId);
    final lesson = lessonId == null ? null : _lessons[lessonId];
    if (lesson == null) return null;
    final index = lesson.activities.indexWhere((a) => a.id == activityId);
    if (index <= 0) return null;
    return lesson.activities[index - 1];
  }

  ActivityContent? nextActivityInLesson(String activityId) {
    final lessonId = lessonIdForActivity(activityId);
    final lesson = lessonId == null ? null : _lessons[lessonId];
    if (lesson == null) return null;
    final index = lesson.activities.indexWhere((a) => a.id == activityId);
    if (index < 0 || index >= lesson.activities.length - 1) return null;
    return lesson.activities[index + 1];
  }

  void markActivityVisited(String activityId) {
    final lessonId = lessonIdForActivity(activityId);
    final unitId = unitIdForActivity(activityId);
    if (lessonId == null || unitId == null) return;
    _replaceSnapshot(
      resume: ResumePointer(
        activityId: activityId,
        lessonId: lessonId,
        unitId: unitId,
        updatedAt: DateTime.now(),
      ),
    );
    _scheduleSave();
  }

  Future<void> handleSessionWrite(
    String activityId,
    Map<String, dynamic> value,
  ) async {
    if (_snapshot == null || !_activities.containsKey(activityId)) return;
    final activity = _activities[activityId]!;
    final now = _now();
    final old = activityProgress(activityId);
    final recordAttempt = value['record_attempt'] == true;
    final reportedComplete = value['complete'] == true;
    final isScored = activity.scoreMode != 'none';
    final score = value['score'] is num
        ? (value['score'] as num).toDouble().clamp(0.0, 1.0).toDouble()
        : (recordAttempt && isScored ? (reportedComplete ? 1.0 : 0.0) : null);

    final nextProgress = ActivityProgress(
      activityId: activityId,
      attempts: old.attempts + (recordAttempt ? 1 : 0),
      completed: old.completed || reportedComplete,
      bestScore: score == null
          ? old.bestScore
          : (old.bestScore == null
                ? score
                : (score > old.bestScore! ? score : old.bestScore)),
      lastAttemptAt: recordAttempt ? now : old.lastAttemptAt,
      firstCompletedAt: old.firstCompletedAt ?? (reportedComplete ? now : null),
    );

    final activities = Map<String, ActivityProgress>.from(_snapshot!.activities)
      ..[activityId] = nextProgress;
    final sessions = Map<String, Map<String, dynamic>>.from(
      _snapshot!.sessionData,
    )..[activityId] = _cleanSessionValue(value);
    final streak = _nextStreak(
      now: now,
      recordStudy: recordAttempt || reportedComplete,
    );

    _snapshot = ProgressSnapshot(
      courseId: _snapshot!.courseId,
      contentVersion: _snapshot!.contentVersion,
      activities: activities,
      sessionData: sessions,
      resume: _snapshot!.resume,
      updatedAt: now,
      currentStreak: streak.current,
      longestStreak: streak.longest,
      lastStudyDate: streak.lastStudyDate,
    );
    final nextResume = _resumeAfterWrite(activityId, nextProgress);
    _snapshot = ProgressSnapshot(
      courseId: _snapshot!.courseId,
      contentVersion: _snapshot!.contentVersion,
      activities: _snapshot!.activities,
      sessionData: _snapshot!.sessionData,
      resume: nextResume,
      updatedAt: now,
      currentStreak: _snapshot!.currentStreak,
      longestStreak: _snapshot!.longestStreak,
      lastStudyDate: _snapshot!.lastStudyDate,
    );
    notifyListeners();
    _scheduleSave();
  }

  Map<String, dynamic> _cleanSessionValue(Map<String, dynamic> input) {
    final result = Map<String, dynamic>.from(input);
    result.remove('record_attempt');
    result.remove('score');
    return result;
  }

  ResumePointer _resumeAfterWrite(
    String activityId,
    ActivityProgress progress,
  ) {
    final lessonId = lessonIdForActivity(activityId);
    final unitId = unitIdForActivity(activityId);
    if (lessonId == null || unitId == null) {
      return _snapshot?.resume ??
          ResumePointer(
            activityId: activityId,
            lessonId: 'u01l01',
            unitId: 'u01',
            updatedAt: DateTime.now(),
          );
    }
    final lessonCompleted = isLessonComplete(lessonId);

    if (!progress.completed && !lessonCompleted) {
      return ResumePointer(
        activityId: activityId,
        lessonId: lessonId,
        unitId: unitId,
        updatedAt: DateTime.now(),
      );
    }

    final index = _activityOrder.indexOf(activityId);
    for (var i = index + 1; i < _activityOrder.length; i++) {
      final candidate = _activityOrder[i];
      final candidateUnit = unitIdForActivity(candidate)!;
      if (!isUnitUnlocked(candidateUnit)) continue;
      return ResumePointer(
        activityId: candidate,
        lessonId: lessonIdForActivity(candidate)!,
        unitId: candidateUnit,
        updatedAt: DateTime.now(),
      );
    }

    final unit = _units[unitId];
    if (unit != null) {
      for (final lesson in unit.lessons) {
        if (isLessonComplete(lesson.id)) continue;
        for (final candidate in lesson.activities) {
          final p = activityProgress(candidate.id);
          if (!p.completed ||
              (candidate.scoreMode != 'none' && (p.bestScore ?? 0) < 1.0)) {
            return ResumePointer(
              activityId: candidate.id,
              lessonId: lesson.id,
              unitId: unitId,
              updatedAt: DateTime.now(),
            );
          }
        }
      }
    }
    return ResumePointer(
      activityId: activityId,
      lessonId: lessonId,
      unitId: unitId,
      updatedAt: DateTime.now(),
    );
  }

  void _replaceSnapshot({ResumePointer? resume}) {
    if (_snapshot == null) return;
    _snapshot = ProgressSnapshot(
      courseId: _snapshot!.courseId,
      contentVersion: _snapshot!.contentVersion,
      activities: _snapshot!.activities,
      sessionData: _snapshot!.sessionData,
      resume: resume ?? _snapshot!.resume,
      updatedAt: _now(),
      currentStreak: _snapshot!.currentStreak,
      longestStreak: _snapshot!.longestStreak,
      lastStudyDate: _snapshot!.lastStudyDate,
    );
    notifyListeners();
  }

  void _setPersistenceError(Object error, StackTrace stackTrace) {
    debugPrint('ADAPA persistence error: $error');
    debugPrintStack(stackTrace: stackTrace);
    const message =
        'No se pudo guardar el progreso. Tus cambios siguen abiertos en esta sesión.';
    if (_persistenceError == message) return;
    _persistenceError = message;
    notifyListeners();
  }

  void _clearPersistenceError() {
    if (_persistenceError == null) return;
    _persistenceError = null;
    notifyListeners();
  }

  Future<void> _persistSnapshot(ProgressSnapshot snapshot) async {
    try {
      await store.save(snapshot);
      _clearPersistenceError();
    } catch (error, stackTrace) {
      _setPersistenceError(error, stackTrace);
    }
  }

  void _scheduleSave() {
    final snapshot = _snapshot;
    if (snapshot == null) return;
    _saveQueue = _saveQueue
        .catchError((Object error, StackTrace stackTrace) {
          _setPersistenceError(error, stackTrace);
        })
        .then((_) => _persistSnapshot(snapshot));
  }

  Future<void> retrySave() async {
    final snapshot = _snapshot;
    if (snapshot == null) return;
    await _persistSnapshot(snapshot);
  }

  Future<void> flush() async {
    await _saveQueue;
  }

  Future<void> resetCourseProgress() async {
    final manifest = _manifest;
    if (manifest == null) return;
    await store.clear(manifest.id);
    _persistenceError = null;
    _snapshot = ProgressSnapshot.empty(
      courseId: manifest.id,
      contentVersion: manifest.contentVersion,
    );
    notifyListeners();
  }

  _StreakState _nextStreak({required DateTime now, required bool recordStudy}) {
    final snapshot = _snapshot!;
    if (!recordStudy) {
      return _StreakState(
        current: snapshot.currentStreak,
        longest: snapshot.longestStreak,
        lastStudyDate: snapshot.lastStudyDate,
      );
    }

    final today = _dateOnly(now);
    final last = snapshot.lastStudyDate == null
        ? null
        : _dateOnly(snapshot.lastStudyDate!);
    if (last == today) {
      return _StreakState(
        current: snapshot.currentStreak,
        longest: snapshot.longestStreak,
        lastStudyDate: last,
      );
    }

    final yesterday = today.subtract(const Duration(days: 1));
    final nextCurrent = last == yesterday ? snapshot.currentStreak + 1 : 1;
    return _StreakState(
      current: nextCurrent,
      longest: nextCurrent > snapshot.longestStreak
          ? nextCurrent
          : snapshot.longestStreak,
      lastStudyDate: today,
    );
  }

  DateTime _dateOnly(DateTime value) =>
      DateTime(value.year, value.month, value.day);
}

class _StreakState {
  const _StreakState({
    required this.current,
    required this.longest,
    required this.lastStudyDate,
  });

  final int current;
  final int longest;
  final DateTime? lastStudyDate;
}
