import 'dart:convert';

import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  test('all production stroke and vocabulary assets are bundled', () async {
    final strokeCatalog = jsonDecode(
      await rootBundle.loadString(
        'assets/content/resources/unit_01_stroke_asset_catalog_v1.0.json',
      ),
    ) as Map<String, dynamic>;

    final paths = <String>{};
    final sets = Map<String, dynamic>.from(strokeCatalog['sets'] as Map);
    for (final raw in sets.values) {
      final set = Map<String, dynamic>.from(raw as Map);
      for (final step in set['steps'] as List? ?? const []) {
        paths.add(step.toString());
      }
      if (set['final'] != null) paths.add(set['final'].toString());
    }

    final visualCatalog = jsonDecode(
      await rootBundle.loadString(
        'assets/content/resources/unit_04_visual_asset_catalog_v1.0.json',
      ),
    ) as Map<String, dynamic>;
    for (final raw in visualCatalog['assets'] as List? ?? const []) {
      final item = Map<String, dynamic>.from(raw as Map);
      paths.add(item['asset'].toString());
    }

    expect(paths.length, 131);

    for (final path in paths) {
      final data = await rootBundle.load(path);
      expect(
        data.lengthInBytes,
        greaterThan(0),
        reason: 'Asset missing or empty: $path',
      );
    }
  });
}
