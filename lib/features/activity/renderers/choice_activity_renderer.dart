import 'dart:async';
import 'dart:math';

import 'package:flutter/material.dart';

import '../../../core/models/activity_content.dart';
import '../../../core/practice/activity_shuffle.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../../../core/services/practice_feedback_service.dart';
import '../widgets/activity_feedback.dart';
import '../widgets/shake_feedback.dart';
import '../widgets/tts_controls.dart';

class _ChoiceRound {
  const _ChoiceRound({
    required this.prompt,
    required this.options,
    required this.correct,
    this.tts,
    this.feedbackMessage,
  });

  final String prompt;
  final List<dynamic> options;
  final List<String> correct;
  final dynamic tts;
  final String? feedbackMessage;
}

class ChoiceActivityRenderer extends StatefulWidget {
  const ChoiceActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  State<ChoiceActivityRenderer> createState() => _ChoiceActivityRendererState();
}

class _ChoiceActivityRendererState extends State<ChoiceActivityRenderer> {
  late final List<_ChoiceRound> _rounds;
  int _roundIndex = 0;
  late List<dynamic> _currentShuffledOptions;
  final Set<String> _selected = <String>{};
  bool? _correct;
  bool _resolvingWrongAnswer = false;
  final Map<String, int> _shakeSignals = <String, int>{};
  bool _activityCompleted = false;

  _ChoiceRound get _currentRound => _rounds[_roundIndex];

  Timer? _autoPlayTimer;

  List<dynamic> get _options => _currentShuffledOptions;

  @override
  void initState() {
    super.initState();
    _rounds = _buildRounds();
    _prepareRoundOptions();
    WidgetsBinding.instance.addPostFrameCallback((_) => _triggerAutoPlay());
  }

  @override
  void dispose() {
    _autoPlayTimer?.cancel();
    super.dispose();
  }

  void _triggerAutoPlay() {
    if (!mounted) return;
    _autoPlayTimer?.cancel();
    final tts = _currentRound.tts;
    if (widget.activity.type == 'listen_and_choose' ||
        (widget.activity.capabilities['hasTts'] == true && tts != null)) {
      final text = tts is Map ? (tts['text'] ?? '').toString() : tts?.toString() ?? '';
      final locale = tts is Map ? (tts['locale'] ?? 'ko-KR').toString() : 'ko-KR';
      if (text.isNotEmpty) {
        _autoPlayTimer = Timer(const Duration(milliseconds: 320), () async {
          if (!mounted) return;
          try {
            final runtime = AdapaRuntime.of(context);
            await runtime.tts.speak(
              text,
              locale: locale,
              rate: runtime.settings.normalTtsRate,
            );
          } catch (_) {
            _showTtsUnavailableMessage();
          }
        });
      }
    }
  }

  Future<void> _onTileTap(String val) async {
    if (_resolvingWrongAnswer || _correct == true) return;
    _choose(val);
    if (_containsKorean(val) && widget.activity.type != 'listen_and_choose') {
      try {
        final runtime = AdapaRuntime.of(context);
        await runtime.tts.speak(val, rate: runtime.settings.normalTtsRate);
      } catch (_) {
        _showTtsUnavailableMessage();
      }
    }
  }

  bool _containsKorean(String value) =>
      RegExp(r'[\u1100-\u11FF\u3130-\u318F\uAC00-\uD7AF]').hasMatch(value);

  void _showTtsUnavailableMessage() {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('No hay una voz coreana disponible.')),
    );
  }

  List<_ChoiceRound> _buildRounds() {
    final payload = widget.activity.payload;

    // 1. Explicit rounds array
    final rawRounds = payload['rounds'];
    if (rawRounds is List && rawRounds.isNotEmpty) {
      final rounds = <_ChoiceRound>[];
      for (final r in rawRounds) {
        if (r is Map) {
          final opts = r['options'] as List? ?? [];
          final corr = (r['correct'] as List? ?? [r['correct']])
              .where((e) => e != null)
              .map((e) => e.toString())
              .toList();
          rounds.add(
            _ChoiceRound(
              prompt: r['prompt']?.toString() ?? widget.activity.prompt ?? '',
              options: opts,
              correct: corr,
              tts: r['tts'],
              feedbackMessage: r['feedback']?.toString(),
            ),
          );
        }
      }
      if (rounds.isNotEmpty) return rounds;
    }

    // 2. Multi-item pool (e.g. all 10 vowels or consonants in a lesson)
    final items = payload['items'];
    if (items is List && items.isNotEmpty) {
      final allTargets = <String>[];
      for (final it in items) {
        if (it is Map) {
          final t = (it['target'] ?? it['hangul'] ?? it['ko'] ?? it['value'] ?? it['id'])?.toString();
          if (t != null && t.isNotEmpty) allTargets.add(t);
        } else if (it is String) {
          allTargets.add(it);
        }
      }

      final optionsPerRound = (payload['options_per_round'] as num?)?.toInt() ?? 3;
      final rounds = <_ChoiceRound>[];
      final random = Random();

      for (final it in items) {
        if (it is Map) {
          final target = (it['target'] ?? it['hangul'] ?? it['ko'] ?? it['value'] ?? it['id'])?.toString() ?? '';
          final hintLabel = (it['romanization'] ?? it['meaning_es'] ?? it['sound'] ?? it['help_es'] ?? it['label'])?.toString() ?? '';
          final prompt = it['prompt']?.toString() ?? 'Selecciona la opción que corresponde a «$hintLabel»:';
          
          List<dynamic> roundOptions;
          if (it['options'] is List && (it['options'] as List).isNotEmpty) {
            roundOptions = (it['options'] as List).toList();
          } else {
            // Pick distractors from pool
            final distractors = allTargets.where((t) => t != target).toList()..shuffle(random);
            final picked = distractors.take(optionsPerRound - 1).toList();
            roundOptions = [...picked, target]..shuffle(random);
          }

          dynamic tts = it['tts'] ?? it['tts_payload'];
          if (tts == null && (it['sound'] != null || it['tts_text'] != null || target.isNotEmpty)) {
            tts = {
              'text': (it['sound'] ?? it['tts_text'] ?? target).toString(),
              'locale': 'ko-KR',
            };
          }

          rounds.add(
            _ChoiceRound(
              prompt: prompt,
              options: roundOptions,
              correct: [target],
              tts: tts,
              feedbackMessage: it['feedback']?.toString(),
            ),
          );
        }
      }
      if (rounds.isNotEmpty) return rounds;
    }

    // 3. Fallback: single round
    final opts = payload['options'] as List? ?? const [];
    final corr = (payload['correct'] as List? ?? const [])
        .map((e) => e.toString())
        .toList();
    return [
      _ChoiceRound(
        prompt: widget.activity.prompt ?? '',
        options: opts,
        correct: corr,
        tts: payload['tts'],
      ),
    ];
  }

  void _prepareRoundOptions() {
    _currentShuffledOptions = ActivityShuffle.copy<dynamic>(_currentRound.options);
  }

  List<String> get _answers => _currentRound.correct;

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
      'complete': ok && (_rounds.length <= 1 || _roundIndex >= _rounds.length - 1),
      'record_attempt': true,
      'score': ok ? 1.0 : 0.0,
    });

    if (ok) {
      PracticeFeedbackService.success();
      setState(() {
        _correct = true;
        if (_rounds.length <= 1 || _roundIndex >= _rounds.length - 1) {
          _activityCompleted = true;
        }
      });
      if (_rounds.length > 1 && _roundIndex < _rounds.length - 1) {
        await Future<void>.delayed(const Duration(milliseconds: 480));
        if (!mounted) return;
        setState(() {
          _roundIndex++;
          _prepareRoundOptions();
          _selected.clear();
          _correct = null;
          _resolvingWrongAnswer = false;
        });
        _triggerAutoPlay();
      }
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
      'complete': ok && (_rounds.length <= 1 || _roundIndex >= _rounds.length - 1),
      'record_attempt': true,
      'score': ok ? 1.0 : 0.0,
    });

    if (ok) {
      PracticeFeedbackService.success();
      if (_rounds.length > 1 && _roundIndex < _rounds.length - 1) {
        await Future<void>.delayed(const Duration(milliseconds: 480));
        if (!mounted) return;
        setState(() {
          _roundIndex++;
          _prepareRoundOptions();
          _selected.clear();
          _correct = null;
          _resolvingWrongAnswer = false;
        });
        _triggerAutoPlay();
      } else {
        setState(() => _activityCompleted = true);
      }
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
    if (_activityCompleted || _correct == true) {
      if (_currentRound.feedbackMessage != null) {
        return _currentRound.feedbackMessage!;
      }
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
    final tts = _currentRound.tts;
    final totalRounds = _rounds.length;
    final scheme = Theme.of(context).colorScheme;
    final isShortCharacterGrid = !_isMultiAnswer &&
        (_rounds.length > 1 || widget.activity.payload['display_mode'] == 'grid') &&
        _options.isNotEmpty &&
        _options.every((opt) => _labelOf(opt).replaceAll('\n', '').length <= 4) &&
        widget.activity.type != 'image_choice';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (totalRounds > 1) ...[
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: scheme.surfaceContainerHighest.withValues(alpha: 0.5),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: scheme.primaryContainer,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '${_roundIndex + 1} / $totalRounds',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 13,
                      color: scheme.onPrimaryContainer,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(6),
                    child: LinearProgressIndicator(
                      value: (_roundIndex + (_activityCompleted ? 1 : 0)) / totalRounds,
                      minHeight: 8,
                      backgroundColor: scheme.surfaceContainerHigh,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          Text(
            _currentRound.prompt,
            style: Theme.of(context).textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 10),
        ],
        Builder(
          builder: (context) {
            final ttsText = tts is Map ? (tts['text'] ?? '').toString() : (tts?.toString() ?? '');
            final ttsLocale = tts is Map ? (tts['locale'] ?? 'ko-KR').toString() : 'ko-KR';
            final slowAvail = tts is Map ? tts['slow_available'] != false : true;
            if (ttsText.trim().isNotEmpty) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 16),
                child: TtsControls(
                  text: ttsText,
                  locale: ttsLocale,
                  slowAvailable: slowAvail,
                ),
              );
            }
            return const SizedBox.shrink();
          },
        ),
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
        else if (isShortCharacterGrid)
          _CharacterTileGrid(
            options: _options,
            selected: _selected,
            correct: _correct,
            shakeSignals: _shakeSignals,
            enabled: !_resolvingWrongAnswer && _correct != true,
            valueOf: _valueOf,
            labelOf: _labelOf,
            onSelected: _onTileTap,
          )
        else
          for (final option in _options)
            Builder(
              builder: (context) {
                final value = _valueOf(option);
                final selected = _selected.contains(value);
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
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
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

class _CharacterTileGrid extends StatelessWidget {
  const _CharacterTileGrid({
    required this.options,
    required this.selected,
    required this.correct,
    required this.shakeSignals,
    required this.enabled,
    required this.valueOf,
    required this.labelOf,
    required this.onSelected,
  });

  final List<dynamic> options;
  final Set<String> selected;
  final bool? correct;
  final Map<String, int> shakeSignals;
  final bool enabled;
  final String Function(dynamic) valueOf;
  final String Function(dynamic) labelOf;
  final ValueChanged<String> onSelected;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;

    Widget buildTile(dynamic option) {
      final val = valueOf(option);
      final isSelected = selected.contains(val);
      final label = labelOf(option);

      final borderColor = !isSelected
          ? scheme.outlineVariant
          : correct == true
              ? scheme.primary
              : correct == false
                  ? scheme.error
                  : scheme.secondary;

      final cardColor = !isSelected
          ? scheme.surfaceContainerLow
          : correct == true
              ? scheme.primaryContainer
              : correct == false
                  ? scheme.errorContainer
                  : scheme.secondaryContainer;

      return ShakeFeedback(
        signal: shakeSignals[val] ?? 0,
        child: Material(
          color: cardColor,
          borderRadius: BorderRadius.circular(18),
          elevation: isSelected ? 2 : 0,
          child: InkWell(
            borderRadius: BorderRadius.circular(18),
            onTap: enabled ? () => onSelected(val) : null,
            child: Container(
              height: options.length <= 3 ? 96 : 84,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(18),
                border: Border.all(
                  width: isSelected ? 3 : 1.5,
                  color: borderColor,
                ),
              ),
              alignment: Alignment.center,
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 8),
              child: Text(
                label,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: label.length <= 2 ? 36 : 22,
                  fontWeight: FontWeight.w800,
                  color: !isSelected
                      ? scheme.onSurface
                      : correct == true
                          ? scheme.onPrimaryContainer
                          : correct == false
                              ? scheme.onErrorContainer
                              : scheme.onSecondaryContainer,
                ),
              ),
            ),
          ),
        ),
      );
    }

    if (options.length <= 3) {
      return Row(
        children: [
          for (int i = 0; i < options.length; i++) ...[
            if (i > 0) const SizedBox(width: 10),
            Expanded(child: buildTile(options[i])),
          ],
        ],
      );
    }

    final rows = <Widget>[];
    for (int i = 0; i < options.length; i += 2) {
      final hasSecond = i + 1 < options.length;
      rows.add(
        Row(
          children: [
            Expanded(child: buildTile(options[i])),
            const SizedBox(width: 10),
            Expanded(
              child: hasSecond ? buildTile(options[i + 1]) : const SizedBox(),
            ),
          ],
        ),
      );
      if (i + 2 < options.length) {
        rows.add(const SizedBox(height: 10));
      }
    }
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: rows,
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
