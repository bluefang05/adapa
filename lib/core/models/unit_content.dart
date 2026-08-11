import 'lesson_content.dart';

class UnitContent {
  const UnitContent({
    required this.id,
    required this.title,
    required this.prerequisites,
    required this.resourceRefs,
    required this.lessons,
    required this.completion,
    required this.source,
    this.goal,
    this.romanizationPolicy,
  });

  final String id;
  final String title;
  final String? goal;
  final List<String> prerequisites;
  final dynamic romanizationPolicy;
  final Map<String, dynamic> source;
  final Map<String, dynamic> resourceRefs;
  final List<LessonContent> lessons;
  final Map<String, dynamic> completion;

  factory UnitContent.fromJson(Map<String, dynamic> json) {
    return UnitContent(
      id: json['id'] as String,
      title: json['title'] as String,
      goal: json['goal'] as String?,
      prerequisites: (json['prerequisites'] as List? ?? const []).map((e) => e.toString()).toList(growable: false),
      romanizationPolicy: json['romanizationPolicy'],
      source: Map<String, dynamic>.from(json['source'] as Map? ?? const {}),
      resourceRefs: Map<String, dynamic>.from(json['resourceRefs'] as Map? ?? const {}),
      lessons: (json['lessons'] as List? ?? const [])
          .map((e) => LessonContent.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList(growable: false),
      completion: Map<String, dynamic>.from(json['completion'] as Map? ?? const {}),
    );
  }
}
