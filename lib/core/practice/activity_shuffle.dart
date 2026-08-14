import 'dart:math';

/// Centralized randomization for practice/answer presentation.
///
/// Content stays immutable. Each renderer receives a shuffled copy so retrying
/// or reopening an exercise can present a different order without touching the
/// answer key stored in the course JSON.
class ActivityShuffle {
  const ActivityShuffle._();

  static List<T> copy<T>(Iterable<T> source, {Random? random}) {
    final original = List<T>.of(source);
    final result = List<T>.of(original)..shuffle(random);
    if (result.length < 2 || !_sameOrder(result, original)) return result;

    // List.shuffle is allowed to return the original permutation. For learning
    // activities that looks as if nothing was shuffled, so force a visible
    // permutation whenever at least two distinguishable values exist.
    for (var i = 0; i < result.length - 1; i++) {
      for (var j = i + 1; j < result.length; j++) {
        if (result[i] == result[j]) continue;
        final tmp = result[i];
        result[i] = result[j];
        result[j] = tmp;
        return result;
      }
    }
    return result;
  }

  /// Shuffles [source] and, when the result can meaningfully differ from
  /// [answerOrder], guarantees it is not left in the solved order.
  static List<T> differentFrom<T>(
    Iterable<T> source,
    List<T> answerOrder, {
    Random? random,
  }) {
    return differentFromBy<T, T>(
      source,
      answerOrder,
      (value) => value,
      random: random,
    );
  }

  /// Variant of [differentFrom] for UI objects whose identity is different
  /// from the value used to grade them. This is important for ordering
  /// exercises that legitimately contain repeated visible tokens: each token
  /// can keep a unique widget key while the grading still compares its text.
  static List<T> differentFromBy<T, K>(
    Iterable<T> source,
    List<K> answerOrder,
    K Function(T value) keyOf, {
    Random? random,
  }) {
    final result = copy<T>(source, random: random);
    if (result.length < 2 || !_sameMappedOrder(result, answerOrder, keyOf)) {
      return result;
    }

    // If the shuffled result accidentally equals the solved order, swap the
    // first two positions whose graded values differ. With duplicate tokens we
    // must not merely swap two visually equal values, because that would still
    // leave the exercise solved.
    for (var i = 0; i < result.length - 1; i++) {
      for (var j = i + 1; j < result.length; j++) {
        if (keyOf(result[i]) == keyOf(result[j])) continue;
        final tmp = result[i];
        result[i] = result[j];
        result[j] = tmp;
        return result;
      }
    }

    // All graded values are identical. There is no meaningful alternative
    // ordering, so returning the shuffled copy is the only honest behavior.
    return result;
  }

  static bool _sameOrder<T>(List<T> left, List<T> right) {
    if (left.length != right.length) return false;
    for (var i = 0; i < left.length; i++) {
      if (left[i] != right[i]) return false;
    }
    return true;
  }

  static bool _sameMappedOrder<T, K>(
    List<T> left,
    List<K> right,
    K Function(T value) keyOf,
  ) {
    if (left.length != right.length) return false;
    for (var i = 0; i < left.length; i++) {
      if (keyOf(left[i]) != right[i]) return false;
    }
    return true;
  }
}
