import 'package:flutter/material.dart';

import '../../../core/models/activity_content.dart';
import '../../../core/models/content_block.dart';
import '../../../core/review/review_logic.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../widgets/tts_controls.dart';

class ReviewActivityRenderer extends StatelessWidget {
  const ReviewActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    return switch (activity.type) {
      'known_block_marking' => _KnownBlockMarkingActivity(activity: activity),
      'self_review' => _ReflectionActivity(activity: activity),
      'self_check' => _ReflectionActivity(activity: activity),
      'progress_reflection' => _ReflectionActivity(activity: activity),
      'final_self_review' => _ReflectionActivity(activity: activity),
      'answer_key_review' => _AnswerKeyReviewActivity(activity: activity),
      'scenario_recall' => _ScenarioRecallActivity(activity: activity),
      _ => Text('Tipo de revisión no soportado: ${activity.type}'),
    };
  }
}

class _KnownBlockMarkingActivity extends StatefulWidget {
  const _KnownBlockMarkingActivity({required this.activity});
  final ActivityContent activity;

  @override
  State<_KnownBlockMarkingActivity> createState() =>
      _KnownBlockMarkingActivityState();
}

class _KnownBlockMarkingActivityState extends State<_KnownBlockMarkingActivity> {
  final Set<String> _selected = {};
  bool _noneYet = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final saved = AdapaRuntime.of(context).sessionStore.read(widget.activity.id);
      final values = (saved?['selected'] as List? ?? const []).map((e) => e.toString());
      setState(() {
        _selected.addAll(values);
        _noneYet = saved?['none_yet'] == true;
      });
    });
  }

  void _save() {
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'selected': _selected.toList(),
      'none_yet': _noneYet,
      'complete': _selected.isNotEmpty || _noneYet,
    });
  }

  @override
  Widget build(BuildContext context) {
    final ref = widget.activity.payload['reading_ref']?.toString() ?? '';
    return FutureBuilder<Map<String, dynamic>?>(
      future: AdapaRuntime.of(context).assetResolver.reading(ref),
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const LinearProgressIndicator();
        }
        final text = snapshot.data?['text_ko']?.toString() ?? '';
        final tokens = ReviewLogic.uniqueReadingTokens(text);
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (text.isNotEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Text(text, style: Theme.of(context).textTheme.titleMedium),
                ),
              ),
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                for (final token in tokens)
                  FilterChip(
                    label: Text(token),
                    selected: _selected.contains(token),
                    onSelected: (value) {
                      setState(() {
                        _noneYet = false;
                        if (value) {
                          _selected.add(token);
                        } else {
                          _selected.remove(token);
                        }
                      });
                      _save();
                    },
                  ),
              ],
            ),
            const SizedBox(height: 12),
            CheckboxListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('Todavía no puedo leer ninguna con seguridad'),
              subtitle: const Text('También es una respuesta válida de autoevaluación.'),
              value: _noneYet,
              onChanged: (value) {
                setState(() {
                  _noneYet = value ?? false;
                  if (_noneYet) _selected.clear();
                });
                _save();
              },
            ),
            if (_selected.isNotEmpty || _noneYet)
              const Card(
                child: ListTile(
                  leading: Icon(Icons.check_circle_outline),
                  title: Text('Autoevaluación registrada'),
                ),
              ),
          ],
        );
      },
    );
  }
}

class _ReflectionActivity extends StatefulWidget {
  const _ReflectionActivity({required this.activity});
  final ActivityContent activity;

  @override
  State<_ReflectionActivity> createState() => _ReflectionActivityState();
}

class _ReflectionActivityState extends State<_ReflectionActivity> {
  final Map<int, bool> _answers = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final saved = AdapaRuntime.of(context).sessionStore.read(widget.activity.id);
      final raw = saved?['answers'];
      if (raw is Map) {
        setState(() {
          for (final entry in raw.entries) {
            final index = int.tryParse(entry.key.toString());
            if (index != null && entry.value is bool) {
              _answers[index] = entry.value as bool;
            }
          }
        });
      }
    });
  }

  List<String> get _items {
    final raw = widget.activity.payload['checks'] ?? widget.activity.payload['items'];
    return (raw as List? ?? const []).map((e) => e.toString()).toList(growable: false);
  }

  void _set(int index, bool value) {
    setState(() => _answers[index] = value);
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'answers': {for (final e in _answers.entries) '${e.key}': e.value},
      'complete': _answers.length == _items.length && _items.isNotEmpty,
    });
  }

  @override
  Widget build(BuildContext context) {
    final items = _items;
    final successEs = widget.activity.payload['success_message_es']?.toString();
    final successKo = widget.activity.payload['success_message_ko']?.toString();
    final complete = ReviewLogic.allItemsReviewed(items.length, _answers);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i = 0; i < items.length; i++)
          Card(
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(items[i], style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: 10),
                  SegmentedButton<bool>(
                    segments: const [
                      ButtonSegment<bool>(value: true, label: Text('Sí')),
                      ButtonSegment<bool>(value: false, label: Text('Todavía no')),
                    ],
                    selected: _answers.containsKey(i) ? {_answers[i]!} : <bool>{},
                    emptySelectionAllowed: true,
                    onSelectionChanged: (values) {
                      if (values.isNotEmpty) _set(i, values.first);
                    },
                  ),
                ],
              ),
            ),
          ),
        if (complete) ...[
          const SizedBox(height: 8),
          Card(
            child: ListTile(
              leading: const Icon(Icons.fact_check_outlined),
              title: Text(successKo ?? 'Revisión completada'),
              subtitle: successEs == null ? null : Text(successEs),
            ),
          ),
        ],
      ],
    );
  }
}

class _AnswerKeyReviewActivity extends StatelessWidget {
  const _AnswerKeyReviewActivity({required this.activity});
  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    final ref = activity.payload['theory_ref']?.toString() ?? '';
    return FutureBuilder<ContentBlock?>(
      future: AdapaRuntime.of(context).assetResolver.contentBlock(ref),
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const LinearProgressIndicator();
        }
        final block = snapshot.data;
        if (block == null) {
          return const Text('No se pudo cargar la clave de respuestas.');
        }
        final items = (block.payload['items'] as List? ?? const [])
            .map((e) => Map<String, dynamic>.from(e as Map))
            .toList(growable: false);
        final note = block.payload['note_es']?.toString();
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            for (final item in items)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Página ${item['page']}',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        (item['answers'] as List? ?? const []).join(' · '),
                        style: Theme.of(context).textTheme.bodyLarge,
                      ),
                      if (item['source_warning_ref'] != null) ...[
                        const SizedBox(height: 8),
                        const Text(
                          '⚠ Esta línea tiene una inconsistencia documentada en la fuente y no se usa para sobrescribir contenido ya validado.',
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            if (note != null) ...[
              const SizedBox(height: 8),
              Text(note, style: Theme.of(context).textTheme.bodySmall),
            ],
            const SizedBox(height: 12),
            FilledButton(
              onPressed: () {
                AdapaRuntime.of(context).sessionStore.write(activity.id, {
                  'reviewed': true,
                  'complete': true,
                });
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Clave revisada.')),
                );
              },
              child: const Text('Ya revisé la clave'),
            ),
          ],
        );
      },
    );
  }
}

class _ScenarioRecallActivity extends StatefulWidget {
  const _ScenarioRecallActivity({required this.activity});
  final ActivityContent activity;

  @override
  State<_ScenarioRecallActivity> createState() => _ScenarioRecallActivityState();
}

class _ScenarioRecallActivityState extends State<_ScenarioRecallActivity> {
  final Set<String> _attempted = {};
  final Set<String> _revealed = {};

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final saved = AdapaRuntime.of(context).sessionStore.read(widget.activity.id);
      setState(() {
        _attempted.addAll((saved?['attempted'] as List? ?? const []).map((e) => e.toString()));
        _revealed.addAll((saved?['revealed'] as List? ?? const []).map((e) => e.toString()));
      });
    });
  }

  List<String> get _refs =>
      (widget.activity.payload['scenario_activity_refs'] as List? ?? const [])
          .map((e) => e.toString())
          .toList(growable: false);

  void _save() {
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'attempted': _attempted.toList(),
      'revealed': _revealed.toList(),
      'complete': _attempted.length == _refs.length && _refs.isNotEmpty,
    });
  }

  String? _model(ActivityContent activity) {
    final payload = activity.payload;
    final accepted = payload['accepted_answers'];
    if (accepted is List && accepted.isNotEmpty) return accepted.first.toString();
    final correct = payload['correct'];
    if (correct is List && correct.isNotEmpty) return correct.first.toString();
    final pattern = payload['pattern'];
    if (pattern != null) return pattern.toString();
    return null;
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<ActivityContent>>(
      future: AdapaRuntime.of(context).assetResolver.activitiesByIds(_refs),
      builder: (context, snapshot) {
        if (snapshot.connectionState != ConnectionState.done) {
          return const LinearProgressIndicator();
        }
        final activities = snapshot.data ?? const <ActivityContent>[];
        if (activities.isEmpty) return const Text('No se pudieron cargar los escenarios.');

        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            for (final scenario in activities) ...[
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(
                        scenario.prompt ?? scenario.objective ?? 'Situación',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: FilledButton.tonal(
                              onPressed: () {
                                setState(() => _attempted.add(scenario.id));
                                _save();
                              },
                              child: Text(
                                _attempted.contains(scenario.id)
                                    ? 'Intentado ✓'
                                    : 'Lo intenté sin mirar',
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          OutlinedButton(
                            onPressed: () => setState(() {
                              _revealed.add(scenario.id);
                            }),
                            child: const Text('Modelo'),
                          ),
                        ],
                      ),
                      if (_revealed.contains(scenario.id)) ...[
                        const SizedBox(height: 10),
                        Text(_model(scenario) ?? 'Respuesta abierta / estructural.'),
                        if (_model(scenario) != null) ...[
                          const SizedBox(height: 8),
                          TtsControls(text: _model(scenario)!),
                        ],
                      ],
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 8),
            ],
            if (_attempted.length == activities.length)
              const Card(
                child: ListTile(
                  leading: Icon(Icons.task_alt),
                  title: Text('Repaso de las ocho situaciones completado'),
                ),
              ),
          ],
        );
      },
    );
  }
}
