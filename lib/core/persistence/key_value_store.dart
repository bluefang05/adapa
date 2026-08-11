import 'package:shared_preferences/shared_preferences.dart';

abstract interface class KeyValueStore {
  Future<String?> getString(String key);

  Future<double?> getDouble(String key);

  Future<void> setString(String key, String value);

  Future<void> setDouble(String key, double value);

  Future<void> remove(String key);

  Future<void> clear({Set<String>? allowList});
}

/// Production implementation.
///
/// `SharedPreferencesAsync` avoids the process-local cache used by the legacy
/// `SharedPreferences` API. On Android, the plugin defaults to DataStore
/// Preferences for the modern async API.
class SharedPreferencesAsyncStore implements KeyValueStore {
  SharedPreferencesAsyncStore({SharedPreferencesAsync? preferences})
      : _preferences = preferences ?? SharedPreferencesAsync();

  final SharedPreferencesAsync _preferences;

  @override
  Future<String?> getString(String key) => _preferences.getString(key);

  @override
  Future<double?> getDouble(String key) => _preferences.getDouble(key);

  @override
  Future<void> setString(String key, String value) =>
      _preferences.setString(key, value);

  @override
  Future<void> setDouble(String key, double value) =>
      _preferences.setDouble(key, value);

  @override
  Future<void> remove(String key) => _preferences.remove(key);

  @override
  Future<void> clear({Set<String>? allowList}) =>
      _preferences.clear(allowList: allowList);
}

/// Small deterministic store for unit/widget tests.
class MemoryKeyValueStore implements KeyValueStore {
  MemoryKeyValueStore([Map<String, Object?>? initial])
      : _values = Map<String, Object?>.from(initial ?? const {});

  final Map<String, Object?> _values;

  Map<String, Object?> get snapshot => Map.unmodifiable(_values);

  @override
  Future<String?> getString(String key) async {
    final value = _values[key];
    return value is String ? value : null;
  }

  @override
  Future<double?> getDouble(String key) async {
    final value = _values[key];
    return value is num ? value.toDouble() : null;
  }

  @override
  Future<void> setString(String key, String value) async {
    _values[key] = value;
  }

  @override
  Future<void> setDouble(String key, double value) async {
    _values[key] = value;
  }

  @override
  Future<void> remove(String key) async {
    _values.remove(key);
  }

  @override
  Future<void> clear({Set<String>? allowList}) async {
    if (allowList == null) {
      _values.clear();
      return;
    }
    for (final key in allowList) {
      _values.remove(key);
    }
  }
}
