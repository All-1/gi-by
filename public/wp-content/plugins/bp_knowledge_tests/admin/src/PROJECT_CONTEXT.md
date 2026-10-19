# Component Context: bp_knowledge_tests/admin/src

React SPA entry and view routing for the test factory.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) · [Plugin root](../../PROJECT_CONTEXT.md)

## Child directories

| Directory | Context |
|-----------|---------|
| [api/](./api/PROJECT_CONTEXT.md) | REST client and DTO types |
| [components/](./components/PROJECT_CONTEXT.md) | Shared UI (editor, dialogs, subnav) |
| [config/](./config/PROJECT_CONTEXT.md) | Settings form schema |
| [pages/](./pages/PROJECT_CONTEXT.md) | §15–18 screens |
| [styles/](./styles/PROJECT_CONTEXT.md) | `admin.css` |

---

## Entry

| File | Role |
|------|------|
| `main.tsx` | Mount `#bp-knowledge-tests-admin-root`, error fallback after 4s if bundle stuck |
| `App.tsx` | View state machine: list / edit / settings / results / analytics |
| `App.tsx` + `AdminSubnav` | Cross-links; uses `adminUrls` for WP multi-page mode |

There is **no react-router** — navigation is `useState<View>` or full page via `window.bpKnowledgeTestsAdmin.adminUrls`.

---

## Data flow

```text
Page component
    → api/catalog.ts | config.ts | reporting.ts
        → api/client.ts (fetch + X-WP-Nonce)
            → REST bp-knowledge-tests/v1
                → PHP Routes → Plugin::catalog() | reporting() | attempts()
```

Types in `api/types.ts` should stay aligned with `Infrastructure\Http\ResourcePresenter` on the server.

---

## Editing checklist

1. Change TS → `npm run build` in `admin/`.
2. If new settings keys → update `settingsSchema.ts` **and** `AdminConfigKeys.php`.
3. New REST fields → update `types.ts` + PHP `ResourcePresenter` + route handler.
