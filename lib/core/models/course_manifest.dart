class UnitSummary {
  const UnitSummary({
    required this.id,
    required this.title,
    required this.lessonCount,
    required this.activityCount,
    required this.asset,
    required this.prerequisites,
  });

  final String id;
  final String title;
  final int lessonCount;
  final int activityCount;
  final String asset;
  final List<String> prerequisites;

  factory UnitSummary.fromJson(Map<String, dynamic> json) {
    return UnitSummary(
      id: json['id'] as String,
      title: json['title'] as String,
      lessonCount: json['lessonCount'] as int,
      activityCount: json['activityCount'] as int,
      asset: json['asset'] as String,
      prerequisites: (json['prerequisites'] as List? ?? const []).map((e) => e.toString()).toList(growable: false),
    );
  }
}

class CourseManifest {
  const CourseManifest({
    required this.id,
    required this.title,
    required this.subtitle,
    required this.level,
    required this.ttsLocale,
    required this.offlineFirst,
    required this.contentVersion,
    required this.units,
    required this.resources,
    required this.summary,
  });

  final String id;
  final String title;
  final String subtitle;
  final String level;
  final String ttsLocale;
  final bool offlineFirst;
  final String contentVersion;
  final List<UnitSummary> units;
  final Map<String, dynamic> resources;
  final Map<String, dynamic> summary;

  factory CourseManifest.fromJson(Map<String, dynamic> json) {
    return CourseManifest(
      id: json['id'] as String,
      title: json['title'] as String,
      subtitle: json['subtitle'] as String,
      level: json['level'] as String,
      ttsLocale: json['ttsLocale'] as String,
      offlineFirst: json['offlineFirst'] as bool,
      contentVersion: json['contentVersion'] as String,
      units: (json['units'] as List)
          .map((e) => UnitSummary.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList(growable: false),
      resources: Map<String, dynamic>.from(json['resources'] as Map? ?? const {}),
      summary: Map<String, dynamic>.from(json['summary'] as Map? ?? const {}),
    );
  }
}
