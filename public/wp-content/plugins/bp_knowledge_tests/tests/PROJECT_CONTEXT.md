# Component Context: bp_knowledge_tests/tests

**CLI-only** tests — no `wp-load.php`, no WordPress bootstrap. Fast feedback for domain rules (User Rules §8.3).

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

## Child directories

| Directory | Context |
|-----------|---------|
| [support/](./support/PROJECT_CONTEXT.md) | `MemoryDBWorker`, test fixtures |

---

## Why this exists

Scoring, retake, validity, and finish flows must stay correct across refactors. These scripts assert behavior against **real service classes** with in-memory or stubbed `DBWorker` ([support/](./support/PROJECT_CONTEXT.md)), so CI or a developer can run:

```bash
cd public/wp-content/plugins/bp_knowledge_tests
php tests/run_scoring_tests.php
```

Run all critical scripts before merging changes to `Domain/` or `Services/Attempt/`.

---

## Script catalog

| Script | Validates |
|--------|-----------|
| `run_scoring_tests.php` | Global net formula, 0% floor, 100% cap |
| `run_selection_grader_tests.php` | Per-checkbox counting §2.1.1 |
| `run_achievement_tier_tests.php` | Medal / lock from score + config |
| `run_retake_eligibility_tests.php` | Delay, materials, lock, notification |
| `run_validity_tests.php` | One-year outdated |
| `run_notification_retake_tests.php` | Retake blocked by notification policy |
| `run_notification_presentation_tests.php` | Popup vs block vs bottom presentation resolver |
| `run_attempt_question_materials_tests.php` | Material rows only for mistake questions |
| `run_attempt_validity_label_tests.php` | Admin results validity column |
| `run_complete_attempt_tests.php` | Full `CompleteAttempt` integration |
| `run_complete_attempt_script.php` | Manual/debug finish helper |
| `run_catalog_capability_tests.php` | `CatalogCapability` |
| `run_admin_config_validator_tests.php` | Settings key validation |

Exit code non-zero on failure — suitable for automation.

---

## What is *not* covered here

- REST contract tests (manual or future PHPUnit with WP test suite).
- React admin E2E.
- WebSocket protocol (Phase 4 — add harness or integration tests in `bp_contracts`).

Product QA checklist: [IMPLEMENTATION_PLAN §12](../spec/IMPLEMENTATION_PLAN.md#12-testing--qa-checklist-v1).
