import 'answer_normalizer.dart';

class DialogueEvaluationResult {
  const DialogueEvaluationResult({
    required this.isCorrect,
    this.fieldId,
  });

  final bool isCorrect;
  final String? fieldId;
}

class DialogueEvaluator {
  const DialogueEvaluator._();

  static final RegExp _hangul =
      RegExp(r'[\u1100-\u11FF\u3130-\u318F\uAC00-\uD7A3]');
  static final RegExp _latin = RegExp(r'[A-Za-z]');

  static bool accepted(
    String value,
    List<dynamic> acceptedValues,
    Map<String, dynamic> rules,
  ) {
    return acceptedValues.any(
      (answer) => AnswerNormalizer.equals(
        value,
        answer.toString(),
        rules,
      ),
    );
  }

  static DialogueEvaluationResult roleplay({
    required Map<int, String> responses,
    required List<int> requiredTurns,
    required Map<String, dynamic> acceptedByTurn,
    required Map<String, dynamic> normalization,
  }) {
    for (final turn in requiredTurns) {
      final value = responses[turn]?.trim() ?? '';
      if (value.isEmpty) {
        return DialogueEvaluationResult(isCorrect: false, fieldId: '$turn');
      }
      final acceptedValues =
          (acceptedByTurn['$turn'] as List? ?? const []).toList(growable: false);
      if (acceptedValues.isNotEmpty &&
          !accepted(value, acceptedValues, normalization)) {
        return DialogueEvaluationResult(isCorrect: false, fieldId: '$turn');
      }
    }
    return const DialogueEvaluationResult(isCorrect: true);
  }

  static DialogueEvaluationResult finalConversation({
    required List<dynamic> turns,
    required Map<String, String> responses,
    required Map<String, dynamic> normalization,
  }) {
    for (final raw in turns) {
      final turn = Map<String, dynamic>.from(raw as Map);
      final fieldId = turn['field_id']?.toString();
      if (fieldId == null) continue;
      final value = responses[fieldId]?.trim() ?? '';

      if (turn['required'] == true && value.isEmpty) {
        return DialogueEvaluationResult(isCorrect: false, fieldId: fieldId);
      }
      if (value.isEmpty) continue;

      final fieldType = turn['field_type']?.toString();
      if (fieldType == 'open_hangul' ||
          fieldType == 'request_prefix' ||
          fieldType == 'controlled') {
        if (!_hangul.hasMatch(value) || _latin.hasMatch(value)) {
          return DialogueEvaluationResult(isCorrect: false, fieldId: fieldId);
        }
      }

      if (fieldType == 'controlled') {
        final acceptedValues =
            (turn['accepted'] as List? ?? const []).toList(growable: false);
        if (acceptedValues.isNotEmpty &&
            !accepted(value, acceptedValues, normalization)) {
          return DialogueEvaluationResult(isCorrect: false, fieldId: fieldId);
        }
      }
    }
    return const DialogueEvaluationResult(isCorrect: true);
  }

  static List<String> finalPlaybackLines({
    required List<dynamic> turns,
    required Map<String, String> responses,
  }) {
    final lines = <String>[];
    for (final raw in turns) {
      final turn = Map<String, dynamic>.from(raw as Map);
      final fixed = turn['fixed']?.toString().trim() ?? '';
      final fieldId = turn['field_id']?.toString();
      var value = fieldId == null ? '' : (responses[fieldId]?.trim() ?? '');
      if (turn['field_type']?.toString() == 'request_prefix' && value.isNotEmpty) {
        value = '$value ${(turn['suffix'] ?? '').toString()}'.trim();
      }
      final text = [fixed, value].where((e) => e.isNotEmpty).join(' ').trim();
      if (text.isNotEmpty) lines.add(text);
    }
    return lines;
  }
}
