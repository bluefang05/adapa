import 'dart:async';

import 'package:flutter/material.dart';

import '../../../core/dialogue/dialogue_scene.dart';
import '../../../core/dialogue/dialogue_variant_builder.dart';
import '../../../core/evaluation/dialogue_evaluator.dart';
import '../../../core/models/activity_content.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../../../core/session/activity_session_store.dart';
import '../widgets/activity_feedback.dart';
import '../widgets/tts_controls.dart';

class DialogueActivityRenderer extends StatelessWidget {
  const DialogueActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    return switch (activity.type) {
      'dialogue' => _InlineDialogueActivity(activity: activity),
      'dialogue_roleplay' => _RoleplayActivity(activity: activity),
      'dialogue_variant' => _DialogueVariantActivity(activity: activity),
      'guided_dialogue_fill' => _GuidedDialogueFillActivity(activity: activity),
      'final_guided_conversation' =>
        _FinalGuidedConversationActivity(activity: activity),
      'tts_dialogue_playback' => _DialoguePlaybackActivity(activity: activity),
      _ => _UnsupportedDialogue(activity: activity),
    };
  }
}

class _InlineDialogueActivity extends StatefulWidget {
  const _InlineDialogueActivity({required this.activity});

  final ActivityContent activity;

  @override
  State<_InlineDialogueActivity> createState() => _InlineDialogueActivityState();
}

class _InlineDialogueActivityState extends State<_InlineDialogueActivity> {
  final Map<int, TextEditingController> _controllers = {};
  bool? _correct;

  List<dynamic> get _turns =>
      (widget.activity.payload['turns'] as List? ?? const []).toList(growable: false);

  @override
  void initState() {
    super.initState();
    for (var i = 0; i < _turns.length; i++) {
      final turn = Map<String, dynamic>.from(_turns[i] as Map);
      if (turn['speaker']?.toString() == 'user') {
        _controllers[i] = TextEditingController();
      }
    }
  }

  @override
  void dispose() {
    for (final controller in _controllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  void _check() {
    var ok = true;
    final playback = <String>[];
    final responses = <String, dynamic>{};
    for (var i = 0; i < _turns.length; i++) {
      final turn = Map<String, dynamic>.from(_turns[i] as Map);
      if (turn['speaker']?.toString() == 'user') {
        final value = _controllers[i]?.text ?? '';
        final accepted = (turn['accepted'] as List? ?? const []).toList(growable: false);
        final rules = Map<String, dynamic>.from(
          turn['normalization'] as Map? ?? widget.activity.normalization,
        );
        if (value.trim().isEmpty ||
            (accepted.isNotEmpty &&
                !DialogueEvaluator.accepted(value, accepted, rules))) {
          ok = false;
        }
        responses['$i'] = value;
        if (value.trim().isNotEmpty) playback.add(value.trim());
      } else {
        final ko = turn['ko']?.toString() ?? '';
        if (ko.isNotEmpty) playback.add(ko);
      }
    }
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'responses': responses,
      'playback_lines': playback,
      'complete': ok,
      'record_attempt': true,
      'score': ok ? 1.0 : 0.0,
    });
    setState(() => _correct = ok);
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i = 0; i < _turns.length; i++) ...[
          _buildTurn(context, i, Map<String, dynamic>.from(_turns[i] as Map)),
          const SizedBox(height: 10),
        ],
        FilledButton(onPressed: _check, child: const Text('Comprobar diálogo')),
        if (_correct != null) ...[
          const SizedBox(height: 12),
          ActivityFeedback(
            isCorrect: _correct!,
            message: _correct!
                ? widget.activity.feedback['correct']?.toString() ?? 'Correcto.'
                : _wrongMessage(widget.activity),
          ),
        ],
      ],
    );
  }

  Widget _buildTurn(BuildContext context, int index, Map<String, dynamic> turn) {
    if (turn['speaker']?.toString() == 'user') {
      return _UserTurnField(
        speaker: 'Tú',
        prompt: turn['prompt_es']?.toString() ?? 'Tu respuesta',
        controller: _controllers[index]!,
        onChanged: (_) => setState(() => _correct = null),
      );
    }
    return _DialogueBubble(
      speaker: turn['speaker']?.toString() ?? '',
      ko: turn['ko']?.toString() ?? '',
      es: turn['es']?.toString(),
    );
  }
}

class _RoleplayActivity extends StatefulWidget {
  const _RoleplayActivity({required this.activity});
  final ActivityContent activity;

  @override
  State<_RoleplayActivity> createState() => _RoleplayActivityState();
}

class _RoleplayActivityState extends State<_RoleplayActivity> {
  final Map<int, TextEditingController> _controllers = {};
  bool? _correct;
  String? _failedTurn;

  List<int> get _required =>
      (widget.activity.payload['required_turns'] as List? ?? const [])
          .map((e) => (e as num).toInt())
          .toList(growable: false);

  @override
  void initState() {
    super.initState();
    for (final index in _required) {
      _controllers[index] = TextEditingController();
    }
  }

  @override
  void dispose() {
    for (final c in _controllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  void _check(DialogueScene scene) {
    final responses = <int, String>{
      for (final entry in _controllers.entries) entry.key: entry.value.text,
    };
    final acceptedByTurn = Map<String, dynamic>.from(
      widget.activity.payload['accepted_by_turn'] as Map? ?? const {},
    );
    final result = DialogueEvaluator.roleplay(
      responses: responses,
      requiredTurns: _required,
      acceptedByTurn: acceptedByTurn,
      normalization: widget.activity.normalization,
    );

    final playback = <String>[];
    for (var i = 0; i < scene.turns.length; i++) {
      final value = responses[i]?.trim();
      playback.add(value != null && value.isNotEmpty ? value : scene.turns[i].ko);
    }
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'responses': {for (final e in responses.entries) '${e.key}': e.value},
      'playback_lines': playback,
      'complete': result.isCorrect,
      'record_attempt': true,
      'score': result.isCorrect ? 1.0 : 0.0,
    });
    setState(() {
      _correct = result.isCorrect;
      _failedTurn = result.fieldId;
    });
  }

  @override
  Widget build(BuildContext context) {
    final ref = widget.activity.payload['dialogue_ref']?.toString() ?? '';
    return FutureBuilder<DialogueScene?>(
      future: AdapaRuntime.of(context).assetResolver.dialogueScene(ref),
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        final scene = snapshot.data;
        if (scene == null) {
          return const Text('No se pudo cargar el diálogo.');
        }
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _SceneHeader(scene: scene),
            const SizedBox(height: 12),
            for (var i = 0; i < scene.turns.length; i++) ...[
              if (_required.contains(i))
                _UserTurnField(
                  speaker: scene.turns[i].speaker,
                  prompt: 'Tu turno como ${widget.activity.payload['user_role'] ?? 'usuario'}',
                  controller: _controllers[i]!,
                  error: _failedTurn == '$i' && _correct == false,
                  onChanged: (_) => setState(() {
                    _correct = null;
                    _failedTurn = null;
                  }),
                )
              else
                _DialogueBubble(
                  speaker: scene.turns[i].speaker,
                  ko: scene.turns[i].ko,
                  es: scene.turns[i].es,
                ),
              const SizedBox(height: 10),
            ],
            FilledButton(
              onPressed: () => _check(scene),
              child: const Text('Comprobar roleplay'),
            ),
            if (_correct != null) ...[
              const SizedBox(height: 12),
              ActivityFeedback(
                isCorrect: _correct!,
                message: _correct!
                    ? widget.activity.feedback['correct']?.toString() ?? 'Correcto.'
                    : _wrongMessage(widget.activity),
              ),
            ],
          ],
        );
      },
    );
  }
}

class _DialogueVariantActivity extends StatefulWidget {
  const _DialogueVariantActivity({required this.activity});
  final ActivityContent activity;

  @override
  State<_DialogueVariantActivity> createState() => _DialogueVariantActivityState();
}

class _DialogueVariantActivityState extends State<_DialogueVariantActivity> {
  dynamic _selected;
  final Set<String> _seen = <String>{};
  bool _hydrated = false;
  ActivitySessionStore? _sessionStore;

  List<dynamic> get _choices =>
      (widget.activity.payload['choices'] as List? ?? const []).toList(growable: false);

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _sessionStore ??= AdapaRuntime.of(context).sessionStore;
    if (_hydrated) return;
    _hydrated = true;
    final saved = _sessionStore?.read(widget.activity.id);
    final seen = (saved?['seen'] as List? ?? const []).map((e)=>e.toString());
    _seen.addAll(seen);
  }

  void _select(dynamic option) {
    final value = option is Map
        ? (option['ko'] ?? option['value'] ?? option['id'] ?? option).toString()
        : option.toString();
    setState(() {
      _selected = option;
      _seen.add(value);
    });
    final complete = widget.activity.completionRule == 'complete_all_variants'
        ? _seen.length >= _choices.length
        : true;
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'selected': value,
      'seen': _seen.toList(),
      'complete': complete,
    });
  }

  @override
  Widget build(BuildContext context) {
    final dialogueRef = widget.activity.payload['dialogue_ref']?.toString();
    if (dialogueRef == null || dialogueRef.isEmpty) {
      return _buildStandaloneChoice(context);
    }
    return FutureBuilder<DialogueScene?>(
      future: AdapaRuntime.of(context).assetResolver.dialogueScene(dialogueRef),
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        final scene = snapshot.data;
        if (scene == null) return const Text('No se pudo cargar el diálogo.');
        final choice = _selected?.toString();
        final variant = choice == null
            ? scene
            : DialogueVariantBuilder.applySlot(
                scene: scene,
                slot: widget.activity.payload['slot']?.toString() ?? '',
                replacement: choice,
              );
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                for (final option in _choices)
                  ChoiceChip(
                    label: Text(option.toString()),
                    selected: choice == option.toString(),
                    onSelected: (_) => _select(option),
                  ),
              ],
            ),
            const SizedBox(height: 16),
            _SceneHeader(scene: variant),
            const SizedBox(height: 8),
            for (final turn in variant.turns) ...[
              _DialogueBubble(speaker: turn.speaker, ko: turn.ko, es: turn.es),
              const SizedBox(height: 8),
            ],
            if (choice != null) ...[
              const SizedBox(height: 8),
              TtsControls(text: variant.turns.map((e) => e.ko).join(' ')),
            ],
          ],
        );
      },
    );
  }

  Widget _buildStandaloneChoice(BuildContext context) {
    final selected = _selected;
    String? ko;
    String? label;
    if (selected is Map) {
      ko = selected['ko']?.toString();
      label = selected['label_es']?.toString();
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (final option in _choices)
          Card(
            child: ListTile(
              onTap: () => _select(option),
              leading: Icon(
                identical(selected, option)
                    ? Icons.radio_button_checked
                    : Icons.radio_button_off,
              ),
              title: Text(
                option is Map
                    ? (option['label_es'] ?? option['ko'] ?? '').toString()
                    : option.toString(),
              ),
              subtitle: option is Map && option['ko'] != null
                  ? Text(option['ko'].toString())
                  : null,
            ),
          ),
        if (ko != null) ...[
          const SizedBox(height: 12),
          Text(
            ko,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.headlineMedium,
          ),
          if (label != null)
            Text(label, textAlign: TextAlign.center),
          const SizedBox(height: 8),
          TtsControls(text: ko),
        ],
      ],
    );
  }
}

class _GuidedDialogueFillActivity extends StatefulWidget {
  const _GuidedDialogueFillActivity({required this.activity});
  final ActivityContent activity;

  @override
  State<_GuidedDialogueFillActivity> createState() => _GuidedDialogueFillActivityState();
}

class _GuidedDialogueFillActivityState extends State<_GuidedDialogueFillActivity> {
  final Map<String, TextEditingController> _controllers = {};
  bool? _correct;

  List<dynamic> get _fields =>
      (widget.activity.payload['fields'] as List? ?? const []).toList(growable: false);

  @override
  void initState() {
    super.initState();
    for (final raw in _fields) {
      final field = Map<String, dynamic>.from(raw as Map);
      _controllers[field['id'].toString()] = TextEditingController();
    }
  }

  @override
  void dispose() {
    for (final c in _controllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  void _check() {
    var ok = true;
    final values = <String, String>{};
    for (final raw in _fields) {
      final field = Map<String, dynamic>.from(raw as Map);
      final id = field['id'].toString();
      final value = _controllers[id]?.text.trim() ?? '';
      values[id] = value;
      if (value.isEmpty) ok = false;
      if (field['scored'] == true) {
        final accepted = (field['accepted'] as List? ?? const []).toList(growable: false);
        if (accepted.isNotEmpty &&
            !DialogueEvaluator.accepted(value, accepted, widget.activity.normalization)) {
          ok = false;
        }
      }
    }
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'responses': values,
      'complete': ok,
      'record_attempt': true,
      if (widget.activity.scoreMode != 'none') 'score': ok ? 1.0 : 0.0,
    });
    setState(() => _correct = ok);
  }

  @override
  Widget build(BuildContext context) {
    final ref = widget.activity.payload['dialogue_ref']?.toString() ?? '';
    return FutureBuilder<DialogueScene?>(
      future: AdapaRuntime.of(context).assetResolver.dialogueScene(ref),
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        final scene = snapshot.data;
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (scene != null) ...[
              _SceneHeader(scene: scene),
              const SizedBox(height: 8),
              for (final turn in scene.turns) ...[
                _DialogueBubble(
                  speaker: turn.speaker,
                  ko: turn.ko,
                  es: turn.es,
                  showTts: false,
                ),
                const SizedBox(height: 6),
              ],
              if (widget.activity.payload['tts_preview'] == true) ...[
                const SizedBox(height: 8),
                TtsControls(text: scene.turns.map((e) => e.ko).join(' ')),
              ],
              const Divider(height: 28),
            ],
            Text('Completa tus respuestas', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            for (final raw in _fields) ...[
              Builder(builder: (context) {
                final field = Map<String, dynamic>.from(raw as Map);
                final id = field['id'].toString();
                final examples = (field['examples'] as List? ?? const []).join(' / ');
                return Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: TextField(
                    controller: _controllers[id],
                    onChanged: (_) => setState(() => _correct = null),
                    decoration: InputDecoration(
                      labelText: field['prompt_es']?.toString() ?? id,
                      hintText: field['example']?.toString() ??
                          (examples.isEmpty ? null : examples),
                    ),
                  ),
                );
              }),
            ],
            FilledButton(onPressed: _check, child: const Text('Comprobar')), 
            if (_correct != null) ...[
              const SizedBox(height: 12),
              ActivityFeedback(
                isCorrect: _correct!,
                message: _correct!
                    ? widget.activity.feedback['correct']?.toString() ?? 'Completado.'
                    : _wrongMessage(widget.activity),
              ),
            ]
          ],
        );
      },
    );
  }
}

class _FinalGuidedConversationActivity extends StatefulWidget {
  const _FinalGuidedConversationActivity({required this.activity});
  final ActivityContent activity;

  @override
  State<_FinalGuidedConversationActivity> createState() =>
      _FinalGuidedConversationActivityState();
}

class _FinalGuidedConversationActivityState
    extends State<_FinalGuidedConversationActivity> {
  final Map<String, TextEditingController> _controllers = {};
  bool? _correct;
  String? _failedField;
  bool _hydrated = false;
  ActivitySessionStore? _sessionStore;
  Timer? _draftTimer;

  List<dynamic> get _turns =>
      (widget.activity.payload['turns'] as List? ?? const []).toList(growable: false);

  @override
  void initState() {
    super.initState();
    for (final raw in _turns) {
      final turn = Map<String, dynamic>.from(raw as Map);
      final id = turn['field_id']?.toString();
      if (id != null) _controllers[id] = TextEditingController();
    }
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _sessionStore ??= AdapaRuntime.of(context).sessionStore;
    if (_hydrated) return;
    _hydrated = true;
    final saved = _sessionStore?.read(widget.activity.id);
    final responses = Map<String, dynamic>.from(saved?['responses'] as Map? ?? const {});
    for (final entry in _controllers.entries) {
      entry.value.text = responses[entry.key]?.toString() ?? '';
    }
  }

  void _persistDraft() {
    final store = _sessionStore;
    if (store == null) return;
    final responses = <String, String>{
      for (final e in _controllers.entries) e.key: e.value.text,
    };
    store.write(widget.activity.id, {
      'responses': responses,
      'playback_lines': DialogueEvaluator.finalPlaybackLines(
        turns: _turns,
        responses: responses,
      ),
      'complete': _correct == true,
    });
  }

  void _scheduleDraftSave() {
    _draftTimer?.cancel();
    _draftTimer = Timer(const Duration(milliseconds: 500), _persistDraft);
  }

  @override
  void dispose() {
    if (_draftTimer?.isActive == true) {
      _draftTimer!.cancel();
      _persistDraft();
    }
    for (final c in _controllers.values) {
      c.dispose();
    }
    super.dispose();
  }

  void _check() {
    final responses = <String, String>{
      for (final e in _controllers.entries) e.key: e.value.text,
    };
    final result = DialogueEvaluator.finalConversation(
      turns: _turns,
      responses: responses,
      normalization: widget.activity.normalization,
    );
    final playback = DialogueEvaluator.finalPlaybackLines(
      turns: _turns,
      responses: responses,
    );
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'responses': responses,
      'playback_lines': playback,
      'complete': result.isCorrect,
      'record_attempt': true,
      'score': result.isCorrect ? 1.0 : 0.0,
    });
    setState(() {
      _correct = result.isCorrect;
      _failedField = result.fieldId;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                const Icon(Icons.block),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Reto final: escribe en Hangul. La romanización y la traducción durante el intento están desactivadas.',
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),
        for (final raw in _turns) ...[
          _finalTurn(Map<String, dynamic>.from(raw as Map)),
          const SizedBox(height: 10),
        ],
        FilledButton(onPressed: _check, child: const Text('Completar reto')), 
        if (_correct != null) ...[
          const SizedBox(height: 12),
          ActivityFeedback(
            isCorrect: _correct!,
            message: _correct!
                ? widget.activity.feedback['correct']?.toString() ?? 'Completado.'
                : _finalWrongMessage(widget.activity, _failedField),
          ),
        ]
      ],
    );
  }

  Widget _finalTurn(Map<String, dynamic> turn) {
    final speaker = turn['speaker']?.toString() ?? '';
    final fixed = turn['fixed']?.toString();
    final fieldId = turn['field_id']?.toString();
    if (fieldId == null) {
      return _DialogueBubble(speaker: speaker, ko: fixed ?? '', showTts: true);
    }
    final suffix = turn['suffix']?.toString();
    final fieldType = turn['field_type']?.toString();
    return Card(
      color: Theme.of(context).colorScheme.surfaceContainerLow,
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(speaker, style: Theme.of(context).textTheme.labelLarge),
            if (fixed != null && fixed.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(fixed, style: Theme.of(context).textTheme.titleLarge),
            ],
            const SizedBox(height: 8),
            TextField(
              controller: _controllers[fieldId],
              onChanged: (_) {
                setState(() {
                  _correct = null;
                  _failedField = null;
                });
                _scheduleDraftSave();
              },
              decoration: InputDecoration(
                labelText: fieldType == 'request_prefix'
                    ? 'Escribe qué quieres pedir'
                    : 'Completa en Hangul',
                suffixText: suffix,
                errorText: _failedField == fieldId && _correct == false
                    ? 'Revisa este campo.'
                    : null,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _DialoguePlaybackActivity extends StatefulWidget {
  const _DialoguePlaybackActivity({required this.activity});
  final ActivityContent activity;

  @override
  State<_DialoguePlaybackActivity> createState() => _DialoguePlaybackActivityState();
}

class _DialoguePlaybackActivityState extends State<_DialoguePlaybackActivity> {
  bool _played = false;
  bool _speaking = false;

  @override
  Widget build(BuildContext context) {
    final source = widget.activity.payload['source_activity']?.toString() ?? '';
    final saved = AdapaRuntime.of(context).sessionStore.read(source);
    final lines = (saved?['playback_lines'] as List? ?? const [])
        .map((e) => e.toString())
        .where((e) => e.trim().isNotEmpty)
        .toList(growable: false);

    if (lines.isEmpty) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Text(
            'Completa primero la conversación anterior para poder escuchar tu propia versión.',
            style: Theme.of(context).textTheme.bodyLarge,
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i = 0; i < lines.length; i++) ...[
          _DialogueBubble(
            speaker: i.isEven ? 'A' : 'B',
            ko: lines[i],
            showTts: false,
          ),
          const SizedBox(height: 7),
        ],
        const SizedBox(height: 8),
        FilledButton.icon(
          onPressed: _speaking
              ? null
              : () async {
                  setState(() => _speaking = true);
                  try {
                    final runtime = AdapaRuntime.of(context);
                    await runtime.tts.speak(
                      lines.join(' '),
                      rate: runtime.settings.normalTtsRate,
                    );
                    if (!context.mounted) return;
                    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
                      'complete': true,
                      'source_activity': source,
                    });
                    setState(() => _played = true);
                  } catch (_) {
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('No hay una voz coreana disponible.')),
                      );
                    }
                  } finally {
                    if (mounted) setState(() => _speaking = false);
                  }
                },
          icon: const Icon(Icons.play_circle_outline),
          label: Text(
            _speaking
                ? 'Reproduciendo…'
                : (_played ? 'Escuchar de nuevo' : 'Escuchar mi conversación'),
          ),
        ),
      ],
    );
  }
}

class _SceneHeader extends StatelessWidget {
  const _SceneHeader({required this.scene});
  final DialogueScene scene;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        const Icon(Icons.forum_outlined),
        const SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(scene.titleEs, style: Theme.of(context).textTheme.titleMedium),
              if (scene.source != null)
                Text(scene.source!, style: Theme.of(context).textTheme.bodySmall),
            ],
          ),
        ),
      ],
    );
  }
}

class _DialogueBubble extends StatelessWidget {
  const _DialogueBubble({
    required this.speaker,
    required this.ko,
    this.es,
    this.showTts = true,
  });

  final String speaker;
  final String ko;
  final String? es;
  final bool showTts;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Card(
      color: speaker == 'A' || speaker == '선생님'
          ? scheme.surfaceContainerHighest
          : scheme.secondaryContainer.withAlpha(140),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            CircleAvatar(radius: 16, child: Text(_speakerInitial(speaker))),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(speaker, style: Theme.of(context).textTheme.labelMedium),
                  const SizedBox(height: 4),
                  Text(ko, style: Theme.of(context).textTheme.titleMedium),
                  if (es != null && es!.trim().isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(es!, style: Theme.of(context).textTheme.bodySmall),
                  ],
                ],
              ),
            ),
            if (showTts && ko.trim().isNotEmpty)
              IconButton(
                tooltip: 'Escuchar',
                icon: const Icon(Icons.volume_up_outlined),
                onPressed: () async {
                  try {
                    final runtime = AdapaRuntime.of(context);
                    await runtime.tts.speak(
                      ko,
                      rate: runtime.settings.normalTtsRate,
                    );
                  } catch (_) {
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('No hay una voz coreana disponible.')),
                      );
                    }
                  }
                },
              ),
          ],
        ),
      ),
    );
  }
}

class _UserTurnField extends StatelessWidget {
  const _UserTurnField({
    required this.speaker,
    required this.prompt,
    required this.controller,
    required this.onChanged,
    this.error = false,
  });

  final String speaker;
  final String prompt;
  final TextEditingController controller;
  final ValueChanged<String> onChanged;
  final bool error;

  @override
  Widget build(BuildContext context) {
    return Card(
      color: Theme.of(context).colorScheme.primaryContainer.withAlpha(107),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(speaker, style: Theme.of(context).textTheme.labelMedium),
            const SizedBox(height: 8),
            TextField(
              controller: controller,
              onChanged: onChanged,
              decoration: InputDecoration(
                labelText: prompt,
                errorText: error ? 'Revisa esta respuesta.' : null,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _UnsupportedDialogue extends StatelessWidget {
  const _UnsupportedDialogue({required this.activity});
  final ActivityContent activity;

  @override
  Widget build(BuildContext context) => Text(
        'Tipo de diálogo no soportado: ${activity.type}',
      );
}

String _wrongMessage(ActivityContent activity) {
  final wrong = activity.feedback['wrong'];
  if (wrong is Map && wrong['default'] != null) {
    return wrong['default'].toString();
  }
  return 'Revisa tus respuestas.';
}

String _finalWrongMessage(ActivityContent activity, String? fieldId) {
  final wrong = activity.feedback['wrong'];
  if (wrong is Map) {
    if (fieldId == 'b_glad_response' && wrong['controlled_b_glad_response'] != null) {
      return wrong['controlled_b_glad_response'].toString();
    }
    if (wrong['default'] != null) return wrong['default'].toString();
  }
  return 'Completa el diálogo en Hangul.';
}

String _speakerInitial(String speaker) {
  if (speaker.isEmpty) return '?';
  return String.fromCharCode(speaker.runes.first);
}
