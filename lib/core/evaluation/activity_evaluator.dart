import 'answer_normalizer.dart';

class EvaluationResult {
  const EvaluationResult({required this.isCorrect, this.message});

  final bool isCorrect;
  final String? message;
}

class ActivityEvaluator {
  const ActivityEvaluator._();

  static final RegExp _hangul = RegExp(r'[\u1100-\u11FF\u3130-\u318F\uAC00-\uD7A3]');

  static EvaluationResult text({
    required String value,
    required String scoreMode,
    required String? completionRule,
    required Map<String, dynamic> payload,
    required Map<String, dynamic> normalization,
  }) {
    if (scoreMode == 'none') {
      return _completionOnly(value, completionRule, payload);
    }

    if (scoreMode == 'structural') {
      return _structural(value, payload, normalization);
    }

    final rawAnswers = payload['accepted_answers'] ?? payload['correct'];
    final answers = (rawAnswers is List
            ? rawAnswers
            : (rawAnswers != null ? [rawAnswers] : const []))
        .map((e) => e.toString())
        .toList(growable: false);
    if (answers.isEmpty) {
      return const EvaluationResult(
        isCorrect: false,
        message: 'La actividad no tiene respuestas configuradas.',
      );
    }

    var matches = answers.any(
      (answer) => AnswerNormalizer.equals(value, answer, normalization),
    );

    if (!matches && payload['template'] != null) {
      final template = payload['template'].toString();
      for (final answer in answers) {
        final fullSentence = template.replaceAll(RegExp(r'_+'), answer);
        if (AnswerNormalizer.equals(value, fullSentence, normalization)) {
          matches = true;
          break;
        }
      }
    }

    return EvaluationResult(isCorrect: matches);
  }

  static EvaluationResult _completionOnly(
    String value,
    String? completionRule,
    Map<String, dynamic> payload,
  ) {
    if (completionRule == 'five_non_empty_lines') {
      final required = (payload['required_lines'] as num?)?.toInt() ?? 5;
      final lines = value
          .split('\n')
          .where((line) => line.trim().isNotEmpty)
          .length;
      return EvaluationResult(
        isCorrect: lines >= required,
        message: lines >= required ? null : 'Completa al menos $required líneas.',
      );
    }
    return EvaluationResult(
      isCorrect: value.trim().isNotEmpty,
      message: value.trim().isEmpty ? 'Escribe algo antes de continuar.' : null,
    );
  }

  static EvaluationResult _structural(
    String value,
    Map<String, dynamic> payload,
    Map<String, dynamic> normalization,
  ) {
    final validation = Map<String, dynamic>.from(
      payload['validation'] as Map? ?? const {},
    );
    final normalized = AnswerNormalizer.normalize(value, normalization);

    final start = validation['must_start_with']?.toString();
    if (start != null &&
        !normalized.startsWith(AnswerNormalizer.normalize(start, normalization))) {
      return const EvaluationResult(isCorrect: false);
    }

    final end = validation['must_end_with']?.toString();
    if (end != null &&
        !normalized.endsWith(AnswerNormalizer.normalize(end, normalization))) {
      return const EvaluationResult(isCorrect: false);
    }

    final ends = (validation['must_end_with_one_of'] as List? ?? const [])
        .map((e) => AnswerNormalizer.normalize(e.toString(), normalization))
        .toList(growable: false);
    if (ends.isNotEmpty && !ends.any(normalized.endsWith)) {
      return const EvaluationResult(isCorrect: false);
    }

    if (validation['must_contain_hangul'] == true && !_hangul.hasMatch(value)) {
      return const EvaluationResult(isCorrect: false);
    }

    final minBefore = (validation['minimum_content_before_pattern'] as num?)?.toInt();
    if (minBefore != null && end != null) {
      final normalizedEnd = AnswerNormalizer.normalize(end, normalization);
      final prefix = normalized.substring(
        0,
        normalized.length - normalizedEnd.length,
      ).trim();
      if (prefix.runes.length < minBefore) {
        return const EvaluationResult(isCorrect: false);
      }
    }

    return const EvaluationResult(isCorrect: true);
  }
}
