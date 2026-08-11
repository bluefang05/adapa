import 'package:flutter/foundation.dart';

import '../persistence/key_value_store.dart';

enum AppColorMode {
  light('light'),
  balanced('balanced'),
  dark('dark');

  const AppColorMode(this.storageValue);

  final String storageValue;

  static AppColorMode fromStorage(String? value) {
    return AppColorMode.values.firstWhere(
      (mode) => mode.storageValue == value,
      orElse: () => AppColorMode.light,
    );
  }
}

class AppSettingsController extends ChangeNotifier {
  AppSettingsController({KeyValueStore? preferences})
    : _preferences = preferences ?? SharedPreferencesAsyncStore();

  static const _colorModeKey = 'adapa_color_mode';
  static const _normalRateKey = 'adapa_tts_normal_rate';
  static const _slowRateKey = 'adapa_tts_slow_rate';

  final KeyValueStore _preferences;

  AppColorMode _colorMode = AppColorMode.light;
  double _normalTtsRate = 0.42;
  double _slowTtsRate = 0.30;

  AppColorMode get colorMode => _colorMode;
  double get normalTtsRate => _normalTtsRate;
  double get slowTtsRate => _slowTtsRate;

  Future<void> initialize() async {
    _colorMode = AppColorMode.fromStorage(
      await _preferences.getString(_colorModeKey),
    );
    _normalTtsRate = (await _preferences.getDouble(_normalRateKey) ?? 0.42)
        .clamp(0.20, 0.70)
        .toDouble();
    _slowTtsRate = (await _preferences.getDouble(_slowRateKey) ?? 0.30)
        .clamp(0.15, 0.55)
        .toDouble();

    if (_slowTtsRate > _normalTtsRate) {
      _slowTtsRate = _normalTtsRate;
    }
    notifyListeners();
  }

  Future<void> setColorMode(AppColorMode value) async {
    if (_colorMode == value) return;
    _colorMode = value;
    notifyListeners();
    await _preferences.setString(_colorModeKey, value.storageValue);
  }

  Future<void> setNormalTtsRate(double value) async {
    _normalTtsRate = value.clamp(0.20, 0.70).toDouble();
    if (_slowTtsRate > _normalTtsRate) {
      _slowTtsRate = _normalTtsRate;
    }

    notifyListeners();
    await _preferences.setDouble(_normalRateKey, _normalTtsRate);
    await _preferences.setDouble(_slowRateKey, _slowTtsRate);
  }

  Future<void> setSlowTtsRate(double value) async {
    _slowTtsRate = value.clamp(0.15, _normalTtsRate).toDouble();
    notifyListeners();
    await _preferences.setDouble(_slowRateKey, _slowTtsRate);
  }

  Future<void> restoreTtsDefaults() async {
    _normalTtsRate = 0.42;
    _slowTtsRate = 0.30;
    notifyListeners();
    await _preferences.setDouble(_normalRateKey, _normalTtsRate);
    await _preferences.setDouble(_slowRateKey, _slowTtsRate);
  }
}
