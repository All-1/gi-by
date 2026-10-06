# Database migrations

**Schema version:** `4` (option `bp_knowledge_tests_schema_version`).

| Step | Version | What runs |
|------|---------|-----------|
| 1 | `1` | `SchemaDefiner::createTables()` + `Seeder::seedDefaults()` |
| 2 | `2` | `SchemaDefiner::installForeignKeys()` |
| 3 | `3` | Medal/rank display seed repair (`Seeder`) |
| 4 | `4` | `upgradeAnswersIsCorrectColumn()` — `is_correct` on `gi_new_test_answers`; drops legacy junction table if present |

| Artifact | Purpose |
|----------|---------|
| [001_baseline.sql](./001_baseline.sql) | Full DDL + FK `ALTER`s (matches [IMPLEMENTATION_PLAN §5](../spec/IMPLEMENTATION_PLAN.md#50-table-index)) |
| [004_is_correct_on_answers.sql](./004_is_correct_on_answers.sql) | v4 column reference |

**Manual DB:** If tables exist without FKs, set option to `1` and re-activate the plugin (runs pending steps), or run SQL from the migration files. Orphan `user_id` / `test_id` values must be fixed first or MySQL will reject constraints.

**User FK:** `user_id` → `gi_new_users.id_user` (personal account; not `wp_users`).

**Correct options:** `gi_new_test_answers.is_correct` (project decision 2026-10-05; supersedes junction table).

**Scoring tests (no WP):** From plugin root, `php tests/run_scoring_tests.php` and `php tests/run_selection_grader_tests.php`.
