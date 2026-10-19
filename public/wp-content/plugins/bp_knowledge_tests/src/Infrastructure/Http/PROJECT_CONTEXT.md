# Component Context: bp_knowledge_tests/src/Infrastructure/Http

WordPress **REST** admin API — namespace `bp-knowledge-tests/v1`.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) · [Plugin root](../../../PROJECT_CONTEXT.md)

## Child directories

| Directory | Context |
|-----------|---------|
| [Routes/](./Routes/PROJECT_CONTEXT.md) | Per-resource `*RestRoutes` classes |

---

## Registration

`AdminRestApi::register()` on `rest_api_init` — wires all classes in [Routes/](./Routes/PROJECT_CONTEXT.md).

---

## Authentication

| Requirement | Detail |
|-------------|--------|
| Session | Logged-in WordPress user (cookie) |
| Nonce | Header `X-WP-Nonce` = `wp_create_nonce('wp_rest')` |
| Capability | `CatalogCapability::canManageCatalog()` — cap `bp_manage_knowledge_tests_catalog`, or `manage_options`, or filter `bp_knowledge_tests_can_manage_catalog` |

Unauthorized requests must not leak catalog data.

---

## JSON conventions

- Request/response bodies: **camelCase** (`achievementArea`, `isCorrect`, `correctAnswerIds`).
- Entities serialized by `ResourcePresenter` — keep field names stable for the React client in [admin/src/api/](../../../admin/src/api/PROJECT_CONTEXT.md).

---

## Route table

| Method | Route | Body (JSON) | Response |
|--------|-------|-------------|----------|
| GET | `/tests` | — | `{ tests: Test[] }` |
| POST | `/tests` | `{ title, achievementArea? }` | `{ test }` |
| GET | `/tests/{id}` | — | `{ test }` |
| PUT/PATCH | `/tests/{id}` | `{ title, achievementArea? }` | `{ test }` |
| DELETE | `/tests/{id}` | — | `{ deleted: true }` |
| GET | `/tests/{testId}/questions` | — | `{ questions }` |
| POST | `/tests/{testId}/questions` | `{ question, explanation, reference }` | `{ question }` |
| GET/PUT/DELETE | `/questions/{id}` | same fields on PUT | `{ question }` or `{ deleted }` |
| GET | `/questions/{questionId}/answers` | — | `{ answers, correctAnswerIds }` |
| POST | `/questions/{questionId}/answers` | `{ answer, isCorrect? }` | `{ answer }` |
| GET/PUT/DELETE | `/answers/{id}` | `{ answer, isCorrect? }` on PUT | `{ answer }` or `{ deleted }` |
| PUT/PATCH | `/questions/{questionId}/correct-answer-ids` | `{ answerIds: number[] }` | `{ correctAnswerIds }` |
| GET | `/config` | — | `{ settings: Record<string,string> }` |
| PUT/PATCH | `/config` | `{ name, value }` or `{ settings: {…} }` | `{ settings, achievementsRecalculated? }` |
| POST | `/tests/{id}/critical-update` | — | `{ test }` |
| GET | `/results` | Query: `testId?`, `userId?`, `status?`, `page?`, `perPage?` | `{ items, total, page, perPage }` |
| GET | `/analytics/tests` | — | `{ tests: TestAnalyticsSummary[] }` |
| GET | `/analytics/tests/{id}` | — | `{ analytics: TestAnalyticsDetail }` |

**Base URL:** `/wp-json/bp-knowledge-tests/v1`

---

## Config PATCH behavior

- Only keys in `AdminConfigKeys::ALL` are accepted.
- Changing `bronze_threshold`, `silver_threshold`, `gold_threshold`, or `lock_threshold` may set `achievementsRecalculated: true` after recalc runs.

---

## Dealer API (future)

Phase 4 may add WS-only commands or a separate REST namespace for dealers. Admin routes remain capability-gated; dealer endpoints must use personal-account auth, not factory caps.
