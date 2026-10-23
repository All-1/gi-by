# Knowledge Tests — Implementation Plan & Project Context

**Plugin**: `bp_knowledge_tests`  
**Companion spec (requirements baseline)**: [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md)  
**Site rules**: [DEVELOPMENT_RULES.md](../../../../DEVELOPMENT_RULES.md) (WordPress / `public/` — not a copy of global rules)  
**Global standards**: Cursor **Settings → Rules** (security, GRASP/SOLID, code size §11, CHANGELOG, automated tests, PR checklist)  
**Plugin context**: [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) (directory index — details in nested `PROJECT_CONTEXT.md` files)  
**Personal account integration**: [bp_contracts/PROJECT_CONTEXT.md](../../bp_contracts/PROJECT_CONTEXT.md)

**Status (repository)**: Phases **1–6** **development complete** (notifications UX + WS). Schema **v3**. **Next:** Phase **7** hardening → **[MANUAL_QA_V1.md](./MANUAL_QA_V1.md)** on staging before production.  
**Delivery model (2026-10-23):** Finish **all V1 development** (Phases 6–7), then one **consolidated manual QA** pass — not a gate between Phase 5 and Phase 6. Keep CLI domain tests green during development.  
**Phase 2 tasks:** [PHASE_2_TASKS.md](./PHASE_2_TASKS.md)  
**Phase 3 tasks:** [PHASE_3_TASKS.md](./PHASE_3_TASKS.md) (admin)  
**Phase 4 tasks:** [PHASE_4_TASKS.md](./PHASE_4_TASKS.md) (WS)  
**Phase 5 tasks:** [PHASE_5_TASKS.md](./PHASE_5_TASKS.md) (dealer vanilla JS in `bp_contracts`)  
**Phase 6 tasks:** [PHASE_6_TASKS.md](./PHASE_6_TASKS.md) (notifications)  
**Phase 7 tasks:** [PHASE_7_TASKS.md](./PHASE_7_TASKS.md) (hardening)  
**Manual QA (post-dev):** [MANUAL_QA_V1.md](./MANUAL_QA_V1.md)  
**Last updated**: 2026-10-23

---

## 1. Purpose of this document

This file is the **single engineering map** for building V1 of the knowledge testing system. It does not replace the product spec; it records:

- how V1 maps to code and plugins;
- phased delivery order and exit criteria;
- database and service boundaries;
- integration with `bp_contracts` and WebSocket;
- documentation obligations;
- risks, timeline, and open decisions.

**Rule hierarchy when documents conflict:**

1. **Cursor User Rules** (global) — security, forbidden patterns, code-size limits, required tests for scoring/auth, CHANGELOG format.
2. **[DEVELOPMENT_RULES.md](../../../../DEVELOPMENT_RULES.md)** — WordPress / this repo (uploads, `$wpdb->prepare`, no new tables without `PROJECT_CONTEXT`, WS title list, legacy containment).
3. Product spec: agreed sections **§2+** override silent edits to baseline **§1**; baseline **§1** must not be rewritten without an explicit project decision (logged in [CHANGELOG.md](../../../../CHANGELOG.md) / §14 here).
4. **This plan** + [plugin PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) — engineering map and integration facts (do not duplicate long rule text from Settings or DEVELOPMENT_RULES).

Implementation must not satisfy the product spec by violating (1) or (2).

---

## 2. Executive summary

| Item | Decision |
|------|----------|
| **Ownership** | Domain, DB, scoring, validity, achievements, analytics, admin CRUD → **`bp_knowledge_tests`** |
| **Shell** | Tests menu, modals, notifications, in-progress session, WS transport, chat stars → **`bp_contracts`** |
| **In-progress attempts** | RAM via **`TestController`** in `bp_contracts`; cleared at **03:00** server restart |
| **Completed attempts** | MySQL tables `gi_new_test_*` (see §5) |
| **V1 UI** | **React** test-factory admin (§15–18); **vanilla JS** dealer Tests (§19–21) in `bp_contracts` (`knowledge_tests.js`, overlay pattern §19.2) — spec behavior, not legacy jQuery modals for new flows |
| **V1 integration** | `bp_contracts`: nav, WS client, dealer page JS, notification host (Phase 6); **`bp_knowledge_tests`**: domain + persistence + admin REST + WS-facing gateway |
| **V1 QA** | **Development** through Phase **7**; **manual** operator checklist once — [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) |
| **Future platform** | API-shaped plugin layers remain compatible with Laravel + PostgreSQL + Reverb (spec §24–25) |

---

## 3. Current repository state

```
bp_knowledge_tests/
├── PROJECT_CONTEXT.md
├── index.php, composer.json
├── migrations/
├── src/                 ← Bootstrap, Domain, Infrastructure, Services
├── tests/run_*.php      ← scoring, selection grader, materials filter (no WP)
└── spec/
    ├── Testing System — Project Documentation.md
    ├── IMPLEMENTATION_PLAN.md
    ├── PHASE_2_TASKS.md
    ├── PHASE_3_TASKS.md
    ├── PHASE_4_TASKS.md
    ├── PHASE_5_TASKS.md
    ├── PHASE_6_TASKS.md
    ├── PHASE_7_TASKS.md
    └── MANUAL_QA_V1.md
```

**Implemented (development):** Phases **1–6** — catalog admin React; `TestController` + [WS_PROTOCOL.md](./WS_PROTOCOL.md); dealer `knowledge_tests.js` + materials + chat stars; notification UX (`knowledge_test_notifications.js`, `NotificationUiQuery`, WS §Phase 6).

**Not yet present (development):**

- Phase **7** — hardening (auth/WS validation, regression automation, deploy checklist prep)

**Not yet run:** [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) — after Phases **6–7** dev complete.

**Doc / integration hygiene (Phase 0):** plugin linked from [public/PROJECT_CONTEXT.md](../../../../PROJECT_CONTEXT.md); WS titles in [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md).

**Phase order (2026-10-12):** Admin (**3**) before WS/dealer (**4–6**) so staging has real catalog content; see [PHASE_3_TASKS.md](./PHASE_3_TASKS.md).

---

## 4. Architecture

### 4.1 Component diagram

```text
┌──────────────────────────────────────────────────────────────┐
│ WordPress (shortcodes, WP Admin)                              │
└───────────────┬──────────────────────────────┬───────────────┘
                │                              │
┌───────────────▼──────────────┐   ┌───────────▼────────────────┐
│ bp_contracts                │   │ bp_knowledge_tests          │
│ • Tests menu / list UI      │   │ • Domain services           │
│ • modal_window integration  │   │ • Repositories / migrations │
│ • TestController (RAM)      │   │ • Admin: tests/results/     │
│ • Chat.php WS routing       │   │   analytics/settings        │
│ • Stars beside chat names   │   │ • Application “API” layer   │
│ • Notification UX           │   │   (PHP, no duplicated rules)│
└───────────────┬──────────────┘   └───────────┬────────────────┘
                │         calls                 │
                └──────────────┬────────────────┘
                               ▼
                    gi_new_test_* tables
```

### 4.2 Layering (spec §25)

```text
User Interface (React — personal account + admin)
        ↓
Testing Interface (WS handlers / REST or RPC as needed / thin controllers)
        ↓
Application services (use cases)
        ↓
Domain (scoring, validity, retake, achievements)
        ↓
Infrastructure (repositories, config, $wpdb / DBWorker)
```

**Hard rule:** `bp_contracts` must **not** implement scoring, pass/fail, validity, or achievement rules — only session state and presentation.

### 4.3 Engineering standards (apply to new / refactored PHP)

Follow User Rules and DEVELOPMENT_RULES §2 — summary for this plugin only:

| Topic | Requirement |
|-------|----------------|
| **GRASP / services** | Scoring, validity, retake, achievements, notifications → `bp_knowledge_tests` services (§6); `Chat.php` / WS handlers stay thin controllers. |
| **Composition** | Register dependencies in plugin bootstrap or `SystemConstructor`; avoid new large **bp_contracts** capability traits — prefer an injected Worker (or similar) wired from `InteractionInterface`. |
| **Code size** | New concrete classes ≤120 lines; methods ≤15 executable statements (split services rather than growing monoliths). |
| **SQL** | Repositories use `$wpdb->prepare()` or framework Workers; document all new `gi_new_test_*` tables in [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md). |
| **Legacy containment** | No new 30+ line business blocks in `Chat.php` or giant `index.php`; extract to plugin `src/`. |
| **Automated tests** | **Required** for scoring, pass/fail thresholds, validity/outdated, retake eligibility, and admin capability checks (User Rules §8.3). Manual QA checklist remains in §12. |

### 4.4 User identity

- Shortcodes use WordPress `get_current_user_id()` and roles (same pattern as `bp_contracts` `analytics()`, `contracts()`).
- Business data keys **`user_id`** → map to **`gi_new_users`** (personal account), not WP users table alone (DEVELOPMENT_RULES §2.5 — no third user store).
- Document the mapping function in plugin PROJECT_CONTEXT when implemented.

### 4.5 WebSocket (spec §23)

- Unfinished attempt: **memory only** (`TestController`), keyed by user id.
- Completed attempt: **database** via `bp_knowledge_tests` finish use case.
- New WS message titles must be listed in [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md).
- Client handlers follow `handler` + `PascalCase(title)` in `communication_server.js`.

**Proposed client → server commands (names to finalize in Phase 4):**

| Command | Purpose |
|---------|---------|
| `showTests` | Refresh test list for user |
| `startTest` | Begin or resume in-memory session |
| `submitTestQuestion` | Answer current question; return immediate feedback |
| `finishTest` | Finalize attempt → persist via plugin |
| `abandonTest` | Drop in-memory session (optional) |
| `getTestMaterials` | Load temporary learning payload |
| `confirmTestMaterialsExamined` | Delete temp rows; enable retake path |

**Proposed server → client responses:**

| Title | Handler |
|-------|---------|
| `TestsOnPage` | `handlerTestsOnPage` |
| `TestQuestionOnPage` | `handlerTestQuestionOnPage` |
| `TestFinishedOnPage` | `handlerTestFinishedOnPage` |
| `TestMaterialsOnPage` | `handlerTestMaterialsOnPage` |

(Adjust names to match existing naming conventions when implementing.)

---

## 5. Database model (V1)

Source of truth for business meaning: product spec **[§8–13](./Testing%20System%20%E2%80%94%20Project%20Documentation.md#8-proposed-database-tables)**.  
This section lists **every proposed column** in one place for migrations and code.

**Explicitly not in V1:** `gi_new_test_versions`, `gi_new_test_attempt_answers`, `gi_new_test_question_stats`, `wp_bp_test_user_states`, `gi_new_test_constants`.

**Migrations:** versioned PHP or SQL in plugin; keep this section in sync when the spec changes.

---

### 5.0 Table index

| Table | Role |
|-------|------|
| `gi_new_tests` | Logical test; `version` bumps on content change |
| `gi_new_test_questions` | Questions |
| `gi_new_test_answers` | **Answer options** (choice text per question; not user selections) |
| `gi_new_test_attempts` | **Completed** attempts only |
| `gi_new_test_attempt_questions` | Per-question stats per completed attempt |
| `gi_new_test_finished_attempts_explanations` | Temporary materials rows until “Examined” |
| `gi_new_test_achievements` | Global rank/level definitions (Bronze/Silver/Gold/Lock) |
| `gi_new_test_user_achievements` | User’s current medal per test |
| `gi_new_test_notifications` | Popup dismiss count per user/test |
| `gi_new_test_config` | Key/value admin settings |

---

### 5.0.1 `gi_new_tests` (spec §8.1)

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | ✓ | `INT UNSIGNED PK AI` | |
| `title` | ✓ | `VARCHAR(255)` | Display name |
| `version` | ✓ | `INT UNSIGNED` | Increment when questions/answers change |
| `achievement_area` | UI only (§15.2) | `VARCHAR(255) NULL` |
| `date_creation` | ✓ | `DATETIME` | |
| `date_modified` | ✓ | `DATETIME` | Used in outdated logic (§2.15) |

---

### 5.0.2 `gi_new_test_questions` (spec §8.2)

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | ✓ | `INT UNSIGNED PK AI` | |
| `test_id` | ✓ | `INT UNSIGNED` | FK → `gi_new_tests.id` |
| `question` | ✓ | `TEXT` | Question text |
| `explanation` | ✓ | `TEXT` | Shown on mistake |
| `reference` | ✓ | `TEXT` | Link / pointer to learning material |
| `date_added` | ✓ | `DATETIME` | |
| `date_modified` | ✓ | `DATETIME` | |

---

### 5.0.3 `gi_new_test_answers` (spec §8.3)

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | ✓ | `INT UNSIGNED PK AI` | |
| `question_id` | ✓ | `INT UNSIGNED` | FK → `gi_new_test_questions.id` |
| `answer` | ✓ | `TEXT` | Option label / body (admin content) |
| `is_correct` | ✓ (D6) | `TINYINT(1)` DEFAULT `0` | `1` = counts toward Maximum Correct Answers |
| `date_added` | ✓ | `DATETIME` | |
| `date_modified` | ✓ | `DATETIME` | |

Unlimited options per question (spec §8.3).

**Naming:** Table/column name `answer` is historical; treat rows as **answer options**, not attempt answers.

**Correct options (spec §8.3 prose):** **`is_correct`** on this table (**D6 resolved**, baseline DDL). Do not confuse with `gi_new_test_attempts.valid_answers` / `invalid_answers` (user selection counts).

API: `catalog()->answers` — `setIsCorrect`, `setCorrectAnswerIdsForQuestion`, `listCorrectAnswerIdsForQuestion`, `maximumCorrectForTest`.

---

### 5.0.4 `gi_new_test_attempts` (spec §9.1)

Completed attempts only — not in-progress (RAM).

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | ✓ | `INT UNSIGNED PK AI` | |
| `user_id` | ✓ | `INT UNSIGNED` | `gi_new_users.id_user` |
| `test_id` | ✓ | `INT UNSIGNED` | FK → `gi_new_tests.id` |
| `test_version` | ✓ | `INT UNSIGNED` | Version at time of completion |
| `valid_answers` | ✓ | `INT UNSIGNED` | Count of correct selections (whole test) |
| `invalid_answers` | ✓ | `INT UNSIGNED` | Count of incorrect selections |
| `score` | ✓ | `DECIMAL(6,2)` | Percent; formula §5.1 |
| `status` | ✓ | `ENUM` or `VARCHAR(20)` | `failed` \| `passed` \| `outdated` |
| `date_finished` | ✓ | `DATETIME` | One-year validity from this date (§2.3) |

---

### 5.0.5 `gi_new_test_attempt_questions` (spec §9.2)

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | — | `INT UNSIGNED PK AI` | Recommended surrogate key |
| `user_id` | ✓ | `INT UNSIGNED` | |
| `attempt_id` | ✓ | `INT UNSIGNED` | |
| `question_id` | ✓ | `INT UNSIGNED` | |
| `right_answers` | ✓ | `INT UNSIGNED` | Correct selections on this question |
| `failed_answers` | ✓ | `INT UNSIGNED` | Incorrect selections on this question |
| `date_finished` | ✓ | `DATETIME` | Align with parent attempt |


No per-answer row table in V1 (spec §9.3).

---

### 5.0.6 `gi_new_test_finished_attempts_explanations` (spec §10.1) - DONE

Temporary until user clicks **Examined** (§10.2).

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | ✓ | `INT UNSIGNED PK AI` | |
| `attempt_id` | ✓ | `INT UNSIGNED` | FK → `gi_new_test_attempts.id` |
| `question_id` | ✓ | `INT UNSIGNED` | Mistake questions only |
| `user_id` | ✓ | `INT UNSIGNED` | |
| `explanation` | ✓ | `TEXT` | Copy for materials UI |
| `reference` | ✓ | `TEXT` | Copy for materials UI |

---

### 5.0.7 `gi_new_test_achievements` (spec §11.1)

Global **rank level** definitions (thresholds come from config; this table stores level metadata).

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | ✓ | `INT UNSIGNED PK AI` | |
| `name` | ✓ | `VARCHAR(64)` | e.g. `Bronze`, `Silver`, `Gold`, `Lock` |
| `value` | ✓ | `VARCHAR(32)` | Display/helper; thresholds also in `gi_new_test_config` |

Example conceptual rows (spec): 

| `rank_name_gold` | e.g. `Guru` |
| `rank_name_silver` | e.g. `Expert` |
| `rank_name_bronze` | e.g. `Specialist` |
| `rank_name_failed` | e.g. `Failed` |

---

### 5.0.8 `gi_new_test_user_achievements` (spec §11.2)

Current medal per user per test (latest result drives update — §2.11).

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | — | `INT UNSIGNED PK AI` | Recommended |
| `user_id` | ✓ | `INT UNSIGNED` | |
| `test_id` | ✓ | `INT UNSIGNED` | |
| `medal_id` | ✓ | `INT UNSIGNED` | FK → `gi_new_test_achievements.id` |

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| Unique key | — | `(user_id, test_id)` | One current achievement row per user per test |

Hover text: `{rank name from config} — {test.achievement_area}`.

---

### 5.0.9 `gi_new_test_notifications` (spec §12.1)

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | ✓ | `INT UNSIGNED PK AI` | |
| `user_id` | ✓ | `INT UNSIGNED` | |
| `test_id` | ✓ | `INT UNSIGNED` | |
| `notification_type` | — | `VARCHAR(128)` | `new_test` \| `failed_test`; unique with `(user_id, test_id)` |
| `dismiss_count` | ✓ | `INT UNSIGNED DEFAULT 0` | Compared to limits in config |

One row per `(user_id, test_id, notification_type)` (Wave E).

---

### 5.0.10 `gi_new_test_config` (spec §13.1)

Generic key/value store — **no** `gi_new_test_constants` table.

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | ✓ | `INT UNSIGNED PK AI` | |
| `name` | ✓ | `VARCHAR(128) UNIQUE` | Config key |
| `value` | ✓ | `TEXT` | String value |

**Documented config keys** (spec §13.1 — store as `name`):

| Config key (`name`) | Purpose |
|---------------------|---------|
| `bronze_threshold` | Pass / bronze % (e.g. 70) |
| `silver_threshold` | Silver % (e.g. 80) |
| `gold_threshold` | Gold % (e.g. 90) |
| `lock_threshold` | Lock test at % (e.g. 95) |
| `retake_delay_days` | Days before next attempt (§2.6) |
| `new_test_notification_dismiss_limit` | Max dismissals |
| `failed_test_notification_dismiss_limit` | Max dismissals |
| `new_test_notification_mode` | e.g. `block` \| `bottom` |
| `failed_test_notification_mode` | e.g. `block` \| `bottom` |

Exact key strings can be finalized in Phase 1; list them in migration seed data.

---

### 5.0.11 No persistent user-state table (spec §14)

States **Not started** / **In progress** are not stored in MySQL:

- **In progress** → `TestController` (RAM until 03:00 restart)
- **Not started** → derived from attempts + RAM

---

### 5.0.12 ER diagram (logical)

```text
gi_new_tests
    ├── gi_new_test_questions
    │         └── gi_new_test_answers
    └── (achievement_area on test)

gi_new_test_attempts
    ├── gi_new_test_attempt_questions
    └── gi_new_test_finished_attempts_explanations 

gi_new_test_achievements
    └── gi_new_test_user_achievements (user_id + test_id → medal_id)

gi_new_test_notifications
gi_new_test_config
```

### 5.1 Scoring (spec §2.1)

```text
Maximum Correct Answers = sum over all questions of (count of correct answer options)

Net Correct = valid_answers − invalid_answers   (from user selections across test)

Score = max(0, 100 × Net Correct / Maximum Correct Answers)
```

Implement in `ScoringService` (Information Expert); **automated** unit tests for edge cases (all wrong → 0%, penalties, 100% cap) — not optional for V1.

#### 5.1.1 Multi-select selection counting (**D4 — resolved**)

Agreed clarification: [product spec §2.1.1](./Testing%20System%20%E2%80%94%20Project%20Documentation.md#211-multi-select-selection-counting).

| Event | Effect on counters |
|-------|-------------------|
| User **selects** a correct option | +1 `valid_answers` (test); +1 `right_answers` (that question) |
| User **selects** an incorrect option | +1 `invalid_answers` (test); +1 `failed_answers` (that question) |
| Correct option **not** selected | No change to `invalid_answers` (not a penalty); analytics may track “missed” separately (spec §4) |

**Official test score:** sum **raw** `valid_answers` and `invalid_answers` across all questions, then apply the formula **once** (global floor at 0%, cap 100%). Do **not** clamp per-question net before summing — that would under-apply penalties.

Per-question % for UI/analytics may use `max(0, right − failed) / correct_options_on_question` for display only unless product changes §2.1.

#### 5.1.2 Correct options: catalog (DB) vs session (RAM) (**D6**)

| Data | Storage | Purpose |
|------|---------|---------|
| Which option rows are **correct** for each question | **`gi_new_test_answers.is_correct`** (D6) | Authoring + every attempt uses the same key |
| User’s **current** checkbox selections, shuffle order | **`TestController` RAM** (Phase 4) | In-progress attempt + immediate feedback |
| Totals after user continues | **Session RAM**, then **attempt** rows on finish | `valid_answers` / `invalid_answers`; per-question `right_answers` / `failed_answers` — counts only (spec §9.2–9.3) |

RAM does **not** replace D6: without persisted correct-option keys, finish/scoring cannot be reproduced.

**D6:** `is_correct` on `gi_new_test_answers` (baseline DDL).

### 5.2 Pass / fail / stars (spec §2.2, §2.11, §5)

- **Passed** ≥ Bronze threshold (configurable).
- **Star/medal** reflects **latest** completed attempt, not historical best.
- **95%+** locks test for user (threshold configurable in `gi_new_test_config`).
- Achievement hover: `{Rank} — {Area}` (rank global, area per test).

### 5.3 Validity & outdated (spec §2.3, §2.15, §6)

- Passed result normally valid **one year** from `date_finished`.
- **Outdated** when validity rules met (including test modified after attempt + time rules per §2.15) or **Critical Update** from admin.
- Outdated → remove achievement; **keep** historical attempt rows.

### 5.4 Retake & materials (spec §2.6, §3, §21)

Retake allowed when:

```text
configured retake period elapsed (completed attempt)
AND
(if failed) temporary materials completed (“Examined”)
AND
test not locked at 95%+
AND
not blocked by mandatory notification config
```

Unfinished in-progress attempt does **not** start retake timer.

### 5.5 Randomization (spec §2.7)

- Shuffle question order and answer order per session in RAM.
- Do **not** persist order.

---

## 6. Application services (bp_knowledge_tests)

Suggested use cases (classes/names indicative):

| Service | Responsibility |
|---------|----------------|
| `TestCatalogQuery` | List tests + derived UI state for user |
| `StartTestSession` | Build session snapshot; delegate RAM to `TestController` |
| `SubmitQuestion` | Validate selections; update running totals; per-question stats in RAM |
| `CompleteAttempt` | Compute score; write attempts + attempt_questions; achievements; notifications |
| `MaterialsQuery` / `ConfirmMaterialsExamined` | Temp explanations lifecycle |
| `RetakeEligibilityQuery` | Retake + materials + lock |
| `CriticalUpdateTest` | Version bump; mark outdated; strip achievements |
| `ValidityMaintenance` | On login/catalog load: outdated transitions |
| `NotificationRepository` + `NotificationRetakePolicy` | Dismiss rows; block mode on retake |
| `AchievementRecalculation` | After config change (§5.6) |
| `AnalyticsQuery` | Test-level and question-level (§18) |

Admin CRUD wraps same repositories with capability checks.

---

## 7. bp_contracts integration

| Component | Location (proposed) | Notes |
|-----------|---------------------|--------|
| `TestController` | `bp_contracts/src/Testing/TestController.php` | In-memory `AttemptSession` per user only (no domain rules) |
| WS glue | `Chat.php` + `InteractionInterface` | Thin; call plugin services; document titles before merge |
| Role wiring | Injected **Worker** (or existing controller path) | **Avoid** new `WorkWith*` traits in `bp_contracts` (DEVELOPMENT_RULES §2.7); capability checks in plugin admin layer |
| Menu item **Tests** | PA nav + `/tests/` page | Red styling on failed (§3.2) |
| Tests & materials UI | **Vanilla JS** (`knowledge_tests.js`, overlay §19.2) | Consumes WS from plugin gateway |
| Stars in messages | `knowledge_test_chat_stars.js` + WS `getKnowledgeTestChatStars` | Achievements from plugin query |
| Notifications | PA notification host + Phase **6** UI | Config-driven block vs bottom bar; `Notification:::` where shared |

Register `TestController` in `SystemConstructor` when introduced.

---

## 8. Admin UI (V1)

**Implementation:** React admin app (test factory). WordPress may enqueue the bundle; business rules stay in `bp_knowledge_tests`. No V1 requirement for classic WP List Tables / PHP meta boxes.

Menus (spec §15):

- **All Tests** — list, version, expandable questions (contracts-admin pattern)
- **Add / Edit Test** — questions, answers, correct flags, explanation, reference, **Achievement For**
- **Critical Update** button on edit (§15.3)
- **Results** — user, test, version, score, status, dates (§17)
- **Analytics** — test + question level (§18); difficulty formula can evolve
- **Settings** — thresholds, retake, notifications, rank names (§16)

---

## 9. User UI (V1)

**Implementation:** Vanilla JS in the personal account (`bp_contracts`, dealer Tests area).

- Single **Tests** page with pagination (§19).
- Per row: title, status, Start / Materials, score, medal.
- Test flow: one question per view, counter, Continue (§20).
- Immediate feedback per question; explanations on mistakes (§20.1).
- Materials: checkboxes + **Examined** (§21).

---

## 10. Notifications (V1)

| Event | Behavior (configurable) |
|-------|-------------------------|
| New test available | Popup with dismiss limit; optional block account; then bottom bar (§3.1) |
| Failed test | Red menu; bottom notification + link to materials (§3.2) |
| Prerequisites | New test notice only if previous test completed (baseline §6) |

Store dismiss counts and policy in `gi_new_test_config` / `gi_new_test_notifications`.

---

## 11. Implementation phases

### Delivery sequence (2026-10-23)

```text
Phases 0–6  →  development complete (repo)
Phase 7     →  hardening (development)
MANUAL_QA   →  [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) on staging (single gate before production)
```

**During development:** run `tests/run_*.php` after domain changes; optional short dealer smoke after large WS/UI diffs. **Do not** block Phase 6 on full operator QA.

### Phase 0 — Documentation & schema (2–3 days)

- [x] `bp_knowledge_tests/PROJECT_CONTEXT.md` (plugin root index — extend with APIs/tables as code lands)
- [x] Link plugin from `public/PROJECT_CONTEXT.md`
- [x] `bp_contracts` testing section in PROJECT_CONTEXT (WS command list → Phase 4 live titles)
- [x] SQL migrations draft + **multi-select** scoring rules (D4 → spec §2.1.1)
- [x] [CHANGELOG.md](../../../../CHANGELOG.md) entries for implementation / doc decisions

**Exit:** ERD signed off; tables documented in plugin `PROJECT_CONTEXT` + §5 here. **D6** storage choice remains before Phase 2 coding.

### Phase 1 — Plugin skeleton & persistence (1–1.5 weeks)

- [x] Plugin bootstrap, autoload, activation migrations (`PluginBootstrap`, `SchemaDefiner`, `Seeder`)
- [x] Repositories for tests, questions, answers, config
- [x] Application catalog CRUD (`Catalog\TestCatalog` etc. via `Plugin::catalog()`)
- [x] Domain: `ScoringService`, pass/fail (`AttemptResultClassifier`), `TestConfigReader` (focused classes per §4.3)
- [x] Automated scoring tests — `tests/run_scoring_tests.php` (User Rules §8.3)
- [x] Temporary PHP smoke page — **Tools → Knowledge Tests (dev)** (removed Phase 3 Wave F; catalog via React admin)

**Exit:** CRUD verifiable without React (smoke page or WP-CLI); scoring tests green. **Met** (smoke + scoring script; smoke retired Wave F).

### Phase 2 — Attempts, materials, achievements (1.5–2 weeks)

**Task breakdown:** [PHASE_2_TASKS.md](./PHASE_2_TASKS.md) (waves A–F, checklist, open decisions).

- [x] Attempt + attempt_questions persistence (`CompleteAttempt`)
- [x] Finished explanations temp table workflow
- [x] Achievements + user achievements (`AttemptAchievementService`, repos)
- [x] Retake eligibility (`RetakeEligibilityQuery`, `tests/run_retake_eligibility_tests.php`)
- [x] Critical update + outdated (`ValidityMaintenance`, `CriticalUpdateTest`, `tests/run_validity_tests.php`)
- [x] Notifications persistence (`NotificationRepository`, retake `notification_blocked`, `notification_type` in baseline DDL)
- [x] Automated tests: complete attempt integration, achievement on complete (retake + validity covered)

**Exit:** Complete attempt lifecycle without UI (scripted).

### Phase 3 — Admin UI (React) (2–2.5 weeks)

**Task breakdown:** [PHASE_3_TASKS.md](./PHASE_3_TASKS.md) (waves A–F, readiness, checklist). *Former plan Phase 6.*

- [x] Admin HTTP API for catalog, config, critical update (capability-checked)
- [x] Capabilities + React mount (`[knowledge_tests_admin]`, Wave B)
- [x] React screens for §15 (All Tests + editor + critical update, Wave C)
- [x] Settings UI §16 (Wave D)
- [x] Critical update from admin UI
- [x] Results §17 + Analytics §18 (Wave E)
- [x] Retire dev Tools catalog dependence (Wave F)

**Exit:** Content team self-service via React admin (at least full test authoring). **Met**.

### Phase 4 — TestController & WebSocket (1.5–2 weeks)

**Task breakdown:** [PHASE_4_TASKS.md](./PHASE_4_TASKS.md) (waves A–F, checklist, cross-plugin ownership). *Former plan Phase 3.*

- [x] Wave A — WS protocol documented ([WS_PROTOCOL.md](./WS_PROTOCOL.md), D1)
- [x] Wave B — `TestController` + `AttemptSession` ([bp_contracts/src/Testing/](../../bp_contracts/src/Testing/))
- [x] Wave C — `KnowledgeTestsSessionWorker` + `bp_knowledge_tests_plugin()`
- [x] Wave D — WS commands in `Chat.php` + client handler stubs
- [x] Wave E — start / submit / finish / materials + `test_in_progress` (E4)
- [x] Wave F — doc sync; operator steps moved to [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) §2 ([PHASE_4_TASKS.md](./PHASE_4_TASKS.md))

**Exit (development):** WS commands + `TestController` in repo; round-trip provable via Tests page or documented WS steps. **Manual verification:** [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) §2 (after Phase 6–7).

### Phase 5 — User-facing UI (vanilla JS) (2 weeks)

**Task breakdown:** [PHASE_5_TASKS.md](./PHASE_5_TASKS.md) (waves A–G, checklist, D2).

- [x] Tests list + pagination (§19) — `knowledge_tests.js` + `/tests/` page
- [x] In-test flow (§20) — overlay + WS two-step feedback
- [x] Materials UI (§21) — reviewed checkboxes + **Изучено**
- [x] Stars beside names in chat (§2.11) — `getKnowledgeTestChatStars`
- [x] Failed Tests nav styling (§3.2) — `navFailedHighlight` + `.bp-ktd-nav--failed`
- [x] Wave G — development close (docs, overlay a11y); dealer journey steps in [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) §3

**Exit (development):** Dealer UI + WS wiring in repo ([PHASE_5_TASKS.md](./PHASE_5_TASKS.md)). **Manual verification:** [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) §3 (after Phase 6–7).

### Phase 6 — Notifications UX (shared PA host) (1 week)

**Task breakdown:** [PHASE_6_TASKS.md](./PHASE_6_TASKS.md) (waves A–D, **D10**).

- [x] Popup / block / bottom bar from config (§3.1, §3.2) — `NotificationUiQuery` + `knowledge_test_notifications.js`
- [x] WS `getKnowledgeTestNotifications` / `dismissKnowledgeTestNotification` (dedicated channel; not `Notification:::` chat payloads)
- [x] **D10** — one row per `(user, test, notification_type)`

**Exit (development):** Notification UX implemented; §4 scenarios in [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) ready to execute.

### Phase 7 — Hardening & release prep (1–1.5 weeks)

**Task breakdown:** [PHASE_7_TASKS.md](./PHASE_7_TASKS.md) (waves A–F, deploy checklist).

- [ ] Auth, WS validation, rate-limit considerations for new WS titles (DEVELOPMENT_RULES §1.2)
- [ ] Regression automation for product spec §28 principles + keep Phases 1–2 CLI runners in CI or documented pre-release script
- [ ] Doc sync; production deploy checklist (User Rules + DEVELOPMENT_RULES §4.1)

**Exit (development):** Code and docs ready for staging sign-off. **Release gate:** [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) complete → production deploy.

### Rough total

**~10–14 weeks** one developer familiar with `bp_contracts`; Phase **3** (admin) before **4** (WS) is the default sequence (2026-10-12).

---

## 12. Testing & QA checklist (V1)

**Operator execution:** Use [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) (sections 2–6) **after** Phase **6–7** development. This section is the rule index; checkboxes are tracked in `MANUAL_QA_V1.md` during QA.

- [ ] Score formula examples from spec §2.1
- [ ] Pass at Bronze; fail below
- [ ] 95% lock
- [ ] Retake: next calendar day vs N days config
- [ ] Failed → materials required → Examined → retake
- [ ] In-progress survives reconnect; lost after 03:00 restart
- [ ] Critical update → outdated, achievement removed, history kept
- [ ] One-year / outdated rules §2.15
- [ ] Randomization differs per session
- [ ] New test notification gating
- [ ] Latest result star, not best
- [ ] Achievement recalc on settings change

---

## 13. Risks & mitigations

| Risk | Mitigation |
|------|------------|
| RAM loss at 03:00 | UX copy; spec accepted behavior |
| `Chat.php` growth | Thin handlers; plugin services; DEVELOPMENT_RULES §2.3 containment |
| Duplicate business rules in contracts | Code review + §4.2 hard rule |
| Line-limit creep in services | Split use cases (§6); do not grow legacy monoliths |
| `gi_new_users` vs WP user mismatch | Single resolver service |
| Unauthenticated legacy endpoints | Do not add new mail endpoints; use `Mailer` pattern |
| Spec ambiguity on multi-select questions | **D4 resolved** — spec §2.1.1; see §5.1.1 |
| Correct options only in RAM | **D6** — catalog must persist keys; see §5.1.2 |

---

## 14. Open decisions (track here)

| ID | Topic | Status |
|----|-------|--------|
| D1 | Exact WS command naming | **Resolved** — [WS_PROTOCOL.md](./WS_PROTOCOL.md) (Phase 4 Wave A) |
| D2 | Which roles see Tests / admin | **Interim (Wave B):** catalog = factory `administrator` + `sales_manager` + `manage_options`; dealer Tests TBD Phase 4–5 |
| D3 | Question difficulty formula | Deferred; raw data in V1 |
| D4 | Multi-select counting (valid/invalid/missed, global vs per-question score) | **Resolved** — spec §2.1.1; engineering §5.1.1 |
| D5 | Plugin root `PROJECT_CONTEXT.md` vs spec-only docs | **Resolved:** lean root index + this plan + product spec (no duplicate column lists in root) |
| D6 | How to store “correct” answer **options** (spec §8.3 prose) | **Resolved** — `gi_new_test_answers.is_correct`; `catalog()->answers` |
| D7 | V1 UI technology | **Resolved:** **React** test-factory admin (§15–18); **vanilla JS** dealer Tests in `bp_contracts` (§19–21); plugin owns API/domain only |
| D8 | `is_correct` on `gi_new_test_answers` | **Superseded** — adopted 2026-10-05 (explicit project decision); replaces junction-table experiment |
| D9 | `gi_new_test_achievements` seed model vs rank **display** names | **Resolved** — medal rows Bronze/Silver/Gold/Lock in achievements; `rank_name_*` in `gi_new_test_config` (Seeder v3) |
| D10 | `gi_new_test_notifications` row shape | **Resolved** — `UNIQUE (user_id, test_id, notification_type)`; types `new_test` \| `failed_test` |

### 14.1 Current gates

| Priority | Item | Blocks |
|----------|------|--------|
| **Phase 7** | Hardening + deploy checklist | Production |
| **Parallel** | **D2** — dealer roles (document vs product) | Menu visibility / `startTest` policy |
| **After Phase 7** | [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) | Production deploy |
| **Deferred** | **D3** — difficulty formula | Analytics UI only |

---

## 15. Documentation maintenance

When code changes:

1. Update **lowest** relevant `PROJECT_CONTEXT.md` (User Rules §6 — relative links only).
2. Update this plan if phases or boundaries shift; keep estimates here — do not duplicate into site `PROJECT_CONTEXT`.
3. Do **not** silently edit spec §1 baseline; record decisions in §14 or [CHANGELOG.md](../../../../CHANGELOG.md) (significant changes per User Rules §9).
4. New WS titles → [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md); restart WebSocket server when protocol changes (DEVELOPMENT_RULES §4).
5. New / altered tables → [bp_knowledge_tests/PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) (columns + purpose).

---

## 16. Related links

| Document | Path |
|----------|------|
| Product specification | [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md) |
| Manual QA (post-dev) | [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) |
| Phase 6 tasks | [PHASE_6_TASKS.md](./PHASE_6_TASKS.md) |
| Site architecture | [public/PROJECT_CONTEXT.md](../../../../PROJECT_CONTEXT.md) |
| Development rules | [public/DEVELOPMENT_RULES.md](../../../../DEVELOPMENT_RULES.md) |
| Plugin index | [bp_knowledge_tests/PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) |
| Personal account | [bp_contracts/PROJECT_CONTEXT.md](../../bp_contracts/PROJECT_CONTEXT.md) |
| WebSocket commands | [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md) |
