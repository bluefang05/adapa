import 'package:adapa/core/theme/adapa_theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('ADAPA themes mount', (WidgetTester tester) async {
    for (final theme in [
      AdapaTheme.light(),
      AdapaTheme.balanced(),
      AdapaTheme.dark(),
    ]) {
      await tester.pumpWidget(
        MaterialApp(
          theme: theme,
          home: const Scaffold(body: Text('ADAPA')),
        ),
      );

      expect(find.text('ADAPA'), findsOneWidget);
    }
  });
}
