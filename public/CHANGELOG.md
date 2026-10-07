# Project Changelog

This document tracks changes to the project. Entry format is defined in **Cursor User Rules** (CHANGELOG section); global rules are not duplicated in [DEVELOPMENT_RULES.md](./DEVELOPMENT_RULES.md).

---

## Log

### [2026-10-07] Knowledge tests: CompleteAttempt persistence (Phase 2 wave A)
**Author**: AI Assistant
**Logic**:
- Completed attempts belong in MySQL via plugin use case; in-progress stays in bp_contracts RAM (Phase 3).
- On fail, reuse existing materials workflow after attempt rows exist.
**Changes**:
- `CompleteAttempt`, `AttemptRepository`/`AttemptQuestionRepository` inserts; `AttemptServices::$complete`.
- `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md`, `PHASE_2_TASKS.md`.

### [2026-10-07] Knowledge tests: Phase 2 task breakdown doc
**Author**: AI Assistant
**Logic**:
- Phase 2 scope is large; a dedicated checklist avoids duplicating waves in IMPLEMENTATION_PLAN and tracks exit criteria for scripted lifecycle.
**Changes**:
- `bp_knowledge_tests/spec/PHASE_2_TASKS.md`: [NEW] waves A–F, checklist, open decisions.
- `spec/IMPLEMENTATION_PLAN.md`, `PROJECT_CONTEXT.md`: links to Phase 2 tasks.

### [2026-10-07] Knowledge tests: materials table `gi_new_test_finished_attempts_explanations`
**Author**: AI Assistant
**Logic**:
- Canonical name uses `gi_new_test_*` prefix in DDL, DBWorker, and spec; no rename migration (greenfield / no legacy data).
**Changes**:
- `DBWorker.php`, `SchemaDefiner.php`, `001_baseline.sql`, spec docs; removed `alignFinishedExplanationsTableName` and v5/v6 migration artifacts.

### [2026-10-07] Knowledge tests: materials filter from attempt-question counts only
**Author**: AI Assistant
**Logic**:
- Align materials question selection with D4 / §9.2: use persisted `right_answers` / `failed_answers` only; drop catalog-based `QuestionMistakeEvaluator`.
**Changes**:
- `AttemptQuestionRecord::qualifiesForMaterials()`, `MaterialsService`; removed `QuestionMistakeEvaluator`; `tests/run_attempt_question_materials_tests.php`.

### [2026-10-06] Knowledge tests: Phase 2 materials workflow (schema v5)
**Author**: AI Assistant
**Logic**:
- Implement temporary learning materials lifecycle (spec §10): populate mistake rows after failed attempts, list pending, confirm Examined deletes rows only.
- Fix explanations table name to `gi_new_finished_attempts_explanations` (aligned with spec, migrations, DBWorker).
**Changes**:
- `MaterialsService`, `AttemptServices`, attempt/explanation repositories, `QuestionMistakeEvaluator`; `Plugin::attempts()`; schema v5 migration; `tests/run_materials_mistake_tests.php`; `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md`.

### [2026-10-06] DBWorker: knowledge test table keys + getPathTable usage
**Author**: AI Assistant
**Logic**:
- Register `gi_new_test_*` tables on DBWorker properties; repositories and schema checks use keys (`KnowledgeTestAnswers`, etc.) so `tableExists` / `columnExists` / `selectUni_2` resolve consistently.
**Changes**:
- `bp_contracts/src/Workers/DBWorker.php`, `bp_knowledge_tests` repositories, `SchemaDefiner.php`, `PROJECT_CONTEXT.md`.

### [2026-10-05] Knowledge tests: D6 = is_correct on answers (schema v4)
**Author**: AI Assistant
**Logic**:
- Project decision: correct options on `gi_new_test_answers.is_correct`; remove junction table; v4 activation migrates junction rows if present.
**Changes**:
- `AnswerRecord`, `AnswerRepository`, `AnswerCatalog`, `SchemaDefiner`, `PluginBootstrap`; docs; `migrations/004_is_correct_on_answers.sql`.

### [2026-10-05] Knowledge tests: D6 correct options + selection grader (schema v3)
**Author**: AI Assistant
**Logic**:
- Persist correct answer option keys in junction table (not `is_correct` on answers); catalog API + `QuestionSelectionGrader`; fix medal vs rank-name seeding (D9).
**Changes**:
- `SchemaDefiner`, `SchemaVersionRepository`, `PluginBootstrap`, `Seeder`, `CorrectAnswerRepository`, `CorrectAnswerCatalog`, `Domain/QuestionSelectionGrader*`, `CatalogServices`, `DomainServices`.
- `migrations/002_correct_answer_options.sql`, `001_baseline.sql`, `tests/run_selection_grader_tests.php`, docs.

### [2026-10-05] Knowledge tests: D4 scoring clarifications + Phase 0 doc sync
**Author**: AI Assistant
**Logic**:
- Document agreed multi-select counting (global valid/invalid, penalties, no per-question clamp for official score) and catalog-vs-RAM correctness (D6 still open on storage shape).
- Sync implementation plan repository state, Phase 0 checkboxes, gates before Phase 2 (D9/D10); align bp_contracts React wording.
**Changes**:
- `bp_knowledge_tests/spec/Testing System — Project Documentation.md`: §2.1.1, §2.1.2.
- `bp_knowledge_tests/spec/IMPLEMENTATION_PLAN.md`: §3, §5.1.1–5.1.2, §11 Phase 0, §13–14.
- `bp_knowledge_tests/PROJECT_CONTEXT.md`, `bp_contracts/PROJECT_CONTEXT.md`.

### [2026-10-05] Knowledge tests: ban is_correct; drop v4 migration code
**Author**: AI Assistant
**Logic**:
- Document prohibition (PROJECT_CONTEXT, IMPLEMENTATION_PLAN D8); remove activation cleanup for `is_correct`; schema target back to v2.
**Changes**:
- `SchemaDefiner.php`, `PluginBootstrap.php`, `SchemaVersionRepository.php`, `migrations/README.md`, `PROJECT_CONTEXT.md`, `spec/IMPLEMENTATION_PLAN.md`.

### [2026-10-05] Knowledge tests: src layout — Services directory
**Author**: AI Assistant
**Logic**:
- Group application entry points under `src/Services/`; keep domain value objects/rules in `Domain/`, persistence in `Infrastructure/`.
**Changes**:
- Moved `CatalogServices`, `DomainServices`, `ScoringService`, `TestConfigReader`, `Catalog/*` from `Application/` to `Services/`.
- Updated `Plugin`, `PluginBootstrap`, scoring tests, `PROJECT_CONTEXT.md`.

### [2026-10-05] Knowledge tests: remove is_correct (align spec §8.3)
**Author**: AI Assistant
**Logic**:
- Product spec §8.3 lists answer columns only; `is_correct` was an mistaken implementation add-on, not specification. Schema v4 drops the column if present; catalog/API reverted.
**Changes**:
- `AnswerRecord`, repositories, catalogs, `SchemaDefiner`, baseline SQL, migrations README.
- `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md` §5.0.3, D6 open again.

### [2026-10-05] Knowledge tests: V1 UI = React (plan D7)
**Author**: AI Assistant
**Logic**:
- Product dealer + admin UIs are React; plugin stays domain/API-only. Phase 4–6 and bp_contracts integration docs updated; PHP Tools page remains dev smoke only.
**Changes**:
- `spec/IMPLEMENTATION_PLAN.md` §2, §7–9, phases 4–6, D7.
- `PROJECT_CONTEXT.md`, `DevToolsMenu.php` disclaimer.

### [2026-10-05] Knowledge tests: Phase 1 complete (scoring + is_correct)
**Author**: AI Assistant
**Logic**:
- Close Phase 1: correct-option persistence (D6), pure domain scoring/pass/lock, mandatory scoring tests, dev Tools smoke page.
- Maximum Correct Answers = count of options with `is_correct = 1` per test (D4).
**Changes**:
- Schema v3: `is_correct` on `gi_new_test_answers`; `AnswerRecord` / catalog API extended.
- `Domain\ScoringService`, `TestConfigReader`, `AttemptResultClassifier`; `Application\DomainServices`; `Plugin::domain()`.
- `tests/run_scoring_tests.php`, `Infrastructure\Admin\DevToolsMenu`.
- `PROJECT_CONTEXT.md`, `spec/IMPLEMENTATION_PLAN.md`, `migrations/README.md`, `001_baseline.sql`.

### [2026-10-02] Knowledge tests: revert container service registration
**Author**: AI Assistant
**Logic**:
- Removed `PluginServiceRegistration` / `ContainerRequire` / container keys; restored explicit wiring in `CatalogServices` and constructor-injected repos/catalogs.
**Changes**:
- Deleted registration helpers; restored `Plugin`, `PluginBootstrap::compose()`, repos, catalogs, `PROJECT_CONTEXT.md`.

### [2026-10-02] Knowledge tests: entry boot without add_action
**Author**: AI Assistant
**Logic**:
- Slim `index.php`: `Requires Plugins: wordpress_framework` so the container exists before this file runs; runtime via `PluginBootstrap::run()`.
**Changes**:
- `index.php`, `PluginBootstrap.php`, `PROJECT_CONTEXT.md`.

### [2026-10-02] Knowledge tests: compose via services container
**Author**: AI Assistant
**Logic**:
- Pass `PersonalAccount\Core\Container` into `compose()` / `CatalogServices` instead of resolving globals and wiring each service in bootstrap.
**Changes**:
- `CatalogServices.php`, `PluginBootstrap.php`, `index.php`, `PROJECT_CONTEXT.md`.

### [2026-10-01] Knowledge tests: remove is_correct; clarify answer options
**Author**: AI Assistant
**Logic**:
- `gi_new_test_answers` holds question **options**, not user selections; premature `is_correct` column confused the model. Correct-option storage deferred to scoring phase (IMPLEMENTATION_PLAN D6).
**Changes**:
- Removed `is_correct` from DDL/migration v3, repositories, `AnswerRecord`, catalog API.
- `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md` §5.0.3, `migrations/README.md`.

### [2026-10-01] Knowledge tests: catalog persistence via DBWorker
**Author**: AI Assistant
**Logic**:
- One shared DB access style across the portal; avoid duplicated SELECT strings in plugin repositories.
**Changes**:
- `DBUtilities::packageWriteColumns` (optional helper); `DBWorker` packaged `insertAssoc` / `updateAssoc` / `deleteAssoc`.
- `bp_knowledge_tests` repositories + `index.php` (`plugins_loaded`, site autoload); `PROJECT_CONTEXT.md`.

### [2026-10-01] Knowledge tests: simplify catalog layer
**Author**: AI Assistant
**Logic**:
- Replace one-class-per-operation use cases with four catalog services; keep version-bump rules on question/answer writes only.
**Changes**:
- `Application/Catalog/*`, `CatalogServices.php`; removed proxy use-case classes and `KnowledgeTestsContext`.
- `Plugin.php`, `PluginBootstrap.php`, `PROJECT_CONTEXT.md`.

### [2026-10-01] Knowledge tests: catalog CRUD use cases
**Author**: AI Assistant
**Logic**:
- Phase 1 needs persistence APIs before admin UI; keep bootstrap flat and expose use cases through `Plugin::context()`.
- Align live DDL with baseline SQL by adding `is_correct` on answers (schema version 3).
**Changes**:
- `bp_knowledge_tests/src/Application/`, `Domain/Record/`, `Infrastructure/Repository/`: tests, questions, answers, config CRUD.
- `Plugin.php`, `PluginBootstrap.php`: wire `KnowledgeTestsContext`.
- `SchemaDefiner.php`, `SchemaVersionRepository.php`: `is_correct` column migration (v3).
- `bp_knowledge_tests/PROJECT_CONTEXT.md`, `migrations/README.md`: runtime and schema status.

### [2026-10-01] Development Rules v2.5 (new-plugin bootstrap)
**Author**: AI Assistant
**Logic**:
- Codify lessons from bp_knowledge_tests scaffolding: flat bootstrap, no premature layers, instance hooks—without duplicating User Rules YAGNI/simplicity text.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: §2.8 new `bp_*` plugin bootstrap, §2.9 WP boundary vs static services; PR checklist note.

### [2026-10-01] Knowledge tests: minimal plugin tree
**Author**: AI Assistant
**Logic**:
- Keep only activation/bootstrap code until features are built; remove unused domain, repositories, and CLI scoring tests.
**Changes**:
- Removed `src/Domain/*`, `src/Infrastructure/Repository/*`, `tests/run_scoring_tests.php`.
- `PROJECT_CONTEXT.md`, `spec/IMPLEMENTATION_PLAN.md`: status and Phase 1 checklist.

### [2026-10-01] Knowledge tests: expose activation on PluginBootstrap
**Author**: AI Assistant
**Logic**:
- Remove ComposedPlugin / PluginActivation indirection; activation sequence lives on `PluginBootstrap::activate()`.
**Changes**:
- `PluginBootstrap.php`, `index.php`: direct `activate($wpdb)` hook.
- Deleted `ComposedPlugin.php`, `Infrastructure/Activation/PluginActivation.php`.

### [2026-10-01] Knowledge tests: SchemaDefiner + Seeder consolidation
**Author**: AI Assistant
**Logic**:
- Reduce activation over-engineering: one class for DDL/FKs, one for seed data; keep `PluginBootstrap::compose()` flow unchanged.
**Changes**:
- `SchemaDefiner.php`, `Seeder.php`: replace step wrappers, catalogs, and separate installers.
- Removed `SchemaTableDefinitions`, `ConfigSeeder`, `ForeignKeyCatalog`, `ForeignKeyInstaller`, `Activation/*Step.php`.
- `PluginActivation` calls `SchemaDefiner` / `Seeder` methods directly.

### [2026-09-30] Knowledge tests: flat bootstrap + activation pipeline
**Author**: AI Assistant
**Logic**:
- Avoid nesting Plugin inside factory-wired SchemaInstaller; compose services in sequence and return `ComposedPlugin`.
**Changes**:
- `Bootstrap/PluginBootstrap.php`, `ComposedPlugin.php`: compose steps, return plugin + activation.
- `Infrastructure/Activation/*`, `SchemaVersionRepository.php`: replace `SchemaInstaller` / `PluginFactory`.
- `Plugin.php`: runtime only (`boot()`); activation lives on `ComposedPlugin`.

### [2026-09-30] Knowledge tests: instance-based plugin bootstrap
**Author**: AI Assistant
**Logic**:
- Replace static schema/FK installers with constructor-injected services and `PluginFactory` wiring per composition rules.
**Changes**:
- `Plugin.php`, `PluginFactory.php`: instance `boot()` / `activate()`.
- `SchemaInstaller`, `ForeignKeyInstaller`, `SchemaTableDefinitions`, `ForeignKeyCatalog`, `ConfigSeeder`: non-static collaborators.
- `index.php`: wires hooks to plugin instance.

### [2026-09-29] Knowledge tests schema: foreign keys (v2)
**Author**: AI Assistant
**Logic**:
- Enforce referential integrity between test tables and `gi_new_users.id_user`; dbDelta does not reliably add FKs, so version 2 runs ALTER constraints on activation.
**Changes**:
- `ForeignKeyInstaller.php`: [NEW] constraint definitions.
- `SchemaInstaller.php`: schema version 2; delegates FK step.
- `migrations/001_baseline.sql`, `migrations/README.md`, `PROJECT_CONTEXT.md`, `spec/IMPLEMENTATION_PLAN.md`: FK documentation.

### [2026-09-29] Knowledge tests schema: int IDs
**Author**: AI Assistant
**Logic**:
- Align DDL with IMPLEMENTATION_PLAN §5 (`INT UNSIGNED`) instead of `bigint` for keys and FKs.
**Changes**:
- `bp_knowledge_tests/src/Infrastructure/SchemaInstaller.php`, `migrations/001_baseline.sql`: `int(10) unsigned` for integer columns.

### [2026-09-29] Knowledge tests Phase 0 close + Phase 1 skeleton
**Author**: AI Assistant
**Logic**:
- Close documentation/schema phase; align plan with User Rules and DEVELOPMENT_RULES v2.4; start plugin code without duplicating domain logic in bp_contracts.
- D4 multi-select scoring documented as sum of correct options per question (spec §2.1).
**Changes**:
- `public/wp-content/plugins/bp_knowledge_tests/`: Plugin bootstrap, `SchemaInstaller`, repositories, `ScoringService` / `TestConfigReader`, scoring test script, migrations README.
- `public/wp-content/plugins/bp_knowledge_tests/PROJECT_CONTEXT.md`: Runtime and schema status.
- `public/wp-content/plugins/bp_knowledge_tests/spec/IMPLEMENTATION_PLAN.md`: Phase 0/1 progress, D4 resolved, `is_correct` in §5.0.3.
- `public/wp-content/plugins/bp_contracts/PROJECT_CONTEXT.md`, `src/PROJECT_CONTEXT.md`: Planned Tests integration and WS stubs.
- `public/PROJECT_CONTEXT.md`: bp_knowledge_tests link path fix.

### [2026-09-25] Site context: link to DEVELOPMENT_RULES
**Author**: AI Assistant
**Logic**:
- Restore navigation from the portal map to project rules without duplicating rule text in PROJECT_CONTEXT.
**Changes**:
- `public/PROJECT_CONTEXT.md`: Related docs line with link to DEVELOPMENT_RULES.md.

### [2026-09-25] Development Rules v2.4 (project-only file)
**Author**: AI Assistant
**Logic**:
- Global rules live only in Cursor Settings; remove Part A and duplicate pointers (CHANGELOG/debugging/code size) from the repo file.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: WordPress / GeoS Ideal sections only (§0–§5); renumbered §3–§5 doc/workflow/debug.
- `public/CHANGELOG.md`: Point format to User Rules.

### [2026-09-24] Development Rules v2.3 (correct Part A / Part B split)
**Author**: AI Assistant
**Logic**:
- Part A = existing universal rules (GRASP, SOLID, composition, docs, CHANGELOG, debugging, code size, security baseline); Part B = WordPress / GeoS Ideal only. Removed abbreviated G1–G8-only Part A from v2.2.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Restructured v2.3; GRASP/SOLID/§9/§10/§11 moved to Part A only.

### [2026-09-24] Development Rules v2.2 (Part A / Part B split)
**Author**: AI Assistant
**Logic**:
- Separate **global** rules (Cursor Settings → Rules) from **GeoS Ideal project** rules in one file to avoid duplicating Part B in Settings; Part A includes G8 activation to read Part B + PROJECT_CONTEXT.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Part A (copy block G1–G8) + Part B (§0–§11 project rules).

### [2026-09-24] Development Rules v2.1 (clarity & precedence)
**Author**: AI Assistant
**Logic**:
- Remove ambiguity around canonical sources (this file vs PROJECT_CONTEXT vs Cursor User Rules), conflict precedence, restricted-file edge cases, CHANGELOG “significant”, PHP line counting, legacy containment, and when tests are expected.
- Keep security and architecture intent from v2.0; align trait/base-class gates with enforceable thresholds.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Rewritten to v2.1 (§0 precedence, §1.1–1.2 clarifications, §9 significance table, §11 counting/tiers, §8.3 tests).

### [2026-09-24] Site context: single source for development rules
**Author**: AI Assistant
**Logic**:
- Keep strict development rules in `DEVELOPMENT_RULES.md` (and Cursor User Rules) only; avoid duplicating the mandatory-read callout in the portal map.
**Changes**:
- `public/PROJECT_CONTEXT.md`: Removed DEVELOPMENT_RULES block from Overview.

### [2026-09-24] Knowledge tests implementation plan
**Author**: AI Assistant
**Logic**:
- Consolidate architecture, phases, DB summary, bp_contracts integration, and QA into one engineering document next to the product spec.
- Add plugin PROJECT_CONTEXT index and site plugin list entry for discoverability.
**Changes**:
- `public/wp-content/plugins/bp_knowledge_tests/spec/IMPLEMENTATION_PLAN.md`: [NEW] Full implementation plan and project context.
- `public/wp-content/plugins/bp_knowledge_tests/PROJECT_CONTEXT.md`: [NEW] Plugin doc index linking spec + plan.
- `public/PROJECT_CONTEXT.md`: Link to bp_knowledge_tests.

### [2026-09-24] Development Rules v2.0 (giby-2.0 adaptation)
**Author**: AI Assistant
**Logic**:
- Align site rules with giby-2.0 `DEVELOPMENT_RULES.md` (GRASP, composition, documentation hierarchy, code size, enforcement) while scoping to this WordPress-only repo (`public/`).
- Document `bp_contracts`, WebSocket, and `PROJECT_CONTEXT` nesting; remove Laravel-only references; add pragmatic legacy exceptions (`bp_zakazi`, traits).
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Rewritten to v2.0 structure with local paths and related-doc index.

### [2026-01-15] Implemented Strict Development Rules
**Author**: AI Assistant
**Logic**: 
- Validated project security requirements (`security_hook.php`) and architectural patterns (`wordpress_framework`).
- Established a single source of truth for rules to prevent legacy anti-patterns (e.g., per-user tables, god files).
- Created a mechanism to track code duplication issues between `bp_kitchen` and `bp_wardrobe`.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: [NEW] Created file with strict rules for Security, Architecture, Workflow, and Code Duplication.
- `public/PROJECT_CONTEXT.md`: Added "Primary Directive" alert linking to the new Rules file.

### [2026-01-16] Unified Changelog Format Definition
**Author**: AI Assistant
**Logic**: 
- Moved the Changelog format definition from `CHANGELOG.md` to `DEVELOPMENT_RULES.md`.
- Ensures a single source of truth for the format and reduces redundancy.
- Prevents the template in the changelog file from becoming outdated.
**Changes**:
- `public/CHANGELOG.md`: Removed "Format" section, replaced with link to Rules.
- `public/DEVELOPMENT_RULES.md`: Added "Format" definition to Section 5.

### [2026-01-16] Added SOLID Principles
**Author**: AI Assistant
**Logic**: 
- User requested explicit inclusion of SOLID principles to ensure code quality and prevent future complaints/issues.
- Strengthens the "Architecture & Code Standards" section.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Added Section 2.5 "SOLID Principles".

### [2026-01-16] Added Debugging Workflow
**Author**: AI Assistant
**Logic**: 
- User suggested using the Changelog as a debugging tool for root cause analysis.
- Formalizes a process to trace bugs by date and component using the **Changes** and **Logic** sections.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Added Section 6 "Debugging Workflow".
