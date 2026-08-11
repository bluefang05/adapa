import 'package:flutter/material.dart';

import '../../../core/models/activity_content.dart';

class PendingActivityRenderer extends StatelessWidget {
  const PendingActivityRenderer({super.key, required this.activity});

  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Familia aún no interactiva', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            Text(
              'El contenido está cargado correctamente, pero el renderer de '
              '${activity.family.label} se implementará en el siguiente bloque.',
            ),
          ],
        ),
      ),
    );
  }
}
