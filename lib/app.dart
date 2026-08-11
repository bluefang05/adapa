import 'package:flutter/material.dart';

import 'core/content/asset_course_repository.dart';
import 'core/content/asset_resolver.dart';
import 'core/persistence/key_value_store.dart';
import 'core/persistence/shared_preferences_progress_store.dart';
import 'core/progress/progress_controller.dart';
import 'core/runtime/adapa_runtime.dart';
import 'core/services/tts_service.dart';
import 'core/session/activity_session_store.dart';
import 'core/settings/app_settings_controller.dart';
import 'core/theme/adapa_theme.dart';
import 'features/course/course_home_screen.dart';

class AdapaApp extends StatefulWidget {
  const AdapaApp({super.key});

  @override
  State<AdapaApp> createState() => _AdapaAppState();
}

class _AdapaAppState extends State<AdapaApp> with WidgetsBindingObserver {
  late final AssetCourseRepository _repository;
  late final AssetResolver _assetResolver;
  late final TtsService _tts;
  late final KeyValueStore _preferences;
  late final ProgressController _progress;
  late final ActivitySessionStore _sessionStore;
  late final AppSettingsController _settings;
  late final Future<void> _initialization;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _repository = AssetCourseRepository();
    _assetResolver = AssetResolver(_repository);
    _tts = FlutterTtsService();
    _preferences = SharedPreferencesAsyncStore();
    _progress = ProgressController(
      repository: _repository,
      store: SharedPreferencesProgressStore(preferences: _preferences),
    );
    _settings = AppSettingsController(preferences: _preferences);
    _sessionStore = ActivitySessionStore(onWrite: _progress.handleSessionWrite);
    _initialization = _initialize();
  }

  Future<void> _initialize() async {
    await Future.wait([_progress.initialize(), _settings.initialize()]);
    _sessionStore.restore(_progress.persistedSessionData);
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.detached ||
        state == AppLifecycleState.inactive) {
      _progress.flush();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _progress.flush();
    _tts.stop();
    _progress.dispose();
    _settings.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AdapaRuntime(
      assetResolver: _assetResolver,
      tts: _tts,
      sessionStore: _sessionStore,
      progress: _progress,
      settings: _settings,
      child: AnimatedBuilder(
        animation: _settings,
        builder: (context, _) => MaterialApp(
          title: 'ADAPA',
          debugShowCheckedModeBanner: false,
          theme: _themeFor(_settings.colorMode),
          home: FutureBuilder<void>(
            future: _initialization,
            builder: (context, snapshot) {
              if (snapshot.hasError) {
                return _StartupError(error: snapshot.error);
              }
              if (snapshot.connectionState != ConnectionState.done) {
                return const _StartupLoading();
              }
              return CourseHomeScreen(repository: _repository);
            },
          ),
        ),
      ),
    );
  }

  ThemeData _themeFor(AppColorMode mode) {
    return switch (mode) {
      AppColorMode.light => AdapaTheme.light(),
      AppColorMode.balanced => AdapaTheme.balanced(),
      AppColorMode.dark => AdapaTheme.dark(),
    };
  }
}

class _StartupLoading extends StatelessWidget {
  const _StartupLoading();

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: EdgeInsets.all(32),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  '한글',
                  style: TextStyle(fontSize: 56, fontWeight: FontWeight.w800),
                ),
                SizedBox(height: 22),
                CircularProgressIndicator(),
                SizedBox(height: 14),
                Text('Preparando tu curso…'),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _StartupError extends StatelessWidget {
  const _StartupError({required this.error});
  final Object? error;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(28),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.error_outline, size: 48),
                const SizedBox(height: 16),
                Text(
                  'ADAPA no pudo iniciar',
                  style: Theme.of(context).textTheme.headlineSmall,
                ),
                const SizedBox(height: 8),
                Text('$error', textAlign: TextAlign.center),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
