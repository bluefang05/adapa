enum ActivityFamily {
  choice,
  matching,
  textInput,
  ordering,
  hangulStructure,
  dialogue,
  readingSpeaking,
  review,
  visualReference;

  String get label => switch (this) {
    ActivityFamily.choice => 'Elección',
    ActivityFamily.matching => 'Emparejamiento',
    ActivityFamily.textInput => 'Escritura',
    ActivityFamily.ordering => 'Ordenar',
    ActivityFamily.hangulStructure => 'Hangul',
    ActivityFamily.dialogue => 'Conversación',
    ActivityFamily.readingSpeaking => 'Lectura y voz',
    ActivityFamily.review => 'Repaso',
    ActivityFamily.visualReference => 'Referencia visual',
  };
}

extension ActivityFamilyParsing on ActivityFamily {
  static ActivityFamily fromJson(String value) {
    return switch (value) {
      'choice' => ActivityFamily.choice,
      'matching' => ActivityFamily.matching,
      'text_input' => ActivityFamily.textInput,
      'ordering' => ActivityFamily.ordering,
      'hangul_structure' => ActivityFamily.hangulStructure,
      'dialogue' => ActivityFamily.dialogue,
      'reading_speaking' => ActivityFamily.readingSpeaking,
      'review' => ActivityFamily.review,
      'visual_reference' => ActivityFamily.visualReference,
      _ => throw FormatException('Unknown activity family: $value'),
    };
  }
}
