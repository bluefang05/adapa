import 'package:flutter_tts/flutter_tts.dart';

abstract interface class TtsService {
  Future<void> initialize();

  Future<bool> isLanguageAvailable(String locale);

  Future<List<String>> availableLanguages();

  Future<void> speak(
    String text, {
    String locale = 'ko-KR',
    double rate = 0.42,
  });

  Future<void> stop();
}

class FlutterTtsService implements TtsService {
  FlutterTtsService({FlutterTts? engine}) : _tts = engine ?? FlutterTts();

  final FlutterTts _tts;
  bool _initialized = false;

  @override
  Future<void> initialize() async {
    if (_initialized) return;
    await _tts.awaitSpeakCompletion(true);
    await _tts.setVolume(1.0);
    await _tts.setPitch(1.0);
    _initialized = true;
  }

  @override
  Future<bool> isLanguageAvailable(String locale) async {
    await initialize();
    if (await _tts.isLanguageAvailable(locale) == true) return true;
    return await _resolveAvailableLanguage(locale) != null;
  }

  @override
  Future<List<String>> availableLanguages() async {
    await initialize();
    final raw = await _tts.getLanguages;
    if (raw is! List) return const [];
    return raw.map((e) => e.toString()).toList(growable: false)..sort();
  }

  @override
  Future<void> speak(
    String text, {
    String locale = 'ko-KR',
    double rate = 0.42,
  }) async {
    final processed = KoreanPhoneticNormalizer.preprocessForSpeech(text);
    if (processed.isEmpty) return;
    await initialize();
    await _tts.stop();
    final resolvedLocale = await _resolveAvailableLanguage(locale);
    if (resolvedLocale == null) {
      throw StateError('El motor TTS no tiene disponible el idioma $locale.');
    }
    await _tts.setLanguage(resolvedLocale);
    await _tts.setSpeechRate(rate.clamp(0.1, 1.0).toDouble());
    await _tts.speak(processed);
  }

  @override
  Future<void> stop() async {
    await _tts.stop();
  }

  Future<String?> _resolveAvailableLanguage(String locale) async {
    if (await _tts.isLanguageAvailable(locale) == true) return locale;

    final languages = await availableLanguages();
    for (final language in languages) {
      if (TtsLocaleMatcher.matches(language, locale)) return language;
    }
    return null;
  }
}

class TtsLocaleMatcher {
  const TtsLocaleMatcher._();

  static bool matches(String available, String requested) {
    final normalizedAvailable = _normalize(available);
    final normalizedRequested = _normalize(requested);
    if (normalizedAvailable == normalizedRequested) return true;

    final requestedLanguage = normalizedRequested.split('-').first;
    final availableLanguage = normalizedAvailable.split('-').first;
    return requestedLanguage.isNotEmpty &&
        requestedLanguage == availableLanguage &&
        requestedLanguage == 'ko';
  }

  static String _normalize(String locale) =>
      locale.trim().replaceAll('_', '-').toLowerCase();
}

class KoreanPhoneticNormalizer {
  const KoreanPhoneticNormalizer._();

  static const Map<String, String> _isolatedVowels = {
    'ㅏ': '아',
    'ㅑ': '야',
    'ㅓ': '어',
    'ㅕ': '여',
    'ㅗ': '오',
    'ㅛ': '요',
    'ㅜ': '우',
    'ㅠ': '유',
    'ㅡ': '으',
    'ㅣ': '이',
    'ㅐ': '애',
    'ㅒ': '얘',
    'ㅔ': '에',
    'ㅖ': '예',
    'ㅘ': '와',
    'ㅙ': '왜',
    'ㅚ': '외',
    'ㅝ': '워',
    'ㅞ': '웨',
    'ㅟ': '위',
    'ㅢ': '의',
  };

  static const Map<String, String> _isolatedConsonants = {
    'ㄱ': '기역',
    'ㄴ': '니은',
    'ㄷ': '디귿',
    'ㄹ': '리을',
    'ㅁ': '미음',
    'ㅂ': '비읍',
    'ㅅ': '시옷',
    'ㅇ': '이응',
    'ㅈ': '지읒',
    'ㅊ': '치읓',
    'ㅋ': '키읔',
    'ㅌ': '티읕',
    'ㅍ': '피읖',
    'ㅎ': '히읗',
    'ㄲ': '쌍기역',
    'ㄸ': '쌍디귿',
    'ㅃ': '쌍비읍',
    'ㅆ': '쌍시옷',
    'ㅉ': '쌍지읒',
  };

  static String preprocessForSpeech(String input) {
    var text = input.trim();
    if (text.isEmpty) return '';

    if (_isolatedVowels.containsKey(text)) {
      return _isolatedVowels[text]!;
    }

    if (_isolatedConsonants.containsKey(text)) {
      return _isolatedConsonants[text]!;
    }

    text = text.replaceAll(RegExp(r'[\*\_~\[\]\(\)<>\\/]+'), ' ').trim();
    return text;
  }
}

class NoopTtsService implements TtsService {
  @override
  Future<void> initialize() async {}

  @override
  Future<bool> isLanguageAvailable(String locale) async => true;

  @override
  Future<List<String>> availableLanguages() async => const ['ko-KR'];

  @override
  Future<void> speak(
    String text, {
    String locale = 'ko-KR',
    double rate = 0.42,
  }) async {}

  @override
  Future<void> stop() async {}
}
