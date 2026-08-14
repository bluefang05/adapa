import 'package:flutter/material.dart';

import '../../../core/evaluation/answer_normalizer.dart';
import '../../../core/models/activity_content.dart';
import '../../../core/practice/activity_shuffle.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../widgets/activity_feedback.dart';

class OrderingActivityRenderer extends StatefulWidget {
  const OrderingActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  State<OrderingActivityRenderer> createState() =>
      _OrderingActivityRendererState();
}

class _OrderingActivityRendererState extends State<OrderingActivityRenderer> {
  late List<_OrderItem> _items;
  bool? _correct;

  @override
  void initState() {
    super.initState();
    final values = _initialValues();
    final indexed = <_OrderItem>[
      for (var i = 0; i < values.length; i++)
        _OrderItem(
          id: '${widget.activity.id}:token:$i',
          value: values[i],
        ),
    ];
    _items = ActivityShuffle.differentFromBy<_OrderItem, String>(
      indexed,
      _answer,
      (item) => item.value,
    );
  }

  List<String> _initialValues() {
    final source =
        widget.activity.payload['tokens'] ?? widget.activity.payload['turns'];
    return (source as List? ?? const [])
        .map((e) => e.toString())
        .toList(growable: false);
  }

  List<String> get _answer =>
      (widget.activity.payload['correct_order'] as List? ?? const [])
          .map((e) => e.toString())
          .toList(growable: false);

  void _check() {
    final rules = widget.activity.normalization;
    final ok = _items.length == _answer.length &&
        List.generate(
          _items.length,
          (i) => AnswerNormalizer.equals(
            _items[i].value,
            _answer[i],
            rules,
          ),
        ).every((value) => value);

    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'order': [for (final item in _items) item.value],
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
          'Mantén pulsado y arrastra para cambiar el orden.',
          style: Theme.of(context).textTheme.bodySmall,
        ),
        const SizedBox(height: 10),
        ReorderableListView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: _items.length,
          onReorderItem: (oldIndex, newIndex) {
            setState(() {
              // Flutter 3.44+ onReorderItem already adjusts newIndex for the
              // removal at oldIndex. Do not apply the legacy decrement here.
              final item = _items.removeAt(oldIndex);
              _items.insert(newIndex, item);
              _correct = null;
            });
          },
          itemBuilder: (context, index) {
            final item = _items[index];
            return Card(
              key: ValueKey(item.id),
              child: ListTile(
                leading: CircleAvatar(child: Text('${index + 1}')),
                title: Text(
                  item.value,
                  style: const TextStyle(fontSize: 17),
                ),
                trailing: const Icon(Icons.drag_handle),
              ),
            );
          },
        ),
        const SizedBox(height: 12),
        FilledButton(
          onPressed: _check,
          child: const Text('Comprobar orden'),
        ),
        if (_correct != null) ...[
          const SizedBox(height: 12),
          ActivityFeedback(
            isCorrect: _correct!,
            message: _correct!
                ? widget.activity.feedback['correct']?.toString() ??
                    'Orden correcto.'
                : ((widget.activity.feedback['wrong'] as Map?)?['default']
                        ?.toString() ??
                    'El orden todavía no es correcto.'),
          ),
        ],
      ],
    );
  }
}

class _OrderItem {
  const _OrderItem({required this.id, required this.value});

  final String id;
  final String value;
}
