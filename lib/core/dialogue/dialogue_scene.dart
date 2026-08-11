class DialogueTurn {
  const DialogueTurn({
    required this.speaker,
    required this.ko,
    this.es,
  });

  final String speaker;
  final String ko;
  final String? es;

  factory DialogueTurn.fromJson(Map<String, dynamic> json) => DialogueTurn(
        speaker: (json['speaker'] ?? '').toString(),
        ko: (json['ko'] ?? '').toString(),
        es: json['es']?.toString(),
      );

  DialogueTurn copyWith({String? ko, String? es}) => DialogueTurn(
        speaker: speaker,
        ko: ko ?? this.ko,
        es: es ?? this.es,
      );
}

class DialogueReplacementSlot {
  const DialogueReplacementSlot({
    required this.slot,
    required this.base,
    required this.allowed,
  });

  final String slot;
  final String base;
  final List<String> allowed;

  factory DialogueReplacementSlot.fromJson(Map<String, dynamic> json) =>
      DialogueReplacementSlot(
        slot: (json['slot'] ?? '').toString(),
        base: (json['base'] ?? '').toString(),
        allowed: (json['allowed'] as List? ?? const [])
            .map((e) => e.toString())
            .toList(growable: false),
      );
}

class DialogueScene {
  const DialogueScene({
    required this.id,
    required this.titleEs,
    required this.turns,
    required this.replacementSlots,
    this.source,
    this.scored = true,
  });

  final String id;
  final String titleEs;
  final String? source;
  final List<DialogueTurn> turns;
  final List<DialogueReplacementSlot> replacementSlots;
  final bool scored;

  factory DialogueScene.fromJson(Map<String, dynamic> json) => DialogueScene(
        id: (json['id'] ?? '').toString(),
        titleEs: (json['title_es'] ?? '').toString(),
        source: json['source']?.toString(),
        turns: (json['turns'] as List? ?? const [])
            .map((e) => DialogueTurn.fromJson(Map<String, dynamic>.from(e as Map)))
            .toList(growable: false),
        replacementSlots: (json['replacement_slots'] as List? ?? const [])
            .map((e) => DialogueReplacementSlot.fromJson(
                  Map<String, dynamic>.from(e as Map),
                ))
            .toList(growable: false),
        scored: json['scored'] != false,
      );
}
