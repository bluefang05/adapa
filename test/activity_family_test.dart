import 'package:adapa/core/models/activity_family.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('every activity family exposes a user-facing label', () {
    expect(ActivityFamily.values, isNotEmpty);
    for (final family in ActivityFamily.values) {
      expect(family.label.trim(), isNotEmpty);
    }
  });

  test('JSON family parsing still maps all production values', () {
    expect(ActivityFamilyParsing.fromJson('choice'), ActivityFamily.choice);
    expect(ActivityFamilyParsing.fromJson('matching'), ActivityFamily.matching);
    expect(ActivityFamilyParsing.fromJson('text_input'), ActivityFamily.textInput);
    expect(ActivityFamilyParsing.fromJson('ordering'), ActivityFamily.ordering);
    expect(ActivityFamilyParsing.fromJson('hangul_structure'), ActivityFamily.hangulStructure);
    expect(ActivityFamilyParsing.fromJson('dialogue'), ActivityFamily.dialogue);
    expect(ActivityFamilyParsing.fromJson('reading_speaking'), ActivityFamily.readingSpeaking);
    expect(ActivityFamilyParsing.fromJson('review'), ActivityFamily.review);
    expect(ActivityFamilyParsing.fromJson('visual_reference'), ActivityFamily.visualReference);
  });
}
