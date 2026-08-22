import 'dart:async';

import 'package:audioplayers/audioplayers.dart';

/// Short, non-blocking audio feedback used by tap-to-answer practice.
///
/// Playback failures are intentionally ignored: audio feedback should enhance
/// an exercise, never prevent the learner from continuing it.
class PracticeFeedbackService {
  PracticeFeedbackService._();

  static AudioPlayer? _successPlayer;
  static AudioPlayer? _errorPlayer;

  static const String _successAsset = 'audio/practice_success.mp3';
  static const String _errorAsset = 'audio/practice_error.mp3';

  static bool enabled = true;

  static void success() {
    if (!enabled) return;
    unawaited(_play(() => _successPlayer ??= AudioPlayer(), _successAsset, volume: 0.72));
  }

  static void error() {
    if (!enabled) return;
    unawaited(_play(() => _errorPlayer ??= AudioPlayer(), _errorAsset, volume: 0.68));
  }

  static Future<void> _play(
    AudioPlayer Function() getPlayer,
    String asset, {
    required double volume,
  }) async {
    if (!enabled) return;
    try {
      final player = getPlayer();
      await player.stop();
      await player.play(AssetSource(asset), volume: volume);
    } catch (_) {
      // Missing platform plugins, unavailable audio output, or an asset issue
      // must not interrupt the learning interaction.
    }
  }
}
