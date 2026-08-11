import 'package:flutter/material.dart';

import '../../../core/models/content_block.dart';
import '../../../core/models/romanization_policy.dart';
import '../../../core/runtime/adapa_runtime.dart';
import '../../activity/widgets/tts_controls.dart';


Future<void> _speakTheoryText(BuildContext context, String text) async {
  if (text.trim().isEmpty) return;
  final runtime = AdapaRuntime.of(context);
  try {
    await runtime.tts.speak(
      text,
      rate: runtime.settings.normalTtsRate,
    );
  } catch (_) {
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No hay una voz coreana disponible.')),
      );
    }
  }
}

class LessonContentBlockCard extends StatefulWidget {
  const LessonContentBlockCard({
    super.key,
    required this.block,
    required this.romanizationPolicy,
  });

  final ContentBlock block;
  final RomanizationPolicy romanizationPolicy;

  @override
  State<LessonContentBlockCard> createState() => _LessonContentBlockCardState();
}

class _LessonContentBlockCardState extends State<LessonContentBlockCard> {
  late bool _showRomanization;

  @override
  void initState() {
    super.initState();
    _showRomanization = widget.romanizationPolicy.visibleByDefault;
  }

  @override
  Widget build(BuildContext context) {
    final block = widget.block;
    final payload = block.payload;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (block.title != null) ...[
              Text(block.title!, style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 10),
            ],
            if (block.text != null) ...[
              Text(block.text!, style: Theme.of(context).textTheme.bodyLarge),
              if (payload.isNotEmpty) const SizedBox(height: 12),
            ],
            _buildPayload(context, block.type, payload),
          ],
        ),
      ),
    );
  }

  Widget _buildPayload(
    BuildContext context,
    String type,
    Map<String, dynamic> payload,
  ) {
    if (payload.isEmpty) return const SizedBox.shrink();

    if (type == 'dialogue_ref') {
      return _DialogueReference(ref: payload['ref']?.toString());
    }
    if (type == 'reading_ref') {
      return _ReadingReference(ref: payload['ref']?.toString());
    }
    if (type == 'vocabulary_ref') {
      return _VocabularyReference(
        readingRef: payload['reading_ref']?.toString(),
      );
    }
    if (type == 'visual_vocabulary') {
      return _VisualVocabulary(
        items: _mapList(payload['items']),
      );
    }
    if (type == 'dialogue_model') {
      return _DialogueTurns(turns: _mapList(payload['turns']));
    }
    if (type == 'hangul_table') {
      return _HangulTable(
        items: _mapList(payload['items']),
        showRomanization: _showRomanization,
        romanizationControl: _romanizationControl(),
      );
    }
    if (type == 'number_table') {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _NumberTable(
            items: _mapList(payload['items']),
            showRomanization: _showRomanization,
          ),
          _romanizationControl(),
        ],
      );
    }
    if (type == 'comparison') {
      return _ComparisonPairs(pairs: _mapList(payload['pairs']));
    }
    if (type == 'syllable_examples') {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _StructuredRows(
            items: _mapList(payload['items']),
            showRomanization: _showRomanization,
          ),
          _romanizationControl(),
        ],
      );
    }
    if (type == 'examples' ||
        type == 'batchim_examples' ||
        type == 'word_breakdown' ||
        type == 'grammar_table' ||
        type == 'sentence_breakdown' ||
        type == 'verb_table' ||
        type == 'scenario_table') {
      return _StructuredRows(items: _mapList(payload['items']));
    }
    if (type == 'source_answer_key') {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _StructuredRows(items: _mapList(payload['items'])),
          if (payload['note_es'] != null) ...[
            const SizedBox(height: 8),
            Text(
              payload['note_es'].toString(),
              style: Theme.of(context).textTheme.bodySmall,
            ),
          ],
        ],
      );
    }
    if (type == 'key_phrase') {
      final ko = payload['hangul']?.toString() ?? '';
      return _PhraseRow(
        ko: ko,
        es: payload['meaning_es']?.toString(),
        tts: payload['tts']?.toString() ?? ko,
      );
    }
    if (type == 'grammar_pattern') {
      final examples = (payload['examples'] as List? ?? const [])
          .map((e) => e.toString())
          .toList(growable: false);
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            payload['pattern']?.toString() ?? '',
            style: Theme.of(context).textTheme.headlineSmall,
          ),
          if (payload['meaning_es'] != null)
            Text(payload['meaning_es'].toString()),
          const SizedBox(height: 10),
          for (final item in examples)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: _KoreanLine(text: item),
            ),
        ],
      );
    }
    if (type == 'key_phrases' ||
        type == 'vocabulary' ||
        type == 'phrase_table' ||
        type == 'functional_phrases') {
      return _PhraseList(items: _mapList(payload['items']));
    }
    if (type == 'reading_list' || type == 'word_bank') {
      final items = (payload['items'] as List? ?? const [])
          .map((e) => e.toString())
          .toList(growable: false);
      return Wrap(
        spacing: 8,
        runSpacing: 8,
        children: [
          for (final item in items)
            ActionChip(
              label: Text(item, style: const TextStyle(fontWeight: FontWeight.w700)),
              onPressed: () => _speak(context, item),
            ),
        ],
      );
    }
    if (type == 'self_correction_rule') {
      final checks = (payload['checks'] as List? ?? const [])
          .map((e) => e.toString())
          .toList(growable: false);
      return Column(
        children: [
          for (final check in checks)
            ListTile(
              contentPadding: EdgeInsets.zero,
              leading: const Icon(Icons.check_circle_outline),
              title: Text(check),
            ),
        ],
      );
    }

    // Generic renderer for any future structured block: it shows meaningful
    // values rather than leaking an internal "Bloque: type" placeholder.
    return _GenericPayload(payload: payload);
  }

  Widget _romanizationControl() {
    if (widget.romanizationPolicy.completelyDisabled ||
        !widget.romanizationPolicy.canReveal) {
      return const SizedBox.shrink();
    }
    if (widget.romanizationPolicy.visibleByDefault) {
      return const SizedBox.shrink();
    }
    return Align(
      alignment: Alignment.centerLeft,
      child: TextButton.icon(
        onPressed: () => setState(() => _showRomanization = !_showRomanization),
        icon: Icon(_showRomanization ? Icons.visibility_off : Icons.visibility),
        label: Text(_showRomanization ? 'Ocultar ayuda' : 'Mostrar ayuda de lectura'),
      ),
    );
  }

  static List<Map<String, dynamic>> _mapList(dynamic value) {
    return [
      for (final raw in value as List? ?? const [])
        if (raw is Map) Map<String, dynamic>.from(raw),
    ];
  }

  Future<void> _speak(BuildContext context, String text) =>
      _speakTheoryText(context, text);
}

class _DialogueReference extends StatelessWidget {
  const _DialogueReference({required this.ref});
  final String? ref;

  @override
  Widget build(BuildContext context) {
    if (ref == null) return const SizedBox.shrink();
    return FutureBuilder(
      future: AdapaRuntime.of(context).assetResolver.dialogueScene(ref!),
      builder: (context, snapshot) {
        final scene = snapshot.data;
        if (scene == null) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const LinearProgressIndicator();
          }
          return const Text('No se pudo cargar el diálogo.');
        }
        final turns = [
          for (final turn in scene.turns)
            {
              'speaker': turn.speaker,
              'ko': turn.ko,
              'es': turn.es,
            },
        ];
        return _DialogueTurns(turns: turns);
      },
    );
  }
}

class _DialogueTurns extends StatelessWidget {
  const _DialogueTurns({required this.turns});
  final List<Map<String, dynamic>> turns;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        for (var i = 0; i < turns.length; i++)
          Align(
            alignment: i.isEven ? Alignment.centerLeft : Alignment.centerRight,
            child: Container(
              constraints: const BoxConstraints(maxWidth: 520),
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: i.isEven ? scheme.primaryContainer : scheme.secondaryContainer,
                borderRadius: BorderRadius.circular(18),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (turns[i]['speaker'] != null)
                    Text(
                      turns[i]['speaker'].toString(),
                      style: Theme.of(context).textTheme.labelSmall,
                    ),
                  Text(
                    turns[i]['ko']?.toString() ?? '',
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                  if (turns[i]['es'] != null) ...[
                    const SizedBox(height: 3),
                    Text(turns[i]['es'].toString()),
                  ],
                  if ((turns[i]['ko']?.toString() ?? '').isNotEmpty) ...[
                    const SizedBox(height: 6),
                    TtsControls(text: turns[i]['ko'].toString(), slowAvailable: true),
                  ],
                ],
              ),
            ),
          ),
      ],
    );
  }
}

class _ReadingReference extends StatelessWidget {
  const _ReadingReference({required this.ref});
  final String? ref;

  @override
  Widget build(BuildContext context) {
    if (ref == null) return const SizedBox.shrink();
    return FutureBuilder<Map<String, dynamic>?>(
      future: AdapaRuntime.of(context).assetResolver.reading(ref!),
      builder: (context, snapshot) {
        if (!snapshot.hasData) {
          if (snapshot.connectionState != ConnectionState.done) {
            return const LinearProgressIndicator();
          }
          return const Text('No se pudo cargar la lectura.');
        }
        final reading = snapshot.data!;
        final text = reading['text_ko']?.toString() ?? '';
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (reading['title_es'] != null)
              Text(reading['title_es'].toString(),
                  style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            SelectableText(
              text,
              style: Theme.of(context).textTheme.titleLarge?.copyWith(height: 1.7),
            ),
            const SizedBox(height: 12),
            TtsControls(text: reading['tts']?.toString() ?? text),
          ],
        );
      },
    );
  }
}

class _VocabularyReference extends StatelessWidget {
  const _VocabularyReference({required this.readingRef});
  final String? readingRef;

  @override
  Widget build(BuildContext context) {
    if (readingRef == null) return const SizedBox.shrink();
    return FutureBuilder<Map<String, dynamic>?>(
      future: AdapaRuntime.of(context).assetResolver.reading(readingRef!),
      builder: (context, snapshot) {
        final reading = snapshot.data;
        if (reading == null) {
          return snapshot.connectionState == ConnectionState.done
              ? const SizedBox.shrink()
              : const LinearProgressIndicator();
        }
        return _PhraseList(items: [
          for (final raw in reading['key_words'] as List? ?? const [])
            if (raw is Map) Map<String, dynamic>.from(raw),
        ]);
      },
    );
  }
}

class _VisualVocabulary extends StatelessWidget {
  const _VisualVocabulary({required this.items});
  final List<Map<String, dynamic>> items;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final twoColumns = constraints.maxWidth >= 500;
        final width = twoColumns ? (constraints.maxWidth - 12) / 2 : constraints.maxWidth;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            for (final item in items)
              SizedBox(
                width: width,
                child: _VisualVocabularyItem(item: item),
              ),
          ],
        );
      },
    );
  }
}

class _VisualVocabularyItem extends StatelessWidget {
  const _VisualVocabularyItem({required this.item});
  final Map<String, dynamic> item;

  @override
  Widget build(BuildContext context) {
    final assetId = item['image_asset']?.toString();
    final ko = item['hangul']?.toString() ?? '';
    return FutureBuilder<String?>(
      future: assetId == null
          ? Future<String?>.value(null)
          : AdapaRuntime.of(context).assetResolver.visualAsset(assetId),
      builder: (context, snapshot) => Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Theme.of(context).colorScheme.surfaceContainerLow,
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          children: [
            if (snapshot.data != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.asset(snapshot.data!, width: 72, height: 72, fit: BoxFit.cover),
              ),
            if (snapshot.data != null) const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(ko, style: Theme.of(context).textTheme.titleLarge),
                  Text(item['meaning_es']?.toString() ?? ''),
                  IconButton(
                    tooltip: 'Escuchar',
                    padding: EdgeInsets.zero,
                    alignment: Alignment.centerLeft,
                    onPressed: ko.isEmpty
                        ? null
                        : () {
                            _speakTheoryText(
                              context,
                              item['tts']?.toString() ?? ko,
                            );
                          },
                    icon: const Icon(Icons.volume_up_outlined),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _HangulTable extends StatelessWidget {
  const _HangulTable({
    required this.items,
    required this.showRomanization,
    required this.romanizationControl,
  });

  final List<Map<String, dynamic>> items;
  final bool showRomanization;
  final Widget romanizationControl;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        for (final item in items)
          ListTile(
            contentPadding: EdgeInsets.zero,
            title: Text(
              item['hangul']?.toString() ?? '',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            subtitle: Text([
              if (showRomanization && item['romanization'] != null)
                item['romanization'].toString(),
              if (item['help_es'] != null) item['help_es'].toString(),
            ].join(' · ')),
            trailing: IconButton(
              tooltip: 'Escuchar',
              onPressed: item['tts_text'] == null
                  ? null
                  : () {
                      _speakTheoryText(
                        context,
                        item['tts_text'].toString(),
                      );
                    },
              icon: const Icon(Icons.volume_up_outlined),
            ),
          ),
        romanizationControl,
      ],
    );
  }
}

class _NumberTable extends StatelessWidget {
  const _NumberTable({required this.items, required this.showRomanization});
  final List<Map<String, dynamic>> items;
  final bool showRomanization;

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 10,
      runSpacing: 10,
      children: [
        for (final item in items)
          Container(
            width: 105,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.surfaceContainerLow,
              borderRadius: BorderRadius.circular(15),
            ),
            child: Column(
              children: [
                Text('${item['number']}', style: Theme.of(context).textTheme.labelLarge),
                Text(item['hangul']?.toString() ?? '',
                    style: Theme.of(context).textTheme.titleLarge),
                if (showRomanization && item['romanization'] != null)
                  Text(item['romanization'].toString()),
              ],
            ),
          ),
      ],
    );
  }
}

class _ComparisonPairs extends StatelessWidget {
  const _ComparisonPairs({required this.pairs});
  final List<Map<String, dynamic>> pairs;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        for (final pair in pairs)
          Padding(
            padding: const EdgeInsets.symmetric(vertical: 6),
            child: Row(
              children: [
                Expanded(
                  child: _BigToken(text: pair['simple']?.toString() ?? ''),
                ),
                const Padding(
                  padding: EdgeInsets.symmetric(horizontal: 12),
                  child: Icon(Icons.arrow_forward),
                ),
                Expanded(
                  child: _BigToken(text: pair['double']?.toString() ?? ''),
                ),
              ],
            ),
          ),
      ],
    );
  }
}

class _BigToken extends StatelessWidget {
  const _BigToken({required this.text});
  final String text;

  @override
  Widget build(BuildContext context) {
    return Container(
      alignment: Alignment.center,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surfaceContainerLow,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Text(text, style: Theme.of(context).textTheme.headlineSmall),
    );
  }
}

class _PhraseList extends StatelessWidget {
  const _PhraseList({required this.items});
  final List<Map<String, dynamic>> items;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        for (final item in items)
          _PhraseRow(
            ko: item['ko']?.toString() ?? item['hangul']?.toString() ?? '',
            es: item['es']?.toString() ??
                item['meaning_es']?.toString() ??
                item['meaning']?.toString(),
            tts: item['tts']?.toString() ??
                item['tts_text']?.toString() ??
                item['ko']?.toString() ??
                item['hangul']?.toString(),
            badge: item['register']?.toString(),
          ),
      ],
    );
  }
}

class _PhraseRow extends StatelessWidget {
  const _PhraseRow({required this.ko, this.es, this.tts, this.badge});
  final String ko;
  final String? es;
  final String? tts;
  final String? badge;

  @override
  Widget build(BuildContext context) {
    return ListTile(
      contentPadding: EdgeInsets.zero,
      title: Text(ko, style: Theme.of(context).textTheme.titleMedium),
      subtitle: es == null
          ? null
          : Row(
              children: [
                Expanded(child: Text(es!)),
                if (badge != null) Chip(label: Text(badge!)),
              ],
            ),
      trailing: (tts ?? '').isEmpty
          ? null
          : IconButton(
              tooltip: 'Escuchar',
              onPressed: () {
                _speakTheoryText(context, tts!);
              },
              icon: const Icon(Icons.volume_up_outlined),
            ),
    );
  }
}

class _KoreanLine extends StatelessWidget {
  const _KoreanLine({required this.text});
  final String text;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(child: Text(text, style: Theme.of(context).textTheme.titleMedium)),
        IconButton(
          onPressed: () {
            _speakTheoryText(context, text);
          },
          icon: const Icon(Icons.volume_up_outlined),
        ),
      ],
    );
  }
}

class _StructuredRows extends StatelessWidget {
  const _StructuredRows({required this.items, this.showRomanization = true});
  final List<Map<String, dynamic>> items;
  final bool showRomanization;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        for (final item in items)
          Container(
            width: double.infinity,
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(13),
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.surfaceContainerLow,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Wrap(
              spacing: 8,
              runSpacing: 6,
              children: [
                for (final entry in item.entries)
                  if (!_internalField(entry.key) &&
                      (entry.key != 'romanization' || showRomanization))
                    _FieldChip(label: _labelFor(entry.key), value: _display(entry.value)),
              ],
            ),
          ),
      ],
    );
  }

  static bool _internalField(String key) =>
      key == 'tts' || key == 'source_warning_ref' || key == 'focus';

  static String _labelFor(String key) => switch (key) {
        'word' => 'Palabra',
        'meaning_es' => 'Significado',
        'sentence' => 'Frase',
        'example' => 'Ejemplo',
        'particle' => 'Partícula',
        'function_es' => 'Función',
        'dictionary' => 'Diccionario',
        'polite' => 'Forma -요',
        'result' => 'Resultado',
        'romanization' => 'Ayuda',
        'batchim' => '받침',
        'page' => 'Página',
        'situation_es' => 'Situación',
        'pattern' => 'Patrón',
        _ => key,
      };

  static String _display(dynamic value) {
    if (value is List) return value.join(' + ');
    return value.toString();
  }
}

class _FieldChip extends StatelessWidget {
  const _FieldChip({required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Chip(
      label: Text('$label: $value'),
      backgroundColor: Colors.white,
    );
  }
}

class _GenericPayload extends StatelessWidget {
  const _GenericPayload({required this.payload});
  final Map<String, dynamic> payload;

  @override
  Widget build(BuildContext context) {
    final values = <Widget>[];
    for (final entry in payload.entries) {
      if (entry.value is List) {
        final list = entry.value as List;
        for (final value in list) {
          if (value is String) {
            values.add(ListTile(
              contentPadding: EdgeInsets.zero,
              leading: const Icon(Icons.circle, size: 7),
              title: Text(value),
            ));
          }
        }
      } else if (entry.value is String || entry.value is num || entry.value is bool) {
        values.add(Padding(
          padding: const EdgeInsets.only(bottom: 6),
          child: Text('${entry.key}: ${entry.value}'),
        ));
      }
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: values,
    );
  }
}
