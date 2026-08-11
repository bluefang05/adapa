class ActivityProgress {
  const ActivityProgress({
    required this.activityId,
    this.attempts = 0,
    this.completed = false,
    this.bestScore,
    this.lastAttemptAt,
    this.firstCompletedAt,
  });

  final String activityId;
  final int attempts;
  final bool completed;
  final double? bestScore;
  final DateTime? lastAttemptAt;
  final DateTime? firstCompletedAt;

  bool get attempted => attempts > 0 || completed;

  ActivityProgress copyWith({
    int? attempts,
    bool? completed,
    double? bestScore,
    DateTime? lastAttemptAt,
    DateTime? firstCompletedAt,
  }) {
    return ActivityProgress(
      activityId: activityId,
      attempts: attempts ?? this.attempts,
      completed: completed ?? this.completed,
      bestScore: bestScore ?? this.bestScore,
      lastAttemptAt: lastAttemptAt ?? this.lastAttemptAt,
      firstCompletedAt: firstCompletedAt ?? this.firstCompletedAt,
    );
  }

  Map<String, dynamic> toJson() => {
        'activityId': activityId,
        'attempts': attempts,
        'completed': completed,
        if (bestScore != null) 'bestScore': bestScore,
        if (lastAttemptAt != null) 'lastAttemptAt': lastAttemptAt!.toIso8601String(),
        if (firstCompletedAt != null) 'firstCompletedAt': firstCompletedAt!.toIso8601String(),
      };

  factory ActivityProgress.fromJson(String id, Map<String, dynamic> json) {
    return ActivityProgress(
      activityId: id,
      attempts: (json['attempts'] as num?)?.toInt() ?? 0,
      completed: json['completed'] == true,
      bestScore: (json['bestScore'] as num?)?.toDouble(),
      lastAttemptAt: DateTime.tryParse(json['lastAttemptAt']?.toString() ?? ''),
      firstCompletedAt: DateTime.tryParse(json['firstCompletedAt']?.toString() ?? ''),
    );
  }
}
