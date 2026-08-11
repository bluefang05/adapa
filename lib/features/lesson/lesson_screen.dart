import 'package:flutter/material.dart';

import '../../core/models/activity_content.dart';
import '../../core/models/lesson_content.dart';
import '../../core/models/romanization_policy.dart';
import '../../core/runtime/adapa_runtime.dart';
import '../activity/activity_screen.dart';
import '../shared/progress_header_card.dart';
import 'widgets/lesson_content_block.dart';

class LessonScreen extends StatelessWidget {
  const LessonScreen({
    super.key,
    required this.lesson,
    required this.romanizationPolicy,
  });

  final LessonContent lesson;
  final RomanizationPolicy romanizationPolicy;

  @override
  Widget build(BuildContext context) {
    final progress = AdapaRuntime.of(context).progress;

    return AnimatedBuilder(
      animation: progress,
      builder: (context, _) {
        final state = progress.lessonProgress(lesson.id);
        final scoreText = '${(state.score * 100).round()}%';

        return Scaffold(
          appBar: AppBar(title: Text(lesson.title)),
          body: ListView(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 40),
            children: [
              if (lesson.objective != null) ...[
                Text('Objetivo', style: Theme.of(context).textTheme.labelLarge),
                const SizedBox(height: 5),
                Text(
                  lesson.objective!,
                  style: Theme.of(context).textTheme.headlineSmall,
                ),
                const SizedBox(height: 18),
              ],
              ProgressHeaderCard(
                title:
                    state.completed ? 'Lección completada' : 'Progreso de la lección',
                fraction: state.activityFraction,
                detail:
                    '${state.completedActivities}/${state.activityCount} completadas · '
                    'rendimiento $scoreText',
                icon: state.completed
                    ? Icons.task_alt
                    : Icons.menu_book_outlined,
              ),
              if (lesson.theory.isNotEmpty) ...[
                const SizedBox(height: 24),
                Text('Aprende', style: Theme.of(context).textTheme.titleLarge),
                const SizedBox(height: 10),
                for (final block in lesson.theory) ...[
                  LessonContentBlockCard(
                    block: block,
                    romanizationPolicy: romanizationPolicy,
                  ),
                  const SizedBox(height: 10),
                ],
              ],
              const SizedBox(height: 18),
              Text('Practica', style: Theme.of(context).textTheme.titleLarge),
              const SizedBox(height: 10),
              for (var i = 0; i < lesson.activities.length; i++) ...[
                _ActivityTile(
                  index: i,
                  activity: lesson.activities[i],
                ),
                const SizedBox(height: 9),
              ],
            ],
          ),
        );
      },
    );
  }
}

class _ActivityTile extends StatelessWidget {
  const _ActivityTile({required this.index, required this.activity});

  final int index;
  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    final progress = AdapaRuntime.of(context).progress;
    final ap = progress.activityProgress(activity.id);

    final statusIcon = ap.completed
        ? Icons.check
        : ap.attempted
            ? Icons.replay_outlined
            : null;

    return Card(
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 5),
        leading: CircleAvatar(
          child: statusIcon == null ? Text('${index + 1}') : Icon(statusIcon),
        ),
        title: Text(activity.prompt ?? activity.objective ?? 'Actividad'),
        subtitle: Padding(
          padding: const EdgeInsets.only(top: 4),
          child: Text(
            ap.attempts > 0
                ? '${activity.family.label} · ${ap.attempts} intento${ap.attempts == 1 ? '' : 's'}'
                : activity.family.label,
          ),
        ),
        trailing: const Icon(Icons.chevron_right),
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => ActivityScreen(activity: activity)),
        ),
      ),
    );
  }
}
