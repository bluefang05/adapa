import 'package:adapa/core/evaluation/dialogue_evaluator.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  const rules = {
    'trim': true,
    'unicode_normalization': 'NFC',
    'collapse_spaces': true,
    'ignore_terminal_punctuation': true,
    'case_sensitive': false,
  };

  test('roleplay accepts configured variants', () {
    final result = DialogueEvaluator.roleplay(
      responses: const {1: '네, 커피 하나 주세요.', 3: '아니요 괜찮아요', 5: '감사합니다.'},
      requiredTurns: const [1, 3, 5],
      acceptedByTurn: const {
        '1': ['네. 커피 하나 주세요', '네, 커피 하나 주세요'],
        '3': ['아니요, 괜찮아요', '아니요 괜찮아요'],
        '5': ['감사합니다'],
      },
      normalization: rules,
    );
    expect(result.isCorrect, isTrue);
  });

  test('final conversation requires Hangul and rejects Latin-only input', () {
    final result = DialogueEvaluator.finalConversation(
      turns: const [
        {
          'speaker': 'B',
          'field_id': 'name',
          'field_type': 'open_hangul',
          'required': true,
        }
      ],
      responses: const {'name': 'Maria'},
      normalization: rules,
    );
    expect(result.isCorrect, isFalse);
  });

  test('final conversation validates controlled response', () {
    final result = DialogueEvaluator.finalConversation(
      turns: const [
        {
          'speaker': 'B',
          'field_id': 'glad',
          'field_type': 'controlled',
          'accepted': ['저도 반가워요'],
          'required': true,
        }
      ],
      responses: const {'glad': '저도 반가워요.'},
      normalization: rules,
    );
    expect(result.isCorrect, isTrue);
  });

  test('builds request prefix playback with suffix', () {
    final lines = DialogueEvaluator.finalPlaybackLines(
      turns: const [
        {
          'speaker': 'A',
          'field_id': 'request',
          'field_type': 'request_prefix',
          'suffix': '주세요.',
          'required': true,
        }
      ],
      responses: const {'request': '커피'},
    );
    expect(lines, const ['커피 주세요.']);
  });
}
