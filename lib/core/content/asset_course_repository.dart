import 'dart:convert';

import 'package:flutter/services.dart';

import '../models/course_manifest.dart';
import '../models/unit_content.dart';
import 'course_repository.dart';

class AssetCourseRepository implements CourseRepository {
  AssetCourseRepository({AssetBundle? bundle}) : _bundle = bundle ?? rootBundle;

  final AssetBundle _bundle;
  CourseManifest? _manifestCache;
  final Map<String, UnitContent> _unitCache = {};
  final Map<String, Map<String, dynamic>> _resourceCache = {};

  @override
  Future<CourseManifest> loadManifest() async {
    final cached = _manifestCache;
    if (cached != null) return cached;

    final json = await _loadJson('assets/content/course_manifest.json');
    final manifest = CourseManifest.fromJson(json);
    _manifestCache = manifest;
    return manifest;
  }

  @override
  Future<UnitContent> loadUnit(UnitSummary unit) async {
    final cached = _unitCache[unit.id];
    if (cached != null) return cached;

    final json = await _loadJson(unit.asset);
    final content = UnitContent.fromJson(json);
    _unitCache[unit.id] = content;
    return content;
  }

  @override
  Future<Map<String, dynamic>> loadResource(String assetPath) async {
    final cached = _resourceCache[assetPath];
    if (cached != null) return cached;

    final value = await _loadJson(assetPath);
    _resourceCache[assetPath] = value;
    return value;
  }

  Future<Map<String, dynamic>> _loadJson(String assetPath) async {
    final raw = await _bundle.loadString(assetPath);
    return Map<String, dynamic>.from(jsonDecode(raw) as Map);
  }
}
