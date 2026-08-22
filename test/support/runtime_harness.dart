import 'dart:convert';
import 'dart:io';

import 'package:adapa/core/content/asset_resolver.dart';
import 'package:adapa/core/content/course_repository.dart';
import 'package:adapa/core/models/course_manifest.dart';
import 'package:adapa/core/models/unit_content.dart';
import 'package:adapa/core/persistence/key_value_store.dart';
import 'package:adapa/core/persistence/progress_store.dart';
import 'package:adapa/core/progress/progress_controller.dart';
import 'package:adapa/core/progress/progress_snapshot.dart';
import 'package:adapa/core/runtime/adapa_runtime.dart';
import 'package:adapa/core/services/practice_feedback_service.dart';
import 'package:adapa/core/services/tts_service.dart';
import 'package:adapa/core/session/activity_session_store.dart';
import 'package:adapa/core/settings/app_settings_controller.dart';
import 'package:adapa/core/theme/adapa_theme.dart';
import 'package:flutter/material.dart';

class DiskCourseRepository implements CourseRepository {
  CourseManifest? _manifestCache;
  final Map<String, UnitContent> _unitCache = {};
  final Map<String, Map<String, dynamic>> _resourceCache = {};

  @override
  Future<CourseManifest> loadManifest() async {
    if (_manifestCache != null) return _manifestCache!;
    final raw = File('assets/content/course_manifest.json').readAsStringSync();
    final json = jsonDecode(raw) as Map<String, dynamic>;
    return _manifestCache = CourseManifest.fromJson(json);
  }

  @override
  Future<UnitContent> loadUnit(UnitSummary unit) async {
    if (_unitCache.containsKey(unit.id)) return _unitCache[unit.id]!;
    final raw = File(unit.asset).readAsStringSync();
    final json = jsonDecode(raw) as Map<String, dynamic>;
    return _unitCache[unit.id] = UnitContent.fromJson(json);
  }

  @override
  Future<Map<String, dynamic>> loadResource(String assetPath) async {
    if (_resourceCache.containsKey(assetPath)) return _resourceCache[assetPath]!;
    final raw = File(assetPath).readAsStringSync();
    final json = jsonDecode(raw) as Map<String, dynamic>;
    return _resourceCache[assetPath] = json;
  }
}

class RuntimeHarness {
  RuntimeHarness._({
    required this.repository,
    required this.resolver,
    required this.progress,
    required this.session,
    required this.settings,
  });

  final CourseRepository repository;
  final AssetResolver resolver;
  final ProgressController progress;
  final ActivitySessionStore session;
  final AppSettingsController settings;

  static Future<RuntimeHarness> create({bool loadCourse = true}) async {
    final repository = DiskCourseRepository();
    final progress = ProgressController(
      repository: repository,
      store: MemoryProgressStore(),
    );
    if (loadCourse) {
      await progress.initialize();
    }

    final settings = AppSettingsController(
      preferences: MemoryKeyValueStore(),
    );
    await settings.initialize();

    final session = ActivitySessionStore(onWrite: progress.handleSessionWrite)
      ..restore(progress.persistedSessionData);

    PracticeFeedbackService.enabled = false;

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
