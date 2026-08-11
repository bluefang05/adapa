import 'package:flutter/material.dart';

class ActivityFeedback extends StatelessWidget {
  const ActivityFeedback({
    super.key,
    required this.isCorrect,
    required this.message,
  });

  final bool isCorrect;
  final String message;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final background =
        isCorrect ? scheme.primaryContainer : scheme.errorContainer;
    final foreground =
        isCorrect ? scheme.onPrimaryContainer : scheme.onErrorContainer;

    return Semantics(
      liveRegion: true,
      label: isCorrect ? 'Respuesta correcta' : 'Respuesta a revisar',
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: background,
          borderRadius: BorderRadius.circular(18),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(
              isCorrect ? Icons.check_circle : Icons.info_outline,
              color: foreground,
            ),
            const SizedBox(width: 11),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    isCorrect ? 'Bien hecho' : 'Revisa esto',
                    style: TextStyle(
                      color: foreground,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(message, style: TextStyle(color: foreground)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
