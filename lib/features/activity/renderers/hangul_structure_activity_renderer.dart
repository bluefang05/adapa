import 'package:flutter/material.dart';

import '../../../core/evaluation/answer_normalizer.dart';
import '../../../core/hangul/hangul_composer.dart';
import '../../../core/models/activity_content.dart';
import '../../../core/practice/activity_shuffle.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../../../core/services/practice_feedback_service.dart';
import '../widgets/activity_feedback.dart';
import '../widgets/shake_feedback.dart';

class HangulStructureActivityRenderer extends StatelessWidget {
  const HangulStructureActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    return switch (activity.type) {
      'syllable_builder' => _SyllableBuilder(activity: activity),
      'batchim_finder' => _SingleSelector(
          activity: activity,
          items: (activity.payload['components'] as List? ?? const [])
              .map((e) => e.toString())
              .toList(growable: false),
          answers: [activity.payload['correct']?.toString() ?? ''],
          header: activity.payload['word']?.toString(),
        ),
      'batchim_finder_multi' => _MultiSelector(
          activity: activity,
          items: (activity.payload['blocks'] as List? ?? const [])
              .map((raw) => Map<String, dynamic>.from(raw as Map)['text'].toString())
              .toList(growable: false),
          answers: (activity.payload['correct'] as List? ?? const [])
              .map((e) => e.toString())
              .toList(growable: false),
          header: activity.payload['word']?.toString(),
        ),
      'highlight_token' => _MultiSelector(
          activity: activity,
          items: (activity.payload['sentence_tokens'] as List? ?? const [])
              .map((e) => e.toString())
              .toList(growable: false),
          answers: (activity.payload['correct_tokens'] as List? ?? const [])
              .map((e) => e.toString())
              .toList(growable: false),
          joinAsSentence: true,
        ),
      'hangul_recall_grid' => _HangulRecallGrid(activity: activity),
      _ => _UnsupportedHangulStructure(activity: activity),
    };
  }
}

String _correctMessage(ActivityContent activity) =>
    activity.feedback['correct']?.toString() ?? 'Correcto.';

String _wrongMessage(ActivityContent activity) {
  final wrong = activity.feedback['wrong'];
  if (wrong is Map && wrong['default'] != null) return wrong['default'].toString();
  return 'Revisa la respuesta y vuelve a intentarlo.';
}

class _SyllableBuilder extends StatefulWidget {
  const _SyllableBuilder({required this.activity});
  final ActivityContent activity;

  @override
  State<_SyllableBuilder> createState() => _SyllableBuilderState();
}

class _SyllableBuilderState extends State<_SyllableBuilder> {
  final List<String> _selected = <String>[];
  late final List<String> _parts;
  bool? _correct;

  @override
  void initState() {
    super.initState();
    _parts = ActivityShuffle.copy<String>(
      (widget.activity.payload['parts'] as List? ?? const [])
          .map((e) => e.toString()),
    );
  }

  String get _answer => widget.activity.payload['correct_block']?.toString() ?? '';

  String? get _composed => HangulComposer.compose(_selected);

  void _add(String value) {
    if (_selected.length >= _parts.length) return;
    final selectedCount = _selected.where((e) => e == value).length;
    final availableCount = _parts.where((e) => e == value).length;
    if (selectedCount >= availableCount) return;
    setState(() {
      _selected.add(value);
      _correct = null;
    });
  }

  void _reset() {
    setState(() {
      _selected.clear();
      _correct = null;
    });
  }

  void _check() {
    final composed = _composed;
    final ok = composed != null &&
        AnswerNormalizer.equals(composed, _answer, widget.activity.normalization);
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'selected': List<String>.from(_selected),
      'composed': composed,
      'complete': ok,
      'record_attempt': true,
      'score': ok ? 1.0 : 0.0,
    });
    setState(() => _correct = ok);
  }

  @override
  Widget build(BuildContext context) {
    final layout = widget.activity.payload['layout']?.toString();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (layout != null) ...[
          _LayoutHint(layout: layout),
          const SizedBox(height: 12),
        ],
        Text('Toca las letras en el orden del bloque.', style: Theme.of(context).textTheme.bodySmall),
        const SizedBox(height: 10),
        Wrap(
          alignment: WrapAlignment.center,
          spacing: 12,
          runSpacing: 12,
          children: [
            for (final part in _parts)
              ActionChip(
                onPressed: () => _add(part),
                label: Text(part, style: const TextStyle(fontSize: 34, fontWeight: FontWeight.w600)),
              ),
          ],
        ),
        const SizedBox(height: 18),
        Card(
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 22, horizontal: 16),
            child: Column(
              children: [
                Text(
                  _selected.isEmpty ? '—' : _selected.join(' + '),
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: 8),
                Text(
                  _composed ?? (_selected.isEmpty ? '?' : _selected.join()),
                  style: const TextStyle(fontSize: 64, fontWeight: FontWeight.w700),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed: _selected.isEmpty ? null : _reset,
                icon: const Icon(Icons.refresh),
                label: const Text('Borrar'),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: FilledButton(
                onPressed: _selected.length == _parts.length ? _check : null,
                child: const Text('Comprobar'),
              ),
            ),
          ],
        ),
        if (_correct != null) ...[
          const SizedBox(height: 12),
          ActivityFeedback(
            isCorrect: _correct!,
            message: _correct! ? _correctMessage(widget.activity) : _wrongMessage(widget.activity),
          ),
        ],
      ],
    );
  }
}

class _LayoutHint extends StatelessWidget {
  const _LayoutHint({required this.layout});
  final String layout;

  @override
  Widget build(BuildContext context) {
    final horizontal = layout == 'horizontal_vowel';
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surfaceContainerHighest,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        children: [
          Icon(horizontal ? Icons.vertical_align_center : Icons.view_column_outlined),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              horizontal
                  ? 'Vocal horizontal: la consonante se organiza arriba y la vocal debajo.'
                  : 'Vocal vertical: la consonante se organiza a la izquierda y la vocal a la derecha.',
            ),
          ),
        ],
      ),
    );
  }
}

class _SingleSelector extends StatefulWidget {
  const _SingleSelector({
    required this.activity,
    required this.items,
    required this.answers,
    this.header,
  });

  final ActivityContent activity;
  final List<String> items;
  final List<String> answers;
  final String? header;

  @override
  State<_SingleSelector> createState() => _SingleSelectorState();
}

class _SingleSelectorState extends State<_SingleSelector> {
  late final List<String> _items;
  String? _selected;
  bool? _correct;
  bool _resolvingWrongAnswer = false;
  final Map<String, int> _shakeSignals = <String, int>{};

  @override
  void initState() {
    super.initState();
    _items = ActivityShuffle.copy<String>(widget.items);
  }

  Future<void> _select(String item) async {
    if (_resolvingWrongAnswer || _correct == true) return;

    final ok = widget.answers.any(
      (answer) => AnswerNormalizer.equals(
        item,
        answer,
        widget.activity.normalization,
      ),
    );

    setState(() {
      _selected = item;
      _correct = ok;
    });
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'selected': item,
      'complete': ok,
      'record_attempt': true,
      'score': ok ? 1.0 : 0.0,
    });

    if (ok) {
      PracticeFeedbackService.success();
      return;
    }

    PracticeFeedbackService.error();
    setState(() {
      _resolvingWrongAnswer = true;
      _shakeSignals[item] = (_shakeSignals[item] ?? 0) + 1;
    });
    await Future<void>.delayed(const Duration(milliseconds: 360));
    if (!mounted) return;
    setState(() {
      _selected = null;
      _resolvingWrongAnswer = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (widget.header != null) ...[
          Center(
            child: Text(
              widget.header!,
              style: const TextStyle(fontSize: 64, fontWeight: FontWeight.w700),
            ),
          ),
          const SizedBox(height: 16),
        ],
        Text(
          'Toca una opción. Si no es, vuelve a intentarlo.',
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.bodySmall,
        ),
        const SizedBox(height: 10),
        Wrap(
          alignment: WrapAlignment.center,
          spacing: 10,
          runSpacing: 10,
          children: [
            for (final item in _items)
              ShakeFeedback(
                signal: _shakeSignals[item] ?? 0,
                child: ChoiceChip(
                  selected: _selected == item,
                  selectedColor: _selected == item && _correct == false
                      ? scheme.errorContainer
                      : null,
                  onSelected: _resolvingWrongAnswer || _correct == true
                      ? null
                      : (_) => _select(item),
                  avatar: _selected == item
                      ? Icon(
                          _correct == true ? Icons.check : Icons.close,
                          size: 18,
                        )
                      : null,
                  label: Text(item, style: const TextStyle(fontSize: 30)),
                ),
              ),
          ],
        ),
        if (_correct != null) ...[
          const SizedBox(height: 16),
          ActivityFeedback(
            isCorrect: _correct!,
            message: _correct!
                ? _correctMessage(widget.activity)
                : _wrongMessage(widget.activity),
          ),
        ],
      ],
    );
  }
}

class _MultiSelector extends StatefulWidget {
  const _MultiSelector({
    required this.activity,
    required this.items,
    required this.answers,
    this.header,
    this.joinAsSentence = false,
  });

  final ActivityContent activity;
  final List<String> items;
  final List<String> answers;
  final String? header;
  final bool joinAsSentence;

  @override
  State<_MultiSelector> createState() => _MultiSelectorState();
}

class _MultiSelectorState extends State<_MultiSelector> {
  final Set<String> _selected = <String>{};
  final Map<String, int> _shakeSignals = <String, int>{};
  late final List<String> _items;
  bool? _correct;
  bool _resolvingWrongAnswer = false;

  @override
  void initState() {
    super.initState();
    _items = ActivityShuffle.copy<String>(widget.items);
  }

  Future<void> _toggle(String item) async {
    if (_resolvingWrongAnswer || _correct == true) return;

    setState(() {
      if (!_selected.add(item)) _selected.remove(item);
      _correct = null;
    });

    final expected = widget.answers.toSet();
    if (expected.isEmpty || _selected.length < expected.length) return;

    final ok = _selected.length == expected.length &&
        _selected.containsAll(expected);

    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'selected': _selected.toList(),
      'complete': ok,
      'record_attempt': true,
      'score': ok ? 1.0 : 0.0,
    });

    if (ok) {
      PracticeFeedbackService.success();
      setState(() => _correct = true);
      return;
    }

    PracticeFeedbackService.error();
    final attempted = _selected.toList(growable: false);
    setState(() {
      _correct = false;
      _resolvingWrongAnswer = true;
      for (final value in attempted) {
        _shakeSignals[value] = (_shakeSignals[value] ?? 0) + 1;
      }
    });

    await Future<void>.delayed(const Duration(milliseconds: 380));
    if (!mounted) return;
    setState(() {
      _selected.clear();
      _resolvingWrongAnswer = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (widget.header != null) ...[
          Center(
            child: Text(
              widget.header!,
              style: const TextStyle(fontSize: 52, fontWeight: FontWeight.w700),
            ),
          ),
          const SizedBox(height: 16),
        ],
        Text(
          widget.answers.length <= 1
              ? 'Toca la respuesta correcta.'
              : 'Selecciona ${widget.answers.length} opciones. Se comprueban automáticamente.',
          textAlign: TextAlign.center,
          style: Theme.of(context).textTheme.bodySmall,
        ),
        const SizedBox(height: 10),
        Wrap(
          alignment: WrapAlignment.center,
          spacing: widget.joinAsSentence ? 4 : 10,
          runSpacing: 10,
          children: [
            for (final item in _items)
              ShakeFeedback(
                signal: _shakeSignals[item] ?? 0,
                child: FilterChip(
                  selected: _selected.contains(item),
                  onSelected: _resolvingWrongAnswer || _correct == true
                      ? null
                      : (_) => _toggle(item),
                  label: Text(
                    item,
                    style: TextStyle(
                      fontSize: widget.joinAsSentence ? 22.0 : 30.0,
                    ),
                  ),
                ),
              ),
          ],
        ),
        if (_correct != null) ...[
          const SizedBox(height: 16),
          ActivityFeedback(
            isCorrect: _correct!,
            message: _correct!
                ? _correctMessage(widget.activity)
                : _wrongMessage(widget.activity),
          ),
        ],
      ],
    );
  }
}

class _HangulRecallGrid extends StatefulWidget {
  const _HangulRecallGrid({required this.activity});
  final ActivityContent activity;

  @override
  State<_HangulRecallGrid> createState() => _HangulRecallGridState();
}

class _HangulRecallGridState extends State<_HangulRecallGrid> {
  late final List<Map<String, String>> _items;
  late final List<TextEditingController> _controllers;
  List<bool>? _results;

  @override
  void initState() {
    super.initState();
    _items = ActivityShuffle.copy<Map<String, String>>(
      (widget.activity.payload['items'] as List? ?? const []).map((raw) {
        final map = Map<String, dynamic>.from(raw as Map);
        return {
          'cue': map['cue'].toString(),
          'answer': map['answer'].toString(),
        };
      }),
    );
    _controllers = List.generate(_items.length, (_) => TextEditingController());
  }

  @override
  void dispose() {
    for (final controller in _controllers) {
      controller.dispose();
    }
    super.dispose();
  }

  void _check() {
    final results = <bool>[];
    for (var i=0; i<_items.length; i++) {
      results.add(AnswerNormalizer.equals(
        _controllers[i].text,
        _items[i]['answer']!,
        widget.activity.normalization,
      ));
    }
    final ok = results.isNotEmpty && results.every((value) => value);
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'answers': [for (final c in _controllers) c.text],
      'results': results,
      'complete': ok,
      'record_attempt': true,
      'score': results.isEmpty ? 0.0 : results.where((x)=>x).length / results.length,
    });
    setState(() => _results = results);
  }

  @override
  Widget build(BuildContext context) {
    final allCorrect = _results != null && _results!.every((e) => e);
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i=0; i<_items.length; i++) ...[
          Row(
            children: [
              SizedBox(
                width: 72,
                child: Text(_items[i]['cue']!, style: Theme.of(context).textTheme.titleLarge),
              ),
              Expanded(
                child: TextField(
                  controller: _controllers[i],
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 30),
                  decoration: InputDecoration(
                    border: const OutlineInputBorder(),
                    hintText: '한글',
                    suffixIcon: _results == null
                        ? null
                        : Icon(
                            _results![i] ? Icons.check_circle : Icons.cancel,
                            color: _results![i]
                                ? Theme.of(context).colorScheme.primary
                                : Theme.of(context).colorScheme.error,
                          ),
                  ),
                  onChanged: (_) {
                    if (_results != null) setState(() => _results = null);
                  },
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
        ],
        FilledButton(onPressed: _check, child: const Text('Comprobar todo')),
        if (_results != null) ...[
          const SizedBox(height: 12),
          ActivityFeedback(
            isCorrect: allCorrect,
            message: allCorrect ? _correctMessage(widget.activity) : _wrongMessage(widget.activity),
          ),
        ],
      ],
    );
  }
}

class _UnsupportedHangulStructure extends StatelessWidget {
  const _UnsupportedHangulStructure({required this.activity});
  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    return Text('Tipo Hangul no implementado: ${activity.type}');
  }
}
