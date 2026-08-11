import 'package:unorm_dart/unorm_dart.dart' as unorm;

class AnswerNormalizer {
  const AnswerNormalizer._();

  static String normalize(String input, Map<String, dynamic> rules) {
    var value = input;

    if ((rules['unicode_normalization'] ?? 'NFC').toString().toUpperCase() == 'NFC') {
      value = unorm.nfc(value);
    }

    if (rules['trim'] == true) {
      value = value.trim();
    }

    if (rules['collapse_spaces'] == true) {
      value = value.replaceAll(RegExp(r'\s+'), ' ');
    }

    if (rules['ignore_terminal_punctuation'] == true) {
      value = value.replaceFirst(RegExp(r'[\.!?。！？]+\s*$'), '');
      value = value.trimRight();
    }

    if (rules['case_sensitive'] == false) {
      value = value.toLowerCase();
    }

    return value;
  }

  static bool equals(
    String left,
    String right,
    Map<String, dynamic> rules,
  ) =>
      normalize(left, rules) == normalize(right, rules);
}
