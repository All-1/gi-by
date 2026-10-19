# Component Context: bp_knowledge_tests/src/Infrastructure/Security

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

| Class | Role |
|-------|------|
| `CatalogCapability` | Cap `bp_manage_knowledge_tests_catalog`; `canManageCatalog()`; `registerCaps()` on activate for `administrator`, `sales_manager` |

Used by REST (`RestPermission`) and admin menu registration.

Filter: `bp_knowledge_tests_can_manage_catalog` for custom grants.

Tests: `tests/run_catalog_capability_tests.php`.
