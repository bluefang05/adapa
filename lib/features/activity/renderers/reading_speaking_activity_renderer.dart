import 'package:flutter/material.dart';

import '../../../core/models/activity_content.dart';
import '../../../core/reading/reading_practice_logic.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../widgets/tts_controls.dart';

class ReadingSpeakingActivityRenderer extends StatelessWidget {
  const ReadingSpeakingActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    return switch (activity.type) {
      'reading_challenge' => _AttemptThenListenActivity(activity: activity),
      'reading_aloud' => _AttemptThenListenActivity(activity: activity),
      'speaking_practice' => _SpeakingPracticeActivity(activity: activity),
      'tts_readback' => _TtsReadbackActivity(activity: activity),
      _ => Text('Tipo de lectura/pronunciación no soportado: ${activity.type}'),
    };
  }
}

class _AttemptThenListenActivity extends StatefulWidget {
  const _AttemptThenListenActivity({required this.activity});

  final ActivityContent activity;

  @override
  State<_AttemptThenListenActivity> createState() =>
      _AttemptThenListenActivityState();
}

class _AttemptThenListenActivityState extends State<_AttemptThenListenActivity> {
  bool _attempted = false;
  bool _listened = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final saved = AdapaRuntime.of(context).sessionStore.read(widget.activity.id);
      if (saved != null) {
        setState(() {
          _attempted = saved['attempted'] == true;
          _listened = saved['listened'] == true;
        });
      }
    });
  }

  void _save() {
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'attempted': _attempted,
      'listened': _listened,
      'complete': ReadingPracticeLogic.attemptThenListenComplete(
        attempted: _attempted,
        listened: _listened,
      ),
    });
  }

  void _markAttempted() {
    setState(() => _attempted = true);
    _save();
  }

  void _markListened() {
    setState(() => _listened = true);
    _save();
  }

  @override
  Widget build(BuildContext context) {
    final items = (widget.activity.payload['items'] as List? ?? const [])
        .map((e) => e.toString())
        .toList(growable: false);
    final readingRef = widget.activity.payload['reading_ref']?.toString();
    final ttsRaw = widget.activity.payload['tts'];

    return FutureBuilder<Map<String, dynamic>?>(
      future: readingRef == null
          ? Future<Map<String, dynamic>?>.value(null)
          : AdapaRuntime.of(context).assetResolver.reading(readingRef),
      builder: (context, snapshot) {
        final reading = snapshot.data;
        final readingText = reading?['text_ko']?.toString();
        final ttsText = ttsRaw is Map
            ? (ttsRaw['text'] ?? '').toString()
            : (reading?['tts'] ?? readingText ?? items.join(' ')).toString();
        final locale = ttsRaw is Map
            ? (ttsRaw['locale'] ?? 'ko-KR').toString()
            : 'ko-KR';
        final slow = ttsRaw is Map ? ttsRaw['slow_available'] != false : true;

        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (readingRef != null && snapshot.connectionState != ConnectionState.done)
              const LinearProgressIndicator(),
            if (readingText != null && readingText.isNotEmpty)
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: SelectableText(
                    readingText,
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                ),
              ),
            if (items.isNotEmpty) ...[
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  for (final item in items)
                    Chip(
                      label: Text(
                        item,
                        style: const TextStyle(fontSize: 18),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 16),
            ],
            if (!_attempted)
              FilledButton.icon(
                onPressed: _markAttempted,
                icon: const Icon(Icons.record_voice_over_outlined),
                label: const Text('Ya intenté leerlo en voz alta'),
              )
            else ...[
              Card(
                child: ListTile(
                  leading: const Icon(Icons.check_circle_outline),
                  title: const Text('Intento registrado'),
                  subtitle: const Text(
                    'Ahora escucha el modelo y compáralo con tu lectura.',
                  ),
                ),
              ),
              const SizedBox(height: 8),
              if (ttsText.trim().isNotEmpty)
                TtsControls(
                  text: ttsText,
                  locale: locale,
                  slowAvailable: slow,
                  onCompleted: _markListened,
                ),
            ],
            if (_attempted && _listened) ...[
              const SizedBox(height: 12),
              const Card(
                child: ListTile(
                  leading: Icon(Icons.task_alt),
                  title: Text('Práctica completada'),
                  subtitle: Text(
                    'ADAPA no asigna una nota de pronunciación sin reconocimiento de voz.',
                  ),
                ),
              ),
            ],
          ],
        );
      },
    );
  }
}

class _SpeakingPracticeActivity extends StatefulWidget {
  const _SpeakingPracticeActivity({required this.activity});

  final ActivityContent activity;

  @override
  State<_SpeakingPracticeActivity> createState() =>
      _SpeakingPracticeActivityState();
}

class _SpeakingPracticeActivityState extends State<_SpeakingPracticeActivity> {
  final Set<int> _repeated = {};

  List<String> get _items =>
      (widget.activity.payload['items'] as List? ?? const [])
          .map((e) => e.toString())
          .toList(growable: false);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final saved = AdapaRuntime.of(context).sessionStore.read(widget.activity.id);
      final indices = (saved?['repeated'] as List? ?? const [])
          .whereType<num>()
          .map((e) => e.toInt());
      setState(() => _repeated.addAll(indices));
    });
  }

  void _toggleRepeated(int index) {
    setState(() {
      if (!_repeated.add(index)) _repeated.remove(index);
    });
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'repeated': _repeated.toList()..sort(),
      'complete': ReadingPracticeLogic.repeatAllComplete(
        itemCount: _items.length,
        repeated: _repeated,
      ),
    });
  }

  @override
  Widget build(BuildContext context) {
    final items = _items;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i = 0; i < items.length; i++) ...[
          Card(
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    items[i],
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                  const SizedBox(height: 10),
                  TtsControls(text: items[i]),
                  const SizedBox(height: 8),
                  CheckboxListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Ya lo repetí en voz alta'),
                    value: _repeated.contains(i),
                    onChanged: (_) => _toggleRepeated(i),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 8),
        ],
        if (items.isNotEmpty && _repeated.length == items.length)
          const Card(
            child: ListTile(
              leading: Icon(Icons.task_alt),
              title: Text('Práctica oral completada'),
              subtitle: Text('Escuchaste y repetiste todas las frases.'),
            ),
          ),
      ],
    );
  }
}

class _TtsReadbackActivity extends StatelessWidget {
  const _TtsReadbackActivity({required this.activity});

  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    final sourceId = activity.payload['source_activity']?.toString() ?? '';
    final saved = AdapaRuntime.of(context).sessionStore.read(sourceId);
    final text = saved?['text']?.toString().trim() ?? '';

    if (text.isEmpty) {
      return const Card(
        child: ListTile(
          leading: Icon(Icons.edit_note),
          title: Text('Primero escribe tus frases'),
          subtitle: Text(
            'Esta actividad reproduce el texto de la actividad de escritura anterior. '
            'Vuelve a esa actividad, guárdala y regresa aquí.',
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: SelectableText(
              text,
              style: Theme.of(context).textTheme.titleMedium,
            ),
          ),
        ),
        const SizedBox(height: 12),
        TtsControls(
          text: text,
          onCompleted: () {
            AdapaRuntime.of(context).sessionStore.write(activity.id, {
              'source_activity': sourceId,
              'played': true,
              'complete': true,
            });
          },
        ),
      ],
    );
  }
}
