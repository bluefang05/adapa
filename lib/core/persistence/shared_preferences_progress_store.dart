import 'dart:convert';

import '../progress/progress_snapshot.dart';
import 'key_value_store.dart';
import 'progress_store.dart';

class SharedPreferencesProgressStore implements ProgressStore {
  SharedPreferencesProgressStore({KeyValueStore? preferences})
      : _preferences = preferences ?? SharedPreferencesAsyncStore();

  static const _prefix = 'adapa.progress.v1.';

  final KeyValueStore _preferences;

  String _key(String courseId) => '$_prefix$courseId';

  @override
  Future<ProgressSnapshot?> load(String courseId) async {
    final raw = await _preferences.getString(_key(courseId));
    if (raw == null || raw.trim().isEmpty) return null;

    try {
      return ProgressSnapshot.fromJson(
        Map<String, dynamic>.from(jsonDecode(raw) as Map),
      );
    } catch (_) {
      // Corrupt progress must never prevent the course from opening.
      return null;
    }
  }

  @override
  Future<void> save(ProgressSnapshot snapshot) async {
    await _preferences.setString(
      _key(snapshot.courseId),
      jsonEncode(snapshot.toJson()),
    );
  }

  @override
  Future<void> clear(String courseId) async {
    await _preferences.remove(_key(courseId));
  }
}
