import 'dart:math';

import 'package:flutter/material.dart';

import '../../core/models/activity_family.dart';
import '../../core/models/unit_content.dart';
import '../../core/services/practice_feedback_service.dart';

enum MissionExerciseType {
  multipleChoice,
  selectMatch,
  build,
  complete,
  contextChoice,
  errorDetection,
  challenge,
}

enum MissionFeedbackState { correct, incorrect, almost, correctWithHint }

enum MissionStatus {
  intro,
  playing,
  feedback,
  roundComplete,
  finalChallenge,
  complete,
}

class MissionRound {
  const MissionRound(this.id, this.title);
  final String id;
  final String title;
}

class MissionOption {
  const MissionOption(this.id, this.label, {this.almost = false});
  final String id;
  final String label;
  final bool almost;
}

class MissionExercise {
  const MissionExercise({
    required this.id,
    required this.round,
    required this.type,
    required this.conceptIds,
    required this.prompt,
    required this.options,
    required this.correctAnswer,
    required this.explanation,
    required this.hint,
    required this.points,
    this.context,
    this.assetKey,
    this.expertOffer = false,
  });

  final String id;
  final MissionRound round;
  final MissionExerciseType type;
  final List<String> conceptIds;
  final String prompt;
  final List<MissionOption> options;
  final List<String> correctAnswer;
  final String explanation;
  final String hint;
  final int points;
  final String? context;
  final String? assetKey;
  final bool expertOffer;
}

class MissionAnswerRecord {
  const MissionAnswerRecord({
    required this.exerciseId,
    required this.conceptIds,
    required this.selectedAnswer,
    required this.correct,
    required this.usedHint,
    required this.scoreAwarded,
    required this.streakAfter,
  });

  final String exerciseId;
  final List<String> conceptIds;
  final Object selectedAnswer;
  final bool correct;
  final bool usedHint;
  final int scoreAwarded;
  final int streakAfter;
}

class _ReviewItem {
  const _ReviewItem(
    this.conceptId,
    this.sourceExerciseId,
    this.reviewAfterIndex,
  );
  final String conceptId;
  final String sourceExerciseId;
  final int reviewAfterIndex;
}

class GamifiedUnitScreen extends StatefulWidget {
  const GamifiedUnitScreen({super.key, required this.unit});

  final UnitContent unit;

  @override
  State<GamifiedUnitScreen> createState() => _GamifiedUnitScreenState();
}

class _GamifiedUnitScreenState extends State<GamifiedUnitScreen> {
  late List<MissionExercise> _exercises;
  final List<String> _failedConcepts = <String>[];
  final List<String> _recoveredConcepts = <String>[];
  final List<_ReviewItem> _reviewQueue = <_ReviewItem>[];
  final List<MissionAnswerRecord> _answers = <MissionAnswerRecord>[];
  final List<String> _built = <String>[];
  int _index = 0;
  int _score = 0;
  int _streak = 0;
  int _bestStreak = 0;
  int _hintsRemaining = 2;
  int _hintsUsed = 0;
  int _correctAnswers = 0;
  int _incorrectAnswers = 0;
  MissionStatus _status = MissionStatus.intro;
  MissionFeedbackState? _feedback;
  String? _selected;
  bool _usedHint = false;
  bool _expertMode = false;

  @override
  void initState() {
    super.initState();
    _exercises = buildMissionExercises(widget.unit);
  }

  MissionExercise get _exercise => _exercises[_index];

  bool get _canCheck =>
      _exercise.type == MissionExerciseType.build ||
          _exercise.type == MissionExerciseType.complete
      ? _built.isNotEmpty
      : _selected != null;

  void _resetInteraction() {
    _feedback = null;
    _selected = null;
    _built.clear();
    _usedHint = false;
    _expertMode = false;
  }

  void _check() {
    if (!_canCheck || _status != MissionStatus.playing) return;
    final answer = _built.isEmpty ? _selected! : _built.join(' ');
    final correct = _exercise.correctAnswer.contains(answer);
    final almost =
        !correct &&
        _exercise.options.any((option) => option.id == answer && option.almost);
    final basePoints = _usedHint
        ? max(50, _exercise.points - 25)
        : _exercise.points;
    final awarded = correct
        ? (_expertMode ? basePoints * 2 : basePoints) + _streakBonus()
        : 0;

    if (correct) {
      PracticeFeedbackService.success();
      _score += awarded;
      _streak += 1;
      _bestStreak = max(_bestStreak, _streak);
      _correctAnswers += 1;
      for (final concept in _exercise.conceptIds) {
        if (_failedConcepts.contains(concept) &&
            !_recoveredConcepts.contains(concept)) {
          _recoveredConcepts.add(concept);
        }
      }
    } else {
      PracticeFeedbackService.error();
      _streak = 0;
      _incorrectAnswers += 1;
      for (final concept in _exercise.conceptIds) {
        if (!_failedConcepts.contains(concept)) _failedConcepts.add(concept);
        _reviewQueue.add(
          _ReviewItem(
            concept,
            _exercise.id,
            min(_index + 4, _exercises.length - 1),
          ),
        );
      }
    }

    _answers.add(
      MissionAnswerRecord(
        exerciseId: _exercise.id,
        conceptIds: _exercise.conceptIds,
        selectedAnswer: answer,
        correct: correct,
        usedHint: _usedHint,
        scoreAwarded: awarded,
        streakAfter: _streak,
      ),
    );
    setState(() {
      _feedback = correct
          ? (_usedHint
                ? MissionFeedbackState.correctWithHint
                : MissionFeedbackState.correct)
          : (almost
                ? MissionFeedbackState.almost
                : MissionFeedbackState.incorrect);
      _status = MissionStatus.feedback;
    });
  }

  int _streakBonus() {
    if (_streak >= 5) return 50;
    if (_streak >= 2) return 25;
    return 0;
  }

  void _continue() {
    if (_index >= _exercises.length - 1) {
      setState(() => _status = MissionStatus.complete);
      return;
    }

    final next = _index + 1;
    final dueReviews = _reviewQueue
        .where((item) => item.reviewAfterIndex <= next)
        .toList();
    if (dueReviews.isNotEmpty) {
      final review = dueReviews.first;
      final reviewIndex = _exercises.indexWhere(
        (candidate) =>
            _exercises.indexOf(candidate) >= next &&
            candidate.id != review.sourceExerciseId &&
            candidate.conceptIds.contains(review.conceptId) &&
            candidate.type != _exercise.type,
      );
      if (reviewIndex > next) {
        final picked = _exercises.removeAt(reviewIndex);
        _exercises.insert(next, picked);
      }
      _reviewQueue.remove(review);
    }

    final previousRound = _exercise.round.id;
    final nextRound = _exercises[next].round.id;
    setState(() {
      _index = next;
      _resetInteraction();
      if (nextRound == 'final' && previousRound != 'final') {
        _status = MissionStatus.finalChallenge;
      } else if ((nextRound == 'build' || nextRound == 'use') &&
          previousRound != nextRound) {
        _status = MissionStatus.roundComplete;
      } else {
        _status = MissionStatus.playing;
      }
    });
  }

  void _restart() {
    setState(() {
      _exercises = buildMissionExercises(widget.unit);
      _failedConcepts.clear();
      _recoveredConcepts.clear();
      _reviewQueue.clear();
      _answers.clear();
      _index = 0;
      _score = 0;
      _streak = 0;
      _bestStreak = 0;
      _hintsRemaining = 2;
      _hintsUsed = 0;
      _correctAnswers = 0;
      _incorrectAnswers = 0;
      _resetInteraction();
      _status = MissionStatus.intro;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Misión · ${widget.unit.title}')),
      body: SafeArea(
        child: switch (_status) {
          MissionStatus.intro => _IntroView(
            unit: widget.unit,
            onStart: () => setState(() => _status = MissionStatus.playing),
          ),
          MissionStatus.roundComplete => _GateView(
            icon: Icons.task_alt,
            title: 'Ronda completada',
            lines: [
              '$_score puntos',
              'Mejor racha: $_bestStreak',
              'Siguiente: ${_exercise.round.title}',
            ],
            button: 'Continuar',
            onPressed: () => setState(() => _status = MissionStatus.playing),
          ),
          MissionStatus.finalChallenge => _GateView(
            icon: Icons.workspace_premium_outlined,
            title: 'Desafío final',
            lines: const ['Combina reconocimiento, construcción y uso.'],
            button: 'Entrar al desafío',
            onPressed: () => setState(() => _status = MissionStatus.playing),
          ),
          MissionStatus.complete => _CompleteView(
            score: _score,
            correct: _correctAnswers,
            incorrect: _incorrectAnswers,
            total: _answers.length,
            bestStreak: _bestStreak,
            hintsUsed: _hintsUsed,
            recovered: _recoveredConcepts.length,
            onRestart: _restart,
            onExit: () => Navigator.of(context).pop(),
          ),
          MissionStatus.playing || MissionStatus.feedback => _PlayView(
            exercise: _exercise,
            index: _index,
            total: _exercises.length,
            score: _score,
            streak: _streak,
            bestStreak: _bestStreak,
            hintsRemaining: _hintsRemaining,
            usedHint: _usedHint,
            feedback: _feedback,
            selected: _selected,
            built: _built,
            expertMode: _expertMode,
            canCheck: _canCheck,
            onSelected: (value) => setState(() => _selected = value),
            onBuildAdd: (value) => setState(() => _built.add(value)),
            onBuildRemove: (index) => setState(() => _built.removeAt(index)),
            onHint: () {
              if (_usedHint || _hintsRemaining == 0) return;
              setState(() {
                _usedHint = true;
                _hintsRemaining -= 1;
                _hintsUsed += 1;
              });
            },
            onExpert: (value) => setState(() => _expertMode = value),
            onCheck: _check,
            onContinue: _continue,
          ),
        },
      ),
    );
  }
}

class _PlayView extends StatelessWidget {
  const _PlayView({
    required this.exercise,
    required this.index,
    required this.total,
    required this.score,
    required this.streak,
    required this.bestStreak,
    required this.hintsRemaining,
    required this.usedHint,
    required this.feedback,
    required this.selected,
    required this.built,
    required this.expertMode,
    required this.canCheck,
    required this.onSelected,
    required this.onBuildAdd,
    required this.onBuildRemove,
    required this.onHint,
    required this.onExpert,
    required this.onCheck,
    required this.onContinue,
  });

  final MissionExercise exercise;
  final int index;
  final int total;
  final int score;
  final int streak;
  final int bestStreak;
  final int hintsRemaining;
  final bool usedHint;
  final MissionFeedbackState? feedback;
  final String? selected;
  final List<String> built;
  final bool expertMode;
  final bool canCheck;
  final ValueChanged<String> onSelected;
  final ValueChanged<String> onBuildAdd;
  final ValueChanged<int> onBuildRemove;
  final VoidCallback onHint;
  final ValueChanged<bool> onExpert;
  final VoidCallback onCheck;
  final VoidCallback onContinue;

  bool get locked => feedback != null;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 28),
      children: [
        _MissionHeader(
          exercise: exercise,
          index: index,
          total: total,
          score: score,
          streak: streak,
          bestStreak: bestStreak,
          hintsRemaining: hintsRemaining,
        ),
        const SizedBox(height: 14),
        if (exercise.expertOffer && !locked)
          SwitchListTile(
            contentPadding: EdgeInsets.zero,
            title: const Text('Modo experto'),
            subtitle: const Text('Doble puntuación si aciertas.'),
            value: expertMode,
            onChanged: onExpert,
          ),
        if (exercise.context != null) _ContextBox(text: exercise.context!),
        _AssetHook(assetKey: exercise.assetKey),
        Text(exercise.prompt, style: Theme.of(context).textTheme.headlineSmall),
        const SizedBox(height: 12),
        _ExerciseBody(
          exercise: exercise,
          selected: selected,
          built: built,
          locked: locked,
          onSelected: onSelected,
          onBuildAdd: onBuildAdd,
          onBuildRemove: onBuildRemove,
        ),
        const SizedBox(height: 12),
        if (usedHint)
          _HintPanel(text: exercise.hint)
        else
          OutlinedButton.icon(
            onPressed: locked || hintsRemaining == 0 ? null : onHint,
            icon: const Icon(Icons.lightbulb_outline),
            label: Text('Usar pista · $hintsRemaining'),
          ),
        const SizedBox(height: 12),
        if (feedback == null)
          FilledButton.icon(
            onPressed: canCheck ? onCheck : null,
            icon: const Icon(Icons.check),
            label: const Text('Comprobar'),
          )
        else ...[
          _FeedbackPanel(
            state: feedback!,
            exercise: exercise,
            selected: built.isEmpty ? selected : built.join(' '),
            expertMode: expertMode,
          ),
          const SizedBox(height: 12),
          FilledButton.icon(
            onPressed: onContinue,
            icon: const Icon(Icons.arrow_forward),
            label: Text(index == total - 1 ? 'Ver resultados' : 'Continuar'),
          ),
        ],
      ],
    );
  }
}

class _ExerciseBody extends StatelessWidget {
  const _ExerciseBody({
    required this.exercise,
    required this.selected,
    required this.built,
    required this.locked,
    required this.onSelected,
    required this.onBuildAdd,
    required this.onBuildRemove,
  });

  final MissionExercise exercise;
  final String? selected;
  final List<String> built;
  final bool locked;
  final ValueChanged<String> onSelected;
  final ValueChanged<String> onBuildAdd;
  final ValueChanged<int> onBuildRemove;

  @override
  Widget build(BuildContext context) {
    if (exercise.type == MissionExerciseType.build ||
        exercise.type == MissionExerciseType.complete) {
      final available = exercise.options.where(
        (option) => !built.contains(option.id),
      );
      return Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
            constraints: const BoxConstraints(minHeight: 60),
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              border: Border.all(
                color: Theme.of(context).colorScheme.outlineVariant,
              ),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                for (var i = 0; i < built.length; i++)
                  InputChip(
                    label: Text(built[i]),
                    onDeleted: locked ? null : () => onBuildRemove(i),
                  ),
                if (built.isEmpty) const Text('Construye tu respuesta aquí.'),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              for (final option in available)
                ActionChip(
                  label: Text(option.label),
                  onPressed: locked ? null : () => onBuildAdd(option.id),
                ),
            ],
          ),
        ],
      );
    }
    return Column(
      children: [
        for (final option in exercise.options)
          Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: Card(
              color: selected == option.id
                  ? Theme.of(context).colorScheme.secondaryContainer
                  : null,
              child: ListTile(
                minVerticalPadding: 14,
                leading: Icon(
                  selected == option.id
                      ? Icons.radio_button_checked
                      : Icons.radio_button_off,
                ),
                title: Text(option.label),
                onTap: locked ? null : () => onSelected(option.id),
              ),
            ),
          ),
      ],
    );
  }
}

class _MissionHeader extends StatelessWidget {
  const _MissionHeader({
    required this.exercise,
    required this.index,
    required this.total,
    required this.score,
    required this.streak,
    required this.bestStreak,
    required this.hintsRemaining,
  });

  final MissionExercise exercise;
  final int index;
  final int total;
  final int score;
  final int streak;
  final int bestStreak;
  final int hintsRemaining;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(
                    '${exercise.round.title} · ${index + 1} / $total',
                  ),
                ),
                Text('$score pts'),
              ],
            ),
            const SizedBox(height: 8),
            LinearProgressIndicator(value: (index + 1) / total),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                Chip(label: Text('Racha $streak')),
                Chip(label: Text('Mejor $bestStreak')),
                Chip(label: Text('Pistas $hintsRemaining')),
                if (streak == 3 || streak == 5 || streak == 8)
                  const Chip(
                    avatar: Icon(
                      Icons.local_fire_department_outlined,
                      size: 18,
                    ),
                    label: Text('Buen ritmo'),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _FeedbackPanel extends StatelessWidget {
  const _FeedbackPanel({
    required this.state,
    required this.exercise,
    required this.selected,
    required this.expertMode,
  });

  final MissionFeedbackState state;
  final MissionExercise exercise;
  final String? selected;
  final bool expertMode;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final correct =
        state == MissionFeedbackState.correct ||
        state == MissionFeedbackState.correctWithHint;
    final almost = state == MissionFeedbackState.almost;
    final bg = correct
        ? scheme.primaryContainer
        : almost
        ? scheme.tertiaryContainer
        : scheme.errorContainer;
    final fg = correct
        ? scheme.onPrimaryContainer
        : almost
        ? scheme.onTertiaryContainer
        : scheme.onErrorContainer;
    final title = switch (state) {
      MissionFeedbackState.correct =>
        'Bien · +${expertMode ? exercise.points * 2 : exercise.points}',
      MissionFeedbackState.correctWithHint => 'Correcto con pista',
      MissionFeedbackState.almost => 'Casi',
      MissionFeedbackState.incorrect => 'Vamos a corregirlo',
    };
    return Semantics(
      liveRegion: true,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: bg,
          borderRadius: BorderRadius.circular(18),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(
              correct ? Icons.check_circle_outline : Icons.info_outline,
              color: fg,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(color: fg, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    correct
                        ? exercise.explanation
                        : 'Elegiste ${selected ?? 'una opción'}. ${exercise.explanation}',
                    style: TextStyle(color: fg),
                  ),
                  if (!correct) ...[
                    const SizedBox(height: 4),
                    Text(
                      'Respuesta: ${exercise.correctAnswer.first}',
                      style: TextStyle(color: fg, fontWeight: FontWeight.w800),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ContextBox extends StatelessWidget {
  const _ContextBox({required this.text});
  final String text;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surfaceContainerHighest,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Icon(Icons.place_outlined),
          const SizedBox(width: 10),
          Expanded(child: Text(text)),
        ],
      ),
    );
  }
}

class _AssetHook extends StatelessWidget {
  const _AssetHook({required this.assetKey});
  final String? assetKey;

  @override
  Widget build(BuildContext context) {
    if (assetKey == null) return const SizedBox.shrink();
    // ASSET HOOK: next phase can resolve this key to pictograms, scenes, or
    // character feedback without changing the mission engine.
    return Align(
      alignment: Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        width: 46,
        height: 46,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          border: Border.all(
            color: Theme.of(context).colorScheme.outlineVariant,
          ),
        ),
        child: const Icon(
          Icons.extension_outlined,
          semanticLabel: 'Espacio visual futuro',
        ),
      ),
    );
  }
}

class _HintPanel extends StatelessWidget {
  const _HintPanel({required this.text});
  final String text;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(13),
      decoration: BoxDecoration(
        border: Border.all(color: Theme.of(context).colorScheme.tertiary),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          const Icon(Icons.lightbulb_outline),
          const SizedBox(width: 9),
          Expanded(child: Text(text)),
        ],
      ),
    );
  }
}

class _IntroView extends StatelessWidget {
  const _IntroView({required this.unit, required this.onStart});
  final UnitContent unit;
  final VoidCallback onStart;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Icon(Icons.flag_outlined, size: 42),
                const SizedBox(height: 12),
                Text(
                  unit.title,
                  style: Theme.of(context).textTheme.headlineMedium,
                ),
                const SizedBox(height: 8),
                Text(unit.goal ?? 'Reconoce, construye y usa lo aprendido.'),
                const SizedBox(height: 16),
                const Text('23 interacciones · 6 rondas · 2 pistas'),
                const SizedBox(height: 18),
                FilledButton.icon(
                  onPressed: onStart,
                  icon: const Icon(Icons.play_arrow),
                  label: const Text('Empezar'),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _GateView extends StatelessWidget {
  const _GateView({
    required this.icon,
    required this.title,
    required this.lines,
    required this.button,
    required this.onPressed,
  });

  final IconData icon;
  final String title;
  final List<String> lines;
  final String button;
  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: ListView(
        shrinkWrap: true,
        padding: const EdgeInsets.all(20),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Icon(icon, size: 44),
                  const SizedBox(height: 10),
                  Text(
                    title,
                    style: Theme.of(context).textTheme.headlineMedium,
                  ),
                  const SizedBox(height: 10),
                  for (final line in lines) Text(line),
                  const SizedBox(height: 16),
                  FilledButton(onPressed: onPressed, child: Text(button)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _CompleteView extends StatelessWidget {
  const _CompleteView({
    required this.score,
    required this.correct,
    required this.incorrect,
    required this.total,
    required this.bestStreak,
    required this.hintsUsed,
    required this.recovered,
    required this.onRestart,
    required this.onExit,
  });

  final int score;
  final int correct;
  final int incorrect;
  final int total;
  final int bestStreak;
  final int hintsUsed;
  final int recovered;
  final VoidCallback onRestart;
  final VoidCallback onExit;

  @override
  Widget build(BuildContext context) {
    final accuracy = total == 0 ? 0 : (correct / total * 100).round();
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Icon(Icons.emoji_events_outlined, size: 44),
                const SizedBox(height: 10),
                Text(
                  'Unidad completada',
                  style: Theme.of(context).textTheme.headlineMedium,
                ),
                const SizedBox(height: 12),
                _ResultRow('Puntos', '$score'),
                _ResultRow('Correctas', '$correct / $total'),
                _ResultRow('Errores', '$incorrect'),
                _ResultRow('Precisión', '$accuracy%'),
                _ResultRow('Mejor racha', '$bestStreak'),
                _ResultRow('Pistas usadas', '$hintsUsed'),
                _ResultRow('Conceptos recuperados', '$recovered'),
                const SizedBox(height: 16),
                FilledButton.icon(
                  onPressed: onRestart,
                  icon: const Icon(Icons.replay),
                  label: const Text('Repetir unidad'),
                ),
                const SizedBox(height: 8),
                OutlinedButton.icon(
                  onPressed: onExit,
                  icon: const Icon(Icons.arrow_back),
                  label: const Text('Volver'),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _ResultRow extends StatelessWidget {
  const _ResultRow(this.label, this.value);
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        children: [
          Expanded(child: Text(label)),
          Text(value, style: const TextStyle(fontWeight: FontWeight.w800)),
        ],
      ),
    );
  }
}

List<MissionExercise> buildMissionExercises(UnitContent unit) {
  final concepts = _conceptsFrom(unit);
  final rounds = {
    'recognize': const MissionRound('recognize', 'Reconocer'),
    'discriminate': const MissionRound('discriminate', 'Discriminar'),
    'build': const MissionRound('build', 'Construir'),
    'use': const MissionRound('use', 'Usar'),
    'challenge': const MissionRound('challenge', 'Reto'),
    'final': const MissionRound('final', 'Desafío final'),
  };
  final result = <MissionExercise>[];
  for (var i = 0; i < 23; i++) {
    final c = concepts[i % concepts.length];
    final near = concepts[(i + 1) % concepts.length];
    final far = concepts[(i + 3) % concepts.length];
    final round = i < 4
        ? 'recognize'
        : i < 8
        ? 'discriminate'
        : i < 12
        ? 'build'
        : i < 16
        ? 'use'
        : i < 20
        ? 'challenge'
        : 'final';
    final buildType = i >= 8 && i < 12 || i == 17 || i == 21;
    final answer = buildType ? c.answer.split(' ').join(' ') : c.id;
    result.add(
      MissionExercise(
        id: '${unit.id}_mission_${i + 1}',
        round: rounds[round]!,
        type: _typeFor(i, buildType),
        conceptIds: [c.id],
        prompt: _promptFor(i, c.label),
        context: round == 'use' || round == 'final'
            ? 'Situación: necesitas usar ${c.label} en una interacción breve.'
            : null,
        options: buildType
            ? _buildOptions(c.answer, near.answer)
            : [
                MissionOption(c.id, c.answer),
                MissionOption(near.id, near.answer, almost: true),
                MissionOption(far.id, far.answer),
                if (round == 'discriminate')
                  MissionOption('error_$i', '${c.answer} / ${near.label}'),
              ],
        correctAnswer: [
          round == 'discriminate' && i.isEven ? 'error_$i' : answer,
        ],
        explanation: round == 'discriminate'
            ? 'La clave es separar ${c.label} de ${near.label}.'
            : 'La respuesta esperada para ${c.label} es ${c.answer}.',
        hint: c.hint,
        points: round == 'challenge' || round == 'final' ? 125 : 100,
        assetKey: c.id,
        expertOffer: i == 17 || i == 20,
      ),
    );
  }
  return result;
}

MissionExerciseType _typeFor(int i, bool buildType) {
  if (buildType) {
    return i == 17 ? MissionExerciseType.complete : MissionExerciseType.build;
  }
  if (i < 4) {
    return i.isEven
        ? MissionExerciseType.multipleChoice
        : MissionExerciseType.selectMatch;
  }
  if (i < 8) {
    return i.isEven
        ? MissionExerciseType.errorDetection
        : MissionExerciseType.multipleChoice;
  }
  if (i < 16) return MissionExerciseType.contextChoice;
  return MissionExerciseType.challenge;
}

String _promptFor(int i, String label) {
  if (i < 4) return 'Reconoce: ¿qué corresponde a $label?';
  if (i < 8) {
    return i.isEven
        ? 'Encuentra la opción que NO corresponde.'
        : 'Distingue la opción más precisa.';
  }
  if (i < 12) return 'Construye: $label.';
  if (i < 16) return '¿Qué elegirías?';
  if (i < 20) return 'Reto de racha: resuelve esta interacción.';
  return 'Desafío: combina lo aprendido.';
}

List<MissionOption> _buildOptions(String answer, String distractor) {
  final tokens = <String>{
    ...answer.split(' '),
    ...distractor.split(' ').take(1),
  };
  return tokens
      .where((token) => token.trim().isNotEmpty)
      .map((token) => MissionOption(token, token))
      .toList();
}

class _ConceptSeed {
  const _ConceptSeed(this.id, this.label, this.answer, this.hint);
  final String id;
  final String label;
  final String answer;
  final String hint;
}

List<_ConceptSeed> _conceptsFrom(UnitContent unit) {
  final seeds = <_ConceptSeed>[];
  for (final lesson in unit.lessons) {
    for (final activity in lesson.activities) {
      final correct = (activity.payload['correct'] as List? ?? const [])
          .map((e) => e.toString())
          .toList();
      if (activity.family == ActivityFamily.choice && correct.isNotEmpty) {
        seeds.add(
          _ConceptSeed(
            activity.id,
            activity.objective ?? activity.prompt ?? correct.first,
            correct.first,
            activity.hints.isNotEmpty
                ? activity.hints.first
                : 'Recuerda el patrón visto en la unidad.',
          ),
        );
      }
      final pairs = activity.payload['pairs'];
      if (pairs is List) {
        for (final pair in pairs.take(2)) {
          if (pair is Map) {
            final left = pair['left']?.toString() ?? '';
            final right = pair['right']?.toString() ?? '';
            if (left.isNotEmpty && right.isNotEmpty) {
              seeds.add(
                _ConceptSeed(
                  '${activity.id}_$left',
                  right,
                  left,
                  activity.hints.isNotEmpty
                      ? activity.hints.first
                      : 'Compara forma y significado.',
                ),
              );
            }
          }
        }
      }
    }
  }
  if (seeds.length >= 8) return seeds;
  return const [
    _ConceptSeed('concept_hana', 'uno', '하나', 'Es el primer número nativo.'),
    _ConceptSeed('concept_water', 'agua', '물', 'Es una bebida básica.'),
    _ConceptSeed('concept_coffee', 'café', '커피', 'Aparece en pedidos simples.'),
    _ConceptSeed(
      'concept_please',
      'por favor / dame',
      '주세요',
      'Cierra muchos pedidos.',
    ),
    _ConceptSeed('concept_school', 'escuela', '학교', 'Es un lugar frecuente.'),
    _ConceptSeed('concept_hospital', 'hospital', '병원', 'Es un lugar de salud.'),
    _ConceptSeed(
      'concept_where',
      'preguntar ubicación',
      '어디에 있어요?',
      'Busca la estructura de ubicación.',
    ),
    _ConceptSeed('concept_bread', 'pan', '빵', 'Es un alimento.'),
  ];
}
