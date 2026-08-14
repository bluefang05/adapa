import 'dart:io';

import 'package:flutter_test/flutter_test.dart';

void main() {
  test('ordering uses onReorderItem without legacy newIndex decrement', () {
    final source = File(
      'lib/features/activity/renderers/ordering_activity_renderer.dart',
    ).readAsStringSync();

    expect(source.contains('onReorderItem:'), isTrue);
    expect(source.contains('if (newIndex > oldIndex) newIndex -= 1;'), isFalse);
  });
}
