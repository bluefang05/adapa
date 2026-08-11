import 'package:flutter/material.dart';

class AdapaTheme {
  static const navy = Color(0xFF163B65);
  static const cyan = Color(0xFF2EB8C6);
  static const magenta = Color(0xFFE94F93);
  static const ink = Color(0xFF172033);
  static const canvas = Color(0xFFF6F8FC);
  static const balancedCanvas = Color(0xFFF1E2C8);
  static const balancedSurface = Color(0xFFFFF7E8);
  static const darkCanvas = Color(0xFF101824);
  static const darkSurface = Color(0xFF182333);

  static ThemeData light() {
    final scheme = ColorScheme.fromSeed(
      seedColor: navy,
      brightness: Brightness.light,
      primary: navy,
      secondary: cyan,
      tertiary: magenta,
      surface: Colors.white,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: canvas,
      appBarTheme: const AppBarTheme(
        centerTitle: false,
        elevation: 0,
        scrolledUnderElevation: 1,
        backgroundColor: canvas,
        foregroundColor: ink,
      ),
      cardTheme: CardThemeData(
        elevation: 0,
        margin: EdgeInsets.zero,
        color: Colors.white,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: const BorderSide(color: Color(0xFFE7ECF3)),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(48, 52),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          textStyle: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size(48, 50),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFFDDE4EE)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFFDDE4EE)),
        ),
      ),
      progressIndicatorTheme: const ProgressIndicatorThemeData(
        linearMinHeight: 8,
        color: cyan,
        linearTrackColor: Color(0xFFE5EAF2),
      ),
      chipTheme: ChipThemeData(
        side: BorderSide.none,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
      dividerTheme: const DividerThemeData(color: Color(0xFFE7ECF3)),
    );
  }

  static ThemeData balanced() {
    final scheme = ColorScheme.fromSeed(
      seedColor: const Color(0xFF9A6A2F),
      brightness: Brightness.light,
      primary: const Color(0xFF70512A),
      secondary: const Color(0xFF0F7E88),
      tertiary: const Color(0xFFB64F6F),
      surface: balancedSurface,
    );

    return _base(
      scheme: scheme,
      scaffoldBackgroundColor: balancedCanvas,
      appBarBackgroundColor: balancedCanvas,
      appBarForegroundColor: const Color(0xFF3D2D1A),
      cardColor: balancedSurface,
      cardBorderColor: const Color(0xFFD2B98E),
      inputFillColor: const Color(0xFFFFFBF2),
      inputBorderColor: const Color(0xFFC8AA76),
      progressTrackColor: const Color(0xFFE3CDA6),
      dividerColor: const Color(0xFFD2B98E),
    );
  }

  static ThemeData dark() {
    final scheme = ColorScheme.fromSeed(
      seedColor: cyan,
      brightness: Brightness.dark,
      primary: const Color(0xFF8EC8FF),
      secondary: const Color(0xFF55D6E1),
      tertiary: const Color(0xFFFF8ABB),
      surface: darkSurface,
    );

    return _base(
      scheme: scheme,
      scaffoldBackgroundColor: darkCanvas,
      appBarBackgroundColor: darkCanvas,
      appBarForegroundColor: const Color(0xFFE9F2FB),
      cardColor: darkSurface,
      cardBorderColor: const Color(0xFF27374B),
      inputFillColor: const Color(0xFF121D2A),
      inputBorderColor: const Color(0xFF31445C),
      progressTrackColor: const Color(0xFF2B3A4E),
      dividerColor: const Color(0xFF27374B),
    );
  }

  static ThemeData _base({
    required ColorScheme scheme,
    required Color scaffoldBackgroundColor,
    required Color appBarBackgroundColor,
    required Color appBarForegroundColor,
    required Color cardColor,
    required Color cardBorderColor,
    required Color inputFillColor,
    required Color inputBorderColor,
    required Color progressTrackColor,
    required Color dividerColor,
  }) {
    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: scaffoldBackgroundColor,
      appBarTheme: AppBarTheme(
        centerTitle: false,
        elevation: 0,
        scrolledUnderElevation: 1,
        backgroundColor: appBarBackgroundColor,
        foregroundColor: appBarForegroundColor,
      ),
      cardTheme: CardThemeData(
        elevation: 0,
        margin: EdgeInsets.zero,
        color: cardColor,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: BorderSide(color: cardBorderColor),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(48, 52),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          textStyle: const TextStyle(fontWeight: FontWeight.w700),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          minimumSize: const Size(48, 50),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: inputFillColor,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: inputBorderColor),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: inputBorderColor),
        ),
      ),
      progressIndicatorTheme: ProgressIndicatorThemeData(
        linearMinHeight: 8,
        color: scheme.secondary,
        linearTrackColor: progressTrackColor,
      ),
      chipTheme: ChipThemeData(
        side: BorderSide.none,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
      dividerTheme: DividerThemeData(color: dividerColor),
    );
  }
}
