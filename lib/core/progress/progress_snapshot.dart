import 'activity_progress.dart';
import 'resume_pointer.dart';

class ProgressSnapshot {
  const ProgressSnapshot({
    required this.courseId,
    required this.contentVersion,
    required this.activities,
    required this.sessionData,
    this.resume,
    this.updatedAt,
    this.currentStreak = 0,
    this.longestStreak = 0,
    this.lastStudyDate,
  });

  final String courseId;
  final String contentVersion;
  final Map<String, ActivityProgress> activities;
  final Map<String, Map<String, dynamic>> sessionData;
  final ResumePointer? resume;
  final DateTime? updatedAt;
  final int currentStreak;
  final int longestStreak;
  final DateTime? lastStudyDate;

  factory ProgressSnapshot.empty({
    required String courseId,
    required String contentVersion,
  }) => ProgressSnapshot(
    courseId: courseId,
    contentVersion: contentVersion,
    activities: const {},
    sessionData: const {},
  );

  Map<String, dynamic> toJson() => {
    'schemaVersion': 1,
    'courseId': courseId,
    'contentVersion': contentVersion,
    'activities': {
      for (final entry in activities.entries) entry.key: entry.value.toJson(),
    },
    'sessionData': sessionData,
    if (resume != null) 'resume': resume!.toJson(),
    'updatedAt': (updatedAt ?? DateTime.now()).toIso8601String(),
    'currentStreak': currentStreak,
    'longestStreak': longestStreak,
    if (lastStudyDate != null)
      'lastStudyDate': _dateOnly(lastStudyDate!).toIso8601String(),
  };

  factory ProgressSnapshot.fromJson(Map<String, dynamic> json) {
    final rawActivities = Map<String, dynamic>.from(
      json['activities'] as Map? ?? const {},
    );
    final rawSession = Map<String, dynamic>.from(
      json['sessionData'] as Map? ?? const {},
    );
    return ProgressSnapshot(
      courseId: json['courseId']?.toString() ?? '',
      contentVersion: json['contentVersion']?.toString() ?? '',
      activities: {
        for (final entry in rawActivities.entries)
          entry.key: ActivityProgress.fromJson(
            entry.key,
            Map<String, dynamic>.from(entry.value as Map),
          ),
      },
      sessionData: {
        for (final entry in rawSession.entries)
          entry.key: Map<String, dynamic>.from(entry.value as Map),
      },
      resume: json['resume'] is Map
          ? ResumePointer.fromJson(
              Map<String, dynamic>.from(json['resume'] as Map),
            )
          : null,
      updatedAt: DateTime.tryParse(json['updatedAt']?.toString() ?? ''),
      currentStreak: (json['currentStreak'] as num?)?.toInt() ?? 0,
      longestStreak: (json['longestStreak'] as num?)?.toInt() ?? 0,
      lastStudyDate: _tryParseDateOnly(json['lastStudyDate']),
    );
  }

  static DateTime _dateOnly(DateTime value) =>
      DateTime(value.year, value.month, value.day);

  static DateTime? _tryParseDateOnly(Object? value) {
    final parsed = DateTime.tryParse(value?.toString() ?? '');
    return parsed == null ? null : _dateOnly(parsed);
  }
}
