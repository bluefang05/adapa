import 'package:flutter/material.dart';

import '../../../core/models/activity_content.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../widgets/activity_feedback.dart';
import '../widgets/tts_controls.dart';

class ChoiceActivityRenderer extends StatefulWidget {
  const ChoiceActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  State<ChoiceActivityRenderer> createState() => _ChoiceActivityRendererState();
}

class _ChoiceActivityRendererState extends State<ChoiceActivityRenderer> {
  String? _selected;
  bool? _correct;

  List<dynamic> get _options =>
      (widget.activity.payload['options'] as List? ?? const []).toList(growable: false);

  List<String> get _answers =>
      (widget.activity.payload['correct'] as List? ?? const [])
          .map((e) => e.toString())
          .toList(growable: false);

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
      return (ko ?? es ?? option['label'] ?? '').toString();
    }
    return option.toString();
  }

  void _check() {
    if (_selected == null) return;
    final ok = _answers.contains(_selected);
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'selected': _selected,
      'complete': ok,
      'record_attempt': true,
      'score': ok ? 1.0 : 0.0,
    });
    setState(() => _correct = ok);
  }

  String _feedbackMessage() {
    if (_correct == true) {
      return widget.activity.feedback['correct']?.toString() ?? 'Correcto.';
    }
    final wrong = widget.activity.feedback['wrong'];
    if (wrong is Map) {
      final specific = wrong[_selected];
      if (specific != null) return specific.toString();
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
        if (widget.activity.type == 'image_choice')
          _ImageOptions(
            options: _options.map(_valueOf).toList(growable: false),
            selected: _selected,
            onSelected: (value) => setState(() {
              _selected = value;
              _correct = null;
            }),
          )
        else
          for (final option in _options)
            Card(
              margin: const EdgeInsets.only(bottom: 8),
              color: _selected == _valueOf(option)
                  ? Theme.of(context).colorScheme.primaryContainer
                  : null,
              child: ListTile(
                onTap: () => setState(() {
                  _selected = _valueOf(option);
                  _correct = null;
                }),
                leading: Icon(
                  _selected == _valueOf(option)
                      ? Icons.radio_button_checked
                      : Icons.radio_button_off,
                ),
                title: Text(
                  _labelOf(option),
                  style: const TextStyle(fontSize: 17),
                ),
              ),
            ),
        const SizedBox(height: 12),
        FilledButton(
          onPressed: _selected == null ? null : _check,
          child: const Text('Comprobar'),
        ),
        if (_correct != null) ...[
          const SizedBox(height: 12),
          ActivityFeedback(isCorrect: _correct!, message: _feedbackMessage()),
        ],
      ],
    );
  }
}

class _ImageOptions extends StatelessWidget {
  const _ImageOptions({
    required this.options,
    required this.selected,
    required this.onSelected,
  });

  final List<String> options;
  final String? selected;
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
                  final isSelected = selected == id;
                  return InkWell(
                    borderRadius: BorderRadius.circular(18),
                    onTap: () => onSelected(id),
                    child: AnimatedContainer(
                      duration: const Duration(milliseconds: 160),
                      width: width,
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(
                          width: isSelected ? 3 : 1,
                          color: isSelected
                              ? Theme.of(context).colorScheme.primary
                              : Theme.of(context).colorScheme.outlineVariant,
                        ),
                      ),
                      child: AspectRatio(
                        aspectRatio: 1,
                        child: snapshot.data == null
                            ? const Center(child: CircularProgressIndicator())
                            : Image.asset(snapshot.data!, fit: BoxFit.contain),
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
