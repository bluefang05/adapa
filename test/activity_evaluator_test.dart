import 'package:flutter_test/flutter_test.dart';
import 'package:adapa/core/evaluation/activity_evaluator.dart';

void main() {
  test('accepts one of the configured answers', () {
    final result = ActivityEvaluator.text(
      value: '아리스.',
      scoreMode: 'auto',
      completionRule: null,
      payload: const {'accepted_answers': ['아리스예요', '아리스']},
      normalization: const {
        'trim': true,
        'unicode_normalization': 'NFC',
        'ignore_terminal_punctuation': true,
      },
    );
    expect(result.isCorrect, isTrue);
  });

  test('validates structural juseyo response', () {
    final result = ActivityEvaluator.text(
      value: '커피 주세요.',
      scoreMode: 'structural',
      completionRule: null,
      payload: const {
        'validation': {
          'must_end_with': '주세요',
          'must_contain_hangul': true,
          'minimum_content_before_pattern': 1,
        }
      },
      normalization: const {
        'trim': true,
        'unicode_normalization': 'NFC',
        'ignore_terminal_punctuation': true,
      },
    );
    expect(result.isCorrect, isTrue);
  });

  test('requires five non-empty lines for free writing', () {
    final result = ActivityEvaluator.text(
      value: '하나\n둘\n셋\n넷\n다섯',
      scoreMode: 'none',
      completionRule: 'five_non_empty_lines',
      payload: const {'required_lines': 5},
      normalization: const {},
    );
    expect(result.isCorrect, isTrue);
  });
}
