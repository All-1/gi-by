# Component Context: bp_knowledge_tests/src/Services/Reporting

Admin read models for spec §17–18 (no write paths).

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

| Query | REST |
|-------|------|
| `ResultsListQuery` | `GET /results` |
| `TestAnalyticsQuery` | `GET /analytics/tests`, `GET /analytics/tests/{id}` |

Repositories: `AttemptAdminRepository`, `AttemptAnalyticsRepository`.

Validity display: `AttemptValidityLabel` → `valid` \| `expired` \| `not_applicable`.
