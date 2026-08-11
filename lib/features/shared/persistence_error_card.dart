import 'package:flutter/material.dart';

import '../../core/runtime/adapa_runtime.dart';

class PersistenceErrorCard extends StatelessWidget {
  const PersistenceErrorCard({super.key});

  @override
  Widget build(BuildContext context) {
    final progress = AdapaRuntime.of(context).progress;
    final message = progress.persistenceError;
    if (message == null) return const SizedBox.shrink();

    final scheme = Theme.of(context).colorScheme;
    return Card(
      color: scheme.errorContainer,
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(Icons.sync_problem_outlined, color: scheme.onErrorContainer),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Problema al guardar',
                    style: TextStyle(
                      color: scheme.onErrorContainer,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(message, style: TextStyle(color: scheme.onErrorContainer)),
                  const SizedBox(height: 8),
                  TextButton.icon(
                    onPressed: progress.retrySave,
                    icon: const Icon(Icons.refresh),
                    label: const Text('Reintentar guardado'),
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
