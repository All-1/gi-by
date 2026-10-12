# Database migrations

**Schema version:** `3` (option `bp_knowledge_tests_schema_version`).

| Step | Version | What runs |
|------|---------|-----------|
| 1 | `1` | `SchemaDefiner::createTables()` + `Seeder::seedDefaults()` |
| 2 | `2` | `SchemaDefiner::installForeignKeys()` |
| 3 | `3` | Medal/rank display seed repair (`Seeder`) |

| Artifact | Purpose |
|----------|---------|
| [001_baseline.sql](./001_baseline.sql) | Full DDL + FK `ALTER`s (`is_correct`, `notification_type`; matches [IMPLEMENTATION_PLAN §5](../spec/IMPLEMENTATION_PLAN.md#50-table-index)) |

**Manual DB:** If tables exist without FKs, set option to `1` and re-activate the plugin (runs pending steps), or run SQL from `001_baseline.sql`. No `ALTER` upgrade steps for greenfield schema. Orphan `user_id` / `test_id` values must be fixed first or MySQL will reject constraints.

**User FK:** `user_id` → `gi_new_users.id_user` (personal account; not `wp_users`).

**Correct options:** `gi_new_test_answers.is_correct` (project decision 2026-10-05; supersedes junction table).

**Scoring tests (no WP):** From plugin root, `php tests/run_scoring_tests.php`, `php tests/run_selection_grader_tests.php`, `php tests/run_attempt_question_materials_tests.php`, `php tests/run_achievement_tier_tests.php`, `php tests/run_retake_eligibility_tests.php`, `php tests/run_validity_tests.php`, `php tests/run_notification_retake_tests.php`.
