import 'package:flutter/material.dart';

import '../../core/content/course_repository.dart';
import '../../core/models/activity_content.dart';
import '../../core/models/activity_family.dart';
import '../../core/models/course_manifest.dart';
import '../../core/models/lesson_content.dart';
import '../../core/models/romanization_policy.dart';
import '../../core/models/unit_content.dart';
import '../../core/runtime/adapa_runtime.dart';
import '../lesson/lesson_screen.dart';
import '../practice/practice_session_screen.dart';
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
          final practiceEntries = _practiceEntries(unit);

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
                  if (practiceEntries.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    _UnitPracticeSection(
                      unit: unit,
                      entries: practiceEntries,
                    ),
                  ],
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


class _PracticeEntry {
  const _PracticeEntry({
    required this.activity,
    required this.lessonTitle,
  });

  final ActivityContent activity;
  final String lessonTitle;
}

List<_PracticeEntry> _practiceEntries(UnitContent unit) {
  final result = <_PracticeEntry>[];
  for (final lesson in unit.lessons) {
    for (final activity in lesson.activities) {
      if (_isReusablePractice(activity)) {
        result.add(
          _PracticeEntry(
            activity: activity,
            lessonTitle: lesson.title,
          ),
        );
      }
    }
  }
  return result;
}

bool _isReusablePractice(ActivityContent activity) {
  return activity.family == ActivityFamily.choice ||
      activity.family == ActivityFamily.matching ||
      activity.family == ActivityFamily.ordering ||
      activity.family == ActivityFamily.hangulStructure;
}

class _UnitPracticeSection extends StatelessWidget {
  const _UnitPracticeSection({
    required this.unit,
    required this.entries,
  });

  final UnitContent unit;
  final List<_PracticeEntry> entries;

  void _open(
    BuildContext context,
    List<ActivityContent> activities, {
    String? title,
  }) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => PracticeSessionScreen(
          activities: activities,
          title: title ?? 'Práctica · ${unit.title}',
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text('Práctica libre', style: Theme.of(context).textTheme.titleLarge),
        const SizedBox(height: 5),
        Text(
          'Repite ejercicios de esta unidad cuando quieras. Los intentos aquí no cambian tu progreso.',
          style: Theme.of(context).textTheme.bodySmall,
        ),
        const SizedBox(height: 10),
        Card(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                child: SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    onPressed: () => _open(
                      context,
                      entries.map((entry) => entry.activity).toList(growable: false),
                    ),
                    icon: const Icon(Icons.play_arrow),
                    label: Text('Practicar todos · ${entries.length} ejercicios'),
                  ),
                ),
              ),
              ExpansionTile(
                leading: const Icon(Icons.grid_view_outlined),
                title: Text('Ejercicios disponibles (${entries.length})'),
                subtitle: const Text('Abre cualquiera directamente'),
                children: [
                  for (final entry in entries)
                    ListTile(
                      leading: Icon(_practiceIcon(entry.activity.family)),
                      title: Text(
                        entry.activity.prompt ??
                            entry.activity.objective ??
                            entry.activity.family.label,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      subtitle: Text(
                        '${entry.lessonTitle} · ${entry.activity.family.label}',
                      ),
                      trailing: const Icon(Icons.chevron_right),
                      onTap: () => _open(
                        context,
                        [entry.activity],
                        title: 'Práctica · ${entry.lessonTitle}',
                      ),
                    ),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }
}

IconData _practiceIcon(ActivityFamily family) {
  return switch (family) {
    ActivityFamily.choice => Icons.touch_app_outlined,
    ActivityFamily.matching => Icons.compare_arrows,
    ActivityFamily.hangulStructure => Icons.grid_4x4_outlined,
    _ => Icons.fitness_center_outlined,
  };
}
