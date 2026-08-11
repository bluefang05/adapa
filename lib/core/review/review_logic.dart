class ReviewLogic {
  const ReviewLogic._();

  static List<String> uniqueReadingTokens(String text) {
    final result = <String>[];
    final seen = <String>{};
    for (final token in text.split(RegExp(r'[\s,.!?。！？]+'))) {
      final value = token.trim();
      if (value.isEmpty || !seen.add(value)) continue;
      result.add(value);
    }
    return result;
  }

  static bool allItemsReviewed(int itemCount, Map<int, bool> answers) {
    return itemCount > 0 && answers.length == itemCount;
  }
}
