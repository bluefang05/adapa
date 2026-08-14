import 'package:adapa/core/evaluation/activity_evaluator.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('scored text activity without answers fails closed', () {
    final result = ActivityEvaluator.text(
      value: 'cualquier cosa',
      scoreMode: 'auto',
      completionRule: null,
      payload: const {},
      normalization: const {'trim': true},
    );

    expect(result.isCorrect, isFalse);
    expect(result.message, contains('no tiene respuestas configuradas'));
  });
}
