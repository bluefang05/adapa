import 'package:flutter/material.dart';

import '../../../core/models/activity_content.dart';
import '../../../core/practice/activity_shuffle.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../../../core/services/practice_feedback_service.dart';
import '../widgets/activity_feedback.dart';
import '../widgets/shake_feedback.dart';
import '../widgets/tts_controls.dart';

class ChoiceActivityRenderer extends StatefulWidget {
  const ChoiceActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  State<ChoiceActivityRenderer> createState() => _ChoiceActivityRendererState();
}

class _ChoiceActivityRendererState extends State<ChoiceActivityRenderer> {
  late final List<dynamic> _shuffledOptions;
  final Set<String> _selected = <String>{};
  bool? _correct;
  bool _resolvingWrongAnswer = false;
  final Map<String, int> _shakeSignals = <String, int>{};

  List<dynamic> get _options => _shuffledOptions;

  @override
  void initState() {
    super.initState();
    _shuffledOptions = ActivityShuffle.copy<dynamic>(
      widget.activity.payload['options'] as List? ?? const [],
    );
  }

  List<String> get _answers =>
      (widget.activity.payload['correct'] as List? ?? const [])
          .map((e) => e.toString())
          .toSet()
          .toList(growable: false);

  bool get _isMultiAnswer =>
      widget.activity.payload['selection_mode']?.toString() == 'multiple' ||
      widget.activity.payload['select_all_correct'] == true;

  String _valueOf(dynamic option) {
    if (option is Map) {
      return (option['ko'] ?? option['value'] ?? option['id'] ?? '').toString();
    }
    return option.toString();
  }

  String _labelOf(dynamic option) {
    if (option is Map) {
      final ko = option['ko']?.toString();
      final es = option['es']?.toString();
      if (ko != null && es != null) return '$ko\n$es';
      return (ko ??
              option['label_es'] ??
              option['label'] ??
              option['value'] ??
              option['id'] ??
              es ??
              '')
          .toString();
    }
    return option.toString();
  }

  Future<void> _choose(String value) async {
    if (_resolvingWrongAnswer || _correct == true) return;

    if (!_isMultiAnswer) {
      await _evaluateSingle(value);
      return;
    }

    setState(() {
      _correct = null;
      if (!_selected.add(value)) {
        _selected.remove(value);
      }
    });

    final expected = _answers.toSet();
    if (_selected.length < expected.length) return;

    final ok = _selected.length == expected.length &&
        _selected.containsAll(expected);

    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'selected': _selected.toList(growable: false),
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
      for (final selected in attempted) {
        _shakeSignals[selected] = (_shakeSignals[selected] ?? 0) + 1;
      }
    });

    await Future<void>.delayed(const Duration(milliseconds: 380));
    if (!mounted) return;
    setState(() {
      _selected.clear();
      _resolvingWrongAnswer = false;
    });
  }

  Future<void> _evaluateSingle(String value) async {
    final ok = _answers.contains(value);
    setState(() {
      _selected
        ..clear()
        ..add(value);
      _correct = ok;
    });

    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'selected': value,
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
      _shakeSignals[value] = (_shakeSignals[value] ?? 0) + 1;
    });

    await Future<void>.delayed(const Duration(milliseconds: 360));
    if (!mounted) return;
    setState(() {
      _selected.clear();
      _resolvingWrongAnswer = false;
    });
  }

  String _feedbackMessage() {
    if (_correct == true) {
      return widget.activity.feedback['correct']?.toString() ?? 'Correcto.';
    }
    final wrong = widget.activity.feedback['wrong'];
    if (wrong is Map) {
      if (!_isMultiAnswer && _selected.length == 1) {
        final specific = wrong[_selected.first];
        if (specific != null) return specific.toString();
      }
      if (wrong['default'] != null) return wrong['default'].toString();
    }
    return 'Inténtalo otra vez.';
  }

  @override
  Widget build(BuildContext context) {
    final tts = widget.activity.payload['tts'];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (tts is Map) ...[
          TtsControls(
            text: (tts['text'] ?? '').toString(),
            locale: (tts['locale'] ?? 'ko-KR').toString(),
            slowAvailable: tts['slow_available'] != false,
          ),
          const SizedBox(height: 16),
        ],
        Text(
          _isMultiAnswer
              ? 'Selecciona ${_answers.length} respuestas. Se comprueban automáticamente.'
              : 'Toca una respuesta. Si no es, prueba otra vez.',
          style: Theme.of(context).textTheme.bodySmall,
        ),
        const SizedBox(height: 10),
        if (widget.activity.type == 'image_choice')
          _ImageOptions(
            options: _options.map(_valueOf).toList(growable: false),
            selected: _selected,
            correct: _correct,
            shakeSignals: _shakeSignals,
            enabled: !_resolvingWrongAnswer && _correct != true,
            multiAnswer: _isMultiAnswer,
            onSelected: _choose,
          )
        else
          for (final option in _options)
            Builder(
              builder: (context) {
                final value = _valueOf(option);
                final selected = _selected.contains(value);
                final scheme = Theme.of(context).colorScheme;
                final cardColor = !selected
                    ? null
                    : _correct == true
                        ? scheme.primaryContainer
                        : _correct == false
                            ? scheme.errorContainer
                            : scheme.secondaryContainer;
                return ShakeFeedback(
                  signal: _shakeSignals[value] ?? 0,
                  child: Card(
                    margin: const EdgeInsets.only(bottom: 8),
                    color: cardColor,
                    child: ListTile(
                      onTap: _resolvingWrongAnswer || _correct == true
                          ? null
                          : () => _choose(value),
                      leading: Icon(
                        !selected
                            ? (_isMultiAnswer
                                ? Icons.check_box_outline_blank
                                : Icons.radio_button_off)
                            : _correct == true
                                ? Icons.check_circle
                                : _correct == false
                                    ? Icons.cancel_outlined
                                    : Icons.check_box,
                      ),
                      title: Text(
                        _labelOf(option),
                        style: const TextStyle(fontSize: 17),
                      ),
                    ),
                  ),
                );
              },
            ),
        if (_correct != null) ...[
          const SizedBox(height: 12),
          ActivityFeedback(
            isCorrect: _correct!,
            message: _feedbackMessage(),
          ),
        ],
      ],
    );
  }
}

class _ImageOptions extends StatelessWidget {
  const _ImageOptions({
    required this.options,
    required this.selected,
    required this.correct,
    required this.shakeSignals,
    required this.enabled,
    required this.multiAnswer,
    required this.onSelected,
  });

  final List<String> options;
  final Set<String> selected;
  final bool? correct;
  final Map<String, int> shakeSignals;
  final bool enabled;
  final bool multiAnswer;
  final ValueChanged<String> onSelected;

  @override
  Widget build(BuildContext context) {
    final resolver = AdapaRuntime.of(context).assetResolver;
    return LayoutBuilder(
      builder: (context, constraints) {
        final width = (constraints.maxWidth - 12) / 2;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            for (final id in options)
              FutureBuilder<String?>(
                future: resolver.visualAsset(id),
                builder: (context, snapshot) {
                  final isSelected = selected.contains(id);
                  final scheme = Theme.of(context).colorScheme;
                  final borderColor = !isSelected
                      ? scheme.outlineVariant
                      : correct == true
                          ? scheme.primary
                          : correct == false
                              ? scheme.error
                              : scheme.secondary;
                  return ShakeFeedback(
                    signal: shakeSignals[id] ?? 0,
                    child: InkWell(
                      borderRadius: BorderRadius.circular(18),
                      onTap: enabled ? () => onSelected(id) : null,
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 160),
                        width: width,
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(
                            width: isSelected ? 3 : 1,
                            color: borderColor,
                          ),
                        ),
                        child: AspectRatio(
                          aspectRatio: 1,
                          child: snapshot.data == null
                              ? const Center(child: CircularProgressIndicator())
                              : Stack(
                                  fit: StackFit.expand,
                                  children: [
                                    Image.asset(
                                      snapshot.data!,
                                      fit: BoxFit.contain,
                                    ),
                                    if (isSelected)
                                      Align(
                                        alignment: Alignment.topRight,
                                        child: Icon(
                                          correct == true
                                              ? Icons.check_circle
                                              : correct == false
                                                  ? Icons.cancel
                                                  : multiAnswer
                                                      ? Icons.check_box
                                                      : Icons.radio_button_checked,
                                          color: correct == true
                                              ? scheme.primary
                                              : correct == false
                                                  ? scheme.error
                                                  : scheme.secondary,
                                        ),
                                      ),
                                  ],
                                ),
                        ),
                      ),
                    ),
                  );
                },
              ),
          ],
        );
      },
    );
  }
}
