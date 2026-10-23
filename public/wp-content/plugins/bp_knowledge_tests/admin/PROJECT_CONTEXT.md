# Component Context: bp_knowledge_tests/admin

**Test factory** React app (product spec §15–18): create tests, questions, correct options, settings, critical update, results, analytics.

Dealer test-taking UI is **not** this package — it lives in `bp_contracts` (`knowledge_tests.js`, Phase 5).

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

## Child directories

| Directory | Context |
|-----------|---------|
| [src/](./src/PROJECT_CONTEXT.md) | React SPA source (`main.tsx`, `App.tsx`, pages) |

---

## Stack

| Piece | Choice |
|-------|--------|
| Build | Vite |
| UI | React 18 + TypeScript |
| Output | [../assets/admin/app.js](../assets/admin/PROJECT_CONTEXT.md) |
| API | WordPress REST `bp-knowledge-tests/v1` — [Http PROJECT_CONTEXT](../src/Infrastructure/Http/PROJECT_CONTEXT.md) |

---

## Mount & bootstrap

1. PHP renders `<div id="bp-knowledge-tests-admin-root" data-initial-view="...">` (+ optional fallback node).
2. `AdminAppConfig` sets `window.bpKnowledgeTestsAdmin`:
   - `restUrl`, `restNonce`, `pluginVersion`
   - `adminUrls` — when set, navigation uses full page loads per WP submenu (list / settings / results / analytics).
3. `main.tsx` mounts `App` inside `AdminErrorBoundary`.

**Initial views** (`data-initial-view`): `list`, `settings`, `results`, `analytics`, `edit-new`, `edit-{id}`.

---

## In-app navigation

`App.tsx` uses local state **or** `adminUrls` redirects:

| View | Page component | Spec |
|------|----------------|------|
| List | `AllTestsPage` | §15.1 |
| Edit | `TestEditorPage` + `QuestionEditor` | §15.2 |
| Settings | `SettingsPage` | §16 |
| Results | `ResultsPage` | §17 |
| Analytics | `AnalyticsPage` | §18 |

`CriticalUpdateDialog` — double confirm before `POST /tests/{id}/critical-update`.

---

---


## Commands

```bash
cd admin
npm install
npm run build    # required after TS/TSX changes
```

Capability: `bp_manage_knowledge_tests_catalog` (see [Security](../src/Infrastructure/Security/PROJECT_CONTEXT.md)).
