# Component Context: bp_knowledge_tests/assets

Built frontend artifacts enqueued by WordPress (not hand-edited).

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)  
**Source:** [../admin/PROJECT_CONTEXT.md](../admin/PROJECT_CONTEXT.md)

## Child directories

| Directory | Context |
|-----------|---------|
| [admin/](./admin/PROJECT_CONTEXT.md) | Built test-factory bundle (`app.js`, optional `app.css`) |

## Build

```bash
cd admin && npm run build
```

Enqueue: `Infrastructure\Admin\AdminAppAssets` + `[knowledge_tests_admin]` / WP **Knowledge Tests** menu.

**Dealer** Tests UI bundles will live under `bp_contracts` (or shared package) in later phases — not here.
