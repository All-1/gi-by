# Knowledge Tests — Implementation Plan & Project Context

**Plugin**: `bp_knowledge_tests`  
**Companion spec (requirements baseline)**: [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md)  
**Site rules**: [DEVELOPMENT_RULES.md](../../../../DEVELOPMENT_RULES.md) (WordPress / `public/` — not a copy of global rules)  
**Global standards**: Cursor **Settings → Rules** (security, GRASP/SOLID, code size §11, CHANGELOG, automated tests, PR checklist)  
**Plugin context**: [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)  
**Personal account integration**: [bp_contracts/PROJECT_CONTEXT.md](../../bp_contracts/PROJECT_CONTEXT.md)

**Status (repository)**: Specification + plugin `PROJECT_CONTEXT` index — no plugin bootstrap, migrations, or `TestController` in `bp_contracts` yet.  
**Last updated**: 2026-09-25

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
| **V1 UI pattern** | Reuse personal-account **open-in-place / modal** mechanism (contracts-style) |
| **Future** | API-shaped layers inside WP plugin for later Laravel + PostgreSQL + React + Reverb (spec §24–25) |

---

## 3. Current repository state

```
bp_knowledge_tests/
├── PROJECT_CONTEXT.md                              ← short index (canonical links)
└── spec/
    ├── Testing System — Project Documentation.md   ← product / business spec
    └── IMPLEMENTATION_PLAN.md                        ← this file
```

**Not yet present (to be created during implementation):**

- `index.php`, `composer.json`, `src/`, migrations
- Expanded plugin `PROJECT_CONTEXT.md` (table columns, public APIs, capabilities) as code lands
- Entry in `public/PROJECT_CONTEXT.md` plugin list
- `bp_contracts` `TestController`, WS commands, `tests.js`, admin menus

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
User Interface (JS + shortcodes)
        ↓
Testing Interface (WS handlers / thin controllers)
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

**Proposed client → server commands (names to finalize in Phase 3):**

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
| `gi_new_test_answers` | Answer options + correct flag |
| `gi_new_test_attempts` | **Completed** attempts only |
| `gi_new_test_attempt_questions` | Per-question stats per completed attempt |
| `gi_new_finished_attempts_explanations` | Temporary materials rows until “Examined” |
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
| `date_creation` | ✓ | `DATETIME` | |
| `date_modified` | ✓ | `DATETIME` | Used in outdated logic (§2.15) |

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `achievement_area` | UI only (§15.2) | `VARCHAR(255) NULL` | **“Achievement For”** — second part of hover text (`Guru — PostgreSQL`). Not listed in §8.1 field block; **required by admin UI** — add on test row. |

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
| `answer` | ✓ | `TEXT` | Answer text |
| `date_added` | ✓ | `DATETIME` | |
| `date_modified` | ✓ | `DATETIME` | |

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `is_correct` | Implied §8.3 | `TINYINT(1) NOT NULL DEFAULT 0` | **Required for scoring** but omitted from spec field list — must exist in implementation. |

Unlimited answers per question (spec §8.3).

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
| `user_id` | ✓ | `INT UNSIGNED` | |
| `test_id` | ✓ | `INT UNSIGNED` | |
| `question_id` | ✓ | `INT UNSIGNED` | |
| `right_answers` | ✓ | `INT UNSIGNED` | Correct selections on this question |
| `failed_answers` | ✓ | `INT UNSIGNED` | Incorrect selections on this question |
| `date_finished` | ✓ | `DATETIME` | Align with parent attempt |

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | — | `INT UNSIGNED PK AI` | Recommended surrogate key |
| `attempt_id` | — | `INT UNSIGNED` | **Recommended** FK → `gi_new_test_attempts.id` (spec lists only user/test/question; link to attempt avoids ambiguity) |

No per-answer row table in V1 (spec §9.3).

---

### 5.0.6 `gi_new_finished_attempts_explanations` (spec §10.1)

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

Example conceptual rows (spec): `Bronze 75%`, `Silver 85%`, `Gold 95%`, `Lock 95%` — actual thresholds are admin-configurable via config keys.

---

### 5.0.8 `gi_new_test_user_achievements` (spec §11.2)

Current medal per user per test (latest result drives update — §2.11).

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `user_id` | ✓ | `INT UNSIGNED` | |
| `test_id` | ✓ | `INT UNSIGNED` | |
| `medal_id` | ✓ | `INT UNSIGNED` | FK → `gi_new_test_achievements.id` |

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | — | `INT UNSIGNED PK AI` | Recommended |
| Unique key | — | `(user_id, test_id)` | One current achievement row per user per test |

Hover text: `{rank name from config} — {test.achievement_area}`.

---

### 5.0.9 `gi_new_test_notifications` (spec §12.1)

| Column | Spec | Suggested SQL type | Notes |
|--------|------|-------------------|--------|
| `id` | ✓ | `INT UNSIGNED PK AI` | |
| `user_id` | ✓ | `INT UNSIGNED` | |
| `test_id` | ✓ | `INT UNSIGNED` | |
| `dismiss_count` | ✓ | `INT UNSIGNED DEFAULT 0` | Compared to limits in config |

Consider `notification_type` (`new_test` \| `failed_test`) if one row per user/test is insufficient — **not in spec**; add only if product confirms.

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
| `rank_name_gold` | e.g. `Guru` |
| `rank_name_silver` | e.g. `Expert` |
| `rank_name_bronze` | e.g. `Specialist` |
| `rank_name_failed` | e.g. `Failed` |

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
    └── gi_new_finished_attempts_explanations (temporary)

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
| `NotificationService` | New test / failed test / dismiss counts / block mode |
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
| Menu item **Tests** | `index.php` shortcode + nav | Red styling on failed (§3.2) |
| Modal UI | `tests.js` + `modal_window.js` patterns | §19.2 |
| Stars in messages | Message author rendering | Load achievements from plugin query |
| Notifications | Extend `notification.js` / WS `Notification` | Config-driven block vs bottom bar |

Register `TestController` in `SystemConstructor` when introduced.

---

## 8. Admin UI (V1)

Menus (spec §15):

- **All Tests** — list, version, expandable questions (contracts-admin pattern)
- **Add / Edit Test** — questions, answers, correct flags, explanation, reference, **Achievement For**
- **Critical Update** button on edit (§15.3)
- **Results** — user, test, version, score, status, dates (§17)
- **Analytics** — test + question level (§18); difficulty formula can evolve
- **Settings** — thresholds, retake, notifications, rank names (§16)

---

## 9. User UI (V1)

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

### Phase 0 — Documentation & schema (2–3 days)

- [x] `bp_knowledge_tests/PROJECT_CONTEXT.md` (plugin root index — extend with APIs/tables as code lands)
- [ ] Link plugin from `public/PROJECT_CONTEXT.md`
- [ ] `bp_contracts` testing section in PROJECT_CONTEXT + WS command list
- [ ] SQL migrations draft + review **multi-select** scoring rules with stakeholders
- [ ] [CHANGELOG.md](../../../../CHANGELOG.md) entry when implementation starts (User Rules §9 format)

**Exit:** ERD signed off; tables documented in plugin `PROJECT_CONTEXT` + §5 here.

### Phase 1 — Plugin skeleton & persistence (1–1.5 weeks)

- [ ] Plugin bootstrap, autoload, activation migrations
- [ ] Repositories for tests, questions, answers, config
- [ ] Domain: `ScoringService`, pass/fail, config reader (focused classes per §4.3)
- [ ] PHPUnit (or project-standard test runner) — **scoring + pass/fail** tests required before Phase 1 exit

**Exit:** CRUD via admin stub or WP-CLI; scoring tests green.

### Phase 2 — Attempts, materials, achievements (1.5–2 weeks)

- [ ] Attempt + attempt_questions persistence
- [ ] Finished explanations temp table workflow
- [ ] Achievements + user achievements
- [ ] Critical update + outdated + validity job on catalog load
- [ ] Notifications persistence
- [ ] Automated tests: validity/outdated, retake eligibility, achievement update on complete

**Exit:** Complete attempt lifecycle without UI (scripted).

### Phase 3 — TestController & WebSocket (1.5–2 weeks)

- [ ] `TestController` + session model
- [ ] WS commands documented and implemented
- [ ] Plugin called from `InteractionInterface` only for domain ops
- [ ] Randomization + per-question feedback payloads

**Exit:** End-to-end test over WS on staging.

### Phase 4 — User-facing UI (2 weeks)

- [ ] Shortcode, `tests.js`, modal integration
- [ ] Materials UI
- [ ] Stars in chat
- [ ] Failed menu styling

**Exit:** Full dealer journey QA.

### Phase 5 — Notifications UX (1 week)

- [ ] Popup / block / bottom bar from config
- [ ] Integration with existing notification stack

**Exit:** Config matrix tested.

### Phase 6 — Admin UI (2–2.5 weeks)

- [ ] All admin screens §15–18
- [ ] Critical update tested

**Exit:** Content team self-service.

### Phase 7 — Hardening & release (1–1.5 weeks)

- [ ] Auth, WS validation, rate-limit considerations for new WS titles (DEVELOPMENT_RULES §1.2)
- [ ] Regression suite for rules §28 (product spec) + automated domain tests from Phases 1–2
- [ ] Doc sync; PR checklist (User Rules + DEVELOPMENT_RULES §4.1)

**Exit:** Production deploy checklist.

### Rough total

**~10–14 weeks** one developer familiar with `bp_contracts`; Phases 1 and 6 can overlap partially.

---

## 12. Testing & QA checklist (V1)

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
| Spec ambiguity on multi-select questions | Clarify before Phase 1 exit |

---

## 14. Open decisions (track here)

| ID | Topic | Status |
|----|-------|--------|
| D1 | Exact WS command naming | Proposed §4.5 — finalize in Phase 3 |
| D2 | Which roles see Tests / admin | TBD with product |
| D3 | Question difficulty formula | Deferred; raw data in V1 |
| D4 | Maximum Correct Answers for multi-select per question | Confirm with spec owners |
| D5 | Plugin root `PROJECT_CONTEXT.md` vs spec-only docs | **Resolved:** lean root index + this plan + product spec (no duplicate column lists in root) |

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
| Site architecture | [public/PROJECT_CONTEXT.md](../../../../PROJECT_CONTEXT.md) |
| Development rules | [public/DEVELOPMENT_RULES.md](../../../../DEVELOPMENT_RULES.md) |
| Plugin index | [bp_knowledge_tests/PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) |
| Personal account | [bp_contracts/PROJECT_CONTEXT.md](../../bp_contracts/PROJECT_CONTEXT.md) |
| WebSocket commands | [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md) |
