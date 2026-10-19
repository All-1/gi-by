# Component Context: bp_knowledge_tests/admin/src/api

REST client for `bp-knowledge-tests/v1`.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

| File | Role |
|------|------|
| `client.ts` | `fetch` + nonce header |
| `catalog.ts` | Tests, questions, answers, critical update |
| `config.ts` | Settings GET/PATCH |
| `reporting.ts` | Results + analytics |
| `types.ts` | Shared DTOs |

Uses `window.bpKnowledgeTestsAdmin.restUrl` and `restNonce`.
