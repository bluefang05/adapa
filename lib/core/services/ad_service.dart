import 'package:flutter/foundation.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';

/// Centralized manager for Google Mobile Ads (AdMob) SDK and Ad Unit IDs.
class AdService {
  AdService._();
  static final AdService instance = AdService._();

  /// Official AdMob Application ID
  static const String appId = 'ca-app-pub-3322493998376707~8557862424';

  /// Production Banner Ad Unit ID configured by user
  static const String productionBannerUnitId =
      'ca-app-pub-3322493998376707/1126094968';

  /// Standard Google Sample Test Banner Ad Unit ID for Android
  /// Reference: https://developers.google.com/admob/android/test-ads
  static const String testBannerUnitId =
      'ca-app-pub-3940256099942544/6300978111';

  bool _initialized = false;
  bool get isInitialized => _initialized;

  /// Returns the appropriate Banner Ad Unit ID.
  /// Uses test ads in Debug/Profile modes to protect AdMob account from invalid traffic penalties.
  /// Uses production ads in Release mode.
  String get bannerAdUnitId {
    if (kReleaseMode) {
      return productionBannerUnitId;
    }
    return testBannerUnitId;
  }

  /// Initialize Google Mobile Ads SDK safely.
  Future<InitializationStatus?> initialize() async {
    if (_initialized) return null;
    try {
      final status = await MobileAds.instance.initialize();
      _initialized = true;
      return status;
    } catch (e) {
      debugPrint('[AdService] MobileAds initialization error: $e');
      return null;
    }
  }
}
