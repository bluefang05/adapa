import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';

import '../../../core/services/ad_service.dart';

/// Reusable Google AdMob Banner Widget with Anchored Adaptive Banner support.
///
/// Features:
/// - Automatically computes Anchored Adaptive Banner size matching the exact
///   screen width to span 100% of the device width without blank margins.
/// - Automatically collapses when no ad is loaded to prevent empty layout voids.
/// - Adapts dynamically if screen orientation changes.
/// - Uses test ad units in Debug mode and production ad units in Release mode.
/// - Correctly disposes the native BannerAd instance on widget removal.
class AdmobBannerWidget extends StatefulWidget {
  const AdmobBannerWidget({
    super.key,
    this.adSize,
    this.margin = EdgeInsets.zero,
  });

  /// Optional specific AdSize override. When null (default), an Anchored
  /// Adaptive Banner spanning the full device width will be computed.
  final AdSize? adSize;
  final EdgeInsetsGeometry margin;

  @override
  State<AdmobBannerWidget> createState() => _AdmobBannerWidgetState();
}

class _AdmobBannerWidgetState extends State<AdmobBannerWidget> {
  BannerAd? _bannerAd;
  bool _isLoaded = false;
  bool _isLoading = false;
  Orientation? _lastOrientation;
  double? _lastWidth;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final orientation = MediaQuery.of(context).orientation;
    final width = MediaQuery.sizeOf(context).width;
    if (_lastOrientation != orientation || _lastWidth != width) {
      _lastOrientation = orientation;
      _lastWidth = width;
      _loadBanner();
    }
  }

  Future<void> _loadBanner() async {
    if (kIsWeb ||
        _isLoading ||
        WidgetsBinding.instance.runtimeType.toString().contains('Test')) {
      return;
    }
    _isLoading = true;

    // Clean up any previously loaded ad instance before re-requesting
    await _bannerAd?.dispose();
    _bannerAd = null;
    if (mounted) {
      setState(() {
        _isLoaded = false;
      });
    }

    AdSize targetSize = widget.adSize ?? AdSize.banner;
    if (widget.adSize == null && mounted) {
      final width = MediaQuery.sizeOf(context).width.truncate();
      if (width > 0) {
        try {
          final adaptiveSize =
              await AdSize.getCurrentOrientationAnchoredAdaptiveBannerAdSize(
                width,
              );
          if (adaptiveSize != null) {
            targetSize = adaptiveSize;
          }
        } catch (_) {
          // Fallback to standard 320x50 banner if platform channel is unavailable (e.g., in unit tests)
          targetSize = AdSize.banner;
        }
      }
    }

    if (!mounted) {
      _isLoading = false;
      return;
    }

    _bannerAd = BannerAd(
      adUnitId: AdService.instance.bannerAdUnitId,
      size: targetSize,
      request: const AdRequest(),
      listener: BannerAdListener(
        onAdLoaded: (ad) {
          if (!mounted) {
            ad.dispose();
            return;
          }
          setState(() {
            _isLoaded = true;
            _isLoading = false;
          });
        },
        onAdFailedToLoad: (ad, error) {
          debugPrint(
            '[AdmobBannerWidget] Failed to load ad: ${error.message} (code: ${error.code})',
          );
          ad.dispose();
          if (mounted) {
            setState(() {
              _bannerAd = null;
              _isLoaded = false;
              _isLoading = false;
            });
          }
        },
      ),
    );

    await _bannerAd!.load();
  }

  @override
  void dispose() {
    _bannerAd?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (!_isLoaded || _bannerAd == null) {
      return const SizedBox.shrink();
    }

    return Container(
      margin: widget.margin,
      alignment: Alignment.center,
      width: double.infinity,
      height: _bannerAd!.size.height.toDouble(),
      child: AdWidget(ad: _bannerAd!),
    );
  }
}
