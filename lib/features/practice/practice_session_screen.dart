import 'package:flutter/material.dart';

import '../../core/models/activity_content.dart';
import '../../core/runtime/adapa_runtime.dart';
import '../../core/session/activity_session_store.dart';
import '../activity/activity_renderer_host.dart';

/// Runs existing course activities as repeatable practice without writing to
/// the learner's academic progress.
class PracticeSessionScreen extends StatefulWidget {
  const PracticeSessionScreen({
    super.key,
    required this.activities,
    required this.title,
  });

  final List<ActivityContent> activities;
  final String title;

  @override
  State<PracticeSessionScreen> createState() => _PracticeSessionScreenState();
}

class _PracticeSessionScreenState extends State<PracticeSessionScreen> {
  late final ActivitySessionStore _practiceStore;
  int _index = 0;
  int _revision = 0;
  bool _solved = false;

  @override
  void initState() {
    super.initState();
    _practiceStore = ActivitySessionStore(onWrite: _handlePracticeWrite);
  }

  Future<void> _handlePracticeWrite(
    String activityId,
    Map<String, dynamic> value,
  ) async {
    if (!mounted || widget.activities.isEmpty) return;
    final current = widget.activities[_index];
    if (activityId != current.id || value['complete'] != true) return;
    if (_solved) return;
    setState(() => _solved = true);
  }

  void _restartAll() {
    _practiceStore.clear();
    setState(() {
      _index = 0;
      _revision += 1;
      _solved = false;
    });
  }

  void _restartCurrent() {
    if (widget.activities.isEmpty) return;
    _practiceStore.remove(widget.activities[_index].id);
    setState(() {
      _revision += 1;
      _solved = false;
    });
  }

  void _next() {
    if (_index >= widget.activities.length - 1) return;
    setState(() {
      _index += 1;
      _revision += 1;
      _solved = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (widget.activities.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: Text(widget.title)),
        body: const Center(
          child: Padding(
            padding: EdgeInsets.all(24),
            child: Text('No hay ejercicios disponibles para esta práctica.'),
          ),
        ),
      );
    }

    final parentRuntime = AdapaRuntime.of(context);
    final activity = widget.activities[_index];
    final last = _index == widget.activities.length - 1;

    return Scaffold(
      appBar: AppBar(title: Text(widget.title)),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 36),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                children: [
                  const Icon(Icons.fitness_center_outlined),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.activities.length == 1
                              ? 'Práctica libre'
                              : 'Ejercicio ${_index + 1} de ${widget.activities.length}',
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        const SizedBox(height: 3),
                        const Text(
                          'Puedes equivocarte y repetir. Esta sesión no modifica tu progreso del curso.',
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          if (widget.activities.length > 1) ...[
            const SizedBox(height: 10),
            LinearProgressIndicator(
              value: (_index + (_solved ? 1 : 0)) / widget.activities.length,
            ),
          ],
          const SizedBox(height: 18),
          Text(
            activity.prompt ?? activity.objective ?? activity.family.label,
            style: Theme.of(context).textTheme.headlineSmall,
          ),
          const SizedBox(height: 14),
          AdapaRuntime(
            assetResolver: parentRuntime.assetResolver,
            tts: parentRuntime.tts,
            sessionStore: _practiceStore,
            progress: parentRuntime.progress,
            settings: parentRuntime.settings,
            child: KeyedSubtree(
              key: ValueKey('${activity.id}-$_revision'),
              child: ActivityRendererHost(activity: activity),
            ),
          ),
          if (_solved) ...[
            const SizedBox(height: 20),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.task_alt),
                        const SizedBox(width: 9),
                        Expanded(
                          child: Text(
                            last ? 'Práctica completada' : 'Ejercicio resuelto',
                            style: Theme.of(context).textTheme.titleMedium,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    if (!last)
                      FilledButton.icon(
                        onPressed: _next,
                        icon: const Icon(Icons.arrow_forward),
                        label: const Text('Siguiente ejercicio'),
                      )
                    else ...[
                      FilledButton.icon(
                        onPressed: _restartAll,
                        icon: const Icon(Icons.replay),
                        label: Text(
                          widget.activities.length == 1
                              ? 'Practicar otra vez'
                              : 'Reiniciar práctica',
                        ),
                      ),
                      const SizedBox(height: 8),
                      OutlinedButton.icon(
                        onPressed: () => Navigator.of(context).pop(),
                        icon: const Icon(Icons.arrow_back),
                        label: const Text('Volver a la unidad'),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ] else ...[
            const SizedBox(height: 14),
            TextButton.icon(
              onPressed: _restartCurrent,
              icon: const Icon(Icons.refresh),
              label: const Text('Reiniciar este ejercicio'),
            ),
          ],
        ],
      ),
    );
  }
}
