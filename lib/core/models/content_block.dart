class ContentBlock {
  const ContentBlock({
    required this.id,
    required this.type,
    required this.payload,
    this.title,
    this.text,
  });

  final String id;
  final String type;
  final String? title;
  final String? text;
  final Map<String, dynamic> payload;

  factory ContentBlock.fromJson(Map<String, dynamic> json) {
    return ContentBlock(
      id: json['id'] as String,
      type: json['type'] as String,
      title: json['title'] as String?,
      text: json['text'] as String?,
      payload: Map<String, dynamic>.from(json['payload'] as Map? ?? const {}),
    );
  }
}
