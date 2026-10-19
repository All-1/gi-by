# Component Context: bp_knowledge_tests/src/Infrastructure/Http/Routes

One class per REST resource group; registered from `AdminRestApi`.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

| Class | Routes |
|-------|--------|
| `TestsRestRoutes` | `/tests` |
| `QuestionsRestRoutes` | `/tests/{testId}/questions`, `/questions/{id}` |
| `AnswersRestRoutes` | `/questions/{questionId}/answers`, `/answers/{id}`, `correct-answer-ids` |
| `ConfigRestRoutes` | `/config` |
| `CriticalUpdateRestRoutes` | `POST /tests/{id}/critical-update` |
| `ResultsRestRoutes` | `/results` |
| `AnalyticsRestRoutes` | `/analytics/tests`, `/analytics/tests/{id}` |

Handlers delegate to `Plugin::catalog()`, `attempts()`, `reporting()` only.
