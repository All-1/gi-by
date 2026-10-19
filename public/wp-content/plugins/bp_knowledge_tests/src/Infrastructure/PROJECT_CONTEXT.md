# Component Context: bp_knowledge_tests/src/Infrastructure

I/O and framework adapters: MySQL via `DBWorker`, WordPress REST and admin UI, schema installation.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

## Child directories

| Directory | Context |
|-----------|---------|
| [Admin/](./Admin/PROJECT_CONTEXT.md) | WP menu, shortcode mount, enqueue |
| [Config/](./Config/PROJECT_CONTEXT.md) | Allowlisted `gi_new_test_config` keys |
| [Http/](./Http/PROJECT_CONTEXT.md) | REST API — route table |
| [Mapping/](./Mapping/PROJECT_CONTEXT.md) | `RecordMapper` |
| [Repository/](./Repository/PROJECT_CONTEXT.md) | `DBWorker` repositories |
| [Security/](./Security/PROJECT_CONTEXT.md) | `CatalogCapability` |

Schema classes (`SchemaDefiner`, `Seeder`, …) live in this folder root — see [migrations/PROJECT_CONTEXT.md](../../migrations/PROJECT_CONTEXT.md).

---

## Responsibilities by area

| Area | Classes | Doc |
|------|---------|-----|
| **Schema** | `SchemaDefiner`, `SchemaVersionRepository`, `Seeder` | [../../migrations/PROJECT_CONTEXT.md](../../migrations/PROJECT_CONTEXT.md) |
| **Time** | `DatabaseClock`, `ClockInterface` | Used for `date_finished`, retake delay |
| **Persistence** | `*Repository` | [Repository/PROJECT_CONTEXT.md](./Repository/PROJECT_CONTEXT.md) |
| **Mapping** | `RecordMapper` | [Mapping/PROJECT_CONTEXT.md](./Mapping/PROJECT_CONTEXT.md) |
| **HTTP API** | `AdminRestApi`, `Routes/*`, `ResourcePresenter` | [Http/PROJECT_CONTEXT.md](./Http/PROJECT_CONTEXT.md) |
| **WP Admin shell** | `AdminCatalogMenu`, `AdminAppMount`, `AdminAppAssets` | [Admin/PROJECT_CONTEXT.md](./Admin/PROJECT_CONTEXT.md) |
| **Security** | `CatalogCapability` | [Security/PROJECT_CONTEXT.md](./Security/PROJECT_CONTEXT.md) |
| **Config allowlist** | `AdminConfigKeys` | [Config/PROJECT_CONTEXT.md](./Config/PROJECT_CONTEXT.md) |

---

## Schema upgrade steps (version 3)

Executed from `PluginBootstrap::installSchemaIfNeeded()` when `bp_knowledge_tests_schema_version` &lt; target:

| Version step | Action |
|--------------|--------|
| &lt; 1 | `createTables()`, `seedDefaults()` |
| &lt; 2 | `installForeignKeys()` |
| &lt; 3 | Medal tier + rank display name seeds |

If `gi_new_tests` is missing, version is treated as 0.

---

## DBWorker table keys

Repositories pass logical names to `DBWorker` (defined in `bp_contracts` `DBWorker.php`), for example:

`KnowledgeTests`, `KnowledgeTestQuestions`, `KnowledgeTestAnswers`, `KnowledgeTestAttempts`, `KnowledgeTestAttemptQuestions`, `KnowledgeTestFinishedExplanations`, `KnowledgeTestAchievements`, `KnowledgeTestUserAchievements`, `KnowledgeTestNotifications`, `KnowledgeTestConfig`.

Physical names are always `gi_new_test_*`. Correct options use column **`is_correct`** on answers (not a separate junction table in current design).

---

## Error handling (REST)

| Component | Role |
|-----------|------|
| `RestPermission` | 401/403 when capability missing |
| `RestError` | Consistent JSON error bodies for routes |
| Route classes | Catch domain `InvalidArgumentException` → 400 where appropriate |

Details per route: [Http/Routes/PROJECT_CONTEXT.md](./Http/Routes/PROJECT_CONTEXT.md).
