import 'package:flutter_test/flutter_test.dart';
import 'package:adapa/core/evaluation/answer_normalizer.dart';

void main() {
  test('ignores configured terminal punctuation and spaces', () {
    const rules = {
      'trim': true,
      'unicode_normalization': 'NFC',
      'collapse_spaces': true,
      'ignore_terminal_punctuation': true,
      'case_sensitive': false,
    };
    expect(AnswerNormalizer.equals('  감사합니다.  ', '감사합니다', rules), isTrue);
  });

  test('NFC composes equivalent Hangul', () {
    const rules = {
      'unicode_normalization': 'NFC',
      'trim': true,
    };
    // ᄀ + ᅡ is canonically equivalent to 가 after NFC.
    expect(AnswerNormalizer.equals('\u1100\u1161', '가', rules), isTrue);
  });
}
