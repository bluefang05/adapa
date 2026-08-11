import 'package:adapa/core/reading/reading_practice_logic.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('attempt then listen requires both actions', () {
    expect(
      ReadingPracticeLogic.attemptThenListenComplete(
        attempted: true,
        listened: false,
      ),
      isFalse,
    );
    expect(
      ReadingPracticeLogic.attemptThenListenComplete(
        attempted: true,
        listened: true,
      ),
      isTrue,
    );
  });

  test('speaking practice completes only after all items are repeated', () {
    expect(
      ReadingPracticeLogic.repeatAllComplete(itemCount: 3, repeated: {0, 2}),
      isFalse,
    );
    expect(
      ReadingPracticeLogic.repeatAllComplete(itemCount: 3, repeated: {0, 1, 2}),
      isTrue,
    );
  });
}
