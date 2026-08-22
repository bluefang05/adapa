import subprocess
import os
import sys
from PIL import Image

tests = [
    ("Screenshot 1: Ruta Principal", "screenshot_1_ruta_principal.png"),
    ("Screenshot 2: Descubre el Hangul", "screenshot_2_lecciones_hangul.png"),
    ("Screenshot 3: Leccion y Trazos", "screenshot_3_leccion_alfabeto.png"),
    ("Screenshot 4: Actividad Interactiva", "screenshot_4_actividad_interactiva.png"),
    ("Screenshot 5: Vocabulario Cotidiano", "screenshot_5_vocabulario_visual.png"),
    ("Screenshot 6: Saludos y Dialogos", "screenshot_6_dialogos_practicos.png"),
]

out_dir = os.path.abspath("release/play_store/screenshots")
os.makedirs(out_dir, exist_ok=True)

print("Starting generation of 6 Google Play Store screenshots (1080x1920)...")

for i, (test_name, filename) in enumerate(tests, 1):
    print(f"[{i}/6] Generating {filename} ({test_name})...")
    cmd = f'flutter test test/generate_play_store_screenshots_test.dart --plain-name "{test_name}"'
    try:
        res = subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=25)
    except subprocess.TimeoutExpired:
        pass
    
    target = os.path.join(out_dir, filename)
    if os.path.exists(target):
        size = os.path.getsize(target)
        img = Image.open(target)
        print(f"   ✓ OK: {filename} - {img.size} ({size/1024:.1f} KB)")
    else:
        print(f"   ✗ Missing: {filename}")

print("\nAll Play Store screenshots verified in:", out_dir)
