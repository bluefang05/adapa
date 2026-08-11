# ADAPA — Android device QA checklist

Record PASS / FAIL and notes for each item.

## Launch and offline
- [ ] Launch with airplane mode.
- [ ] Course home appears without network.
- [ ] No content image is missing.

## 320–360 dp narrow-phone behavior
- [ ] Home hero has no yellow/black overflow.
- [ ] Unit cards fit.
- [ ] Long lesson titles wrap.
- [ ] Bottom Previous / Next controls fit.
- [ ] Keyboard does not cover the answer currently being edited.

## Hangul
- [ ] 10 basic vowel stroke sets open.
- [ ] 14 basic consonant stroke sets open.
- [ ] 5 double consonant stroke sets open.
- [ ] Step arrows/order remain legible.
- [ ] Syllable builder composes 가, 무 and CVC examples correctly.

## TTS
- [ ] Settings reports ko-KR if installed.
- [ ] Normal voice plays.
- [ ] Slow voice plays more slowly.
- [ ] Missing ko-KR is handled without crash.
- [ ] Dialogue playback does not auto-skip user interaction.

## Activities
- [ ] Choice
- [ ] Matching
- [ ] Text input
- [ ] Ordering
- [ ] Hangul structure
- [ ] Dialogue
- [ ] Reading/speaking
- [ ] Review
- [ ] Visual reference

## Progress
- [ ] Attempts increase.
- [ ] Best score is retained after a worse retry.
- [ ] Lesson threshold behaves correctly.
- [ ] Unit unlock follows prerequisites.
- [ ] Continue returns to a sensible activity.
- [ ] Free-writing draft survives restart.
- [ ] Reset progress actually clears saved state.

## Final course behavior
- [ ] U7/U8 do not reveal romanization.
- [ ] Final conversation requires Hangul.
- [ ] Open writing is not falsely grammar-scored.
- [ ] Course-complete state appears only after required completion.
