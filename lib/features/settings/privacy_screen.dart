import 'package:flutter/material.dart';

import '../../core/runtime/adapa_runtime.dart';

class PrivacyScreen extends StatelessWidget {
  const PrivacyScreen({super.key});

  Future<void> _resetProgress(BuildContext context) async {
    final progress = AdapaRuntime.of(context).progress;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Reiniciar progreso'),
        content: const Text(
          'Se borrarán intentos, resultados, respuestas guardadas y el punto '
          'de continuación de este dispositivo.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Reiniciar'),
          ),
        ],
      ),
    );

    if (confirmed != true || !context.mounted) return;
    await progress.resetCourseProgress();
    if (!context.mounted) return;
    AdapaRuntime.of(context).sessionStore.clear();
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Progreso reiniciado.')),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Privacidad y datos')),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 32),
        children: [
          Text(
            'Tus datos de estudio',
            style: Theme.of(context).textTheme.headlineSmall,
          ),
          const SizedBox(height: 8),
          const Text(
            'ADAPA no requiere cuenta. El progreso del curso, los intentos, '
            'las respuestas guardadas y los ajustes se conservan localmente '
            'en este dispositivo.',
          ),
          const SizedBox(height: 16),
          const Card(
            child: Column(
              children: [
                ListTile(
                  leading: Icon(Icons.cloud_off_outlined),
                  title: Text('Sin nube propia'),
                  subtitle: Text(
                    'ADAPA no tiene backend, sincronización de cuenta ni '
                    'analítica en esta versión.',
                  ),
                ),
                Divider(height: 1),
                ListTile(
                  leading: Icon(Icons.wifi_off_outlined),
                  title: Text('Curso offline-first'),
                  subtitle: Text(
                    'El contenido del curso, las imágenes y los trazos se '
                    'incluyen con la aplicación.',
                  ),
                ),
                Divider(height: 1),
                ListTile(
                  leading: Icon(Icons.record_voice_over_outlined),
                  title: Text('Texto a voz del dispositivo'),
                  subtitle: Text(
                    'Cuando usas Escuchar, ADAPA entrega el texto al motor '
                    'TTS instalado o seleccionado en tu dispositivo. Ese '
                    'motor es un componente separado y puede tener sus propias '
                    'condiciones y opciones de privacidad.',
                  ),
                ),
                Divider(height: 1),
                ListTile(
                  leading: Icon(Icons.backup_outlined),
                  title: Text('Backup de la aplicación desactivado'),
                  subtitle: Text(
                    'ADAPA desactiva el backup en nube de sus datos de '
                    'aplicación. La migración directa entre dispositivos '
                    'puede depender del sistema Android y del fabricante.',
                  ),
                ),
                Divider(height: 1),
                ListTile(
                  leading: Icon(Icons.ads_click_outlined),
                  title: Text('Publicidad (Google AdMob)'),
                  subtitle: Text(
                    'Para mantener el curso gratuito, ADAPA muestra banners '
                    'de Google AdMob. Google puede tratar identificadores de '
                    'anuncios y diagnósticos según su política de privacidad.',
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 22),
          Text(
            'Control',
            style: Theme.of(context).textTheme.titleLarge,
          ),
          const SizedBox(height: 8),
          Card(
            child: ListTile(
              leading: const Icon(Icons.restart_alt),
              title: const Text('Reiniciar mi progreso'),
              subtitle: const Text(
                'Reinicia los datos de estudio guardados por ADAPA en este dispositivo.',
              ),
              onTap: () => _resetProgress(context),
            ),
          ),
          const SizedBox(height: 22),
          const Card(
            child: Column(
              children: [
                ListTile(
                  leading: Icon(Icons.email_outlined),
                  title: Text('Contacto de soporte y privacidad'),
                  subtitle: Text('enmandom@gmail.com'),
                ),
                Divider(height: 1),
                ListTile(
                  leading: Icon(Icons.info_outline),
                  title: Text('Versión de privacidad'),
                  subtitle: Text('Política oficial de ADAPA — 22 de agosto de 2026'),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
