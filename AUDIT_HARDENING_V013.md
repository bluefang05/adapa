# ADAPA Flutter v0.13 — audit hardening

This iteration applies only the useful findings from the external code/UI audit.

## Applied

- 500 ms debounce for autosaved writing and final-conversation drafts.
- Pending draft is flushed when leaving the widget.
- Save failures are logged, exposed in UI, and can be retried.
- Theory TTS buttons now show the same missing-ko-KR feedback as other TTS controls.
- Scored generic text activities are checked by tests/static preflight so an accidental missing `accepted_answers` cannot silently turn into “any answer is correct”.
- Strict UTF-8/Hangul audit protects key Korean source files and all course JSON.
- Home reset duplication removed; reset terminology unified under Privacy & data.
- Lesson progress wording simplified; untouched lessons say “No iniciada”.
- Korean voice setup reduced to one normal voice test plus optional slow-speed test.
- TTS speed sliders use human labels rather than meaningless percentages.
- Untouched scored activities ask for confirmation before being skipped.

## Additional compatibility correction

- Ordering was migrated from deprecated `onReorder` + manual index adjustment to `onReorderItem`, which performs that index adjustment for the current Flutter baseline.

## Explicitly not applied

- No `guided_dialogue_fill -> tts_dialogue_playback` patch was applied because current course data contains no such chain. The actual playback activity points to `final_guided_conversation`, which already persists `playback_lines`.
- Romanization remains intentionally controlled by unit/block pedagogy, not a global learner override.

## Encoding verification

The project source extracted from the v0.12 full ZIP contains valid UTF-8 Hangul. The mojibake seen in the external dump was not present in the actual source artifact.
