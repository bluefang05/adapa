import 'dart:async';

import 'package:audioplayers/audioplayers.dart';

/// Short, non-blocking audio feedback used by tap-to-answer practice.
///
/// Playback failures are intentionally ignored: audio feedback should enhance
/// an exercise, never prevent the learner from continuing it.
class PracticeFeedbackService {
  PracticeFeedbackService._();

  static final AudioPlayer _successPlayer = AudioPlayer();
  static final AudioPlayer _errorPlayer = AudioPlayer();

  static const String _successAsset = 'audio/practice_success.mp3';
  static const String _errorAsset = 'audio/practice_error.mp3';

  static void success() {
    unawaited(_play(_successPlayer, _successAsset, volume: 0.72));
  }

  static void error() {
    unawaited(_play(_errorPlayer, _errorAsset, volume: 0.68));
  }

  static Future<void> _play(
    AudioPlayer player,
    String asset, {
    required double volume,
  }) async {
    try {
      await player.stop();
      await player.play(AssetSource(asset), volume: volume);
    } catch (_) {
      // Missing platform plugins, unavailable audio output, or an asset issue
      // must not interrupt the learning interaction.
    }
  }
}
