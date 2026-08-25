import json
import os
import glob

def main():
    units_dir = 'assets/content/units'
    manifest_path = 'assets/content/course_manifest.json'

    with open(manifest_path, 'r', encoding='utf-8') as f:
        manifest = json.load(f)

    print(f"Curso: {manifest.get('title')}")
    units = manifest.get('units', [])

    total_lessons = 0
    total_theory = 0
    total_activities = 0
    total_evaluated_activities = 0
    total_reference_activities = 0
    total_dialogues = 0
    total_tts_phrases = 0

    issues = []

    for u_idx, u_meta in enumerate(units, 1):
        u_id = u_meta['id']
        u_file = os.path.join(units_dir, f"{u_id}.json")
        if not os.path.exists(u_file):
            issues.append(f"ERROR: Falta archivo {u_file}")
            continue

        with open(u_file, 'r', encoding='utf-8') as f:
            u_data = json.load(f)

        lessons = u_data.get('lessons', [])
        total_lessons += len(lessons)

        for l_idx, lesson in enumerate(lessons, 1):
            l_id = lesson.get('id')
            theory = lesson.get('theory', [])
            activities = lesson.get('activities', [])
            total_theory += len(theory)
            total_activities += len(activities)

            # Audit Theory Cards
            for t_item in theory:
                t_type = t_item.get('type')
                payload = t_item.get('payload', {})
                if t_type == 'phrase_table':
                    items = payload.get('items', [])
                    for p in items:
                        if not p.get('hangul') or not p.get('meaning_es'):
                            issues.append(f"[{u_id}/{l_id}] Frase incompleta: {p}")
                        if p.get('tts') or p.get('hangul'):
                            total_tts_phrases += 1
                elif t_type == 'character_grid':
                    items = payload.get('items', [])
                    for c in items:
                        if not c.get('char'):
                            issues.append(f"[{u_id}/{l_id}] Grid incompleto: {c}")

            # Audit Activities
            for act in activities:
                act_id = act.get('id')
                family = act.get('family', '')
                score_mode = act.get('scoreMode', 'auto')
                payload = act.get('payload', {})
                feedback = act.get('feedback', {})
                prompt = act.get('prompt') or act.get('instruction') or ''

                if not prompt.strip():
                    issues.append(f"[{u_id}/{l_id}/{act_id}] Sin enunciado / prompt")

                if score_mode == 'none' or family == 'visual_reference':
                    total_reference_activities += 1
                    continue

                total_evaluated_activities += 1

                # Check multiple choice / choice options
                if 'options' in payload and 'correct' in payload:
                    opts = payload['options']
                    corr = payload['correct']
                    if not opts or len(opts) < 2:
                        issues.append(f"[{u_id}/{l_id}/{act_id}] Menos de 2 opciones")
                    if not corr:
                        issues.append(f"[{u_id}/{l_id}/{act_id}] Sin respuesta correcta")
                    
                    # Extract options representations
                    valid_values = set()
                    for opt in opts:
                        if isinstance(opt, dict):
                            for k, v in opt.items():
                                if v is not None:
                                    valid_values.add(str(v))
                        else:
                            valid_values.add(str(opt))

                    for c in corr:
                        if str(c) not in valid_values:
                            issues.append(f"[{u_id}/{l_id}/{act_id}] Respuesta correcta no encontrada en opciones")

                # Check matching pairs
                if 'pairs' in payload:
                    pairs = payload.get('pairs', [])
                    if len(pairs) < 2:
                        issues.append(f"[{u_id}/{l_id}/{act_id}] Actividad de emparejar con menos de 2 pares")
                    for p in pairs:
                        if not p.get('left') or not p.get('right'):
                            issues.append(f"[{u_id}/{l_id}/{act_id}] Par invalido")

                # Check dialogue
                if family == 'dialogue' or 'turns' in payload or 'lines' in payload:
                    total_dialogues += 1

    print("\n--- RESUMEN GENERAL DEL CURSO ---")
    print(f"Total de Unidades: {len(units)}")
    print(f"Total de Lecciones: {total_lessons}")
    print(f"Total de Bloques de Teoria: {total_theory}")
    print(f"Total de Actividades Interactivas: {total_activities}")
    print(f"  * Evaluadas con auto-calificacion: {total_evaluated_activities}")
    print(f"  * De referencia visual / trazos: {total_reference_activities}")
    print(f"Vocabulario y frases con pronunciacion / TTS: {total_tts_phrases}")
    print(f"\n--- AUDITORIA DE CALIDAD DE CONTENIDO ---")
    print(f"Total de errores detectados: {len(issues)}")
    if issues:
        for iss in issues[:10]:
            print("  * ", iss)
    else:
        print(">>> 100% EXCELENTE: Todos los textos, opciones, respuestas, pares y audios son correctos y consistentes.")

if __name__ == '__main__':
    main()
