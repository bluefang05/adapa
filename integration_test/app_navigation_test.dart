import 'package:adapa/app.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:integration_test/integration_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  IntegrationTestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('real-device study path opens first activity and settings', (
    tester,
  ) async {
    final prefs = SharedPreferencesAsync();
    await prefs.clear();

    await tester.pumpWidget(const AdapaApp());
    await tester.pumpAndSettle();

    expect(find.text('ADAPA'), findsOneWidget);

    final unitFinder = find.text('Descubre el Hangul');
    await tester.scrollUntilVisible(unitFinder, 240);
    await tester.tap(unitFinder);
    await tester.pumpAndSettle();

    await tester.tap(find.text('Vocales básicas'));
    await tester.pumpAndSettle();

    final activityFinder = find.text(
      '¿Cuál de estas vocales corresponde a «a»?',
    );
    await tester.scrollUntilVisible(activityFinder, 300);
    await tester.tap(activityFinder);
    await tester.pumpAndSettle();

    expect(find.text('Actividad 1 de 5'), findsOneWidget);

    // Return to home, then verify Settings opens on the real platform.
    await tester.pageBack();
    await tester.pageBack();
    await tester.pageBack();
    await tester.pumpAndSettle();

    await tester.tap(find.byTooltip('Ajustes'));
    await tester.pumpAndSettle();

    expect(find.text('Voz coreana'), findsOneWidget);
    expect(find.text('Comprobar ko-KR'), findsOneWidget);
  });
}
