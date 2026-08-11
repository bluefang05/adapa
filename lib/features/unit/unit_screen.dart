import 'package:flutter/material.dart';

import '../../core/content/course_repository.dart';
import '../../core/models/course_manifest.dart';
import '../../core/models/lesson_content.dart';
import '../../core/models/romanization_policy.dart';
import '../../core/models/unit_content.dart';
import '../../core/runtime/adapa_runtime.dart';
import '../lesson/lesson_screen.dart';
import '../shared/progress_header_card.dart';

class UnitScreen extends StatelessWidget {
  const UnitScreen({
    super.key,
    required this.repository,
    required this.summary,
  });

  final CourseRepository repository;
  final UnitSummary summary;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(summary.title)),
      body: FutureBuilder<UnitContent>(
        future: repository.loadUnit(summary),
        builder: (context, snapshot) {
          if (snapshot.hasError) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Text('No se pudo cargar la unidad: ${snapshot.error}'),
              ),
            );
          }
          if (!snapshot.hasData) {
            return const Center(child: CircularProgressIndicator());
          }

          final unit = snapshot.data!;
          final romanization =
              RomanizationPolicy.fromDynamic(unit.romanizationPolicy);
          final progress = AdapaRuntime.of(context).progress;

          return AnimatedBuilder(
            animation: progress,
            builder: (context, _) {
              final state = progress.unitProgress(unit.id);
              return ListView(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 36),
                children: [
                  if (unit.goal != null) ...[
                    Text('Objetivo de la unidad',
                        style: Theme.of(context).textTheme.labelLarge),
                    const SizedBox(height: 5),
                    Text(
                      unit.goal!,
                      style: Theme.of(context).textTheme.headlineSmall,
                    ),
                    const SizedBox(height: 18),
                  ],
                  ProgressHeaderCard(
                    title: state.completed
                        ? 'Unidad completada'
                        : 'Progreso de la unidad',
                    fraction: state.fraction,
                    detail:
                        '${state.completedLessons}/${state.lessonCount} lecciones · '
                        '${state.completedActivities}/${state.activityCount} actividades',
                    icon: state.completed
                        ? Icons.check_circle_outline
                        : Icons.route_outlined,
                  ),
                  const SizedBox(height: 22),
                  Text('Lecciones', style: Theme.of(context).textTheme.titleLarge),
                  const SizedBox(height: 10),
                  for (var i = 0; i < unit.lessons.length; i++)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 10),
                      child: _LessonTile(
                        index: i,
                        lesson: unit.lessons[i],
                        romanizationPolicy: romanization,
                      ),
                    ),
                ],
              );
            },
          );
        },
      ),
    );
  }
}

class _LessonTile extends StatelessWidget {
  const _LessonTile({
    required this.index,
    required this.lesson,
    required this.romanizationPolicy,
  });

  final int index;
  final LessonContent lesson;
  final RomanizationPolicy romanizationPolicy;

  @override
  Widget build(BuildContext context) {
    final progress = AdapaRuntime.of(context).progress;
    final state = progress.lessonProgress(lesson.id);
    final percent = (state.score * 100).round();

    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(20),
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => LessonScreen(
              lesson: lesson,
              romanizationPolicy: romanizationPolicy,
            ),
          ),
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              CircleAvatar(
                child: state.completed
                    ? const Icon(Icons.check)
                    : Text('${index + 1}'),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text(lesson.title,
                        style: Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 3),
                    Text(
                      '${lesson.activities.length} actividades'
                      '${lesson.estimatedMinutes == null ? '' : ' · ~${lesson.estimatedMinutes} min'}',
                    ),
                    const SizedBox(height: 10),
                    LinearProgressIndicator(value: state.activityFraction),
                    const SizedBox(height: 6),
                    Text(
                      state.completed
                          ? 'Completada · rendimiento $percent%'
                          : state.attemptedRequired == 0
                              ? 'No iniciada'
                              : '${state.completedActivities}/${state.activityCount} completadas · $percent%',
                      style: Theme.of(context).textTheme.bodySmall,
                    ),
                  ],
                ),
              ),
              const Padding(
                padding: EdgeInsets.only(top: 5),
                child: Icon(Icons.chevron_right),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
