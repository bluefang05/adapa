class ResumePointer {
  const ResumePointer({
    required this.activityId,
    required this.lessonId,
    required this.unitId,
    required this.updatedAt,
  });

  final String activityId;
  final String lessonId;
  final String unitId;
  final DateTime updatedAt;

  Map<String, dynamic> toJson() => {
        'activityId': activityId,
        'lessonId': lessonId,
        'unitId': unitId,
        'updatedAt': updatedAt.toIso8601String(),
      };

  factory ResumePointer.fromJson(Map<String, dynamic> json) => ResumePointer(
        activityId: json['activityId']?.toString() ?? '',
        lessonId: json['lessonId']?.toString() ?? '',
        unitId: json['unitId']?.toString() ?? '',
        updatedAt: DateTime.tryParse(json['updatedAt']?.toString() ?? '') ?? DateTime.now(),
      );
}
