# Database migrations

**Schema version:** `2` (option `bp_knowledge_tests_schema_version`).

| Step | Version | What runs |
|------|---------|-----------|
| 1 | `1` | `SchemaDefiner::createTables()` + `Seeder::seedDefaults()` |
| 2 | `2` | `SchemaDefiner::installForeignKeys()` |

| Artifact | Purpose |
|----------|---------|
| [001_baseline.sql](./001_baseline.sql) | Full DDL + FK `ALTER`s + seed (matches [IMPLEMENTATION_PLAN §5](../spec/IMPLEMENTATION_PLAN.md#50-table-index)) |
| `SchemaDefiner.php`, `Seeder.php`, `PluginBootstrap::activate()` | Applied on plugin activation |

**Manual DB:** If tables exist without FKs, set option to `1` and re-activate the plugin (runs FK step only), or run the `ALTER` block from `001_baseline.sql`. Orphan `user_id` / `test_id` values must be fixed first or MySQL will reject constraints.

**User FK:** `user_id` → `gi_new_users.id_user` (personal account; not `wp_users`).

**Legacy `is_correct` column:** Do **not** add or use `is_correct` on `gi_new_test_answers` (not in product spec §8.3). If an old dev database still has it, drop manually: `ALTER TABLE gi_new_test_answers DROP COLUMN is_correct;` — see [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md).

**Scoring tests (no WP):** From plugin root, `php tests/run_scoring_tests.php` (requires `composer install` in the plugin directory, or uses built-in autoload fallback).
