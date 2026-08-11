import '../progress/progress_snapshot.dart';

abstract class ProgressStore {
  Future<ProgressSnapshot?> load(String courseId);
  Future<void> save(ProgressSnapshot snapshot);
  Future<void> clear(String courseId);
}
