import 'package:flutter/material.dart';

import '../../../core/models/activity_content.dart';
import '../../../core/practice/activity_shuffle.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../../../core/services/practice_feedback_service.dart';
import '../widgets/activity_feedback.dart';
import '../widgets/shake_feedback.dart';

class MatchingActivityRenderer extends StatefulWidget {
  const MatchingActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  State<MatchingActivityRenderer> createState() =>
      _MatchingActivityRendererState();
}

class _MatchingActivityRendererState extends State<MatchingActivityRenderer> {
  String? _activePairId;
  final Set<String> _resolvedPairIds = <String>{};
  final Set<String> _usedRightOptionIds = <String>{};
  final Map<String, int> _leftShakeSignals = <String, int>{};
  final Map<String, int> _rightShakeSignals = <String, int>{};
  bool? _correct;
  bool _resolvingWrongPair = false;

  late final List<_MatchPair> _pairs;
  late final List<_RightOption> _rightOptions;

  @override
  void initState() {
    super.initState();
    final parsedPairs = <_MatchPair>[];
    final rawPairs = widget.activity.payload['pairs'] as List? ?? const [];
    for (var i = 0; i < rawPairs.length; i++) {
      final map = Map<String, dynamic>.from(rawPairs[i] as Map);
      parsedPairs.add(
        _MatchPair(
          id: '${widget.activity.id}:pair:$i',
          left: map['left'].toString(),
          right: map['right'].toString(),
        ),
      );
    }

    _pairs = ActivityShuffle.copy<_MatchPair>(parsedPairs);
    _rightOptions = ActivityShuffle.copy<_RightOption>([
      for (final pair in parsedPairs)
        _RightOption(
          id: '${pair.id}:right',
          pairId: pair.id,
          label: pair.right,
        ),
    ]);
  }

  _MatchPair? _pairById(String id) {
    for (final pair in _pairs) {
      if (pair.id == id) return pair;
    }
    return null;
  }

  void _selectLeft(_MatchPair pair) {
    if (_resolvingWrongPair ||
        _resolvedPairIds.contains(pair.id) ||
        _correct == true) {
      return;
    }
    setState(() {
      _activePairId = pair.id;
      _correct = null;
    });
  }

  Future<void> _assign(_RightOption option) async {
    final pairId = _activePairId;
    if (pairId == null ||
        _resolvingWrongPair ||
        _usedRightOptionIds.contains(option.id) ||
        _correct == true) {
      return;
    }

    final activePair = _pairById(pairId);
    final ok = activePair != null && option.label == activePair.right;
    if (!ok) {
      PracticeFeedbackService.error();
      setState(() {
        _correct = false;
        _resolvingWrongPair = true;
        _leftShakeSignals[pairId] = (_leftShakeSignals[pairId] ?? 0) + 1;
        _rightShakeSignals[option.id] =
            (_rightShakeSignals[option.id] ?? 0) + 1;
      });
      await Future<void>.delayed(const Duration(milliseconds: 360));
      if (!mounted) return;
      setState(() {
        _activePairId = null;
        _resolvingWrongPair = false;
      });
      return;
    }

    PracticeFeedbackService.success();
    setState(() {
      _resolvedPairIds.add(pairId);
      _usedRightOptionIds.add(option.id);
      _activePairId = null;
      _correct = null;
    });

    if (_resolvedPairIds.length == _pairs.length && _pairs.isNotEmpty) {
      AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
        // Preserve the existing session-data contract (left -> right).
        // Duplicate left labels are rejected by the content audit because they
        // would be visually ambiguous for the learner.
        'matches': <String, String>{
          for (final pair in _pairs)
            if (_resolvedPairIds.contains(pair.id)) pair.left: pair.right,
        },
        'complete': true,
        'record_attempt': true,
        'score': 1.0,
      });
      setState(() => _correct = true);
    }
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text(
          'Toca un elemento de la izquierda y luego su pareja. Si no coincide, puedes probar otra vez.',
          style: Theme.of(context).textTheme.bodySmall,
        ),
        const SizedBox(height: 10),
        Text(
          '${_resolvedPairIds.length}/${_pairs.length} parejas resueltas',
          style: Theme.of(context).textTheme.labelLarge,
        ),
        const SizedBox(height: 12),
        for (final pair in _pairs)
          ShakeFeedback(
            signal: _leftShakeSignals[pair.id] ?? 0,
            child: Card(
              key: ValueKey(pair.id),
              color: _resolvedPairIds.contains(pair.id)
                  ? scheme.primaryContainer
                  : _activePairId == pair.id
                      ? scheme.secondaryContainer
                      : null,
              child: ListTile(
                onTap: () => _selectLeft(pair),
                leading: Icon(
                  _resolvedPairIds.contains(pair.id)
                      ? Icons.check_circle
                      : _activePairId == pair.id
                          ? Icons.touch_app
                          : Icons.radio_button_unchecked,
                ),
                title: Text(
                  pair.left,
                  style: const TextStyle(fontSize: 18),
                ),
                trailing: _resolvedPairIds.contains(pair.id)
                    ? Chip(label: Text(pair.right))
                    : const Icon(Icons.chevron_right),
              ),
            ),
          ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            for (final option in _rightOptions)
              ShakeFeedback(
                signal: _rightShakeSignals[option.id] ?? 0,
                child: ActionChip(
                  key: ValueKey(option.id),
                  onPressed: _activePairId == null ||
                          _resolvingWrongPair ||
                          _usedRightOptionIds.contains(option.id) ||
                          _correct == true
                      ? null
                      : () => _assign(option),
                  avatar: _usedRightOptionIds.contains(option.id)
                      ? const Icon(Icons.check, size: 18)
                      : null,
                  label: Text(option.label),
                ),
              ),
          ],
        ),
        if (_correct != null) ...[
          const SizedBox(height: 16),
          ActivityFeedback(
            isCorrect: _correct!,
            message: _correct!
                ? widget.activity.feedback['correct']?.toString() ??
                    'Todas las parejas están correctas.'
                : ((widget.activity.feedback['wrong'] as Map?)?['default']
                        ?.toString() ??
                    'Esa pareja no corresponde. Prueba otra vez.'),
          ),
        ],
      ],
    );
  }
}

class _MatchPair {
  const _MatchPair({
    required this.id,
    required this.left,
    required this.right,
  });

  final String id;
  final String left;
  final String right;
}

class _RightOption {
  const _RightOption({
    required this.id,
    required this.pairId,
    required this.label,
  });

  final String id;
  final String pairId;
  final String label;
}
