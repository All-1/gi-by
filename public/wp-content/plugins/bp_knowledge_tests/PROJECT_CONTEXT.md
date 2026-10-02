# Component Context: bp_knowledge_tests

## Overview

WordPress plugin for **dealer knowledge tests** in the personal account. Domain logic lives here; **`bp_contracts`** provides UI shell, WebSocket transport, and in-memory `TestController` for unfinished attempts (later phases).

**Status**: Bootstrap, activation (schema v2 + seed), **admin catalog CRUD** (tests / questions / answer options / config) via catalog services. Scoring domain and automated tests deferred. No admin UI or WS yet.

## Documentation (canonical)

| Document | Purpose |
|----------|---------|
| [spec/Testing System — Project Documentation.md](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md) | Business requirements, rules, DB fields, UI (baseline §1 must not be silently changed) |
| [spec/IMPLEMENTATION_PLAN.md](./spec/IMPLEMENTATION_PLAN.md) | Architecture, phases, integration, WS proposal, engineering standards (§4.3), QA checklist |

## Integration

- [bp_contracts/PROJECT_CONTEXT.md](../bp_contracts/PROJECT_CONTEXT.md) — personal account, Ratchet, modals, planned Tests WS (see that file)
- [Site PROJECT_CONTEXT.md](../../../PROJECT_CONTEXT.md) — portal map

## Runtime (current code)

| Item | Location |
|------|----------|
| Entry | `index.php` — autoload, `PluginBootstrap::run()` (no hooks in entry) |
| Runtime | `Plugin::boot()` — hooks TBD; catalog API via `Plugin::catalog()` |
| Application API | `Application\CatalogServices` — wires catalogs/repos; pulls `DBWorker` / `DBUtilities` from `PersonalAccount\Core\Container` |
| Activation | `PluginBootstrap::activate()` — versioned schema |
| DDL + FKs | `Infrastructure\SchemaDefiner` |
| Seed | `Infrastructure\Seeder` |
| Repositories | **`DBWorker`** (reads/writes) + **`DBUtilities::packageWriteColumns`** for write payloads; rows via `RecordMapper` |
| DB dependency | **`wordpress_framework`** required (`Requires Plugins` header). `PluginBootstrap::run()` uses `global $servicesContainer` after the framework plugin file has loaded. Activation/schema uses `$wpdb` only. |
| Schema version | `Infrastructure\SchemaVersionRepository` → option `bp_knowledge_tests_schema_version` (target **2**) |
| Autoload | `composer.json` → `BpKnowledgeTests\` |

### Catalog services (admin CRUD)

| Service | Operations |
|---------|------------|
| `catalog()->tests` | `listAll`, `find`, `create`, `update`, `delete` |
| `catalog()->questions` | `listForTest`, `find`, `create`, `update`, `delete` (bumps parent test `version` on content change) |
| `catalog()->answers` | **Answer options** for a question: `listForQuestion`, `find`, `create`, `update`, `delete` (bumps test `version`) — not user attempt selections |
| `catalog()->config` | `all`, `get`, `set` |

Example: `$plugin->catalog()->tests->create('Title', 'Area')`; `$plugin->catalog()->answers->create($questionId, 'Option text')`.

## Database

All tables use prefix `gi_new_test_*` (see spec for exact table names).

- **Column lists:** [spec/IMPLEMENTATION_PLAN.md §5.0](./spec/IMPLEMENTATION_PLAN.md#50-table-index)
- **DDL reference:** [migrations/](./migrations/)
- **Business rules per table:** [spec/Testing System — Project Documentation.md §8–13](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md#8-proposed-database-tables)

### `gi_new_test_answers` (answer options)

Rows are **predefined choices** for a question (`question_id` + `answer` text). They are **not** “what the user selected” on an attempt (in-progress selections stay in RAM; completed attempts use aggregate counts per [spec §9](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md#9-completed-attempts)).

Product spec §8.3 requires marking which options are **correct** for scoring; that storage is **not** in the current DDL or catalog CRUD — add when `ScoringService` / admin “correct flags” land ([IMPLEMENTATION_PLAN §5.0.3](./spec/IMPLEMENTATION_PLAN.md#503-gi_new_test_answers-spec-83)).

**Foreign keys:** `SchemaDefiner::installForeignKeys()` / `001_baseline.sql` — `user_id` → `gi_new_users.id_user`.

**As-built note:** If tables already exist, set option below target and re-activate to apply pending steps, or run SQL `ALTER`s manually.
