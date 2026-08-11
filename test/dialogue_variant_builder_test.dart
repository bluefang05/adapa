import 'package:adapa/core/dialogue/dialogue_scene.dart';
import 'package:adapa/core/dialogue/dialogue_variant_builder.dart';
import 'package:adapa/core/dialogue/korean_particle_helper.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('detects batchim for subject particle selection', () {
    expect(KoreanParticleHelper.subjectParticle('은행'), '이');
    expect(KoreanParticleHelper.subjectParticle('학교'), '가');
    expect(KoreanParticleHelper.subjectParticle('카페'), '가');
  });

  test('location variant does not produce 학교이', () {
    const scene = DialogueScene(
      id: 'dlg_location',
      titleEs: 'Ubicación',
      turns: [DialogueTurn(speaker: 'A', ko: '화장실이 어디에 있어요?')],
      replacementSlots: [
        DialogueReplacementSlot(
          slot: 'place',
          base: '화장실',
          allowed: ['학교', '카페', '은행'],
        ),
      ],
    );
    final variant = DialogueVariantBuilder.applySlot(
      scene: scene,
      slot: 'place',
      replacement: '학교',
    );
    expect(variant.turns.single.ko, '학교가 어디에 있어요?');
  });
}
