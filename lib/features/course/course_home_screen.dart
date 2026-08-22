import 'package:flutter/material.dart';

import '../../core/content/course_repository.dart';
import '../../core/models/course_manifest.dart';
import '../../core/runtime/adapa_runtime.dart';
import '../activity/activity_screen.dart';
import '../common/widgets/admob_banner.dart';
import '../settings/settings_screen.dart';
import '../unit/unit_screen.dart';

class CourseHomeScreen extends StatelessWidget {
  const CourseHomeScreen({super.key, required this.repository});

  final CourseRepository repository;

  Future<void> _continueCourse(BuildContext context) async {
    final progress = AdapaRuntime.of(context).progress;
    final resume = progress.resume;
    if (resume != null) {
      final activity = progress.activityById(resume.activityId);
      if (activity != null && context.mounted) {
        await Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => ActivityScreen(activity: activity)),
        );
        return;
      }
    }

    for (final unit in progress.manifest.units) {
      if (!progress.isUnitUnlocked(unit.id)) continue;
      final content = progress.unitById(unit.id);
      if (content == null) continue;
      for (final lesson in content.lessons) {
        if (lesson.activities.isNotEmpty) {
          if (!context.mounted) return;
          await Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => ActivityScreen(activity: lesson.activities.first),
            ),
          );
          return;
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final progress = AdapaRuntime.of(context).progress;
    return AnimatedBuilder(
      animation: progress,
      builder: (context, _) {
        final course = progress.manifest;
        final resume = progress.resume;
        final resumeLesson = resume == null
            ? null
            : progress.lessonById(resume.lessonId);
        final percent = (progress.courseFraction * 100).round();

        return Scaffold(
          appBar: AppBar(
            title: const Text(
              'ADAPA',
              style: TextStyle(fontWeight: FontWeight.w800),
            ),
            actions: [
              IconButton(
                tooltip: 'Ajustes',
                onPressed: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const SettingsScreen()),
                ),
                icon: const Icon(Icons.settings_outlined),
              ),
            ],
          ),
          body: CustomScrollView(
            slivers: [
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 14),
                sliver: SliverToBoxAdapter(
                  child: _CourseHero(
                    title: course.title,
                    subtitle: course.subtitle,
                    level: course.level,
                    percent: percent,
                    completedUnits: progress.completedUnitCount,
                    totalUnits: course.units.length,
                    completedActivities: progress.completedActivityCount,
                    totalActivities: progress.activityCount,
                    currentStreak: progress.currentStreak,
                    longestStreak: progress.longestStreak,
                    courseComplete: progress.courseComplete,
                    resumeLessonTitle: resumeLesson?.title,
                    onContinue: progress.courseComplete
                        ? null
                        : () => _continueCourse(context),
                  ),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 6, 16, 8),
                sliver: SliverToBoxAdapter(
                  child: Text(
                    'Tu ruta',
                    style: Theme.of(context).textTheme.headlineSmall,
                  ),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 36),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, i) => _LearningPathUnit(
                      index: i,
                      summary: course.units[i],
                      repository: repository,
                      last: i == course.units.length - 1,
                    ),
                    childCount: course.units.length,
                  ),
                ),
              ),
            ],
          ),
          bottomNavigationBar: const SafeArea(
            child: AdmobBannerWidget(),
          ),
        );
      },
    );
  }
}

class _CourseHero extends StatelessWidget {
  const _CourseHero({
    required this.title,
    required this.subtitle,
    required this.level,
    required this.percent,
    required this.completedUnits,
    required this.totalUnits,
    required this.completedActivities,
    required this.totalActivities,
    required this.currentStreak,
    required this.longestStreak,
    required this.courseComplete,
    required this.resumeLessonTitle,
    required this.onContinue,
  });

  final String title;
  final String subtitle;
  final String level;
  final int percent;
  final int completedUnits;
  final int totalUnits;
  final int completedActivities;
  final int totalActivities;
  final int currentStreak;
  final int longestStreak;
  final bool courseComplete;
  final String? resumeLessonTitle;
  final VoidCallback? onContinue;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final isDark = scheme.brightness == Brightness.dark;
    final isSepia = scheme.primary == const Color(0xFF70512A);
    final heroColors = isDark
        ? const [Color(0xFF172638), Color(0xFF0D141E)]
        : isSepia
        ? const [Color(0xFFE3C38A), Color(0xFF9A6A2F)]
        : [scheme.primary, const Color(0xFF245A8B)];
    final heroTextColor = isDark || isSepia
        ? scheme.onPrimaryContainer
        : Colors.white;
    final heroMutedColor = heroTextColor.withValues(alpha: .72);
    final heroTrackColor = heroTextColor.withValues(alpha: .22);
    final buttonBackground = isDark
        ? const Color(0xFF243449)
        : isSepia
        ? const Color(0xFFFFF7E8)
        : Colors.white;
    final buttonForeground = isDark
        ? const Color(0xFFE9F2FB)
        : isSepia
        ? const Color(0xFF70512A)
        : scheme.primary;
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: heroColors,
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(26),
        border: Border.all(
          color: heroTextColor.withValues(alpha: isDark ? .10 : .16),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Align(
            alignment: Alignment.centerRight,
            child: Text(
              '한국어',
              style: TextStyle(
                fontSize: 46,
                height: 1,
                color: heroMutedColor,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          Text(
            title,
            style: Theme.of(context).textTheme.headlineMedium?.copyWith(
              color: heroTextColor,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 4),
          Text('$subtitle · $level', style: TextStyle(color: heroMutedColor)),
          const SizedBox(height: 22),
          Row(
            children: [
              Expanded(
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(99),
                  child: LinearProgressIndicator(
                    value: percent / 100,
                    minHeight: 10,
                    color: scheme.tertiary,
                    backgroundColor: heroTrackColor,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Text(
                '$percent%',
                style: TextStyle(
                  color: heroTextColor,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            '$completedUnits/$totalUnits unidades · '
            '$completedActivities/$totalActivities actividades',
            style: TextStyle(color: heroMutedColor),
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              _HeroMetric(
                icon: Icons.local_fire_department_outlined,
                text: '$currentStreak día${currentStreak == 1 ? '' : 's'}',
                color: heroTextColor,
                backgroundColor: heroTrackColor,
              ),
              if (longestStreak > currentStreak)
                _HeroMetric(
                  icon: Icons.emoji_events_outlined,
                  text: 'mejor $longestStreak',
                  color: heroTextColor,
                  backgroundColor: heroTrackColor,
                ),
            ],
          ),
          const SizedBox(height: 18),
          FilledButton.icon(
            style: FilledButton.styleFrom(
              backgroundColor: buttonBackground,
              foregroundColor: buttonForeground,
            ),
            onPressed: onContinue,
            icon: Icon(
              courseComplete ? Icons.emoji_events_outlined : Icons.play_arrow,
            ),
            label: Text(
              courseComplete
                  ? 'Curso completado'
                  : resumeLessonTitle == null
                  ? 'Comenzar curso'
                  : 'Continuar · $resumeLessonTitle',
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }
}

class _HeroMetric extends StatelessWidget {
  const _HeroMetric({
    required this.icon,
    required this.text,
    required this.color,
    required this.backgroundColor,
  });

  final IconData icon;
  final String text;
  final Color color;
  final Color backgroundColor;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 17, color: color),
            const SizedBox(width: 5),
            Text(
              text,
              style: TextStyle(color: color, fontWeight: FontWeight.w700),
            ),
          ],
        ),
      ),
    );
  }
}

class _LearningPathUnit extends StatelessWidget {
  const _LearningPathUnit({
    required this.index,
    required this.summary,
    required this.repository,
    required this.last,
  });

  final int index;
  final UnitSummary summary;
  final CourseRepository repository;
  final bool last;

  @override
  Widget build(BuildContext context) {
    final progress = AdapaRuntime.of(context).progress;
    final state = progress.unitProgress(summary.id);
    final scheme = Theme.of(context).colorScheme;

    final status = state.completed
        ? 'Completada'
        : state.unlocked
        ? state.completedActivities == 0
              ? 'Disponible'
              : 'En progreso'
        : 'Bloqueada';

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 54,
          child: Column(
            children: [
              CircleAvatar(
                radius: 22,
                backgroundColor: state.completed
                    ? scheme.primary
                    : state.unlocked
                    ? scheme.primaryContainer
                    : scheme.surfaceContainerHighest,
                foregroundColor: state.completed
                    ? scheme.onPrimary
                    : state.unlocked
                    ? scheme.onPrimaryContainer
                    : scheme.onSurfaceVariant,
                child: state.completed
                    ? const Icon(Icons.check)
                    : state.unlocked
                    ? Text(
                        '${index + 1}',
                        style: const TextStyle(fontWeight: FontWeight.w800),
                      )
                    : const Icon(Icons.lock_outline),
              ),
              if (!last)
                Container(
                  width: 3,
                  height: 126,
                  margin: const EdgeInsets.symmetric(vertical: 4),
                  color: state.completed
                      ? scheme.primary.withValues(alpha: .35)
                      : scheme.outlineVariant,
                ),
            ],
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Padding(
            padding: EdgeInsets.only(bottom: last ? 0 : 14),
            child: Card(
              child: InkWell(
                borderRadius: BorderRadius.circular(20),
                onTap: !state.unlocked
                    ? null
                    : () => Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => UnitScreen(
                            repository: repository,
                            summary: summary,
                          ),
                        ),
                      ),
                child: Padding(
                  padding: const EdgeInsets.all(17),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Text(
                              summary.title,
                              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: state.completed
                                  ? scheme.primaryContainer
                                  : state.unlocked
                                  ? scheme.secondaryContainer
                                  : scheme.surfaceContainerHighest,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              status,
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: state.completed
                                    ? scheme.onPrimaryContainer
                                    : state.unlocked
                                    ? scheme.onSecondaryContainer
                                    : scheme.onSurfaceVariant,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${summary.lessonCount} lecciones · '
                        '${summary.activityCount} actividades',
                      ),
                      const SizedBox(height: 12),
                      LinearProgressIndicator(value: state.fraction),
                      const SizedBox(height: 7),
                      Text(
                        state.unlocked
                            ? '${state.completedLessons}/${state.lessonCount} lecciones completadas'
                            : 'Completa las unidades previas para continuar',
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}
