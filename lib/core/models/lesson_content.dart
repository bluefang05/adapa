import 'activity_content.dart';
import 'content_block.dart';

class LessonContent {
  const LessonContent({
    required this.id,
    required this.title,
    required this.theory,
    required this.activities,
    required this.completion,
    required this.sourcePages,
    required this.dialogueRefs,
    this.objective,
    this.estimatedMinutes,
    this.dialogueRef,
  });

  final String id;
  final String title;
  final String? objective;
  final int? estimatedMinutes;
  final List<dynamic> sourcePages;
  final String? dialogueRef;
  final List<String> dialogueRefs;
  final List<ContentBlock> theory;
  final List<ActivityContent> activities;
  final Map<String, dynamic> completion;

  factory LessonContent.fromJson(Map<String, dynamic> json) {
    return LessonContent(
      id: json['id'] as String,
      title: json['title'] as String,
      objective: json['objective'] as String?,
      estimatedMinutes: json['estimatedMinutes'] as int?,
      sourcePages: List<dynamic>.from(json['sourcePages'] as List? ?? const []),
      dialogueRef: json['dialogueRef'] as String?,
      dialogueRefs: (json['dialogueRefs'] as List? ?? const []).map((e) => e.toString()).toList(growable: false),
      theory: (json['theory'] as List? ?? const [])
          .map((e) => ContentBlock.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList(growable: false),
      activities: (json['activities'] as List? ?? const [])
          .map((e) => ActivityContent.fromJson(Map<String, dynamic>.from(e as Map)))
          .toList(growable: false),
      completion: Map<String, dynamic>.from(json['completion'] as Map? ?? const {}),
    );
  }
}
