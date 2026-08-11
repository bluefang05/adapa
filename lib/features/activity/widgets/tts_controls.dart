import 'package:flutter/material.dart';

import '../../../core/runtime/adapa_runtime.dart';

class TtsControls extends StatefulWidget {
  const TtsControls({
    super.key,
    required this.text,
    this.locale = 'ko-KR',
    this.slowAvailable = true,
    this.onCompleted,
  });

  final String text;
  final String locale;
  final bool slowAvailable;
  final VoidCallback? onCompleted;

  @override
  State<TtsControls> createState() => _TtsControlsState();
}

class _TtsControlsState extends State<TtsControls> {
  bool _speaking = false;

  Future<void> _speak(double rate) async {
    if (_speaking) return;
    setState(() => _speaking = true);
    try {
      await AdapaRuntime.of(context).tts.speak(
            widget.text,
            locale: widget.locale,
            rate: rate,
          );
      widget.onCompleted?.call();
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'No hay una voz ${widget.locale} disponible en el motor TTS del dispositivo.',
            ),
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _speaking = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final settings = AdapaRuntime.of(context).settings;
    return AnimatedBuilder(
      animation: settings,
      builder: (context, _) => Wrap(
        spacing: 8,
        runSpacing: 8,
        children: [
          FilledButton.tonalIcon(
            onPressed: _speaking ? null : () => _speak(settings.normalTtsRate),
            icon: const Icon(Icons.volume_up_outlined),
            label: Text(_speaking ? 'Reproduciendo…' : 'Escuchar'),
          ),
          if (widget.slowAvailable)
            OutlinedButton.icon(
              onPressed: _speaking ? null : () => _speak(settings.slowTtsRate),
              icon: const Icon(Icons.slow_motion_video),
              label: const Text('Lento'),
            ),
        ],
      ),
    );
  }
}
