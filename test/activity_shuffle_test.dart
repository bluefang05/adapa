import 'dart:math';

import 'package:adapa/core/practice/activity_shuffle.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('shuffle returns a copy without changing source', () {
    final source = <String>['A', 'B', 'C', 'D'];
    final shuffled = ActivityShuffle.copy(source, random: Random(7));

    expect(source, <String>['A', 'B', 'C', 'D']);
    expect(shuffled.toSet(), source.toSet());
  });

  test('ordering shuffle never leaves a solvable list already solved when avoidable', () {
    final answer = <String>['uno', 'dos', 'tres', 'cuatro'];

    for (var seed = 0; seed < 40; seed++) {
      final shuffled = ActivityShuffle.differentFrom(
        answer,
        answer,
        random: Random(seed),
      );
      expect(shuffled, isNot(equals(answer)));
      expect(shuffled.toSet(), answer.toSet());
    }
  });

  test('copy never returns source order when a different order exists', () {
    const source = <String>['A', 'B', 'C', 'D'];

    for (var seed = 0; seed < 40; seed++) {
      final shuffled = ActivityShuffle.copy<String>(
        source,
        random: Random(seed),
      );
      expect(shuffled, isNot(equals(source)), reason: 'Seed $seed');
      expect(shuffled.toSet(), source.toSet());
    }
  });

  test('differentFromBy handles duplicate visible values without losing identity', () {
    final source = <_Token>[
      const _Token('a1', '가'),
      const _Token('b', '나'),
      const _Token('a2', '가'),
    ];
    final answer = <String>['가', '나', '가'];

    for (var seed = 0; seed < 40; seed++) {
      final shuffled = ActivityShuffle.differentFromBy<_Token, String>(
        source,
        answer,
        (token) => token.value,
        random: Random(seed),
      );
      expect(
        shuffled.map((e) => e.value).toList(),
        isNot(equals(answer)),
        reason: 'Seed $seed dejó el ejercicio ya resuelto.',
      );
      expect(shuffled.map((e) => e.id).toSet(), {'a1', 'a2', 'b'});
    }
  });
}

class _Token {
  const _Token(this.id, this.value);

  final String id;
  final String value;
}
