# Component Context: bp_knowledge_tests/spec

Product requirements and engineering plans for the knowledge testing system. **Spec prose wins over code comments** when they disagree; code should be fixed or the spec updated via an explicit decision.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

## Child directories

No subdirectories — product and phase markdown files live in this folder (see **Document map** below).

---

## Document map

| File | Audience | Contents |
|------|----------|----------|
| [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md) | Product, QA, integrators | §1 original baseline (frozen); §2+ business rules; UI §15–21; DB §8–13 |
| [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md) | Developers | Phases 0–7, architecture, full column list §5.0, WS sketch, risks, open decisions §14 |
| [PHASE_2_TASKS.md](./PHASE_2_TASKS.md) | Developers | Attempts, materials, achievements — **complete** |
| [PHASE_3_TASKS.md](./PHASE_3_TASKS.md) | Developers | Admin REST + React — **complete** |
| [PHASE_4_TASKS.md](./PHASE_4_TASKS.md) | Developers | `TestController`, WS — **next** |

---

## How spec sections map to code

| Spec area | Primary code location |
|-----------|------------------------|
| §2.1 scoring | `Domain/QuestionSelectionGrader`, `Services/ScoringService`, `CompleteAttempt` |
| §2.6 retake | `RetakeEligibilityQuery`, `MaterialsService` |
| §2.12 lock | `AttemptResultClassifier` + config `lock_threshold` |
| §2.15 outdated | `ValidityMaintenance`, `CriticalUpdateTest` |
| §6 critical update | `CriticalUpdateTest`, REST `POST .../critical-update` |
| §15–18 admin UI | `admin/src/pages/*`, REST under `Infrastructure/Http` |
| §19–21 dealer UI | **Not in this plugin yet** — `bp_contracts` Phase 5 |
| §23 RAM session | **Not in this plugin** — `bp_contracts` Phase 4 |

---

## Editing rules

1. **§1 baseline** — do not rewrite silently; use §2+ or CHANGELOG for changes.
2. **DB columns** — maintain [IMPLEMENTATION_PLAN §5.0](./IMPLEMENTATION_PLAN.md#50-table-index) when migrations change; sync [migrations/PROJECT_CONTEXT.md](../migrations/PROJECT_CONTEXT.md).
3. **WS titles** — when Phase 4 merges, update [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md) and plan §4.5 together.

---

## Phase status (at a glance)

| Phase | Scope | Status |
|-------|--------|--------|
| 1 | Plugin skeleton, catalog, scoring tests | Done |
| 2 | Complete attempt, materials, achievements | Done |
| 3 | Admin REST + React factory | Done |
| 4 | WS + TestController | Not started |
| 5 | Dealer React | Not started |
| 6 | Notifications UX | Not started |
| 7 | Hardening / release | Not started |
