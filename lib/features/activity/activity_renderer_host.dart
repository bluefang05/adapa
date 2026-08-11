import 'package:flutter/material.dart';

import '../../core/models/activity_content.dart';
import '../../core/models/activity_family.dart';
import 'renderers/choice_activity_renderer.dart';
import 'renderers/dialogue_activity_renderer.dart';
import 'renderers/matching_activity_renderer.dart';
import 'renderers/hangul_structure_activity_renderer.dart';
import 'renderers/ordering_activity_renderer.dart';
import 'renderers/reading_speaking_activity_renderer.dart';
import 'renderers/review_activity_renderer.dart';
import 'renderers/text_input_activity_renderer.dart';
import 'renderers/visual_reference_activity_renderer.dart';

class ActivityRendererHost extends StatelessWidget {
  const ActivityRendererHost({super.key, required this.activity});

  final ActivityContent activity;

  @override
  Widget build(BuildContext context) {
    return switch (activity.family) {
      ActivityFamily.choice => ChoiceActivityRenderer(activity: activity),
      ActivityFamily.matching => MatchingActivityRenderer(activity: activity),
      ActivityFamily.textInput => TextInputActivityRenderer(activity: activity),
      ActivityFamily.ordering => OrderingActivityRenderer(activity: activity),
      ActivityFamily.hangulStructure => HangulStructureActivityRenderer(activity: activity),
      ActivityFamily.dialogue => DialogueActivityRenderer(activity: activity),
      ActivityFamily.visualReference => VisualReferenceActivityRenderer(activity: activity),
      ActivityFamily.readingSpeaking => ReadingSpeakingActivityRenderer(activity: activity),
      ActivityFamily.review => ReviewActivityRenderer(activity: activity),
    };
  }
}
