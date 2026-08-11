import 'package:adapa/core/hangul/hangul_composer.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  group('HangulComposer', () {
    test('composes simple CV syllables used by ADAPA', () {
      expect(HangulComposer.compose(['ㄱ', 'ㅏ']), '가');
      expect(HangulComposer.compose(['ㅁ', 'ㅜ']), '무');
      expect(HangulComposer.compose(['ㅎ', 'ㅏ']), '하');
    });

    test('supports a final consonant for future structure activities', () {
      expect(HangulComposer.compose(['ㅅ', 'ㅏ', 'ㄴ']), '산');
      expect(HangulComposer.compose(['ㅁ', 'ㅜ', 'ㄹ']), '물');
    });

    test('supports tense initials', () {
      expect(HangulComposer.compose(['ㄲ', 'ㅏ']), '까');
    });

    test('returns null for invalid part order', () {
      expect(HangulComposer.compose(['ㅏ', 'ㄱ']), isNull);
      expect(HangulComposer.compose(['ㄱ']), isNull);
    });
  });
}
