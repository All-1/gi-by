# Component Context: bp_knowledge_tests

## Overview

WordPress plugin for **dealer knowledge tests** in the personal account. Domain logic lives here; **`bp_contracts`** provides UI shell, WebSocket transport, and in-memory `TestController` for unfinished attempts (later phases).

**Status**: Phase **2** **complete**. Phase **3** Waves **A–C** shipped (REST, mount, §15 catalog UI). **Next:** Wave D (settings §16). Phase **4** = `TestController` + WS. Dev Tools page may retire in Wave F.

## Documentation (canonical)

| Document | Purpose |
|----------|---------|
| [spec/Testing System — Project Documentation.md](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md) | Business requirements, rules, DB fields, UI (baseline §1 must not be silently changed) |
| [spec/IMPLEMENTATION_PLAN.md](./spec/IMPLEMENTATION_PLAN.md) | Architecture, phases, integration, WS proposal, engineering standards (§4.3), QA checklist |
| [spec/PHASE_2_TASKS.md](./spec/PHASE_2_TASKS.md) | Phase 2 work breakdown (waves, checklist, exit criteria) |
| [spec/PHASE_3_TASKS.md](./spec/PHASE_3_TASKS.md) | Phase 3 work breakdown (admin UI / test factory) |
| [spec/PHASE_4_TASKS.md](./spec/PHASE_4_TASKS.md) | Phase 4 work breakdown (`TestController`, WS, plugin bridge) |

## Integration

- [bp_contracts/PROJECT_CONTEXT.md](../bp_contracts/PROJECT_CONTEXT.md) — personal account shell, Ratchet, **React mount**, planned Tests WS (see that file)
- [Site PROJECT_CONTEXT.md](../../../PROJECT_CONTEXT.md) — portal map

## UI (React)

| Layer | Owner |
|-------|--------|
| Dealer Tests + materials (§19–21) | React in personal account |
| Test-factory admin (§15–18) | React admin |
| Domain, scoring, DB, catalog API | **`bp_knowledge_tests`** (this plugin) |
| In-progress session transport | **`bp_contracts`** `TestController` + WS (Phase 4) |

This plugin does **not** ship React bundles. Expose stable application/WS contracts for the frontend; avoid new jQuery/`modal_window.js` flows for knowledge tests.

## Runtime (current code)

| Item | Location |
|------|----------|
| Entry | `index.php` — autoload fallback; `PluginBootstrap::run()` on `plugins_loaded` (11) after `wordpress_framework` sets `$servicesContainer` |
| Runtime | `Plugin::boot()` — admin REST + dev Tools; `catalog()` / `domain()` / `attempts()` APIs |
| Services | `CatalogServices`, `DomainServices`, `AttemptServices` (`CompleteAttempt`, `MaterialsService`), `ScoringService`, `Catalog\*` — wired in `PluginBootstrap::compose()` |
| Domain | `Domain\TestConfig`, `Domain\AttemptResultClassifier`, `Domain\QuestionSelectionGrader`, `Domain\Record\*` |
| Activation | `PluginBootstrap::activate()` — versioned schema |
| DDL + FKs | `Infrastructure\SchemaDefiner` |
| Seed | `Infrastructure\Seeder` |
| Repositories | **`DBWorker`** (reads/writes) + **`DBUtilities::packageWriteColumns`** for write payloads; rows via `RecordMapper`. Table args use **DBWorker keys** (e.g. `KnowledgeTestAnswers` → `gi_new_test_answers` via `getPathTable()` in [DBWorker.php](../bp_contracts/src/Workers/DBWorker.php)). |
| DB dependency | **`wordpress_framework`** required (`Requires Plugins` header). `PluginBootstrap::run()` uses `global $servicesContainer` after the framework plugin file has loaded. Activation/schema uses `$wpdb` only. |
| Schema version | `Infrastructure\SchemaVersionRepository` → option `bp_knowledge_tests_schema_version` (target **3**) |
| Autoload | `composer.json` → `BpKnowledgeTests\` |
| Dev smoke (non-React) | WP Admin → **Tools → Knowledge Tests (dev)** — temporary until React admin lists catalog |
| Scoring tests | `php tests/run_scoring_tests.php` (plugin root; no WordPress) |
| Selection grader tests | `php tests/run_selection_grader_tests.php` |
| Attempt-question materials filter tests | `php tests/run_attempt_question_materials_tests.php` |
| Achievement tier mapping tests | `php tests/run_achievement_tier_tests.php` |
| Retake eligibility tests | `php tests/run_retake_eligibility_tests.php` |
| Validity / outdated tests | `php tests/run_validity_tests.php` |
| Notification retake block tests | `php tests/run_notification_retake_tests.php` |
| Complete attempt integration (A4) | `php tests/run_complete_attempt_tests.php` (or `run_complete_attempt_script.php`) |
| Admin REST (Phase 3 Wave A) | `Infrastructure\Http\AdminRestApi` — namespace `bp-knowledge-tests/v1` |
| Catalog capability (Wave B) | `Infrastructure\Security\CatalogCapability` — `bp_manage_knowledge_tests_catalog` |
| Admin React mount (Wave B) | WP Admin **Knowledge Tests** menu (`AdminCatalogMenu`) + shortcode `[knowledge_tests_admin]`; bundle `assets/admin/app.js` (build: `admin/`) |

### Admin REST API (Phase 3 Wave A)

**Base URL:** `/wp-json/bp-knowledge-tests/v1`  
**Auth:** logged-in user with `bp_manage_knowledge_tests_catalog`, or `manage_options`, or filter `bp_knowledge_tests_can_manage_catalog`; send cookie session + `X-WP-Nonce` from `wp_create_nonce('wp_rest')`.  
**JSON:** camelCase in request/response bodies.

### Admin React mount (Phase 3 Wave B)

- WP Admin sidebar: **Knowledge Tests** (factory admin / `manage_options`; React shell).
- Optional page shortcode: **`[knowledge_tests_admin]`** (personal account).
- Global `window.bpKnowledgeTestsAdmin`: `{ restUrl, restNonce, pluginVersion }` (`restUrl` includes `/wp-json/bp-knowledge-tests/v1`).
- Rebuild after UI changes: `cd admin && npm run build` (see `admin/README.md`).

| Method | Route | Body (JSON) | Response |
|--------|-------|-------------|----------|
| GET | `/tests` | — | `{ tests: Test[] }` |
| POST | `/tests` | `{ title, achievementArea? }` | `{ test }` |
| GET | `/tests/{id}` | — | `{ test }` |
| PUT/PATCH | `/tests/{id}` | `{ title, achievementArea? }` | `{ test }` |
| DELETE | `/tests/{id}` | — | `{ deleted: true }` |
| GET | `/tests/{testId}/questions` | — | `{ questions }` |
| POST | `/tests/{testId}/questions` | `{ question, explanation, reference }` | `{ question }` |
| GET/PUT/DELETE | `/questions/{id}` | same fields on PUT | `{ question }` or `{ deleted }` |
| GET | `/questions/{questionId}/answers` | — | `{ answers, correctAnswerIds }` |
| POST | `/questions/{questionId}/answers` | `{ answer, isCorrect? }` | `{ answer }` |
| GET/PUT/DELETE | `/answers/{id}` | `{ answer, isCorrect? }` on PUT | `{ answer }` or `{ deleted }` |
| PUT/PATCH | `/questions/{questionId}/correct-answer-ids` | `{ answerIds: number[] }` | `{ correctAnswerIds }` |
| GET | `/config` | — | `{ settings: Record<string,string> }` |
| PUT/PATCH | `/config` | `{ name, value }` or `{ settings: {…} }` | `{ settings }` |
| POST | `/tests/{id}/critical-update` | — | `{ test }` (version bumped) |

`Test`, `Question`, `Answer` shapes match `Infrastructure\Http\ResourcePresenter`.

### Catalog services (admin CRUD)

| Service | Operations |
|---------|------------|
| `catalog()->tests` | `listAll`, `find`, `create`, `update`, `delete` |
| `catalog()->questions` | `listForTest`, `find`, `create`, `update`, `delete` (bumps parent test `version` on content change) |
| `catalog()->answers` | **Answer options**: `listForQuestion`, `find`, `create`, `update`, `delete`, `setIsCorrect`, `setCorrectAnswerIdsForQuestion`, `listCorrectAnswerIdsForQuestion`, `maximumCorrectForTest` |
| `catalog()->config` | `all`, `get`, `set` |

Example: `$plugin->catalog()->answers->create($questionId, 'Option text', true)`;  
`$plugin->catalog()->answers->setCorrectAnswerIdsForQuestion($questionId, [$answerId])`.

### Domain (Phase 1)

| Service | Role |
|---------|------|
| `domain()->scoring` | `calculateScore(valid, invalid, maximumCorrect)` per spec §2.1 — global net, floor 0% / cap 100%; selection rules §2.1.1 |
| `domain()->config` | `read()` → `TestConfig` thresholds from `gi_new_test_config` |
| `domain()->classifier` | `classify(score, TestConfig)` → pass, medal, lock |
| `domain()->selection` | `grade(correctAnswerIds, selectedAnswerIds)` → `QuestionSelectionCounts` (§2.1.1) |

### Attempts / materials (Phase 2)

| Service | Role |
|---------|------|
| `attempts()->complete` | `finish(userId, testId, questionLines)` → `AttemptRecord`; materials on fail; syncs user achievement |
| `attempts()->achievements` | `applyAfterComplete`, `recalculateForUserAndTest` (spec §11; latest result §2.11) |
| `attempts()->materials` | `populateFromFailedAttempt`, `listForAttempt`, `listPendingForUserAndTest`, `hasPendingMaterials`, `confirmExamined` (spec §10) |
| `attempts()->retake` | `evaluate(userId, testId)` → `RetakeEligibility` (`canStart`, reasons: `retake_delay`, `pending_materials`, `test_locked`, `notification_blocked`; spec §2.6, §2.12, §3) |
| `attempts()->notifications` | `NotificationRepository` — `findByUserTestAndType`, `resetDismissCount`, `incrementDismissCount`, `deleteByUserTestAndType` (types: `NotificationRecord::TYPE_*`); failed/pass hooks in `CompleteAttempt`; retake block via `NotificationRetakePolicy` + `TestConfigReader::notificationSettings()` |
| `attempts()->validity` | `refreshForUser`, `refreshForUserAndTest` — latest `passed` → `outdated` after one year; strip achievement (§2.3, §2.15) |
| `attempts()->criticalUpdate` | `execute(testId)` — all `passed` → `outdated`, clear test achievements, bump test `version` (§6) |

**Finish payload:** list of `['questionId' => int, 'rightAnswers' => int, 'failedAnswers' => int]` — full question set for the test. Phase 4 `TestController` builds this on `finishTest`.

## Database

All tables use prefix `gi_new_test_*` (see spec for exact table names).

- **Column lists:** [spec/IMPLEMENTATION_PLAN.md §5.0](./spec/IMPLEMENTATION_PLAN.md#50-table-index)
- **DDL reference:** [migrations/](./migrations/)
- **Business rules per table:** [spec/Testing System — Project Documentation.md §8–13](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md#8-proposed-database-tables)

### `gi_new_test_answers` (answer options)

Columns include **`is_correct`** (`tinyint(1)`, default `0`) — which predefined options count toward scoring (D6). Rows are **choices** for a question, not user attempt selections.

**Foreign keys:** `SchemaDefiner::installForeignKeys()` / `001_baseline.sql` — `user_id` → `gi_new_users.id_user`.

**As-built note:** If tables already exist, set option below target and re-activate to apply pending steps, or run SQL `ALTER`s manually.
