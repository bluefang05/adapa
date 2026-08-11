import 'package:adapa/core/models/romanization_policy.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('Unit 1 policy is visible by default', () {
    final policy = RomanizationPolicy.fromDynamic('visible_por_defecto');
    expect(policy.mode, RomanizationMode.visible);
    expect(policy.visibleByDefault, isTrue);
    expect(policy.canReveal, isTrue);
  });

  test('hint_only is hidden until requested', () {
    final policy = RomanizationPolicy.fromDynamic({'default': 'hint_only'});
    expect(policy.mode, RomanizationMode.hintOnly);
    expect(policy.visibleByDefault, isFalse);
    expect(policy.canReveal, isTrue);
  });

  test('disabled cannot be revealed', () {
    final policy = RomanizationPolicy.fromDynamic({
      'default': 'disabled',
      'available_as_hint': false,
    });
    expect(policy.mode, RomanizationMode.disabled);
    expect(policy.completelyDisabled, isTrue);
    expect(policy.canReveal, isFalse);
  });
}
