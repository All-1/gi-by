# Phase 2 — Task breakdown (attempts, materials, achievements)

**Plugin:** `bp_knowledge_tests`  
**Parent plan:** [IMPLEMENTATION_PLAN.md §11](./IMPLEMENTATION_PLAN.md#11-implementation-phases)  
**Requirements:** [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md) (§9–12, §2.6, §2.11, §2.15, §6)  
**Live status index:** [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

**Goal (exit criterion):** Complete attempt lifecycle **without UI** — finish attempt → DB → materials on fail → achievements on pass → retake/validity/notifications queryable — verifiable via CLI or dev script (not React/WebSocket).

**Progress (2026-10-08):** **Wave C** shipped (`RetakeEligibilityQuery`). **Next:** Wave D (validity), E (notifications), optional A4 smoke script.

**Estimate:** ~1.5–2 weeks one developer (matches plan §11).

---

## Already done (skip)

| Area | What exists |
|------|-------------|
| Schema | `gi_new_test_attempts`, `attempt_questions`, `finished_attempts_explanations`, achievements, notifications, config ([001_baseline.sql](../migrations/001_baseline.sql)) |
| Reads | `AttemptRepository::findById`, `AttemptQuestionRepository::listByAttemptId` |
| Materials | `attempts()->materials` (`MaterialsService`, `FinishedExplanationRepository`) |
| Tests | `tests/run_scoring_tests.php`, `run_selection_grader_tests.php`, `run_materials_mistake_tests.php`, `run_attempt_question_materials_tests.php` |
| Domain for finish | `ScoringService`, `QuestionSelectionGrader`, `AttemptResultClassifier`, `TestConfig`, `catalog()->answers->maximumCorrectForTest` |
| **Complete attempt** | `attempts()->complete` → `Services\Attempt\CompleteAttempt::finish`; `AttemptRepository` / `AttemptQuestionRepository` **insert** |

**Gap (remaining):** A4 integration smoke; validity/critical update, notifications (waves D–E).

---

## Wave A — Attempt persistence (critical path) — **done** (except A4)

### A1. Finish payload (Phase 2 ↔ Phase 3 boundary) — **done**

Phase 3 `TestController` RAM will produce this; Phase 2 tests pass it manually.

| Field | Source |
|-------|--------|
| `userId`, `testId`, `testVersion` | User + `TestRecord` at finish |
| `dateFinished` | `DatabaseClock` |
| Per question | `questionId`, `rightAnswers`, `failedAnswers` (graded counts only — spec §9.2–9.3) |

**Validation:** Questions belong to test; counts ≥ 0; at least one question row.

**Deliverable:** Array question lines + `attemptRow` keys in `CompleteAttempt::persist` — see [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md).

### A2. Repository writes — **done**

**`AttemptRepository`**

- `insert(...)` → new `id`
- Columns: `user_id`, `test_id`, `test_version`, `valid_answers`, `invalid_answers`, `score`, `status` (`failed` \| `passed` \| `outdated`), `date_finished`
- Pattern: `DBWorker` + `DBUtilities::packageWriteColumns` (same as catalog repos)

**`AttemptQuestionRepository`**

- Insert rows: `user_id`, `attempt_id`, `question_id`, `right_answers`, `failed_answers`, `date_finished`
- **DDL note:** Baseline has no `test_id` on this table (spec §9.2 prose may mention it — follow DDL).

**Optional reads for later waves**

- `findLatestByUserAndTest(userId, testId)`
- `listByUserAndTest` / `updateStatus` (needed for Wave D)

**Acceptance:** Insert + read round-trip (dev Tools or integration script).

### A3. `CompleteAttempt` use case — **done**

**Code:** `Services\Attempt\CompleteAttempt`, `AttemptServices::$complete` → `attempts()->complete->finish(...)`.

**Steps:**

1. Sum per-question `right_answers` / `failed_answers` → attempt `valid_answers` / `invalid_answers`.
2. `maximumCorrect = catalog()->answers->maximumCorrectForTest($testId)`.
3. `score = domain()->scoring->calculateScore(...)`.
4. `classification = domain()->classifier->classify($score, domain()->config->read())`.
5. `status = passed ? 'passed' : 'failed'`.
6. Insert attempt + question rows (transaction if available).
7. If **failed:** `materials->populateFromFailedAttempt($attemptId)`.
8. If **passed:** achievement hook (Wave B).
9. Return: `AttemptRecord` (medal/lock via `domain()->classifier` when needed).

**Acceptance:** Failed attempt with mistakes → materials rows; passed → no materials.

### A4. Scripted lifecycle

- New script e.g. `tests/run_complete_attempt_script.php` (or guarded dev Tools action).
- Uses seeded catalog + known counts → calls `CompleteAttempt` → asserts DB side effects.

**Acceptance:** Documented next to other `run_*` scripts in [migrations/README.md](../migrations/README.md) or `PROJECT_CONTEXT`.

---

## Wave B — Achievements — **done**

**Decision (failed latest):** remove `gi_new_test_user_achievements` row (no star); passed latest upserts tier (`lock` when score ≥ lock threshold).

### B1. Repositories — **done**

- `AchievementRepository` — resolve `medal_id` from `gi_new_test_achievements` (tiers seeded: Bronze, Silver, Gold, Lock — see `Seeder`).
- `UserAchievementRepository` — `upsertForUserTest(userId, testId, medalId)` on unique `(user_id, test_id)`.

**Rules (spec §2.11, §5):**

- **Latest** completed attempt drives star row (not historical best).
- Pass → medal from thresholds; **95%+** → lock semantics via `AttemptClassification`.
- Fail → clarify against spec §2.2 / §2.11 (delete row vs failed tier vs keep previous) — **decision + CHANGELOG** when implemented.

### B2. Wire `CompleteAttempt` → achievements — **done**

`CompleteAttempt::finish` calls `attempts()->achievements->applyAfterComplete` after persist.

### B3. `AchievementRecalculation` (spec §5.6, rule 36) — **done**

`AttemptAchievementService::recalculateForUserAndTest` — latest non-`outdated` attempt; wire from admin/config save later.

---

## Wave C — Retake eligibility — **done**

### C1. `RetakeEligibilityQuery` — **done**

Input: `userId`, `testId`. Output: `canStart`, `reasons[]` (codes for WS/React).

Checks ([IMPLEMENTATION_PLAN §5.4](./IMPLEMENTATION_PLAN.md#54-retake--materials-spec-26--3--21)):

1. In-progress session — Phase 2: document “no RAM” / always true; Phase 3 must integrate.
2. `retake_delay_days` + latest completed `date_finished` (spec §2.6: **1 = next calendar day**).
3. Latest finish **failed** → `!hasPendingMaterials`.
4. Lock threshold on latest relevant result.
5. Notification block — when Wave E exists.

**Acceptance:** CLI tests with injectable / faked clock for date edges.

---

## Wave D — Validity, outdated, critical update

### D1. `ValidityMaintenance`

Callable on catalog load / login (hook later). Phase 2: service + tests.

- Passed + older than **one year** from `date_finished` → `status = outdated`.
- Test `date_modified` vs attempt + §2.15 combined rules.
- Outdated → remove `user_achievements`; **keep** attempt rows.

### D2. `CriticalUpdateTest` (admin)

- Bump test version (align with explicit **Critical Update** — spec §6).
- Mark affected attempts outdated; strip achievements; do not delete history.

**Depends on:** `AttemptRepository` bulk/status updates.

---

## Wave E — Notifications persistence

### E1. `NotificationRepository` + `NotificationService`

- `gi_new_test_notifications` (`dismiss_count` per user/test).
- On failed complete: create/update state per spec §3.2 and config keys in `gi_new_test_config`.

### E2. Retake gate

Wire notification block mode into `RetakeEligibilityQuery` when spec §3.1 requires it.

---

## Wave F — Close Phase 2

| Task | Notes |
|------|--------|
| Automated tests | Complete attempt (pass/fail), retake, outdated/critical, achievement on complete |
| `PROJECT_CONTEXT` | Document `attempts()->complete` and related APIs |
| [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md) §11 | Check boxes; refresh §3 repository state |
| Doc hygiene | Reconcile schema version notes (v4 target vs migration `005_*` reference) if needed |

---

## Suggested implementation order

```text
A1 → A2 → A3 → A4
         ↓
    B1 → B2 → B3
         ↓
    C1 (needs A3 + materials)
         ↓
    D1 → D2 (needs attempt updates + achievements)
         ↓
    E1 → E2
         ↓
    F
```

**Minimum before Phase 3:** **A1–A4**, **B1–B2**, **C1**. D, E, F complete Phase 2 exit per plan §11.

---

## Open decisions (resolve while coding)

| # | Topic | Where to look |
|---|--------|----------------|
| 1 | Failed attempt effect on `user_achievements` | Spec §2.11 |
| 2 | Lock at 95%: `Lock` tier row vs eligibility-only | Spec §2.2, `AttemptResultClassifier` |
| 3 | Retake delay: calendar day vs 24h from `date_finished` | Spec §2.6, config `retake_delay_days` |
| 4 | `user_id` | `gi_new_users.id_user` (all inserts) |

Log significant product choices in [CHANGELOG.md](../../../../CHANGELOG.md).

---

## Task checklist (copy for PRs)

- [x] A1 Finish payload contract (array lines on `CompleteAttempt::finish`)
- [x] A2 Attempt + attempt_question writes (+ optional reads/updates)
- [x] A3 `CompleteAttempt` (`attempts()->complete`)
- [ ] A4 Scripted lifecycle script (WP/DB integration or dev Tools action)
- [x] B1 Achievement + user achievement repos
- [x] B2 Hook complete → achievements
- [x] B3 Achievement recalculation (`recalculateForUserAndTest`)
- [x] C1 `RetakeEligibilityQuery` + tests
- [ ] D1 `ValidityMaintenance` + tests
- [ ] D2 `CriticalUpdateTest` + tests
- [ ] E1 Notification persistence + failed-complete hook
- [ ] E2 Retake ↔ notification block
- [x] F Phase 2 breakdown doc + Wave A status in IMPLEMENTATION_PLAN / PROJECT_CONTEXT
- [ ] F Remaining §11 checkboxes when B–E land
