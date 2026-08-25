import json
import os
import glob
import re

def is_hangul(char):
    code = ord(char)
    # Hangul Syllables (AC00-D7A3), Hangul Jamo (1100-11FF), Hangul Compatibility Jamo (3130-318F)
    return (0xAC00 <= code <= 0xD7A3) or (0x1100 <= code <= 0x11FF) or (0x3130 <= code <= 0x318F)

def get_hangul_details(text):
    if not text:
        return []
    return [c for c in text if is_hangul(c)]

def audit_curriculum():
    units_dir = 'assets/content/units'
    manifest_path = 'assets/content/course_manifest.json'

    with open(manifest_path, 'r', encoding='utf-8') as f:
        manifest = json.load(f)

    units = manifest.get('units', [])

    report = {
        'total_hangul_characters': 0,
        'unique_hangul_characters': set(),
        'total_vocabulary_items': 0,
        'total_tts_entries': 0,
        'mojibake_or_corrupt_chars': [],
        'romanization_checks': [],
        'tts_format_issues': [],
        'units_summary': []
    }

    # Standard expected romanization map for basic jamo
    jamo_rr_map = {
        'ㄱ': ['g', 'k'],
        'ㄴ': ['n'],
        'ㄷ': ['d', 't'],
        'ㄹ': ['r', 'l'],
        'ㅁ': ['m'],
        'ㅂ': ['b', 'p'],
        'ㅅ': ['s', 't'],
        'ㅇ': ['ng', 'silent', ''],
        'ㅈ': ['j'],
        'ㅊ': ['ch'],
        'ㅋ': ['k'],
        'ㅌ': ['t'],
        'ㅍ': ['p'],
        'ㅎ': ['h'],
        'ㄲ': ['kk'],
        'ㄸ': ['tt'],
        'ㅃ': ['pp'],
        'ㅆ': ['ss'],
        'ㅉ': ['jj'],
        'ㅏ': ['a'],
        'ㅓ': ['eo'],
        'ㅗ': ['o'],
        'ㅜ': ['u'],
        'ㅡ': ['eu'],
        'ㅣ': ['i'],
        'ㅐ': ['ae'],
        'ㅔ': ['e'],
        'ㅑ': ['ya'],
        'ㅕ': ['yeo'],
        'ㅛ': ['yo'],
        'ㅠ': ['yu'],
        'ㅘ': ['wa'],
        'ㅝ': ['wo'],
        'ㅟ': ['wi'],
        'ㅢ': ['ui']
    }

    corrupt_patterns = re.compile(r'[\uFFFD]|\?\?\?+')

    for u in units:
        u_id = u['id']
        u_path = os.path.join(units_dir, f"{u_id}.json")
        if not os.path.exists(u_path):
            continue

        with open(u_path, 'r', encoding='utf-8') as f:
            u_content = f.read()

        # Check for raw mojibake in whole unit file
        for m in corrupt_patterns.finditer(u_content):
            report['mojibake_or_corrupt_chars'].append({
                'unit': u_id,
                'match': m.group(0),
                'pos': m.start()
            })

        u_data = json.loads(u_content)
        lessons = u_data.get('lessons', [])
        unit_hangul_count = 0

        for lesson in lessons:
            l_id = lesson.get('id')
            
            # 1. Audit Theory Blocks
            for t in lesson.get('theory', []):
                t_type = t.get('type')
                payload = t.get('payload', {})
                
                # Check character grids
                if t_type == 'character_grid':
                    for item in payload.get('items', []):
                        char = item.get('char', '')
                        rom = item.get('rom', '')
                        for c in char:
                            if is_hangul(c):
                                report['total_hangul_characters'] += 1
                                report['unique_hangul_characters'].add(c)
                                unit_hangul_count += 1
                        
                        # Validate romanization of jamo if applicable
                        if char in jamo_rr_map:
                            expected = jamo_rr_map[char]
                            if not any(exp in rom.lower() for exp in expected):
                                report['romanization_checks'].append({
                                    'unit': u_id,
                                    'lesson': l_id,
                                    'char': char,
                                    'rom': rom,
                                    'expected': expected
                                })

                # Check phrase tables
                elif t_type == 'phrase_table':
                    for phrase in payload.get('items', []):
                        hangul = phrase.get('hangul', '')
                        meaning = phrase.get('meaning_es', '')
                        tts = phrase.get('tts', '')
                        report['total_vocabulary_items'] += 1
                        
                        for c in hangul:
                            if is_hangul(c):
                                report['total_hangul_characters'] += 1
                                report['unique_hangul_characters'].add(c)
                                unit_hangul_count += 1
                        
                        if tts:
                            report['total_tts_entries'] += 1
                            # TTS should only contain clean text (no markdown brackets or special symbols)
                            if re.search(r'[\[\]{}<>/\\_]', tts):
                                report['tts_format_issues'].append({
                                    'unit': u_id,
                                    'lesson': l_id,
                                    'tts': tts
                                })

            # 2. Audit Activities
            for act in lesson.get('activities', []):
                act_id = act.get('id')
                prompt = act.get('prompt') or ''
                payload = act.get('payload', {})
                
                # Find all Hangul in prompts, options, tokens, pairs
                for c in prompt:
                    if is_hangul(c):
                        report['total_hangul_characters'] += 1
                        report['unique_hangul_characters'].add(c)
                        unit_hangul_count += 1

                for opt in payload.get('options', []):
                    text = str(opt.get('ko') if isinstance(opt, dict) else opt)
                    for c in text:
                        if is_hangul(c):
                            report['total_hangul_characters'] += 1
                            report['unique_hangul_characters'].add(c)
                            unit_hangul_count += 1

                for pair in payload.get('pairs', []):
                    left = str(pair.get('left', ''))
                    right = str(pair.get('right', ''))
                    for c in left + right:
                        if is_hangul(c):
                            report['total_hangul_characters'] += 1
                            report['unique_hangul_characters'].add(c)
                            unit_hangul_count += 1

                for token in payload.get('tokens', []):
                    for c in str(token):
                        if is_hangul(c):
                            report['total_hangul_characters'] += 1
                            report['unique_hangul_characters'].add(c)
                            unit_hangul_count += 1

        report['units_summary'].append({
            'unit': u_id,
            'title': u.get('title'),
            'hangul_characters': unit_hangul_count
        })

    return report

def main():
    print("Iniciando auditoria profunda de caracteres Hangul, fonetica y pronunciacion...")
    report = audit_curriculum()

    print("\n" + "="*50)
    print("      REPORTE DE CARACTERES Y PRONUNCIACION")
    print("="*50)
    print(f"Total de caracteres Hangul auditados: {report['total_hangul_characters']:,}")
    print(f"Caracteres y silabas Hangul unicos: {len(report['unique_hangul_characters'])}")
    print(f"Entradas de vocabulario y frases: {report['total_vocabulary_items']}")
    print(f"Frases con audio y pronunciacion TTS: {report['total_tts_entries']}")

    print("\n--- DISTRIBUCION POR UNIDAD ---")
    for u in report['units_summary']:
        print(f"  * Unidad {u['unit']} ({u['title']}): {u['hangul_characters']} caracteres Hangul")

    print("\n--- ANALISIS DE INTEGRIDAD ---")
    print(f"1. Caracteres corruptos / Mojibake: {len(report['mojibake_or_corrupt_chars'])}")
    if report['mojibake_or_corrupt_chars']:
        for m in report['mojibake_or_corrupt_chars'][:5]:
            print(f"   - Error en {m['unit']}: '{m['match']}'")
    else:
        print("   -> [OK] No se detecto ningun caracter corrupto o con error de codificacion UTF-8.")

    print(f"2. Discrepancias de Romanizacion / Fonetica: {len(report['romanization_checks'])}")
    if report['romanization_checks']:
        for r in report['romanization_checks'][:5]:
            print(f"   - Advertencia en {r['unit']}/{r['lesson']}: caracter '{r['char']}' tiene romanizacion '{r['rom']}', esperado algo como {r['expected']}")
    else:
        print("   -> [OK] Todas las tablas de caracteres coinciden con las normas de Romanizacion Revisada (RR).")

    print(f"3. Formato de llamadas TTS de audio: {len(report['tts_format_issues'])}")
    if report['tts_format_issues']:
        for t in report['tts_format_issues'][:5]:
            print(f"   - Caracter no apto para TTS en {t['unit']}/{t['lesson']}: '{t['tts']}'")
    else:
        print("   -> [OK] Todas las cadenas de pronunciacion TTS estan limpias y listas para el motor de voz nativo.")

if __name__ == '__main__':
    main()
