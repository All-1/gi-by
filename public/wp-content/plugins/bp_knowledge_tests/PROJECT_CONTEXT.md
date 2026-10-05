# Component Context: bp_knowledge_tests

## Overview

WordPress plugin for **dealer knowledge tests** in the personal account. Domain logic lives here; **`bp_contracts`** provides UI shell, WebSocket transport, and in-memory `TestController` for unfinished attempts (later phases).

**Status**: Phase **1 complete** — bootstrap, schema **v2**, catalog CRUD, domain scoring, automated scoring tests. **Product UI is React** (dealer + admin) — not in this plugin. Temporary WP **Tools → Knowledge Tests (dev)** page is smoke-only. Attempts / WS / React UI → Phase 2+.

## Documentation (canonical)

| Document | Purpose |
|----------|---------|
| [spec/Testing System — Project Documentation.md](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md) | Business requirements, rules, DB fields, UI (baseline §1 must not be silently changed) |
| [spec/IMPLEMENTATION_PLAN.md](./spec/IMPLEMENTATION_PLAN.md) | Architecture, phases, integration, WS proposal, engineering standards (§4.3), QA checklist |

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
| Runtime | `Plugin::boot()` — dev admin page; `catalog()` / `domain()` APIs |
| Services | `Services\CatalogServices`, `Services\DomainServices`, `Services\ScoringService`, `Services\Catalog\*` — wired in `PluginBootstrap::compose()` |
| Domain | `Domain\TestConfig`, `Domain\AttemptResultClassifier`, `Domain\Record\*` (rules + value objects) |
| Activation | `PluginBootstrap::activate()` — versioned schema |
| DDL + FKs | `Infrastructure\SchemaDefiner` |
| Seed | `Infrastructure\Seeder` |
| Repositories | **`DBWorker`** (reads/writes) + **`DBUtilities::packageWriteColumns`** for write payloads; rows via `RecordMapper` |
| DB dependency | **`wordpress_framework`** required (`Requires Plugins` header). `PluginBootstrap::run()` uses `global $servicesContainer` after the framework plugin file has loaded. Activation/schema uses `$wpdb` only. |
| Schema version | `Infrastructure\SchemaVersionRepository` → option `bp_knowledge_tests_schema_version` (target **2**) |
| Autoload | `composer.json` → `BpKnowledgeTests\` |
| Dev smoke (non-React) | WP Admin → **Tools → Knowledge Tests (dev)** — temporary until React admin lists catalog |
| Scoring tests | `php tests/run_scoring_tests.php` (plugin root; no WordPress) |

### Catalog services (admin CRUD)

| Service | Operations |
|---------|------------|
| `catalog()->tests` | `listAll`, `find`, `create`, `update`, `delete` |
| `catalog()->questions` | `listForTest`, `find`, `create`, `update`, `delete` (bumps parent test `version` on content change) |
| `catalog()->answers` | **Answer options**: `listForQuestion`, `find`, `create`, `update`, `delete` (bumps test `version`) — not user attempt selections |
| `catalog()->config` | `all`, `get`, `set` |

Example: `$plugin->catalog()->answers->create($questionId, 'Option text')`.

### Domain (Phase 1)

| Service | Role |
|---------|------|
| `domain()->scoring` | `calculateScore(valid, invalid, maximumCorrect)` per spec §2.1 — `maximumCorrect` supplied at runtime when correctness is known (session/admin; see D6) |
| `domain()->config` | `read()` → `TestConfig` thresholds from `gi_new_test_config` |
| `domain()->classifier` | `classify(score, TestConfig)` → pass, medal, lock |

## Database

All tables use prefix `gi_new_test_*` (see spec for exact table names).

- **Column lists:** [spec/IMPLEMENTATION_PLAN.md §5.0](./spec/IMPLEMENTATION_PLAN.md#50-table-index)
- **DDL reference:** [migrations/](./migrations/)
- **Business rules per table:** [spec/Testing System — Project Documentation.md §8–13](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md#8-proposed-database-tables)

### `gi_new_test_answers` (answer options)

Per spec **§8.3** columns: `id`, `question_id`, `answer`, `date_added`, `date_modified` only. Rows are **predefined choices** for a question, not user selections on an attempt.

How to persist “which options are correct” for scoring (spec §8.3 prose) is **not** decided yet — see IMPLEMENTATION_PLAN **D6** (open).

#### Prohibited: `is_correct`

**Do not** add, migrate, or reference a column or field named `is_correct` (or equivalent flag) on `gi_new_test_answers`. It is **not** in the product spec field list; a prior dev experiment was removed. Correct-option storage must follow an explicit **D6** decision — not this column. If a legacy DB still has `is_correct`, drop it manually ([migrations/README.md](./migrations/README.md)).

**Foreign keys:** `SchemaDefiner::installForeignKeys()` / `001_baseline.sql` — `user_id` → `gi_new_users.id_user`.

**As-built note:** If tables already exist, set option below target and re-activate to apply pending steps, or run SQL `ALTER`s manually.
