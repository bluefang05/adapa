import '../models/course_manifest.dart';
import '../models/unit_content.dart';

abstract interface class CourseRepository {
  Future<CourseManifest> loadManifest();
  Future<UnitContent> loadUnit(UnitSummary unit);
  Future<Map<String, dynamic>> loadResource(String assetPath);
}
