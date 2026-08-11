/// Small deterministic Hangul composer used by the syllable-building activity.
class HangulComposer {
  const HangulComposer._();

  static const List<String> _initials = <String>[
    'ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅃ', 'ㅅ',
    'ㅆ', 'ㅇ', 'ㅈ', 'ㅉ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ',
  ];

  static const List<String> _medials = <String>[
    'ㅏ', 'ㅐ', 'ㅑ', 'ㅒ', 'ㅓ', 'ㅔ', 'ㅕ', 'ㅖ', 'ㅗ', 'ㅘ',
    'ㅙ', 'ㅚ', 'ㅛ', 'ㅜ', 'ㅝ', 'ㅞ', 'ㅟ', 'ㅠ', 'ㅡ', 'ㅢ', 'ㅣ',
  ];

  static const List<String> _finals = <String>[
    '', 'ㄱ', 'ㄲ', 'ㄳ', 'ㄴ', 'ㄵ', 'ㄶ', 'ㄷ', 'ㄹ', 'ㄺ',
    'ㄻ', 'ㄼ', 'ㄽ', 'ㄾ', 'ㄿ', 'ㅀ', 'ㅁ', 'ㅂ', 'ㅄ', 'ㅅ',
    'ㅆ', 'ㅇ', 'ㅈ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ',
  ];

  static String? compose(List<String> parts) {
    if (parts.length != 2 && parts.length != 3) return null;

    final initialIndex = _initials.indexOf(parts[0]);
    final medialIndex = _medials.indexOf(parts[1]);
    final finalIndex = parts.length == 3 ? _finals.indexOf(parts[2]) : 0;

    if (initialIndex < 0 || medialIndex < 0 || finalIndex < 0) return null;

    const syllableBase = 0xAC00;
    const medialCount = 21;
    const finalCount = 28;
    final codePoint = syllableBase +
        ((initialIndex * medialCount) + medialIndex) * finalCount +
        finalIndex;
    return String.fromCharCode(codePoint);
  }
}
