import 'package:flutter/material.dart';

import '../../../core/models/activity_content.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../widgets/activity_feedback.dart';

class MatchingActivityRenderer extends StatefulWidget {
  const MatchingActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  State<MatchingActivityRenderer> createState() => _MatchingActivityRendererState();
}

class _MatchingActivityRendererState extends State<MatchingActivityRenderer> {
  String? _activeLeft;
  final Map<String, String> _matches = {};
  bool? _correct;

  late final List<Map<String, String>> _pairs;
  late final List<String> _rightOptions;

  @override
  void initState() {
    super.initState();
    _pairs = (widget.activity.payload['pairs'] as List? ?? const [])
        .map((raw) {
          final map = Map<String, dynamic>.from(raw as Map);
          return {
            'left': map['left'].toString(),
            'right': map['right'].toString(),
          };
        })
        .toList(growable: false);
    _rightOptions = _pairs
        .map((pair) => pair['right']!)
        .toList(growable: false)
        .reversed
        .toList(growable: false);
  }

  void _assign(String right) {
    final left = _activeLeft;
    if (left == null) return;
    setState(() {
      _matches.removeWhere((_, value) => value == right);
      _matches[left] = right;
      _activeLeft = null;
      _correct = null;
    });
  }

  void _check() {
    final ok = _pairs.every(
      (pair) => _matches[pair['left']] == pair['right'],
    );
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'matches': Map<String, String>.from(_matches),
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
        Text(
          '1. Toca un elemento de la izquierda.  2. Toca su pareja.',
          style: Theme.of(context).textTheme.bodySmall,
        ),
        const SizedBox(height: 12),
        for (final pair in _pairs)
          Card(
            color: _activeLeft == pair['left']
                ? Theme.of(context).colorScheme.primaryContainer
                : null,
            child: ListTile(
              onTap: () => setState(() {
                _activeLeft = pair['left'];
                _correct = null;
              }),
              title: Text(pair['left']!, style: const TextStyle(fontSize: 18)),
              trailing: _matches[pair['left']] == null
                  ? const Icon(Icons.touch_app_outlined)
                  : Chip(label: Text(_matches[pair['left']]!)),
            ),
          ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            for (final right in _rightOptions)
              ActionChip(
                onPressed: _activeLeft == null ? null : () => _assign(right),
                avatar: _matches.containsValue(right)
                    ? const Icon(Icons.check, size: 18)
                    : null,
                label: Text(right),
              ),
          ],
        ),
        const SizedBox(height: 16),
        FilledButton(
          onPressed: _matches.length == _pairs.length ? _check : null,
          child: const Text('Comprobar parejas'),
        ),
        if (_correct != null) ...[
          const SizedBox(height: 12),
          ActivityFeedback(
            isCorrect: _correct!,
            message: _correct!
                ? widget.activity.feedback['correct']?.toString() ?? 'Correcto.'
                : ((widget.activity.feedback['wrong'] as Map?)?['default']?.toString() ??
                    'Hay alguna pareja incorrecta. Revisa e inténtalo de nuevo.'),
          ),
        ],
      ],
    );
  }
}
