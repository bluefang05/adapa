import 'package:flutter/material.dart';

import '../../core/runtime/adapa_runtime.dart';
import '../../core/settings/app_settings_controller.dart';
import 'privacy_screen.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool? _koreanAvailable;
  bool _checking = false;

  Future<void> _testVoice({required bool slow}) async {
    if (_checking) return;
    setState(() => _checking = true);
    final runtime = AdapaRuntime.of(context);
    try {
      await runtime.tts.speak(
        '안녕하세요. 한국어를 공부해요.',
        locale: 'ko-KR',
        rate: slow
            ? runtime.settings.slowTtsRate
            : runtime.settings.normalTtsRate,
      );
      if (mounted) setState(() => _koreanAvailable = true);
    } catch (_) {
      if (mounted) {
        setState(() => _koreanAvailable = false);
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
              'No se encontró una voz coreana ko-KR en el motor TTS del dispositivo.',
            ),
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _checking = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final runtime = AdapaRuntime.of(context);
    final settings = runtime.settings;

    return AnimatedBuilder(
      animation: settings,
      builder: (context, _) => Scaffold(
        appBar: AppBar(title: const Text('Ajustes')),
        body: ListView(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
          children: [
            Text(
              'Apariencia',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(height: 10),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: SegmentedButton<AppColorMode>(
                  segments: const [
                    ButtonSegment(
                      value: AppColorMode.light,
                      icon: Icon(Icons.light_mode_outlined),
                      label: Text('Claro'),
                    ),
                    ButtonSegment(
                      value: AppColorMode.balanced,
                      icon: Icon(Icons.contrast_outlined),
                      label: Text('Medio'),
                    ),
                    ButtonSegment(
                      value: AppColorMode.dark,
                      icon: Icon(Icons.dark_mode_outlined),
                      label: Text('Oscuro'),
                    ),
                  ],
                  selected: {settings.colorMode},
                  onSelectionChanged: (selection) {
                    settings.setColorMode(selection.first);
                  },
                ),
              ),
            ),
            const SizedBox(height: 22),
            Text(
              'Voz coreana',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(height: 6),
            const Text(
              'ADAPA usa la voz del motor TTS instalado en tu dispositivo. '
              'El curso funciona offline; la disponibilidad de ko-KR depende del sistema.',
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        Icon(
                          _koreanAvailable == true
                              ? Icons.check_circle
                              : _koreanAvailable == false
                              ? Icons.warning_amber_rounded
                              : Icons.record_voice_over_outlined,
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            _koreanAvailable == true
                                ? 'Voz ko-KR disponible'
                                : _koreanAvailable == false
                                ? 'No se detectó ko-KR'
                                : 'Prueba la voz coreana',
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    FilledButton.tonalIcon(
                      onPressed: _checking
                          ? null
                          : () => _testVoice(slow: false),
                      icon: _checking
                          ? const SizedBox.square(
                              dimension: 18,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Icon(Icons.volume_up_outlined),
                      label: const Text('Probar voz coreana'),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),
            Text('Velocidad', style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 8),
            Card(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(18, 14, 18, 18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    _RateSlider(
                      label: 'Normal',
                      value: settings.normalTtsRate,
                      min: 0.20,
                      max: 0.70,
                      onChanged: settings.setNormalTtsRate,
                    ),
                    const Divider(height: 28),
                    _RateSlider(
                      label: 'Lenta',
                      value: settings.slowTtsRate,
                      min: 0.15,
                      max: settings.normalTtsRate,
                      onChanged: settings.setSlowTtsRate,
                    ),
                    const SizedBox(height: 8),
                    OutlinedButton.icon(
                      onPressed: _checking
                          ? null
                          : () => _testVoice(slow: true),
                      icon: const Icon(Icons.slow_motion_video),
                      label: const Text('Probar velocidad lenta'),
                    ),
                    TextButton(
                      onPressed: () async {
                        await settings.restoreTtsDefaults();
                        if (!context.mounted) return;
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Velocidades restauradas.'),
                          ),
                        );
                      },
                      child: const Text(
                        'Restaurar velocidades predeterminadas',
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),
            Card(
              child: ListTile(
                leading: const Icon(Icons.privacy_tip_outlined),
                title: const Text('Privacidad y datos'),
                subtitle: const Text(
                  'Qué guarda ADAPA, cómo funciona el TTS y cómo reiniciar tu progreso.',
                ),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const PrivacyScreen()),
                ),
              ),
            ),
            const SizedBox(height: 10),
            Card(
              child: ListTile(
                leading: const Icon(Icons.offline_bolt_outlined),
                title: const Text('Curso offline-first'),
                subtitle: const Text(
                  'Contenido, progreso, imágenes y trazos están guardados localmente. '
                  'ADAPA no necesita una cuenta ni una conexión para estudiar.',
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _RateSlider extends StatelessWidget {
  const _RateSlider({
    required this.label,
    required this.value,
    required this.min,
    required this.max,
    required this.onChanged,
  });

  final String label;
  final double value;
  final double min;
  final double max;
  final ValueChanged<double> onChanged;

  @override
  Widget build(BuildContext context) {
    final normalized = ((value - min) / (max - min == 0 ? 1 : max - min)).clamp(
      0.0,
      1.0,
    );
    final descriptor = normalized < 0.2
        ? 'Muy lenta'
        : normalized < 0.4
        ? 'Lenta'
        : normalized < 0.65
        ? 'Media'
        : normalized < 0.85
        ? 'Rápida'
        : 'Muy rápida';
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              child: Text(
                label,
                style: Theme.of(context).textTheme.titleMedium,
              ),
            ),
            Text(descriptor),
          ],
        ),
        Slider(
          value: value.clamp(min, max).toDouble(),
          min: min,
          max: max,
          divisions: 20,
          onChanged: onChanged,
        ),
      ],
    );
  }
}
