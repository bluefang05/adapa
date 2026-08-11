import 'package:flutter/material.dart';

import '../../core/models/activity_content.dart';
import '../../core/runtime/adapa_runtime.dart';
import '../shared/persistence_error_card.dart';
import 'activity_renderer_host.dart';

class ActivityScreen extends StatefulWidget {
  const ActivityScreen({super.key, required this.activity});

  final ActivityContent activity;

  @override
  State<ActivityScreen> createState() => _ActivityScreenState();
}

class _ActivityScreenState extends State<ActivityScreen> {
  bool _markedVisited = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_markedVisited) return;
    _markedVisited = true;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        AdapaRuntime.of(context).progress.markActivityVisited(widget.activity.id);
      }
    });
  }

  void _replaceWith(ActivityContent activity) {
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => ActivityScreen(activity: activity)),
    );
  }

  Future<void> _goNext(
    ActivityContent? next,
    bool attempted,
  ) async {
    if (next == null) {
      Navigator.of(context).pop();
      return;
    }

    if (widget.activity.scoreMode != 'none' && !attempted) {
      final continueAnyway = await showDialog<bool>(
        context: context,
        builder: (context) => AlertDialog(
          title: const Text('Actividad pendiente'),
          content: const Text(
            'Aún no has respondido esta actividad evaluable. Puedes continuar, '
            'pero la lección seguirá pendiente hasta que vuelvas a completarla.',
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Quedarme aquí'),
            ),
            FilledButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Continuar'),
            ),
          ],
        ),
      );
      if (continueAnyway != true || !mounted) return;
    }

    _replaceWith(next);
  }

  @override
  Widget build(BuildContext context) {
    final progress = AdapaRuntime.of(context).progress;

    return AnimatedBuilder(
      animation: progress,
      builder: (context, _) {
        final ap = progress.activityProgress(widget.activity.id);
        final lessonId = progress.lessonIdForActivity(widget.activity.id);
        final lesson = lessonId == null ? null : progress.lessonById(lessonId);
        final index = progress.activityIndexInLesson(widget.activity.id);
        final previous = progress.previousActivityInLesson(widget.activity.id);
        final next = progress.nextActivityInLesson(widget.activity.id);
        final count = lesson?.activities.length ?? 1;
        final position = index < 0 ? 1 : index + 1;
        final positionFraction = count == 0 ? 0.0 : position / count;

        return Scaffold(
          appBar: AppBar(
            title: Text(
              lesson?.title ?? widget.activity.family.label,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          bottomNavigationBar: SafeArea(
            minimum: const EdgeInsets.fromLTRB(16, 8, 16, 12),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: previous == null ? null : () => _replaceWith(previous),
                    icon: const Icon(Icons.arrow_back),
                    label: const Text('Anterior'),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: FilledButton.icon(
                    onPressed: () => _goNext(next, ap.attempted),
                    icon: Icon(next == null ? Icons.done : Icons.arrow_forward),
                    label: Text(next == null ? 'Volver a la lección' : 'Siguiente'),
                  ),
                ),
              ],
            ),
          ),
          body: ListView(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 30),
            children: [
              Row(
                children: [
                  Expanded(
                    child: Text(
                      'Actividad $position de $count',
                      style: Theme.of(context).textTheme.labelLarge,
                    ),
                  ),
                  Text('${(positionFraction * 100).round()}% del recorrido'),
                ],
              ),
              const SizedBox(height: 8),
              LinearProgressIndicator(value: positionFraction),
              const SizedBox(height: 12),
              const PersistenceErrorCard(),
              const SizedBox(height: 8),
              if (widget.activity.prompt != null || widget.activity.objective != null)
                Text(
                  widget.activity.prompt ??
                      widget.activity.objective ??
                      'Actividad',
                  style: Theme.of(context).textTheme.headlineSmall,
                ),
              if (widget.activity.objective != null &&
                  widget.activity.prompt != null) ...[
                const SizedBox(height: 7),
                Text(widget.activity.objective!),
              ],
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  Chip(
                    avatar: Icon(
                      widget.activity.scoreMode == 'none'
                          ? Icons.fitness_center_outlined
                          : Icons.fact_check_outlined,
                      size: 18,
                    ),
                    label: Text(
                      widget.activity.scoreMode == 'none'
                          ? 'Práctica'
                          : 'Evaluable',
                    ),
                  ),
                  if (ap.completed)
                    const Chip(
                      avatar: Icon(Icons.check_circle, size: 18),
                      label: Text('Completada'),
                    ),
                  if (ap.attempts > 0)
                    Chip(
                      label: Text(
                        '${ap.attempts} intento${ap.attempts == 1 ? '' : 's'}',
                      ),
                    ),
                  if (ap.bestScore != null)
                    Chip(label: Text('Mejor ${(ap.bestScore! * 100).round()}%')),
                ],
              ),
              if (widget.activity.hints.isNotEmpty) ...[
                const SizedBox(height: 8),
                ExpansionTile(
                  tilePadding: EdgeInsets.zero,
                  leading: const Icon(Icons.lightbulb_outline),
                  title: const Text('Necesito una pista'),
                  children: [
                    for (final hint in widget.activity.hints)
                      ListTile(
                        contentPadding: EdgeInsets.zero,
                        title: Text(hint),
                      ),
                  ],
                ),
              ],
              const SizedBox(height: 14),
              ActivityRendererHost(activity: widget.activity),
              if (widget.activity.note != null) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(13),
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.surfaceContainerLow,
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Text(
                    widget.activity.note!,
                    style: Theme.of(context).textTheme.bodySmall,
                  ),
                ),
              ],
            ],
          ),
        );
      },
    );
  }
}
