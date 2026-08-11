class ReadingPracticeLogic {
  const ReadingPracticeLogic._();

  static bool attemptThenListenComplete({
    required bool attempted,
    required bool listened,
  }) => attempted && listened;

  static bool repeatAllComplete({
    required int itemCount,
    required Set<int> repeated,
  }) => itemCount > 0 && repeated.length == itemCount;
}
