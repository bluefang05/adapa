import 'package:adapa/core/review/review_logic.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('extracts unique reading tokens without punctuation', () {
    expect(
      ReviewLogic.uniqueReadingTokens('물을 마셔요. 학교에 가요. 물을 마셔요.'),
      ['물을', '마셔요', '학교에', '가요'],
    );
  });

  test('reflection is complete after every item has an answer', () {
    expect(ReviewLogic.allItemsReviewed(3, {0: true, 1: false}), isFalse);
    expect(ReviewLogic.allItemsReviewed(3, {0: true, 1: false, 2: true}), isTrue);
  });
}
