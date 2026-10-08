# Component Context: bp_knowledge_tests

## Overview

WordPress plugin for **dealer knowledge tests** in the personal account. Domain logic lives here; **`bp_contracts`** provides UI shell, WebSocket transport, and in-memory `TestController` for unfinished attempts (later phases).

**Status**: Phase **2** in progress — schema **v4**, `CompleteAttempt`, materials, **achievements** on finish. Validity / notifications / retake still pending. **Product UI is React** — not in this plugin. **Tools → Knowledge Tests (dev)** is smoke-only.

## Documentation (canonical)

| Document | Purpose |
|----------|---------|
| [spec/Testing System — Project Documentation.md](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md) | Business requirements, rules, DB fields, UI (baseline §1 must not be silently changed) |
| [spec/IMPLEMENTATION_PLAN.md](./spec/IMPLEMENTATION_PLAN.md) | Architecture, phases, integration, WS proposal, engineering standards (§4.3), QA checklist |
| [spec/PHASE_2_TASKS.md](./spec/PHASE_2_TASKS.md) | Phase 2 work breakdown (waves, checklist, exit criteria) |

## Integration

- [bp_contracts/PROJECT_CONTEXT.md](../bp_contracts/PROJECT_CONTEXT.md) — personal account shell, Ratchet, **React mount**, planned Tests WS (see that file)
- [Site PROJECT_CONTEXT.md](../../../PROJECT_CONTEXT.md) — portal map

## UI (React)

| Layer | Owner |
|-------|--------|
| Dealer Tests + materials (§19–21) | React in personal account |
| Test-factory admin (§15–18) | React admin |
| Domain, scoring, DB, catalog API | **`bp_knowledge_tests`** (this plugin) |
| In-progress session transport | **`bp_contracts`** `TestController` + WS (Phase 3) |

This plugin does **not** ship React bundles. Expose stable application/WS contracts for the frontend; avoid new jQuery/`modal_window.js` flows for knowledge tests.

## Runtime (current code)

| Item | Location |
|------|----------|
| Entry | `index.php` — autoload, `PluginBootstrap::run()` (no hooks in entry) |
| Runtime | `Plugin::boot()` — dev admin page; `catalog()` / `domain()` / `attempts()` APIs |
| Services | `CatalogServices`, `DomainServices`, `AttemptServices` (`CompleteAttempt`, `MaterialsService`), `ScoringService`, `Catalog\*` — wired in `PluginBootstrap::compose()` |
| Domain | `Domain\TestConfig`, `Domain\AttemptResultClassifier`, `Domain\QuestionSelectionGrader`, `Domain\Record\*` |
| Activation | `PluginBootstrap::activate()` — versioned schema |
| DDL + FKs | `Infrastructure\SchemaDefiner` |
| Seed | `Infrastructure\Seeder` |
| Repositories | **`DBWorker`** (reads/writes) + **`DBUtilities::packageWriteColumns`** for write payloads; rows via `RecordMapper`. Table args use **DBWorker keys** (e.g. `KnowledgeTestAnswers` → `gi_new_test_answers` via `getPathTable()` in [DBWorker.php](../bp_contracts/src/Workers/DBWorker.php)). |
| DB dependency | **`wordpress_framework`** required (`Requires Plugins` header). `PluginBootstrap::run()` uses `global $servicesContainer` after the framework plugin file has loaded. Activation/schema uses `$wpdb` only. |
| Schema version | `Infrastructure\SchemaVersionRepository` → option `bp_knowledge_tests_schema_version` (target **4**) |
| Autoload | `composer.json` → `BpKnowledgeTests\` |
| Dev smoke (non-React) | WP Admin → **Tools → Knowledge Tests (dev)** — temporary until React admin lists catalog |
| Scoring tests | `php tests/run_scoring_tests.php` (plugin root; no WordPress) |
| Selection grader tests | `php tests/run_selection_grader_tests.php` |
| Attempt-question materials filter tests | `php tests/run_attempt_question_materials_tests.php` |
| Achievement tier mapping tests | `php tests/run_achievement_tier_tests.php` |

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

**Finish payload:** list of `['questionId' => int, 'rightAnswers' => int, 'failedAnswers' => int]` — full question set for the test. Phase 3 `TestController` builds this on `finishTest`.

## Database

All tables use prefix `gi_new_test_*` (see spec for exact table names).

- **Column lists:** [spec/IMPLEMENTATION_PLAN.md §5.0](./spec/IMPLEMENTATION_PLAN.md#50-table-index)
- **DDL reference:** [migrations/](./migrations/)
- **Business rules per table:** [spec/Testing System — Project Documentation.md §8–13](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md#8-proposed-database-tables)

### `gi_new_test_answers` (answer options)

Columns include **`is_correct`** (`tinyint(1)`, default `0`) — which predefined options count toward scoring (D6). Rows are **choices** for a question, not user attempt selections.

**Foreign keys:** `SchemaDefiner::installForeignKeys()` / `001_baseline.sql` — `user_id` → `gi_new_users.id_user`.

**As-built note:** If tables already exist, set option below target and re-activate to apply pending steps, or run SQL `ALTER`s manually.
