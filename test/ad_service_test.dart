import 'package:adapa/core/services/ad_service.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  group('AdService Configuration', () {
    test('contains exact AdMob App ID and Banner Ad Unit IDs', () {
      expect(AdService.appId, 'ca-app-pub-3322493998376707~8557862424');
      expect(
        AdService.productionBannerUnitId,
        'ca-app-pub-3322493998376707/1126094968',
      );
      expect(
        AdService.testBannerUnitId,
        'ca-app-pub-3940256099942544/6300978111',
      );
    });

    test('uses test banner unit ID during testing/debug to comply with AdMob policy', () {
      // In test / debug mode, bannerAdUnitId must be the official Google sample ad unit
      expect(AdService.instance.bannerAdUnitId, AdService.testBannerUnitId);
    });
  });
}
