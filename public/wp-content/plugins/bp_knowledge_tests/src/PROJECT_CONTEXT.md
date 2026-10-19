# Component Context: bp_knowledge_tests/src

PHP application (`BpKnowledgeTests\` namespace). This is the **implementation** of the testing domain; product rules remain in the [spec](../spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md).

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

## Child directories

| Directory | Context |
|-----------|---------|
| [Bootstrap/](./Bootstrap/PROJECT_CONTEXT.md) | WordPress hooks, activation, `compose()` |
| [Domain/](./Domain/PROJECT_CONTEXT.md) | Pure rules and value objects |
| [Services/](./Services/PROJECT_CONTEXT.md) | Use cases (`catalog`, `attempts`, …) |
| [Infrastructure/](./Infrastructure/PROJECT_CONTEXT.md) | DB, REST, admin mount, schema |

---

## Layering rules

| Layer | May depend on | Must not |
|-------|----------------|----------|
| **Domain** | Other domain types | WordPress, `$wpdb`, HTTP, repositories |
| **Services** | Domain, repositories (via ctor), other services | `$_GET`, REST specifics |
| **Infrastructure** | Services (only from HTTP/admin entry), Domain records | Business formulas duplicated from Domain |
| **Plugin.php** | All facades | Grow with feature logic — keep as accessor + `boot()` |

New use cases → new small service class under `Services/` (≤120 lines per project rules), wired in `*Services` aggregate or `PluginBootstrap::compose()`.

---

## File roles

| Path | Responsibility |
|------|----------------|
| `Plugin.php` | `boot()`, `catalog()`, `domain()`, `attempts()`, `reporting()` |
| `Bootstrap/PluginBootstrap.php` | Activation schema steps, `compose()`, `run()` |
| `Domain/` | Scoring math, classification, value objects — [Domain/PROJECT_CONTEXT.md](./Domain/PROJECT_CONTEXT.md) |
| `Services/` | Orchestration — [Services/PROJECT_CONTEXT.md](./Services/PROJECT_CONTEXT.md) |
| `Infrastructure/` | Schema, repos, REST, WP admin — [Infrastructure/PROJECT_CONTEXT.md](./Infrastructure/PROJECT_CONTEXT.md) |

---

## Composition graph

```text
PluginBootstrap::compose()
    CatalogServices(container)
    DomainServices(catalog)          ← reads config via catalog
    AttemptServices(container, catalog, domain)
    ReportingServices(container, catalog)
    → Plugin
```

`AttemptServices` constructs repositories internally (not global singletons) to keep wiring visible in one place.

---

## External dependencies

| Dependency | Usage |
|------------|--------|
| `PersonalAccount\Core\Container` | Resolve `DBWorker`, `DBUtilities` |
| `DBWorker` table keys | `KnowledgeTests`, `KnowledgeTestQuestions`, `KnowledgeTestAnswers`, … |
| `$wpdb` | `SchemaDefiner` / `Seeder` only on activation path |

---

## Phase 4 touchpoints (where WS will call in)

| Operation | Service entry |
|-----------|----------------|
| Grade one question | `domain()->selection->grade($correctIds, $selectedIds)` |
| Finish test | `attempts()->complete->finish($userId, $testId, $questionLines)` |
| Can start? | `attempts()->retake->evaluate($userId, $testId)` |
| Materials | `attempts()->materials` |
| List tests + state | New query or catalog + attempt repos (to be added with WS) |

Do not add scoring in `bp_contracts` — pass selected ids and let the plugin grade.
