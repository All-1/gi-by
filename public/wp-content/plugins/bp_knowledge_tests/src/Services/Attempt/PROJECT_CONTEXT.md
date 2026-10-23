# Component Context: bp_knowledge_tests/src/Services/Attempt

Completed-attempt lifecycle, materials, retake gates, validity, critical invalidation. **In-progress RAM** is Phase 4 in `bp_contracts`, not here.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) · [../../PROJECT_CONTEXT.md](../../PROJECT_CONTEXT.md)

---

## `CompleteAttempt::finish` flow

```text
finish(userId, testId, questionLines)
  1. Load test; validate questionLines match full catalog question set
  2. Sum rightAnswers / failedAnswers → valid_answers, invalid_answers
  3. domain()->scoring->calculateScore(...)
  4. domain()->classifier->classify(...) → status passed|failed
  5. INSERT gi_new_test_attempts + gi_new_test_attempt_questions
  6. If failed → materials->populateFromFailedAttempt; reset failed_test notification
     Else → delete failed_test notification row
  7. achievements->applyAfterComplete (latest result §2.11)
  8. Return AttemptRecord
```

**Input contract** (Phase 4 WS must produce this):

```php
[
  ['questionId' => 1, 'rightAnswers' => 2, 'failedAnswers' => 0],
  ['questionId' => 2, 'rightAnswers' => 1, 'failedAnswers' => 1],
  // ... every question id for the test exactly once
]
```

Counts come from `QuestionSelectionGrader` per question when the user submits selections in RAM.

---

## Other services

| Service | When it runs |
|---------|----------------|
| `MaterialsService` | After failed finish; `confirmExamined` deletes temp rows |
| `AttemptAchievementService` | After every finish; `recalculateForUserAndTest` on threshold config change |
| `RetakeEligibilityQuery` | Before starting new attempt — delay, materials, lock, notification block |
| `ValidityMaintenance` | On login/catalog refresh — one-year outdated |
| `CriticalUpdateTest` | Admin critical update — all `passed` → `outdated`, strip medals, bump `version` |
| `NotificationRetakePolicy` | Blocks retake when notification config requires action |
| `NotificationUiQuery` | PA list/dismiss; lazy `new_test` rows when prior tests completed (catalog order) |

Policies: `RetakeEligibilityPolicy`, `AttemptValidityPolicy` — keep rules testable in isolation.

---

## Related tables

| Table | Writer |
|-------|--------|
| `gi_new_test_attempts` | `AttemptRepository` |
| `gi_new_test_attempt_questions` | `AttemptQuestionRepository` |
| `gi_new_test_finished_attempts_explanations` | `MaterialsService` |
| `gi_new_test_user_achievements` | `AttemptAchievementService` |
| `gi_new_test_notifications` | `CompleteAttempt`, dismiss flows (Phase 6 UI) |

---

## Tests

| Script | Coverage |
|--------|----------|
| `run_complete_attempt_tests.php` | End-to-end finish + side effects |
| `run_retake_eligibility_tests.php` | Gates |
| `run_validity_tests.php` | Outdated |
| `run_notification_retake_tests.php` | Notification block |
| `run_attempt_question_materials_tests.php` | Which questions get material rows |
