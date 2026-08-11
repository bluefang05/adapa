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
    return await _tts.isLanguageAvailable(locale) == true;
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
    if (text.trim().isEmpty) return;
    await initialize();
    await _tts.stop();
    final available = await isLanguageAvailable(locale);
    if (!available) {
      throw StateError('El motor TTS no tiene disponible el idioma $locale.');
    }
    await _tts.setLanguage(locale);
    await _tts.setSpeechRate(rate.clamp(0.1, 1.0).toDouble());
    await _tts.speak(text);
  }

  @override
  Future<void> stop() async {
    await _tts.stop();
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
  Future<void> speak(String text, {String locale = 'ko-KR', double rate = 0.42}) async {}

  @override
  Future<void> stop() async {}
}
