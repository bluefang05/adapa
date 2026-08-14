import 'dart:convert';
import 'dart:io';

import 'package:adapa/core/content/asset_course_repository.dart';
import 'package:adapa/core/content/asset_resolver.dart';
import 'package:adapa/core/dialogue/dialogue_scene.dart';
import 'package:adapa/core/hangul/hangul_composer.dart';
import 'package:adapa/core/models/activity_content.dart';
import 'package:adapa/core/models/activity_family.dart';
import 'package:adapa/features/activity/activity_renderer_registry.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  test(
    'all production exercises are structurally complete and references resolve',
    () async {
      final repository = AssetCourseRepository();
      final resolver = AssetResolver(repository);
      final manifest = await repository.loadManifest();
      final activities = <String, ActivityContent>{};
      final locations = <String, String>{};
      final unitPracticeCounts = <String, int>{};
      var lessonCount = 0;

      for (final summary in manifest.units) {
        final unit = await repository.loadUnit(summary);
        expect(
          unit.lessons.length,
          summary.lessonCount,
          reason: '${unit.id}: lessonCount del manifest no coincide.',
        );
        lessonCount += unit.lessons.length;
        var practiceCount = 0;

        for (final lesson in unit.lessons) {
          for (final activity in lesson.activities) {
            final previous = activities[activity.id];
            expect(
              previous,
              isNull,
              reason: 'ID de actividad duplicado: ${activity.id}',
            );
            activities[activity.id] = activity;
            locations[activity.id] = '${unit.id}/${lesson.id}';
            if (_isReusablePractice(activity)) practiceCount += 1;
          }
        }
        unitPracticeCounts[unit.id] = practiceCount;
      }

      final audit = _AuditCollector();
      await _auditRawContentJson(audit);
      for (final entry in activities.entries) {
        await _auditActivity(
          entry.value,
          resolver: resolver,
          activities: activities,
          location: locations[entry.key] ?? '?',
          audit: audit,
        );
        audit.currentActivityId = null;
      }

      for (final entry in unitPracticeCounts.entries) {
        if (entry.value == 0) {
          audit.warn(
            '${entry.key}: no tiene ejercicios reutilizables de selección, '
            'emparejamiento, orden o Hangul para práctica libre.',
          );
        }
      }

      await _scanPotentialMojibake(audit);

      // Evidence that the complete production corpus was traversed. These are
      // contractual counts already asserted elsewhere, repeated here so the
      // audit log cannot silently cover only a subset of the course.
      expect(manifest.units.length, 8);
      expect(lessonCount, 36);
      expect(activities.length, 171);

      audit.printReport(
        units: manifest.units.length,
        lessons: lessonCount,
        activities: activities.length,
      );

      expect(
        audit.problems,
        isEmpty,
        reason: 'Se encontraron ejercicios incompletos o referencias rotas:\n'
            '${audit.problems.map((e) => '  - $e').join('\n')}',
      );
    },
  );
}

Future<void> _auditActivity(
  ActivityContent activity, {
  required AssetResolver resolver,
  required Map<String, ActivityContent> activities,
  required String location,
  required _AuditCollector audit,
}) async {
  final id = '${activity.id} (${activity.type}) [$location]';
  audit.currentActivityId = activity.id;
  audit.visited.add(activity.id);
  audit.activityLocations[activity.id] = location;
  audit.activityTypeCounts[activity.type] =
      (audit.activityTypeCounts[activity.type] ?? 0) + 1;
  if (_isShuffledPresentation(activity)) {
    audit.shuffledTypes.add(activity.type);
  }

  if (activity.id.trim().isEmpty) {
    audit.problem('Actividad sin id en $location.');
    return;
  }
  if (activity.type.trim().isEmpty) {
    audit.problem('$id: type vacío.');
    return;
  }
  if ((activity.prompt?.trim().isEmpty ?? true) &&
      (activity.objective?.trim().isEmpty ?? true)) {
    audit.warn('$id: no tiene prompt ni objective visible.');
  }

  final registeredFamily = ActivityRendererRegistry.typeToFamily[activity.type];
  if (registeredFamily == null) {
    audit.problem(
      '$id: el tipo no está registrado; podría terminar en un renderer no soportado.',
    );
  } else if (registeredFamily != activity.family) {
    audit.problem(
      '$id: el registro lo clasifica como ${registeredFamily.name}, pero el '
      'contenido declara ${activity.family.name}.',
    );
  }

  if (activity.family == ActivityFamily.choice) {
    await _auditChoice(activity, resolver, audit, id);
  } else if (activity.family == ActivityFamily.matching) {
    _auditMatching(activity, audit, id);
  } else if (activity.family == ActivityFamily.textInput) {
    _auditTextInput(activity, audit, id);
  } else if (activity.family == ActivityFamily.ordering) {
    _auditOrdering(activity, audit, id);
  } else if (activity.family == ActivityFamily.hangulStructure) {
    _auditHangul(activity, audit, id);
  } else if (activity.family == ActivityFamily.dialogue) {
    await _auditDialogue(activity, resolver, activities, audit, id);
  } else if (activity.family == ActivityFamily.readingSpeaking) {
    await _auditReading(activity, resolver, activities, audit, id);
  } else if (activity.family == ActivityFamily.review) {
    await _auditReview(activity, resolver, activities, audit, id);
  } else if (activity.family == ActivityFamily.visualReference) {
    await _auditVisual(activity, resolver, audit, id);
  }
}

Future<void> _auditChoice(
  ActivityContent activity,
  AssetResolver resolver,
  _AuditCollector audit,
  String id,
) async {
  final rawOptions = activity.payload['options'] as List? ?? const [];
  final options = rawOptions.map(_choiceValue).toList(growable: false);
  final labels = rawOptions.map(_choiceLabel).toList(growable: false);
  final correct = (activity.payload['correct'] as List? ?? const [])
      .map((e) => e.toString().trim())
      .where((e) => e.isNotEmpty)
      .toList(growable: false);

  if (options.length < 2) {
    audit.problem('$id: selección con menos de 2 opciones (${options.length}).');
  }
  if (options.any((e) => e.isEmpty)) {
    audit.problem('$id: contiene una opción sin valor evaluable.');
  }
  if (activity.type != 'image_choice' && labels.any((e) => e.isEmpty)) {
    audit.problem('$id: contiene una opción sin etiqueta visible.');
  }
  if (options.toSet().length != options.length) {
    audit.problem(
      '$id: contiene opciones con el mismo valor evaluable; el tap sería ambiguo.',
    );
  }
  if (correct.isEmpty) {
    audit.problem('$id: no tiene respuesta correcta configurada.');
  }
  if (correct.toSet().length != correct.length) {
    audit.problem('$id: correct contiene respuestas duplicadas.');
  }
  for (final answer in correct) {
    if (!options.contains(answer)) {
      audit.problem('$id: respuesta correcta "$answer" no aparece en options.');
    }
  }
  if (options.length > 1 && correct.toSet().containsAll(options.toSet())) {
    audit.problem('$id: todas las opciones son correctas; no hay distractor.');
  }
  final explicitMultiSelect =
      activity.payload['selection_mode']?.toString() == 'multiple' ||
      activity.payload['select_all_correct'] == true;
  if (explicitMultiSelect && correct.length < 2) {
    audit.problem(
      '$id: declara selección múltiple pero tiene menos de 2 respuestas correctas.',
    );
  }
  if (explicitMultiSelect) {
    audit.multiAnswerChoices.add(
      '$id: selección múltiple explícita con ${correct.length} respuestas de ${options.length} opciones.',
    );
  } else if (correct.length > 1) {
    audit.warn(
      '$id: tiene ${correct.length} valores en correct sin selection_mode=multiple; '
      'se conservan como respuestas alternativas (una sola basta).',
    );
  }

  if (activity.type == 'listen_and_choose') {
    final tts = activity.payload['tts'];
    final text = tts is Map ? tts['text']?.toString().trim() ?? '' : '';
    if (text.isEmpty) {
      audit.problem('$id: listen_and_choose sin texto TTS.');
    }
  }

  if (activity.type == 'image_choice') {
    for (final optionId in options) {
      final path = await resolver.visualAsset(optionId);
      if (path == null || path.trim().isEmpty) {
        audit.problem('$id: image_choice referencia visual inexistente "$optionId".');
        continue;
      }
      await _expectAssetExists(path, audit, '$id: imagen de "$optionId"');
    }
  }
}

void _auditMatching(
  ActivityContent activity,
  _AuditCollector audit,
  String id,
) {
  final rawPairs = activity.payload['pairs'] as List? ?? const [];
  if (rawPairs.length < 2) {
    audit.problem('$id: emparejamiento con menos de 2 parejas.');
    return;
  }

  final pairSignatures = <String>[];
  for (var i = 0; i < rawPairs.length; i++) {
    final raw = rawPairs[i];
    if (raw is! Map) {
      audit.problem('$id: pareja ${i + 1} no es un objeto válido.');
      continue;
    }
    final pair = Map<String, dynamic>.from(raw);
    final left = pair['left']?.toString().trim() ?? '';
    final right = pair['right']?.toString().trim() ?? '';
    if (left.isEmpty || right.isEmpty) {
      audit.problem('$id: pareja ${i + 1} tiene un lado vacío.');
    }
    pairSignatures.add('$left\u0000$right');
  }

  if (pairSignatures.toSet().length != pairSignatures.length) {
    audit.problem('$id: contiene una pareja idéntica duplicada.');
  }

  // Repeated right-side labels are handled as interchangeable visible answers.
  // Repeated left-side labels remain ambiguous because the learner cannot know
  // which identical prompt expects which different partner.
  final leftValues = <String>[];
  final rightValues = <String>[];
  for (final raw in rawPairs.whereType<Map>()) {
    final pair = Map<String, dynamic>.from(raw);
    leftValues.add(pair['left']?.toString().trim() ?? '');
    rightValues.add(pair['right']?.toString().trim() ?? '');
  }
  if (leftValues.toSet().length != leftValues.length) {
    audit.problem('$id: repite etiquetas a la izquierda y crea parejas visualmente ambiguas.');
  }
  if (rightValues.toSet().length != rightValues.length) {
    audit.warn('$id: repite etiquetas a la derecha; el renderer las trata como respuestas visualmente equivalentes.');
  }
}

void _auditTextInput(
  ActivityContent activity,
  _AuditCollector audit,
  String id,
) {
  if (activity.scoreMode == 'none') {
    if (activity.completionRule == 'five_non_empty_lines') {
      final required = (activity.payload['required_lines'] as num?)?.toInt() ?? 5;
      if (required < 2) {
        audit.problem('$id: práctica multilínea configurada para solo $required línea.');
      }
    }
    return;
  }

  if (activity.scoreMode == 'structural') {
    final validation = Map<String, dynamic>.from(
      activity.payload['validation'] as Map? ?? const {},
    );
    const meaningfulKeys = <String>{
      'must_start_with',
      'must_end_with',
      'must_end_with_one_of',
      'must_contain_hangul',
      'minimum_content_before_pattern',
    };
    if (!validation.keys.any(meaningfulKeys.contains)) {
      audit.problem('$id: evaluación structural sin reglas de validación.');
    }

    final start = validation['must_start_with']?.toString().trim();
    final end = validation['must_end_with']?.toString().trim();
    final ends = _stringList(validation['must_end_with_one_of']);
    if (start != null && start.isEmpty) {
      audit.problem('$id: must_start_with está vacío.');
    }
    if (end != null && end.isEmpty) {
      audit.problem('$id: must_end_with está vacío.');
    }
    if ((validation['must_end_with_one_of'] is List) && ends.isEmpty) {
      audit.problem('$id: must_end_with_one_of existe pero no tiene valores válidos.');
    }
    final minimum = (validation['minimum_content_before_pattern'] as num?)?.toInt();
    if (minimum != null && minimum < 1) {
      audit.problem('$id: minimum_content_before_pattern debe ser al menos 1.');
    }
    return;
  }

  final answers = (activity.payload['accepted_answers'] as List? ?? const [])
      .map((e) => e.toString().trim())
      .toList(growable: false);
  if (answers.isEmpty) {
    audit.problem('$id: actividad evaluable sin accepted_answers.');
    return;
  }
  if (answers.any((e) => e.isEmpty)) {
    audit.problem('$id: accepted_answers contiene una respuesta vacía.');
  }
  if (answers.toSet().length != answers.length) {
    audit.warn('$id: accepted_answers contiene duplicados literales.');
  }

  final nonEmpty = answers.where((e) => e.isNotEmpty).toList(growable: false);
  final oneChar = nonEmpty.where((e) => e.runes.length == 1).toList(growable: false);
  if (oneChar.isNotEmpty) {
    final context = [
      activity.prompt ?? '',
      activity.objective ?? '',
      activity.note ?? '',
    ].join(' ').toLowerCase();
    final looksCharacterFocused = <String>[
      'carácter',
      'caracter',
      'letra',
      'jamo',
      'sílaba',
      'silaba',
      'vocal',
      'consonante',
      'batchim',
      'bloque',
    ].any(context.contains);

    final allAnswersAreOneChar = oneChar.length == nonEmpty.length;
    final record = '$id: ${oneChar.join(' / ')}';
    if (looksCharacterFocused) {
      audit.oneCharLikelyLegit.add(record);
      audit.warn('$record — respuesta de un carácter coherente con el enunciado.');
    } else if (allAnswersAreOneChar) {
      audit.oneCharNeedsReview.add(record);
      audit.warn(
        '$record — TODAS las respuestas son de un carácter y el enunciado no '
        'parece pedir explícitamente una letra/jamo/sílaba; revisar posible truncado.',
      );
    } else {
      audit.oneCharNeedsReview.add(record);
      audit.warn(
        '$record — hay respuestas de un carácter mezcladas con respuestas más '
        'largas; confirmar que sean alternativas equivalentes y no datos truncados.',
      );
    }
  }
}

void _auditOrdering(
  ActivityContent activity,
  _AuditCollector audit,
  String id,
) {
  final source = activity.payload['tokens'] ?? activity.payload['turns'];
  final items = (source as List? ?? const [])
      .map((e) => e.toString().trim())
      .toList(growable: false);
  final answer = (activity.payload['correct_order'] as List? ?? const [])
      .map((e) => e.toString().trim())
      .toList(growable: false);

  if (items.length < 2) {
    audit.problem('$id: ejercicio de ordenar con menos de 2 elementos.');
  }
  if (items.any((e) => e.isEmpty) || answer.any((e) => e.isEmpty)) {
    audit.problem('$id: contiene elementos vacíos.');
  }
  if (answer.length != items.length) {
    audit.problem(
      '$id: correct_order tiene ${answer.length} elementos pero el ejercicio '
      'muestra ${items.length}.',
    );
  } else if (!_sameMultiset(items, answer)) {
    audit.problem('$id: correct_order no contiene exactamente el mismo multiconjunto de tokens.');
  }

  final unique = items.toSet();
  if (items.length > 1 && unique.length == 1) {
    audit.problem('$id: todos los tokens son idénticos; no existe un orden significativo.');
  } else if (unique.length != items.length) {
    audit.warn(
      '$id: contiene tokens repetidos. El renderer usa IDs únicos y los soporta; '
      'confirmar que la repetición sea intencional.',
    );
  }
}

void _auditHangul(
  ActivityContent activity,
  _AuditCollector audit,
  String id,
) {
  if (activity.type == 'syllable_builder') {
    final parts = (activity.payload['parts'] as List? ?? const [])
        .map((e) => e.toString().trim())
        .toList(growable: false);
    final answer = activity.payload['correct_block']?.toString().trim() ?? '';
    if (parts.length != 2 && parts.length != 3) {
      audit.problem('$id: syllable_builder necesita 2 o 3 componentes; tiene ${parts.length}.');
    }
    if (parts.any((e) => e.isEmpty) || answer.isEmpty) {
      audit.problem('$id: componentes o correct_block vacíos.');
    } else if (parts.length == 2 || parts.length == 3) {
      final composed = HangulComposer.compose(parts);
      if (composed != answer) {
        audit.problem('$id: parts componen "$composed", no "$answer".');
      }
    }
    return;
  }

  if (activity.type == 'batchim_finder') {
    _auditSelector(
      id,
      _stringList(activity.payload['components']),
      [activity.payload['correct']?.toString().trim() ?? ''],
      audit,
    );
    return;
  }

  if (activity.type == 'batchim_finder_multi') {
    final blocks = activity.payload['blocks'] as List? ?? const [];
    final items = <String>[];
    for (final raw in blocks) {
      if (raw is Map) {
        items.add(Map<String, dynamic>.from(raw)['text']?.toString().trim() ?? '');
      } else {
        items.add('');
      }
    }
    _auditSelector(id, items, _stringList(activity.payload['correct']), audit);
    return;
  }

  if (activity.type == 'highlight_token') {
    _auditSelector(
      id,
      _stringList(activity.payload['sentence_tokens']),
      _stringList(activity.payload['correct_tokens']),
      audit,
    );
    return;
  }

  if (activity.type == 'hangul_recall_grid') {
    final rawItems = activity.payload['items'] as List? ?? const [];
    if (rawItems.length < 2) {
      audit.problem('$id: cuadrícula con menos de 2 elementos (${rawItems.length}).');
    }
    final cues = <String>[];
    for (final raw in rawItems) {
      if (raw is! Map) {
        audit.problem('$id: fila de recuerdo inválida.');
        continue;
      }
      final item = Map<String, dynamic>.from(raw);
      final cue = item['cue']?.toString().trim() ?? '';
      final answer = item['answer']?.toString().trim() ?? '';
      if (cue.isEmpty || answer.isEmpty) {
        audit.problem('$id: fila con cue o answer vacío.');
      }
      cues.add(cue);
    }
    if (cues.toSet().length != cues.length) {
      audit.problem('$id: cues duplicados en hangul_recall_grid.');
    }
  }
}

void _auditSelector(
  String id,
  List<String> items,
  List<String> answers,
  _AuditCollector audit,
) {
  final cleanAnswers = answers.where((e) => e.trim().isNotEmpty).toList(growable: false);
  if (items.length < 2) audit.problem('$id: selector con menos de 2 opciones.');
  if (items.any((e) => e.trim().isEmpty)) audit.problem('$id: selector con opción vacía.');
  if (items.toSet().length != items.length) audit.problem('$id: selector con opciones duplicadas.');
  if (cleanAnswers.isEmpty) audit.problem('$id: selector sin respuesta correcta.');
  for (final answer in cleanAnswers) {
    if (!items.contains(answer)) audit.problem('$id: respuesta "$answer" no está entre las opciones.');
  }
  if (items.length > 1 && cleanAnswers.toSet().containsAll(items.toSet())) {
    audit.problem('$id: todas las opciones son correctas; no hay distractor.');
  }
}

Future<void> _auditDialogue(
  ActivityContent activity,
  AssetResolver resolver,
  Map<String, ActivityContent> activities,
  _AuditCollector audit,
  String id,
) async {
  if (activity.type == 'dialogue') {
    final turns = activity.payload['turns'] as List? ?? const [];
    if (turns.length < 2) audit.problem('$id: diálogo con menos de 2 turnos.');
    for (var i = 0; i < turns.length; i++) {
      final raw = turns[i];
      if (raw is! Map) {
        audit.problem('$id: turno ${i + 1} inválido.');
        continue;
      }
      final turn = Map<String, dynamic>.from(raw);
      final speaker = turn['speaker']?.toString().trim() ?? '';
      if (speaker.isEmpty) audit.problem('$id: turno ${i + 1} sin speaker.');
      if (speaker == 'user') {
        final accepted = turn['accepted'] as List? ?? const [];
        if (activity.scoreMode != 'none' && accepted.isEmpty) {
          audit.problem('$id: turno de usuario ${i + 1} evaluable sin accepted.');
        }
      } else if ((turn['ko']?.toString().trim() ?? '').isEmpty) {
        audit.problem('$id: turno ${i + 1} sin texto ko.');
      }
    }
    return;
  }

  if (activity.type == 'dialogue_variant') {
    final choices = activity.payload['choices'] as List? ?? const [];
    if (choices.length < 2) audit.problem('$id: variante con menos de 2 opciones.');
    final choiceValues = choices.map(_choiceValue).toList(growable: false);
    if (choiceValues.any((e) => e.isEmpty)) {
      audit.problem('$id: variante de diálogo contiene una opción sin valor.');
    }
    if (choiceValues.toSet().length != choiceValues.length) {
      audit.problem('$id: variante de diálogo contiene opciones duplicadas.');
    }
    final ref = activity.payload['dialogue_ref']?.toString().trim() ?? '';
    if (ref.isEmpty) return; // standalone variants are supported.
    final scene = await resolver.dialogueScene(ref);
    if (scene == null) {
      audit.problem('$id: dialogue_ref inexistente "$ref".');
      return;
    }
    final slot = activity.payload['slot']?.toString().trim() ?? '';
    if (slot.isEmpty) {
      audit.problem('$id: variante con dialogue_ref pero sin slot.');
      return;
    }
    DialogueReplacementSlot? definition;
    for (final item in scene.replacementSlots) {
      if (item.slot == slot) {
        definition = item;
        break;
      }
    }
    if (definition == null) {
      audit.problem('$id: slot "$slot" no existe en escena "$ref".');
      return;
    }
    for (final value in choiceValues) {
      if (definition.allowed.isNotEmpty && !definition.allowed.contains(value)) {
        audit.warn('$id: variante "$value" no figura en allowed del slot "$slot".');
      }
    }
    return;
  }

  if (activity.type == 'dialogue_roleplay') {
    final ref = activity.payload['dialogue_ref']?.toString().trim() ?? '';
    if (ref.isEmpty) {
      audit.problem('$id: roleplay sin dialogue_ref.');
      return;
    }
    final scene = await resolver.dialogueScene(ref);
    if (scene == null) {
      audit.problem('$id: dialogue_ref inexistente "$ref".');
      return;
    }
    final required = (activity.payload['required_turns'] as List? ?? const [])
        .whereType<num>()
        .map((e) => e.toInt())
        .toList(growable: false);
    if (required.isEmpty) audit.problem('$id: roleplay sin required_turns.');
    for (final index in required) {
      if (index < 0 || index >= scene.turns.length) {
        audit.problem('$id: required_turn $index fuera de rango (0-${scene.turns.length - 1}).');
      }
    }
    return;
  }

  if (activity.type == 'guided_dialogue_fill') {
    final fields = activity.payload['fields'] as List? ?? const [];
    if (fields.isEmpty) audit.problem('$id: diálogo guiado sin fields.');
    final fieldIds = <String>[];
    for (final raw in fields.whereType<Map>()) {
      final field = Map<String, dynamic>.from(raw);
      final fieldId = field['id']?.toString().trim() ?? '';
      if (fieldId.isEmpty) audit.problem('$id: field sin id.');
      fieldIds.add(fieldId);
      if (field['scored'] == true && (field['accepted'] as List? ?? const []).isEmpty) {
        audit.problem('$id: field "$fieldId" scored sin accepted.');
      }
    }
    if (fieldIds.toSet().length != fieldIds.length) audit.problem('$id: field ids duplicados.');
    final ref = activity.payload['dialogue_ref']?.toString().trim() ?? '';
    if (ref.isNotEmpty && await resolver.dialogueScene(ref) == null) {
      audit.problem('$id: dialogue_ref inexistente "$ref".');
    }
    return;
  }

  if (activity.type == 'final_guided_conversation') {
    final turns = activity.payload['turns'] as List? ?? const [];
    if (turns.isEmpty) {
      audit.problem('$id: conversación final sin turnos.');
      return;
    }
    final fieldIds = <String>[];
    for (final raw in turns.whereType<Map>()) {
      final turn = Map<String, dynamic>.from(raw);
      final fieldId = turn['field_id']?.toString().trim();
      if (fieldId == null || fieldId.isEmpty) continue;
      fieldIds.add(fieldId);
      if (turn['field_type']?.toString() == 'controlled' &&
          (turn['accepted'] as List? ?? const []).isEmpty) {
        audit.problem('$id: campo controlled "$fieldId" sin accepted.');
      }
    }
    if (fieldIds.isEmpty) audit.problem('$id: conversación final sin campos evaluables.');
    if (fieldIds.toSet().length != fieldIds.length) audit.problem('$id: field_id duplicado.');
    return;
  }

  if (activity.type == 'tts_dialogue_playback') {
    final source = activity.payload['source_activity']?.toString().trim() ?? '';
    final sourceActivity = activities[source];
    if (source.isEmpty || sourceActivity == null) {
      audit.problem('$id: source_activity inexistente "$source".');
      return;
    }
    const compatible = <String>{
      'dialogue',
      'dialogue_roleplay',
      'final_guided_conversation',
    };
    if (!compatible.contains(sourceActivity.type)) {
      audit.problem('$id: source_activity "$source" (${sourceActivity.type}) no produce playback_lines.');
    }
  }
}

Future<void> _auditReading(
  ActivityContent activity,
  AssetResolver resolver,
  Map<String, ActivityContent> activities,
  _AuditCollector audit,
  String id,
) async {
  if (activity.type == 'reading_challenge' || activity.type == 'reading_aloud') {
    final ref = activity.payload['reading_ref']?.toString().trim() ?? '';
    final items = activity.payload['items'] as List? ?? const [];
    final tts = activity.payload['tts'];
    if (ref.isEmpty && items.isEmpty && tts == null) {
      audit.problem('$id: no tiene reading_ref, items ni TTS.');
    }
    if (ref.isNotEmpty) {
      final reading = await resolver.reading(ref);
      if (reading == null || (reading['text_ko']?.toString().trim() ?? '').isEmpty) {
        audit.problem('$id: reading_ref inexistente o vacío "$ref".');
      }
    }
    return;
  }

  if (activity.type == 'speaking_practice') {
    final items = _stringList(activity.payload['items']);
    if (items.isEmpty) audit.problem('$id: speaking_practice sin items.');
    if (items.any((e) => e.isEmpty)) audit.problem('$id: speaking_practice con item vacío.');
    return;
  }

  if (activity.type == 'tts_readback') {
    final source = activity.payload['source_activity']?.toString().trim() ?? '';
    final sourceActivity = activities[source];
    if (source.isEmpty || sourceActivity == null) {
      audit.problem('$id: source_activity inexistente "$source".');
    } else if (sourceActivity.family != ActivityFamily.textInput) {
      audit.warn('$id: tts_readback apunta a ${sourceActivity.type}, no a textInput.');
    }
  }
}

Future<void> _auditReview(
  ActivityContent activity,
  AssetResolver resolver,
  Map<String, ActivityContent> activities,
  _AuditCollector audit,
  String id,
) async {
  if (activity.type == 'known_block_marking') {
    final ref = activity.payload['reading_ref']?.toString().trim() ?? '';
    if (ref.isEmpty || await resolver.reading(ref) == null) {
      audit.problem('$id: reading_ref inexistente "$ref".');
    }
    return;
  }

  if (activity.type == 'self_review' ||
      activity.type == 'self_check' ||
      activity.type == 'progress_reflection' ||
      activity.type == 'final_self_review') {
    final raw = activity.payload['checks'] ?? activity.payload['items'];
    final items = (raw as List? ?? const []).map((e) => e.toString().trim()).toList();
    if (items.isEmpty) audit.problem('$id: revisión sin items/checks.');
    if (items.any((e) => e.isEmpty)) audit.problem('$id: revisión contiene item vacío.');
    return;
  }

  if (activity.type == 'answer_key_review') {
    final ref = activity.payload['theory_ref']?.toString().trim() ?? '';
    if (ref.isEmpty || await resolver.contentBlock(ref) == null) {
      audit.problem('$id: theory_ref inexistente "$ref".');
    }
    return;
  }

  if (activity.type == 'scenario_recall') {
    final refs = _stringList(activity.payload['scenario_activity_refs']);
    if (refs.isEmpty) audit.problem('$id: scenario_recall sin referencias.');
    for (final ref in refs) {
      if (!activities.containsKey(ref)) audit.problem('$id: escenario inexistente "$ref".');
    }
  }
}

Future<void> _auditVisual(
  ActivityContent activity,
  AssetResolver resolver,
  _AuditCollector audit,
  String id,
) async {
  if (activity.type != 'stroke_viewer') return;
  final setIds = _stringList(activity.payload['asset_sets']);
  if (setIds.isEmpty) {
    audit.problem('$id: stroke_viewer sin asset_sets.');
    return;
  }
  for (final setId in setIds) {
    final set = await resolver.strokeSet(setId);
    if (set == null) {
      audit.problem('$id: stroke set inexistente "$setId".');
      continue;
    }
    final steps = _stringList(set['steps']);
    final finalAsset = set['final']?.toString().trim() ?? '';
    if (steps.isEmpty || finalAsset.isEmpty) {
      audit.problem('$id: stroke set "$setId" sin steps o final.');
    }
    for (final path in [...steps, if (finalAsset.isNotEmpty) finalAsset]) {
      await _expectAssetExists(path, audit, '$id: stroke "$setId"');
    }
  }
}

Future<void> _expectAssetExists(
  String assetPath,
  _AuditCollector audit,
  String context,
) async {
  try {
    await rootBundle.load(assetPath);
  } catch (_) {
    audit.problem('$context apunta a asset inexistente "$assetPath".');
  }
}

Future<void> _auditRawContentJson(_AuditCollector audit) async {
  const targets = <String>[
    'assets/content/course_manifest.json',
    'assets/content/units',
    'assets/content/resources',
    'assets/content/schema',
  ];

  final files = <File>[];
  for (final target in targets) {
    final file = File(target);
    if (file.existsSync()) {
      files.add(file);
      continue;
    }
    final directory = Directory(target);
    if (!directory.existsSync()) {
      audit.problem('Ruta de contenido requerida inexistente: $target');
      continue;
    }
    await for (final entity in directory.list(recursive: true, followLinks: false)) {
      if (entity is File && entity.path.toLowerCase().endsWith('.json')) {
        files.add(entity);
      }
    }
  }

  files.sort((a, b) => a.path.compareTo(b.path));
  if (files.isEmpty) {
    audit.problem('No se encontraron JSON de contenido para auditar.');
    return;
  }

  for (final file in files) {
    final path = file.path.replaceAll('\\', '/');
    try {
      final raw = await file.readAsString();
      jsonDecode(raw);
      audit.rawJsonFiles.add(path);
    } on FormatException catch (error) {
      audit.problem('$path: JSON inválido (${error.message}).');
    } catch (error) {
      audit.problem('$path: no se pudo leer/decodificar ($error).');
    }
  }
}

Future<void> _scanPotentialMojibake(_AuditCollector audit) async {
  const suspicious = <String>['├', 'ΓÇ', 'φò', '∞¥', 'πà', 'πä', '┬', '╕Ω'];
  const roots = <String>['lib', 'assets/content'];
  for (final root in roots) {
    final directory = Directory(root);
    if (!directory.existsSync()) continue;
    await for (final entity in directory.list(recursive: true, followLinks: false)) {
      if (entity is! File) continue;
      final path = entity.path.toLowerCase();
      if (!(path.endsWith('.dart') || path.endsWith('.json') || path.endsWith('.yaml'))) {
        continue;
      }
      String text;
      try {
        text = await entity.readAsString();
      } catch (_) {
        continue;
      }
      final hits = suspicious.where(text.contains).toList(growable: false);
      if (hits.isNotEmpty) {
        audit.encodingWarnings.add('${entity.path}: ${hits.join(', ')}');
      }
    }
  }
  for (final warning in audit.encodingWarnings) {
    audit.warn('Posible mojibake: $warning');
  }
}

String _choiceValue(dynamic option) {
  if (option is Map) {
    final map = Map<String, dynamic>.from(option);
    return (map['ko'] ?? map['value'] ?? map['id'] ?? '').toString().trim();
  }
  return option.toString().trim();
}

String _choiceLabel(dynamic option) {
  if (option is Map) {
    final map = Map<String, dynamic>.from(option);
    final ko = map['ko']?.toString().trim();
    final es = map['es']?.toString().trim();
    if (ko != null && ko.isNotEmpty && es != null && es.isNotEmpty) {
      return '$ko\n$es';
    }
    return (map['label_es'] ??
            map['label'] ??
            map['ko'] ??
            map['value'] ??
            map['id'] ??
            map['es'] ??
            '')
        .toString()
        .trim();
  }
  return option.toString().trim();
}

List<String> _stringList(dynamic raw) => (raw as List? ?? const [])
    .map((e) => e.toString().trim())
    .toList(growable: false);

bool _sameMultiset(List<String> left, List<String> right) {
  if (left.length != right.length) return false;
  final counts = <String, int>{};
  for (final value in left) {
    counts[value] = (counts[value] ?? 0) + 1;
  }
  for (final value in right) {
    final count = counts[value] ?? 0;
    if (count == 0) return false;
    if (count == 1) {
      counts.remove(value);
    } else {
      counts[value] = count - 1;
    }
  }
  return counts.isEmpty;
}

bool _isReusablePractice(ActivityContent activity) =>
    activity.family == ActivityFamily.choice ||
    activity.family == ActivityFamily.matching ||
    activity.family == ActivityFamily.ordering ||
    activity.family == ActivityFamily.hangulStructure;

bool _isShuffledPresentation(ActivityContent activity) {
  if (activity.family == ActivityFamily.choice ||
      activity.family == ActivityFamily.matching ||
      activity.family == ActivityFamily.ordering) {
    return true;
  }
  if (activity.family != ActivityFamily.hangulStructure) return false;
  return const <String>{
    'syllable_builder',
    'batchim_finder',
    'batchim_finder_multi',
    'highlight_token',
    'hangul_recall_grid',
  }.contains(activity.type);
}

class _AuditCollector {
  String? currentActivityId;
  final List<String> problems = <String>[];
  final List<String> warnings = <String>[];
  final Set<String> visited = <String>{};
  final List<String> oneCharLikelyLegit = <String>[];
  final List<String> oneCharNeedsReview = <String>[];
  final List<String> encodingWarnings = <String>[];
  final List<String> multiAnswerChoices = <String>[];
  final List<String> rawJsonFiles = <String>[];
  final Map<String, int> activityTypeCounts = <String, int>{};
  final Set<String> shuffledTypes = <String>{};
  final Set<String> problemActivityIds = <String>{};
  final Set<String> warningActivityIds = <String>{};
  final Map<String, String> activityLocations = <String, String>{};

  void problem(String message) {
    problems.add(message);
    final id = currentActivityId;
    if (id != null) problemActivityIds.add(id);
  }

  void warn(String message) {
    warnings.add(message);
    final id = currentActivityId;
    if (id != null) warningActivityIds.add(id);
  }

  void printReport({
    required int units,
    required int lessons,
    required int activities,
  }) {
    // ignore: avoid_print
    print('\n============================================================');
    // ignore: avoid_print
    print('ADAPA CONTENT AUDIT - INFORME');
    // ignore: avoid_print
    print('============================================================');
    // ignore: avoid_print
    print('Unidades recorridas: $units');
    // ignore: avoid_print
    print('Lecciones recorridas: $lessons');
    // ignore: avoid_print
    print('Actividades recorridas: $activities');
    // ignore: avoid_print
    print('IDs únicos auditados: ${visited.length}');
    // ignore: avoid_print
    print('Errores bloqueantes: ${problems.length}');
    // ignore: avoid_print
    print('Advertencias: ${warnings.length}');
    // ignore: avoid_print
    print('Actividades con error bloqueante: ${problemActivityIds.length}');
    // ignore: avoid_print
    print('Actividades sin error bloqueante: ${activities - problemActivityIds.length}');
    // ignore: avoid_print
    print('Actividades con advertencias: ${warningActivityIds.length}');
    // ignore: avoid_print
    print('Respuestas de 1 carácter probablemente legítimas: ${oneCharLikelyLegit.length}');
    // ignore: avoid_print
    print('Respuestas de 1 carácter a revisar: ${oneCharNeedsReview.length}');
    // ignore: avoid_print
    print('Archivos con posible mojibake: ${encodingWarnings.length}');
    // ignore: avoid_print
    print('Selecciones múltiples explícitas: ${multiAnswerChoices.length}');
    // ignore: avoid_print
    print('JSON de contenido/catálogos decodificados: ${rawJsonFiles.length}');
    final sortedShuffledTypes = shuffledTypes.toList()..sort();
    // ignore: avoid_print
    print('Tipos con presentación barajada: $sortedShuffledTypes');

    if (rawJsonFiles.isNotEmpty) {
      // ignore: avoid_print
      print('\n[JSON DE CONTENIDO Y CATÁLOGOS DECODIFICADOS]');
      for (final path in rawJsonFiles) {
        // ignore: avoid_print
        print('  $path');
      }
    }
    if (visited.isNotEmpty) {
      // ignore: avoid_print
      print('\n[171 ACTIVIDADES AUDITADAS - ID Y UBICACIÓN]');
      final ids = visited.toList()..sort();
      for (final id in ids) {
        // ignore: avoid_print
        print('  $id  [${activityLocations[id] ?? '?'}]');
      }
    }
    if (activityTypeCounts.isNotEmpty) {
      // ignore: avoid_print
      print('\n[ACTIVIDADES POR TIPO]');
      final entries = activityTypeCounts.entries.toList()
        ..sort((a, b) => a.key.compareTo(b.key));
      for (final entry in entries) {
        // ignore: avoid_print
        print('  ${entry.key}: ${entry.value}');
      }
    }
    if (multiAnswerChoices.isNotEmpty) {
      // ignore: avoid_print
      print('\n[SELECCIÓN MÚLTIPLE REAL]');
      for (final item in multiAnswerChoices) {
        // ignore: avoid_print
        print('  $item');
      }
    }
    if (oneCharLikelyLegit.isNotEmpty) {
      // ignore: avoid_print
      print('\n[1 CARÁCTER - PROBABLEMENTE LEGÍTIMO]');
      for (final item in oneCharLikelyLegit) {
        // ignore: avoid_print
        print('  $item');
      }
    }
    if (oneCharNeedsReview.isNotEmpty) {
      // ignore: avoid_print
      print('\n[1 CARÁCTER - REVISAR POSIBLE TRUNCADO]');
      for (final item in oneCharNeedsReview) {
        // ignore: avoid_print
        print('  $item');
      }
    }
    if (warnings.isNotEmpty) {
      // ignore: avoid_print
      print('\n[ADVERTENCIAS]');
      for (final warning in warnings) {
        // ignore: avoid_print
        print('  AVISO: $warning');
      }
    }
    if (problems.isNotEmpty) {
      // ignore: avoid_print
      print('\n[ERRORES BLOQUEANTES]');
      for (final problem in problems) {
        // ignore: avoid_print
        print('  ERROR: $problem');
      }
    }
    // ignore: avoid_print
    print('============================================================\n');
  }
}
