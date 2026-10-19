# Component Context: bp_knowledge_tests/src/Bootstrap

WordPress plugin lifecycle and dependency wiring.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) · [../../PROJECT_CONTEXT.md](../../PROJECT_CONTEXT.md)

---

## Call chain

```text
index.php
  register_activation_hook → bp_knowledge_tests_activate()
      → PluginBootstrap::activate()
  plugins_loaded (priority 11) → bp_knowledge_tests_boot()
      → resolve Container or admin notice
      → PluginBootstrap::run()
```

---

## `PluginBootstrap`

| Method | Behavior |
|--------|----------|
| `compose()` | `new CatalogServices`, `DomainServices`, `AttemptServices`, `ReportingServices` → `Plugin` |
| `run()` | `CatalogCapability::registerCaps` on `init`; schema; `compose()->boot()` |
| `activate()` | Register caps; schema only (no REST boot) |
| `installSchemaIfNeeded()` | Versioned migrations + seed — see [Infrastructure/PROJECT_CONTEXT.md](../Infrastructure/PROJECT_CONTEXT.md) |

**Dependencies:** `wpdb` + `PersonalAccount\Core\Container` from `wordpress_framework`. Without container, activation fails; runtime shows notice and skips boot.

---

## Extending wiring

When adding a new use case:

1. Implement service under `Services/`.
2. Expose via existing aggregate or new property on `Plugin` if it is a top-level API.
3. Construct in `compose()` or inside the aggregate constructor — avoid static `::getInstance()`.

Phase 4 may add a thin `KnowledgeTestsBridge` class here **only** if needed for `bp_contracts` to resolve `Plugin` from container; prefer explicit injection from WS glue.
