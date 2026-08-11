import '../dialogue/dialogue_scene.dart';
import '../models/activity_content.dart';
import '../models/content_block.dart';
import 'course_repository.dart';

class AssetResolver {
  AssetResolver(this.repository);

  final CourseRepository repository;
  Map<String, dynamic>? _strokeSets;
  Map<String, dynamic>? _visualById;
  Map<String, dynamic>? _readingById;
  Map<String, dynamic>? _dialogueById;
  Map<String, ActivityContent>? _activityById;
  Map<String, ContentBlock>? _contentBlockById;

  Future<Map<String, dynamic>?> strokeSet(String setId) async {
    _strokeSets ??= await _loadStrokeSets();
    final item = _strokeSets![setId];
    if (item is! Map) return null;
    return Map<String, dynamic>.from(item);
  }

  Future<List<String>> strokeSteps(String setId) async {
    final item = await strokeSet(setId);
    if (item == null) return const [];
    return (item['steps'] as List? ?? const []).map((e) => e.toString()).toList(growable: false);
  }

  Future<String?> strokeFinal(String setId) async {
    final item = await strokeSet(setId);
    return item?['final']?.toString();
  }

  Future<String?> visualAsset(String id) async {
    _visualById ??= await _loadVisuals();
    final item = _visualById![id];
    return item is Map ? item['asset']?.toString() : null;
  }

  Future<Map<String, dynamic>?> reading(String id) async {
    _readingById ??= await _loadReadings();
    final item = _readingById![id];
    return item is Map ? Map<String, dynamic>.from(item) : null;
  }

  Future<String?> readingText(String id) async {
    final item = await reading(id);
    return item?['text_ko']?.toString();
  }

  Future<ActivityContent?> activityById(String id) async {
    _activityById ??= await _loadActivities();
    return _activityById![id];
  }

  Future<List<ActivityContent>> activitiesByIds(List<String> ids) async {
    _activityById ??= await _loadActivities();
    return [for (final id in ids) if (_activityById![id] != null) _activityById![id]!];
  }

  Future<ContentBlock?> contentBlock(String id) async {
    _contentBlockById ??= await _loadContentBlocks();
    return _contentBlockById![id];
  }

  Future<DialogueScene?> dialogueScene(String id) async {
    _dialogueById ??= await _loadDialogues();
    final item = _dialogueById![id];
    if (item is! Map) return null;
    return DialogueScene.fromJson(Map<String, dynamic>.from(item));
  }


  Future<Map<String, ActivityContent>> _loadActivities() async {
    final manifest = await repository.loadManifest();
    final result = <String, ActivityContent>{};
    for (final summary in manifest.units) {
      final unit = await repository.loadUnit(summary);
      for (final lesson in unit.lessons) {
        for (final activity in lesson.activities) {
          result[activity.id] = activity;
        }
      }
    }
    return result;
  }

  Future<Map<String, ContentBlock>> _loadContentBlocks() async {
    final manifest = await repository.loadManifest();
    final result = <String, ContentBlock>{};
    for (final summary in manifest.units) {
      final unit = await repository.loadUnit(summary);
      for (final lesson in unit.lessons) {
        for (final block in lesson.theory) {
          result[block.id] = block;
        }
      }
    }
    return result;
  }

  Future<Map<String, dynamic>> _loadStrokeSets() async {
    final json = await repository.loadResource(
      'assets/content/resources/unit_01_stroke_asset_catalog_v1.0.json',
    );
    return Map<String, dynamic>.from(json['sets'] as Map? ?? const {});
  }

  Future<Map<String, dynamic>> _loadReadings() async {
    final json = await repository.loadResource(
      'assets/content/resources/unit_07_reading_catalog_v1.0.json',
    );
    final result = <String, dynamic>{};
    for (final raw in json['readings'] as List? ?? const []) {
      final item = Map<String, dynamic>.from(raw as Map);
      result[item['id'].toString()] = item;
    }
    return result;
  }


  Future<Map<String, dynamic>> _loadDialogues() async {
    final json = await repository.loadResource(
      'assets/content/resources/unit_06_dialogue_catalog_v1.0.json',
    );
    final result = <String, dynamic>{};
    for (final raw in json['scenes'] as List? ?? const []) {
      final item = Map<String, dynamic>.from(raw as Map);
      result[item['id'].toString()] = item;
    }
    return result;
  }

  Future<Map<String, dynamic>> _loadVisuals() async {
    final json = await repository.loadResource(
      'assets/content/resources/unit_04_visual_asset_catalog_v1.0.json',
    );
    final result = <String, dynamic>{};
    for (final raw in json['assets'] as List? ?? const []) {
      final item = Map<String, dynamic>.from(raw as Map);
      result[item['id'].toString()] = item;
    }
    return result;
  }
}
