import 'package:adapa/core/services/tts_service.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  group('TtsLocaleMatcher', () {
    test('accepts common Korean locale variants', () {
      expect(TtsLocaleMatcher.matches('ko-KR', 'ko-KR'), isTrue);
      expect(TtsLocaleMatcher.matches('ko_KR', 'ko-KR'), isTrue);
      expect(TtsLocaleMatcher.matches('ko', 'ko-KR'), isTrue);
      expect(TtsLocaleMatcher.matches('ko-KR-x-lvariant', 'ko-KR'), isTrue);
    });

    test('does not accept non-Korean fallbacks', () {
      expect(TtsLocaleMatcher.matches('en-US', 'ko-KR'), isFalse);
      expect(TtsLocaleMatcher.matches('ja-JP', 'ko-KR'), isFalse);
      expect(TtsLocaleMatcher.matches('', 'ko-KR'), isFalse);
    });
  });

  group('KoreanPhoneticNormalizer', () {
    test('normalizes isolated vowels into pronounceable syllables', () {
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('ㅏ'), equals('아'));
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('ㅗ'), equals('오'));
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('ㅜ'), equals('우'));
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('ㅡ'), equals('으'));
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('ㅣ'), equals('이'));
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('ㅢ'), equals('의'));
    });

    test('normalizes isolated consonants into letter names', () {
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('ㄱ'), equals('기역'));
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('ㄴ'), equals('니은'));
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('ㄲ'), equals('쌍기역'));
    });

    test('preserves whole words and strips formatting symbols', () {
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('안녕하세요'), equals('안녕하세요'));
      expect(KoreanPhoneticNormalizer.preprocessForSpeech('**안녕하세요**'), equals('안녕하세요'));
      expect(KoreanPhoneticNormalizer.preprocessForSpeech(''), equals(''));
    });
  });
}
