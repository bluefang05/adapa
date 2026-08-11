import 'dart:async';

typedef SessionWriteListener = Future<void> Function(
  String activityId,
  Map<String, dynamic> value,
);

class ActivitySessionStore {
  ActivitySessionStore({this.onWrite});

  final SessionWriteListener? onWrite;
  final Map<String, Map<String, dynamic>> _values = {};

  Map<String, dynamic>? read(String activityId) {
    final value = _values[activityId];
    return value == null ? null : Map<String, dynamic>.from(value);
  }

  void restore(Map<String, Map<String, dynamic>> values) {
    _values
      ..clear()
      ..addAll({
        for (final entry in values.entries)
          entry.key: Map<String, dynamic>.from(entry.value),
      });
  }

  void write(String activityId, Map<String, dynamic> value) {
    _values[activityId] = Map<String, dynamic>.from(value);
    final listener = onWrite;
    if (listener != null) {
      // UI writes should never wait on disk I/O. ProgressController serializes saves.
      unawaited(listener(activityId, Map<String, dynamic>.from(value)));
    }
  }

  void remove(String activityId) => _values.remove(activityId);

  void clear() => _values.clear();
}
