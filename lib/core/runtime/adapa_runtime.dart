import 'package:flutter/widgets.dart';

import '../content/asset_resolver.dart';
import '../progress/progress_controller.dart';
import '../services/tts_service.dart';
import '../session/activity_session_store.dart';
import '../settings/app_settings_controller.dart';

class AdapaRuntime extends InheritedWidget {
  const AdapaRuntime({
    super.key,
    required this.assetResolver,
    required this.tts,
    required this.sessionStore,
    required this.progress,
    required this.settings,
    required super.child,
  });

  final AssetResolver assetResolver;
  final TtsService tts;
  final ActivitySessionStore sessionStore;
  final ProgressController progress;
  final AppSettingsController settings;

  static AdapaRuntime of(BuildContext context) {
    final runtime = context.dependOnInheritedWidgetOfExactType<AdapaRuntime>();
    assert(runtime != null, 'AdapaRuntime not found above this context.');
    return runtime!;
  }

  @override
  bool updateShouldNotify(AdapaRuntime oldWidget) =>
      assetResolver != oldWidget.assetResolver ||
      tts != oldWidget.tts ||
      sessionStore != oldWidget.sessionStore ||
      progress != oldWidget.progress ||
      settings != oldWidget.settings;
}
