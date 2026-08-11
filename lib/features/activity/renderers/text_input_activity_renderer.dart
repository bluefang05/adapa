import 'dart:async';

import 'package:flutter/material.dart';

import '../../../core/evaluation/activity_evaluator.dart';
import '../../../core/models/activity_content.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../../../core/session/activity_session_store.dart';
import '../widgets/activity_feedback.dart';
import '../widgets/tts_controls.dart';

class TextInputActivityRenderer extends StatefulWidget {
  const TextInputActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  State<TextInputActivityRenderer> createState() => _TextInputActivityRendererState();
}

class _TextInputActivityRendererState extends State<TextInputActivityRenderer> {
  final _controller = TextEditingController();
  EvaluationResult? _result;
  ActivitySessionStore? _sessionStore;
  Timer? _autosaveTimer;

  bool get _multiline => widget.activity.type == 'free_writing' ||
      widget.activity.type == 'copy_practice';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final saved = AdapaRuntime.of(context).sessionStore.read(widget.activity.id);
      final text = saved?['text']?.toString();
      if (text != null && text.isNotEmpty) {
        _controller.text = text;
      }
    });
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _sessionStore ??= AdapaRuntime.of(context).sessionStore;
  }

  void _persistDraft() {
    final store = _sessionStore;
    if (store == null || widget.activity.payload['autosave'] != true) return;
    final previous = store.read(widget.activity.id);
    store.write(widget.activity.id, {
      'text': _controller.text,
      'complete': previous?['complete'] == true,
    });
  }

  void _scheduleDraftSave() {
    _autosaveTimer?.cancel();
    _autosaveTimer = Timer(const Duration(milliseconds: 500), _persistDraft);
  }

  @override
  void dispose() {
    if (_autosaveTimer?.isActive == true) {
      _autosaveTimer!.cancel();
      _persistDraft();
    }
    _controller.dispose();
    super.dispose();
  }

  void _check() {
    final result = ActivityEvaluator.text(
      value: _controller.text,
      scoreMode: widget.activity.scoreMode,
      completionRule: widget.activity.completionRule,
      payload: widget.activity.payload,
      normalization: widget.activity.normalization,
    );
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'text': _controller.text,
      'complete': result.isCorrect,
      'score_mode': widget.activity.scoreMode,
      'record_attempt': true,
      if (widget.activity.scoreMode != 'none') 'score': result.isCorrect ? 1.0 : 0.0,
    });
    setState(() => _result = result);
  }

  String _message(EvaluationResult result) {
    if (result.message != null) return result.message!;
    if (result.isCorrect) {
      return widget.activity.feedback['correct']?.toString() ??
          (widget.activity.scoreMode == 'none' ? 'Actividad completada.' : 'Correcto.');
    }
    final wrong = widget.activity.feedback['wrong'];
    if (wrong is Map && wrong['default'] != null) return wrong['default'].toString();
    return 'Revisa la respuesta y vuelve a intentarlo.';
  }

  @override
  Widget build(BuildContext context) {
    final tts = widget.activity.payload['tts'];
    final template = widget.activity.payload['template']?.toString();
    final wordBank = (widget.activity.payload['word_bank'] as List? ?? const [])
        .map((e) => e.toString())
        .toList(growable: false);
    final examples = (widget.activity.payload['examples'] as List? ?? const [])
        .map((e) => e.toString())
        .toList(growable: false);
    final sourceTextRef = widget.activity.payload['source_text_ref']?.toString();

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
        if (sourceTextRef != null) ...[
          FutureBuilder<String?>(
            future: AdapaRuntime.of(context).assetResolver.readingText(sourceTextRef),
            builder: (context, snapshot) => Card(
              child: Padding(
                padding: const EdgeInsets.all(14),
                child: snapshot.hasData
                    ? SelectableText(snapshot.data!, style: const TextStyle(fontSize: 18))
                    : const LinearProgressIndicator(),
              ),
            ),
          ),
          const SizedBox(height: 12),
        ],
        if (template != null) ...[
          Card(
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Text(template, style: Theme.of(context).textTheme.titleMedium),
            ),
          ),
          const SizedBox(height: 12),
        ],
        if (wordBank.isNotEmpty) ...[
          Text('Banco de palabras', style: Theme.of(context).textTheme.labelLarge),
          const SizedBox(height: 6),
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: [for (final word in wordBank) Chip(label: Text(word))],
          ),
          const SizedBox(height: 14),
        ],
        if (examples.isNotEmpty) ...[
          ExpansionTile(
            tilePadding: EdgeInsets.zero,
            title: const Text('Ver ejemplos'),
            children: [for (final item in examples) ListTile(title: Text(item))],
          ),
          const SizedBox(height: 8),
        ],
        TextField(
          controller: _controller,
          minLines: _multiline ? 5 : 1,
          maxLines: _multiline ? 10 : 2,
          textInputAction: _multiline ? TextInputAction.newline : TextInputAction.done,
          decoration: InputDecoration(
            border: const OutlineInputBorder(),
            labelText: _multiline ? 'Escribe aquí' : 'Tu respuesta',
            helperText: widget.activity.type == 'free_writing'
                ? 'Escribe una frase por línea.'
                : null,
          ),
          onChanged: (value) {
            if (_result != null) setState(() => _result = null);
            if (widget.activity.payload['autosave'] == true) {
              _scheduleDraftSave();
            }
          },
          onSubmitted: _multiline ? null : (_) => _check(),
        ),
        const SizedBox(height: 12),
        FilledButton(
          onPressed: _check,
          child: Text(widget.activity.scoreMode == 'none' ? 'Guardar / continuar' : 'Comprobar'),
        ),
        if (_result != null) ...[
          const SizedBox(height: 12),
          ActivityFeedback(
            isCorrect: _result!.isCorrect,
            message: _message(_result!),
          ),
        ],
      ],
    );
  }
}
