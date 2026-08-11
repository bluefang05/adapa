class KoreanParticleHelper {
  const KoreanParticleHelper._();

  /// Returns true when the final Hangul syllable has a jongseong/batchim.
  static bool hasBatchim(String word) {
    final runes = word.trim().runes.toList(growable: false);
    if (runes.isEmpty) return false;
    final code = runes.last;
    if (code < 0xAC00 || code > 0xD7A3) return false;
    return (code - 0xAC00) % 28 != 0;
  }

  static String subjectParticle(String word) => hasBatchim(word) ? '이' : '가';

  static String topicParticle(String word) => hasBatchim(word) ? '은' : '는';

  static String objectParticle(String word) => hasBatchim(word) ? '을' : '를';
}
