import 'dialogue_scene.dart';
import 'korean_particle_helper.dart';

class DialogueVariantBuilder {
  const DialogueVariantBuilder._();

  static DialogueScene applySlot({
    required DialogueScene scene,
    required String slot,
    required String replacement,
  }) {
    DialogueReplacementSlot? definition;
    for (final item in scene.replacementSlots) {
      if (item.slot == slot) {
        definition = item;
        break;
      }
    }
    if (definition == null || definition.base.isEmpty) return scene;

    final base = definition.base;
    return DialogueScene(
      id: scene.id,
      titleEs: scene.titleEs,
      source: scene.source,
      scored: scene.scored,
      replacementSlots: scene.replacementSlots,
      turns: scene.turns
          .map(
            (turn) => turn.copyWith(
              ko: _replaceKoreanSlot(turn.ko, base, replacement),
            ),
          )
          .toList(growable: false),
    );
  }

  static String _replaceKoreanSlot(
    String text,
    String base,
    String replacement,
  ) {
    // Some catalog slots occur immediately before the subject particle.
    // A blind string replacement would produce 학교이 / 카페이. Preserve
    // grammatical particle choice when the source token is base+이/가.
    final subjectTokenI = '$base이';
    final subjectTokenGa = '$base가';
    final replacementSubject =
        '$replacement${KoreanParticleHelper.subjectParticle(replacement)}';

    var result = text;
    if (result.contains(subjectTokenI)) {
      result = result.replaceAll(subjectTokenI, replacementSubject);
    }
    if (result.contains(subjectTokenGa)) {
      result = result.replaceAll(subjectTokenGa, replacementSubject);
    }
    return result.replaceAll(base, replacement);
  }
}
