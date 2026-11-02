**Prerequisite:** Phases **1–5** **development complete** in repo ([PHASE_5_TASKS.md](./PHASE_5_TASKS.md)). Manual QA is **not** a prerequisite — run [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) after Phase **6** + **7** development.

# Phase 6 — Task breakdown (Notifications UX)

**Primary owner:** `bp_contracts` (popup / bottom bar / shared `Notification:::` host, nav block mode)  
**Domain owner:** `bp_knowledge_tests` (`NotificationRepository`, retake block policy, config reads — no duplicate scoring)  
**Parent plan:** [IMPLEMENTATION_PLAN.md §10–11](./IMPLEMENTATION_PLAN.md)  
**Requirements:** [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md) §3.1–3.4, §10 (plan), baseline §6 prerequisites  
**Live status index:** [spec/PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md)

**Status:** **Development complete** (2026-10-23). Operator verification → [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) §4 only (after Phase 7 dev).

**Goal (development exit):** Config-driven **new-test** and **failed-test** notification UX (popup, dismiss limits, optional account block, bottom bar) integrated with existing PA notification patterns. Persistence hooks from Phase 2 (`CompleteAttempt`, `gi_new_test_notifications`) wired to UI.

**Estimate:** ~1 week one developer (plan §11).

---

## Readiness

| Ready | Decide / implement first |
|-------|---------------------------|
| Notification rows written on complete / retake policy | **D10** — one row per `(user, test, notification_type)` vs single row per `(user, test)` |
| `gi_new_test_config` notification fields | Product §16 settings already in admin React |
| `NotificationWorker` / `Notification:::` in PA | How knowledge-test payloads merge with existing JSON |

**Resolve D10** in [IMPLEMENTATION_PLAN §14](./IMPLEMENTATION_PLAN.md#14-open-decisions-track-here) before merging schema-dependent UI.

---

## Wave A — D10 + read API

- Document row shape in plugin `PROJECT_CONTEXT` + migrations note if DDL changes.
- Expose dismiss / state reads for contracts (thin WS or REST — prefer existing notification channel if sufficient).

## Wave B — New test (§3.1)

- Popup + dismiss counter; optional block; fallback bottom bar.
- Prerequisite gating (baseline §6).

## Wave C — Failed test (§3.2)

- Complement existing `navFailedHighlight` with bottom notification + materials link.
- Align with retake / materials-not-reviewed (§3.3–3.4).

## Wave D — Close Phase 6 (development)

- [x] IMPLEMENTATION_PLAN §11 Phase 6 checkboxes
- [x] Lowest `PROJECT_CONTEXT.md` updates (`bp_contracts`, plugin Attempt services)
- [x] Operator steps → [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) §4 (not duplicated here)

---

## Out of scope

| Item | Phase |
|------|--------|
| Full production deploy, rate-limit audit | **7** |
| Consolidated operator pass (WS + dealer + notifications + §12) | [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) |

---

## Task checklist (copy for PRs)

- [x] A D10 decided + documented
- [x] A Read/dismiss API at plugin boundary (`NotificationUiQuery`, WS)
- [x] B New-test popup / block / bottom bar
- [x] C Failed-test bottom notification + materials link
- [x] D Docs + MANUAL_QA §4 (operator steps live in MANUAL_QA only)
