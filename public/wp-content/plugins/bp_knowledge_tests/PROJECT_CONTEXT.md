# Component Context: bp_knowledge_tests

## Overview

WordPress plugin for **dealer knowledge tests** in the personal account. Domain logic will live here; **`bp_contracts`** provides UI shell, WebSocket transport, and in-memory `TestController` for unfinished attempts (later phases).

**Status**: Minimal plugin — bootstrap, activation (schema + seed). No domain services, repositories, or automated tests in tree yet.

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
| Entry | `index.php` — `PluginBootstrap`, hooks |
| Runtime | `Plugin::boot()` (placeholder) |
| Activation | `PluginBootstrap::activate()` — versioned schema |
| DDL + FKs | `Infrastructure\SchemaDefiner` |
| Seed | `Infrastructure\Seeder` |
| Schema version | `Infrastructure\SchemaVersionRepository` → option `bp_knowledge_tests_schema_version` |
| Autoload | `composer.json` → `BpKnowledgeTests\` |

## Database

All tables use prefix `gi_new_test_*` (see spec for exact table names).

- **Column lists:** [spec/IMPLEMENTATION_PLAN.md §5.0](./spec/IMPLEMENTATION_PLAN.md#50-table-index)
- **DDL reference:** [migrations/](./migrations/)
- **Business rules per table:** [spec/Testing System — Project Documentation.md §8–13](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md#8-proposed-database-tables)

**Foreign keys:** `SchemaDefiner::installForeignKeys()` / `001_baseline.sql` — `user_id` → `gi_new_users.id_user`.

**As-built note:** If tables already exist, set option to `1` and re-activate to apply FK step (`2`), or run SQL `ALTER`s manually.
