import 'dart:io';
import 'dart:ui' as ui;

import 'package:adapa/core/content/course_repository.dart';
import 'package:adapa/core/models/activity_content.dart';
import 'package:adapa/core/models/activity_family.dart';
import 'package:adapa/core/models/course_manifest.dart';
import 'package:adapa/core/models/romanization_policy.dart';
import 'package:adapa/core/models/unit_content.dart';
import 'package:adapa/core/progress/progress_controller.dart';
import 'package:adapa/core/runtime/adapa_runtime.dart';
import 'package:adapa/core/services/tts_service.dart';
import 'package:adapa/core/theme/adapa_theme.dart';
import 'package:adapa/features/activity/activity_renderer_host.dart';
import 'package:adapa/features/course/course_home_screen.dart';
import 'package:adapa/features/lesson/lesson_screen.dart';
import 'package:adapa/features/unit/unit_screen.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';

import 'support/runtime_harness.dart';

class SynchronousCourseRepository implements CourseRepository {
  SynchronousCourseRepository(this.manifest, this.units);
  final CourseManifest manifest;
  final Map<String, UnitContent> units;

  @override
  Future<CourseManifest> loadManifest() => SynchronousFuture(manifest);

  @override
  Future<UnitContent> loadUnit(UnitSummary unit) =>
      SynchronousFuture(units[unit.id]!);

  @override
  Future<Map<String, dynamic>> loadResource(String assetPath) =>
      SynchronousFuture({});
}

Widget buildAdBar() {
  return Container(
    height: 48,
    width: double.infinity,
    color: const Color(0xFFF1F5F9),
    alignment: Alignment.center,
    child: Row(
      mainAxisAlignment: MainAxisAlignment.center,
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
          decoration: BoxDecoration(
            color: const Color(0xFF94A3B8),
            borderRadius: BorderRadius.circular(4),
          ),
          child: const Text(
            'AD',
            style: TextStyle(
              color: Colors.white,
              fontSize: 10,
              fontWeight: FontWeight.bold,
              letterSpacing: 0.8,
            ),
          ),
        ),
        const SizedBox(width: 8),
        const Text(
          'Espacio Publicitario de Google AdMob',
          style: TextStyle(
            color: Color(0xFF64748B),
            fontSize: 11,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    ),
  );
}

Future<void> loadFonts() async {
  final fontFile = File(r'C:\Windows\Fonts\malgun.ttf');
  if (fontFile.existsSync()) {
    final bytes = fontFile.readAsBytesSync();
    for (final fontName in [
      'Roboto',
      'Noto Sans KR',
      'sans-serif',
      'Segoe UI',
    ]) {
      final loader = FontLoader(fontName);
      loader.addFont(Future.value(ByteData.view(bytes.buffer)));
      await loader.load();
    }
  }

  final iconFile = File(
    r'C:\src\flutter\bin\cache\artifacts\material_fonts\MaterialIcons-Regular.otf',
  );
  if (iconFile.existsSync()) {
    final iconBytes = iconFile.readAsBytesSync();
    final iconLoader = FontLoader('MaterialIcons');
    iconLoader.addFont(Future.value(ByteData.view(iconBytes.buffer)));
    await iconLoader.load();
  }
}

Future<void> captureScreen(
  WidgetTester tester,
  Widget widget,
  String filename,
) async {
  tester.view.physicalSize = const Size(1080, 1920);
  tester.view.devicePixelRatio = 2.625;

  final diskRepo = DiskCourseRepository();
  final manifest = await diskRepo.loadManifest();
  final Map<String, UnitContent> units = {};
  for (final u in manifest.units) {
    units[u.id] = await diskRepo.loadUnit(u);
  }
  final syncRepo = SynchronousCourseRepository(manifest, units);

  final harness = await RuntimeHarness.create(loadCourse: false);
  final progress = ProgressController(
    repository: syncRepo,
    store: MemoryProgressStore(),
  );
  await progress.initialize();

  final key = GlobalKey();

  await tester.pumpWidget(
    AdapaRuntime(
      assetResolver: harness.resolver,
      tts: NoopTtsService(),
      sessionStore: harness.session,
      progress: progress,
      settings: harness.settings,
      child: MaterialApp(
        debugShowCheckedModeBanner: false,
        theme: AdapaTheme.light().copyWith(
          textTheme: AdapaTheme.light().textTheme.apply(fontFamily: 'Roboto'),
        ),
        home: RepaintBoundary(key: key, child: widget),
      ),
    ),
  );
  await tester.pump();
  await tester.pump(const Duration(milliseconds: 150));

  final boundary =
      key.currentContext!.findRenderObject() as RenderRepaintBoundary;
  final ui.Image image = await boundary.toImage(pixelRatio: 2.625);
  final ByteData? byteData = await image.toByteData(
    format: ui.ImageByteFormat.png,
  );
  final bytes = byteData!.buffer.asUint8List();
  File('release/play_store/screenshots/$filename').writeAsBytesSync(bytes);
  debugPrint('CAPTURED: $filename (${bytes.length} bytes)');
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUpAll(() async {
    await loadFonts();
    final outDir = Directory('release/play_store/screenshots');
    if (!outDir.existsSync()) {
      outDir.createSync(recursive: true);
    }
  });

  testWidgets('Screenshot 1: Ruta Principal', (tester) async {
    final diskRepo = DiskCourseRepository();
    final manifest = await diskRepo.loadManifest();
    final Map<String, UnitContent> units = {};
    for (final u in manifest.units) {
      units[u.id] = await diskRepo.loadUnit(u);
    }
    final syncRepo = SynchronousCourseRepository(manifest, units);

    await captureScreen(
      tester,
      CourseHomeScreen(repository: syncRepo),
      'screenshot_1_ruta_principal.png',
    );
  });

  testWidgets('Screenshot 2: Descubre el Hangul', (tester) async {
    final diskRepo = DiskCourseRepository();
    final manifest = await diskRepo.loadManifest();
    final unit1 = await diskRepo.loadUnit(manifest.units[0]);
    final syncRepo = SynchronousCourseRepository(manifest, {unit1.id: unit1});

    await captureScreen(
      tester,
      Scaffold(
        body: UnitScreen(summary: manifest.units[0], repository: syncRepo),
        bottomNavigationBar: SafeArea(child: buildAdBar()),
      ),
      'screenshot_2_lecciones_hangul.png',
    );
  });

  testWidgets('Screenshot 3: Leccion y Trazos', (tester) async {
    final diskRepo = DiskCourseRepository();
    final manifest = await diskRepo.loadManifest();
    final unit1 = await diskRepo.loadUnit(manifest.units[0]);

    await captureScreen(
      tester,
      Scaffold(
        body: LessonScreen(
          lesson: unit1.lessons[0],
          romanizationPolicy: const RomanizationPolicy(
            mode: RomanizationMode.visible,
            canReveal: true,
          ),
        ),
        bottomNavigationBar: SafeArea(child: buildAdBar()),
      ),
      'screenshot_3_leccion_alfabeto.png',
    );
  });

  testWidgets('Screenshot 4: Actividad Interactiva', (tester) async {
    final act = ActivityContent(
      id: 'hangul_syllable_showcase',
      type: 'multiple_choice',
      family: ActivityFamily.choice,
      scoreMode: 'auto',
      prompt: '¿Cómo se forma la sílaba «GA» combinando consonante y vocal?',
      normalization: const {},
      hints: const ['Combina la consonante inicial ㄱ con la vocal ㅏ.'],
      feedback: const {
        'correct': '¡Correcto! ㄱ (g/k) + ㅏ (a) = 가 (ga).',
        'wrong': {'default': 'Recuerda el orden: consonante inicial + vocal.'},
      },
      capabilities: const {'hasTts': false},
      payload: const {
        'options': [
          {'ko': '가 (ga)', 'es': 'ㄱ (g/k) + ㅏ (a)'},
          {'ko': '나 (na)', 'es': 'ㄴ (n) + ㅏ (a)'},
          {'ko': '다 (da)', 'es': 'ㄷ (d) + ㅏ (a)'},
          {'ko': '라 (ra)', 'es': 'ㄹ (r/l) + ㅏ (a)'},
        ],
        'correct': ['가 (ga)'],
        'selection_mode': 'single',
      },
    );

    await captureScreen(
      tester,
      Scaffold(
        appBar: AppBar(
          title: const Text('Construcción de Sílabas'),
          elevation: 0,
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: ActivityRendererHost(activity: act),
        ),
        bottomNavigationBar: SafeArea(child: buildAdBar()),
      ),
      'screenshot_4_actividad_interactiva.png',
    );
  });

  testWidgets('Screenshot 5: Vocabulario Cotidiano', (tester) async {
    final act = ActivityContent(
      id: 'vocab_everyday_showcase',
      type: 'multiple_choice',
      family: ActivityFamily.choice,
      scoreMode: 'auto',
      prompt: 'Selecciona la palabra en coreano para «Manzana»:',
      normalization: const {},
      hints: const ['Pronunciación: sagwa.'],
      feedback: const {
        'correct': '¡Correcto! 사과 (sagwa) significa manzana.',
        'wrong': {'default': 'Inténtalo de nuevo.'},
      },
      capabilities: const {'hasTts': false},
      payload: const {
        'options': [
          {'ko': '사과 (sagwa)', 'es': 'Manzana (Fruta)'},
          {'ko': '물 (mul)', 'es': 'Agua'},
          {'ko': '책 (chaek)', 'es': 'Libro'},
          {'ko': '빵 (ppang)', 'es': 'Pan'},
        ],
        'correct': ['사과 (sagwa)'],
        'selection_mode': 'single',
      },
    );

    await captureScreen(
      tester,
      Scaffold(
        appBar: AppBar(
          title: const Text('Vocabulario Esencial · Cotidiano'),
          elevation: 0,
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: ActivityRendererHost(activity: act),
        ),
        bottomNavigationBar: SafeArea(child: buildAdBar()),
      ),
      'screenshot_5_vocabulario_visual.png',
    );
  });

  testWidgets('Screenshot 6: Saludos y Dialogos', (tester) async {
    final act = ActivityContent(
      id: 'dialogue_greeting_showcase',
      type: 'multiple_choice',
      family: ActivityFamily.choice,
      scoreMode: 'auto',
      prompt: '¿Cuál es el saludo formal y educado más común en Corea?',
      normalization: const {},
      hints: const ['Se usa cotidianamente para decir «Hola» de forma cortés.'],
      feedback: const {
        'correct': '¡Exacto! 안녕하세요 (Annyeonghaseyo) es el saludo estándar.',
        'wrong': {'default': 'Revisa las opciones de cortesía.'},
      },
      capabilities: const {'hasTts': false},
      payload: const {
        'options': [
          {
            'ko': '안녕하세요 (Annyeonghaseyo)',
            'es': 'Hola · Saludo formal y cortés',
          },
          {
            'ko': '감사합니다 (Gamsahamnida)',
            'es': 'Muchas gracias · Agradecimiento',
          },
          {
            'ko': '죄송합니다 (Joesonghamnida)',
            'es': 'Lo siento / Disculpe · Disculpa formal',
          },
          {
            'ko': '안녕히 가세요 (Annyeonghi gaseyo)',
            'es': 'Adiós · Despedida a quien se va',
          },
        ],
        'correct': ['안녕하세요 (Annyeonghaseyo)'],
        'selection_mode': 'single',
      },
    );

    await captureScreen(
      tester,
      Scaffold(
        appBar: AppBar(
          title: const Text('Saludos y Cortesía Cotidiana'),
          elevation: 0,
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: ActivityRendererHost(activity: act),
        ),
        bottomNavigationBar: SafeArea(child: buildAdBar()),
      ),
      'screenshot_6_dialogos_practicos.png',
    );
  });
}
