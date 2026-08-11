import 'package:flutter/material.dart';

import '../../../core/models/activity_content.dart';
import '../../../core/runtime/adapa_runtime.dart';

class VisualReferenceActivityRenderer extends StatelessWidget {
  const VisualReferenceActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    if (activity.type == 'stroke_viewer') {
      return _StrokeViewer(activity: activity);
    }
    return Text('Referencia visual no implementada: ${activity.type}');
  }
}

class _StrokeViewer extends StatefulWidget {
  const _StrokeViewer({required this.activity});
  final ActivityContent activity;

  @override
  State<_StrokeViewer> createState() => _StrokeViewerState();
}

class _StrokeViewerState extends State<_StrokeViewer> {
  int _setIndex = 0;
  int _frameIndex = 0;
  final Set<String> _finishedSets = <String>{};

  List<String> get _setIds =>
      (widget.activity.payload['asset_sets'] as List? ?? const [])
          .map((e) => e.toString())
          .toList(growable: false);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final saved = AdapaRuntime.of(
        context,
      ).sessionStore.read(widget.activity.id);
      final values = (saved?['finished_sets'] as List? ?? const []).map(
        (e) => e.toString(),
      );
      setState(() => _finishedSets.addAll(values));
    });
  }

  void _persist() {
    final complete =
        _setIds.isNotEmpty && _finishedSets.length >= _setIds.length;
    AdapaRuntime.of(context).sessionStore.write(widget.activity.id, {
      'finished_sets': _finishedSets.toList(),
      'complete': complete,
    });
  }

  void _chooseSet(int index) {
    setState(() {
      _setIndex = index;
      _frameIndex = 0;
    });
  }

  void _advance(int frameCount, String setId) {
    setState(() {
      if (_frameIndex < frameCount - 1) _frameIndex += 1;
      if (_frameIndex == frameCount - 1) _finishedSets.add(setId);
    });
    _persist();
  }

  void _back() {
    setState(() {
      if (_frameIndex > 0) _frameIndex -= 1;
    });
  }

  @override
  Widget build(BuildContext context) {
    final ids = _setIds;
    if (ids.isEmpty) {
      return const Text('No hay conjuntos de trazos en esta actividad.');
    }
    final resolver = AdapaRuntime.of(context).assetResolver;
    final selectedId = ids[_setIndex.clamp(0, ids.length - 1).toInt()];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                'Caracteres revisados: ${_finishedSets.length}/${ids.length}',
                style: Theme.of(context).textTheme.labelLarge,
              ),
            ),
            if (_finishedSets.length == ids.length)
              const Chip(
                avatar: Icon(Icons.check, size: 18),
                label: Text('Revisados'),
              ),
          ],
        ),
        const SizedBox(height: 10),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          child: Row(
            children: [
              for (var i = 0; i < ids.length; i++) ...[
                FutureBuilder<Map<String, dynamic>?>(
                  future: resolver.strokeSet(ids[i]),
                  builder: (context, snapshot) {
                    final char = snapshot.data?['character']?.toString() ?? '…';
                    return Padding(
                      padding: const EdgeInsets.only(right: 6),
                      child: ChoiceChip(
                        selected: i == _setIndex,
                        onSelected: (_) => _chooseSet(i),
                        label: Text(char, style: const TextStyle(fontSize: 25)),
                      ),
                    );
                  },
                ),
              ],
            ],
          ),
        ),
        const SizedBox(height: 14),
        FutureBuilder<Map<String, dynamic>?>(
          future: resolver.strokeSet(selectedId),
          builder: (context, snapshot) {
            if (snapshot.connectionState != ConnectionState.done) {
              return const SizedBox(
                height: 320,
                child: Center(child: CircularProgressIndicator()),
              );
            }
            final set = snapshot.data;
            if (set == null) {
              return const Card(
                child: Padding(
                  padding: EdgeInsets.all(16),
                  child: Text('No se encontró este conjunto de trazos.'),
                ),
              );
            }
            final steps = (set['steps'] as List? ?? const [])
                .map((e) => e.toString())
                .toList();
            final finalAsset = set['final']?.toString();
            final frames = <String>[...steps, ?finalAsset];
            if (frames.isEmpty) {
              return const Text('El conjunto no contiene imágenes.');
            }

            final safeFrame = _frameIndex.clamp(0, frames.length - 1).toInt();
            final isFinal = safeFrame == frames.length - 1;
            final char = set['character']?.toString() ?? '';

            return Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Card(
                  clipBehavior: Clip.antiAlias,
                  child: AspectRatio(
                    aspectRatio: 1,
                    child: Image.asset(frames[safeFrame], fit: BoxFit.contain),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  isFinal
                      ? '$char · forma final'
                      : '$char · paso ${safeFrame + 1}/${steps.length}',
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.titleMedium,
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: safeFrame == 0 ? null : _back,
                        icon: const Icon(Icons.arrow_back),
                        label: const Text('Anterior'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: FilledButton.icon(
                        onPressed: isFinal
                            ? () {
                                setState(() => _finishedSets.add(selectedId));
                                _persist();
                              }
                            : () => _advance(frames.length, selectedId),
                        icon: Icon(isFinal ? Icons.check : Icons.arrow_forward),
                        label: Text(isFinal ? 'Visto' : 'Siguiente'),
                      ),
                    ),
                  ],
                ),
              ],
            );
          },
        ),
      ],
    );
  }
}
