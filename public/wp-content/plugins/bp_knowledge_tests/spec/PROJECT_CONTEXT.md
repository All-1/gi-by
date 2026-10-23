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
| [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) | QA, operators | **Single manual gate** after Phase 6–7 development |
| [PHASE_2_TASKS.md](./PHASE_2_TASKS.md) | Developers | Attempts, materials, achievements — **complete** |
| [PHASE_3_TASKS.md](./PHASE_3_TASKS.md) | Developers | Admin REST + React — **complete** |
| [WS_PROTOCOL.md](./WS_PROTOCOL.md) | Integrators | Locked WS titles + JSON shapes (Phase 4 Wave A) |
| [PHASE_4_TASKS.md](./PHASE_4_TASKS.md) | Developers | `TestController`, WS — **development complete** |
| [PHASE_5_TASKS.md](./PHASE_5_TASKS.md) | Developers | Dealer vanilla JS §19–21 — **development complete** |
| [PHASE_6_TASKS.md](./PHASE_6_TASKS.md) | Developers | Notifications UX — **next (development)** |

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
| §19–21 dealer UI | `bp_contracts/js/knowledge_tests.js`, overlay, chat stars |
| §3.1–3.4 notifications UI | Phase **6** — `bp_contracts` host + plugin notification services |
| §23 RAM session | **`bp_contracts`** `TestController` + WS ([WS_PROTOCOL.md](./WS_PROTOCOL.md)) |

---

## Editing rules

1. **§1 baseline** — do not rewrite silently; use §2+ or CHANGELOG for changes.
2. **DB columns** — maintain [IMPLEMENTATION_PLAN §5.0](./IMPLEMENTATION_PLAN.md#50-table-index) when migrations change; sync [migrations/PROJECT_CONTEXT.md](../migrations/PROJECT_CONTEXT.md).
3. **WS titles** — canonical [WS_PROTOCOL.md](./WS_PROTOCOL.md); keep [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md) in sync when titles change.

---

## Phase status (at a glance)

| Phase | Scope | Status |
|-------|--------|--------|
| 0 | Docs + schema | Done |
| 1 | Plugin skeleton, catalog, scoring tests | Done |
| 2 | Complete attempt, materials, achievements | Done |
| 3 | Admin REST + React factory | Done |
| 4 | WS + TestController | Dev complete — manual §2 in [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) |
| 5 | Dealer vanilla JS (`bp_contracts`) | Dev complete — manual §3 in [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) |
| 6 | Notifications UX | **Next** — [PHASE_6_TASKS.md](./PHASE_6_TASKS.md) |
| 7 | Hardening / release prep | Not started |
| — | Manual QA (staging) | **After** Phase 6–7 — [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) |
