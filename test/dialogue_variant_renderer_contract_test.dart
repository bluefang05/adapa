import 'dart:io';

import 'package:flutter_test/flutter_test.dart';

void main() {
  test('dialogue_variant shuffles and never injects Map.toString as Korean', () {
    final source = File(
      'lib/features/activity/renderers/dialogue_activity_renderer.dart',
    ).readAsStringSync();

    expect(
      source.contains("import '../../../core/practice/activity_shuffle.dart';"),
      isTrue,
    );
    expect(source.contains('ActivityShuffle.copy<dynamic>('), isTrue);
    expect(
      source.contains(
        'final choice = _selected == null ? null : _valueOf(_selected);',
      ),
      isTrue,
    );
    expect(source.contains('final choice = _selected?.toString();'), isFalse);
    expect(source.contains('label: Text(_labelOf(option)),'), isTrue);
    expect(source.contains('selected: choice == _valueOf(option),'), isTrue);
  });
}
