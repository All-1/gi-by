# Component Context: bp_knowledge_tests/migrations

Versioned database DDL for `gi_new_test_*` tables.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

## Child directories

None (SQL and README only).

**Column reference:** [../spec/IMPLEMENTATION_PLAN.md §5.0](../spec/IMPLEMENTATION_PLAN.md#50-table-index)  
**Business meaning:** [../spec/Testing System — Project Documentation.md §8–13](../spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md#8-proposed-database-tables)

## Runtime

| Item | Location |
|------|----------|
| Install / upgrade | `src/Infrastructure/SchemaDefiner.php` (activation + boot) |
| Version option | `bp_knowledge_tests_schema_version` via `SchemaVersionRepository` |
| Target version | **3** |
| Seed data | `src/Infrastructure/Seeder.php` (achievements, default config keys) |
| SQL reference | `001_baseline.sql`, [README.md](./README.md) |

## Notes

- `user_id` columns FK → `gi_new_users.id_user` (see baseline SQL).
- `gi_new_test_answers.is_correct` — catalog correct options (D6); not user selections.
- Re-activate plugin or lower schema option to re-run pending steps on existing DBs.
