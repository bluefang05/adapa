import 'package:adapa/core/persistence/key_value_store.dart';
import 'package:adapa/core/settings/app_settings_controller.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('color mode persists and invalid values fall back to light', () async {
    final preferences = MemoryKeyValueStore();

    final settings = AppSettingsController(preferences: preferences);
    await settings.initialize();
    expect(settings.colorMode, AppColorMode.light);

    await settings.setColorMode(AppColorMode.dark);

    final restored = AppSettingsController(preferences: preferences);
    await restored.initialize();
    expect(restored.colorMode, AppColorMode.dark);

    final invalid = AppSettingsController(
      preferences: MemoryKeyValueStore({'adapa_color_mode': 'sepia'}),
    );
    await invalid.initialize();
    expect(invalid.colorMode, AppColorMode.light);
  });

  test('TTS rates persist and slow rate never exceeds normal rate', () async {
    final preferences = MemoryKeyValueStore();

    final settings = AppSettingsController(preferences: preferences);
    await settings.initialize();

    await settings.setNormalTtsRate(0.50);
    await settings.setSlowTtsRate(0.35);

    final restored = AppSettingsController(preferences: preferences);
    await restored.initialize();

    expect(restored.normalTtsRate, 0.50);
    expect(restored.slowTtsRate, 0.35);

    await restored.setNormalTtsRate(0.25);
    expect(restored.slowTtsRate, lessThanOrEqualTo(restored.normalTtsRate));
  });

  test('out-of-range persisted TTS values are clamped at startup', () async {
    final preferences = MemoryKeyValueStore({
      'adapa_tts_normal_rate': 2.0,
      'adapa_tts_slow_rate': -1.0,
    });

    final settings = AppSettingsController(preferences: preferences);
    await settings.initialize();

    expect(settings.normalTtsRate, 0.70);
    expect(settings.slowTtsRate, 0.15);
  });
}
