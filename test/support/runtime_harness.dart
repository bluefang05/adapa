import 'package:adapa/core/content/asset_course_repository.dart';
import 'package:adapa/core/content/asset_resolver.dart';
import 'package:adapa/core/persistence/key_value_store.dart';
import 'package:adapa/core/persistence/progress_store.dart';
import 'package:adapa/core/progress/progress_controller.dart';
import 'package:adapa/core/progress/progress_snapshot.dart';
import 'package:adapa/core/runtime/adapa_runtime.dart';
import 'package:adapa/core/services/tts_service.dart';
import 'package:adapa/core/session/activity_session_store.dart';
import 'package:adapa/core/settings/app_settings_controller.dart';
import 'package:adapa/core/theme/adapa_theme.dart';
import 'package:flutter/material.dart';

class RuntimeHarness {
  RuntimeHarness._({
    required this.repository,
    required this.resolver,
    required this.progress,
    required this.session,
    required this.settings,
  });

  final AssetCourseRepository repository;
  final AssetResolver resolver;
  final ProgressController progress;
  final ActivitySessionStore session;
  final AppSettingsController settings;

  static Future<RuntimeHarness> create() async {
    final repository = AssetCourseRepository();
    final progress = ProgressController(
      repository: repository,
      store: MemoryProgressStore(),
    );
    await progress.initialize();

    final settings = AppSettingsController(
      preferences: MemoryKeyValueStore(),
    );
    await settings.initialize();

    final session = ActivitySessionStore(onWrite: progress.handleSessionWrite)
      ..restore(progress.persistedSessionData);

    return RuntimeHarness._(
      repository: repository,
      resolver: AssetResolver(repository),
      progress: progress,
      session: session,
      settings: settings,
    );
  }

  Widget wrap(Widget child) {
    return AdapaRuntime(
      assetResolver: resolver,
      tts: NoopTtsService(),
      sessionStore: session,
      progress: progress,
      settings: settings,
      child: MaterialApp(
        debugShowCheckedModeBanner: false,
        theme: AdapaTheme.light(),
        home: Scaffold(
          body: SafeArea(child: child),
        ),
      ),
    );
  }

  void dispose() {
    progress.dispose();
    settings.dispose();
  }
}

class MemoryProgressStore implements ProgressStore {
  ProgressSnapshot? value;

  @override
  Future<void> clear(String courseId) async {
    value = null;
  }

  @override
  Future<ProgressSnapshot?> load(String courseId) async => value;

  @override
  Future<void> save(ProgressSnapshot snapshot) async {
    value = ProgressSnapshot.fromJson(snapshot.toJson());
  }
}
