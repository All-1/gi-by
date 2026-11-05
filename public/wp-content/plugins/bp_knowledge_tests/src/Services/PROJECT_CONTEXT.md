# Component Context: bp_knowledge_tests/src/Services

Application **use cases** — the only layer Phase 4 WS and REST should call for business behavior.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) · [Plugin root](../../PROJECT_CONTEXT.md)

## Child directories

| Directory | Context |
|-----------|---------|
| [Catalog/](./Catalog/PROJECT_CONTEXT.md) | Test-factory CRUD and config |
| [Attempt/](./Attempt/PROJECT_CONTEXT.md) | Finish, materials, retake, validity |
| [Reporting/](./Reporting/PROJECT_CONTEXT.md) | Admin results and analytics queries |

---

## Facades on `Plugin`

| Method | Aggregate | Public properties / entry points |
|--------|-----------|----------------------------------|
| `catalog()` | `CatalogServices` | `tests`, `questions`, `answers`, `config` |
| `domain()` | `DomainServices` | `scoring`, `config`, `classifier`, `selection` |
| `attempts()` | `AttemptServices` | `complete`, `materials`, `achievements`, `retake`, `validity`, `criticalUpdate`, `notificationUi`, `standing` |
| `reporting()` | `ReportingServices` | `results`, `analytics` (via query objects) |

---

## Subpackages

| Folder | Responsibility | Doc |
|--------|----------------|-----|
| `Catalog/` | Authoring CRUD, version bumps, config save + validation | [Catalog/PROJECT_CONTEXT.md](./Catalog/PROJECT_CONTEXT.md) |
| `Attempt/` | Finish, materials, medals, retake, outdated, critical update | [Attempt/PROJECT_CONTEXT.md](./Attempt/PROJECT_CONTEXT.md) |
| `Reporting/` | Read-only admin §17–18 | [Reporting/PROJECT_CONTEXT.md](./Reporting/PROJECT_CONTEXT.md) |
| (root) | `ScoringService`, `TestConfigReader`, `DomainServices` wiring | [Domain/PROJECT_CONTEXT.md](../Domain/PROJECT_CONTEXT.md) |

---

## Design notes

- **Catalog writes** bump `gi_new_tests.version` when the test, a question, or an answer changes. Critical update bumps it too. Attempts store `test_version` at finish time.
- **Config changes** to bronze/silver/gold/lock may trigger achievement recalculation via `ConfigSaveService` (REST PATCH `/config`).
- Repositories are constructed inside aggregates (`AttemptServices`, `CatalogServices`) rather than a global service locator.
- `AttemptServices` only wires use cases. Latest attempt, medal, and rank name are `standing->forUserAndTest`. `NotificationRepository` stays inside that wiring.

---

## Who calls Services?

| Caller | Examples |
|--------|----------|
| `Infrastructure\Http\Routes\*` | All admin REST |
| Future `bp_contracts` WS glue | `complete`, `retake`, `materials` |
| CLI tests | Direct instantiation or fixtures in `tests/support/` |

Never duplicate formulas in callers — if REST needs a new field, extend `ResourcePresenter` or a dedicated query class.
