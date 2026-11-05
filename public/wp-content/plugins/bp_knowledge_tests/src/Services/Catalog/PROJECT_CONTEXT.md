# Component Context: bp_knowledge_tests/src/Services/Catalog

Test-factory CRUD — bumps `gi_new_tests.version` when the test record, a question, or an answer changes, and on critical update.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

| Service | Operations |
|---------|------------|
| `TestCatalog` | `listAll`, `find`, `create`, `update`, `delete` |
| `QuestionCatalog` | per-test questions CRUD |
| `AnswerCatalog` | answer **options**; `setCorrectAnswerIdsForQuestion`, `maximumCorrectForTest` |
| `ConfigCatalog` | `gi_new_test_config` get/set |
| `ConfigSaveService` | batch settings + achievement recalc trigger |
| `AdminConfigValidator` | allowlisted keys / value validation |

HTTP: [../../Infrastructure/Http/PROJECT_CONTEXT.md](../../Infrastructure/Http/PROJECT_CONTEXT.md).
