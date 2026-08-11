import 'activity_family.dart';

class ActivityContent {
  const ActivityContent({
    required this.id,
    required this.type,
    required this.family,
    required this.scoreMode,
    required this.normalization,
    required this.hints,
    required this.feedback,
    required this.capabilities,
    required this.payload,
    this.objective,
    this.prompt,
    this.completionRule,
    this.note,
  });

  final String id;
  final String type;
  final ActivityFamily family;
  final String scoreMode;
  final String? objective;
  final String? prompt;
  final String? completionRule;
  final String? note;
  final Map<String, dynamic> normalization;
  final List<String> hints;
  final Map<String, dynamic> feedback;
  final Map<String, dynamic> capabilities;
  final Map<String, dynamic> payload;

  factory ActivityContent.fromJson(Map<String, dynamic> json) {
    return ActivityContent(
      id: json['id'] as String,
      type: json['type'] as String,
      family: ActivityFamilyParsing.fromJson(json['family'] as String),
      scoreMode: (json['scoreMode'] ?? 'auto').toString(),
      objective: json['objective'] as String?,
      prompt: json['prompt'] as String?,
      completionRule: json['completionRule']?.toString(),
      note: json['note'] as String?,
      normalization: Map<String, dynamic>.from(json['normalization'] as Map? ?? const {}),
      hints: (json['hints'] as List? ?? const []).map((e) => e.toString()).toList(growable: false),
      feedback: Map<String, dynamic>.from(json['feedback'] as Map? ?? const {}),
      capabilities: Map<String, dynamic>.from(json['capabilities'] as Map? ?? const {}),
      payload: Map<String, dynamic>.from(json['payload'] as Map? ?? const {}),
    );
  }
}
