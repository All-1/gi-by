# V1 manual QA (operator checklist)

**When to run:** Once **development** for Phases **6** and **7** is complete in the repo. Do **not** treat this as a gate *before* starting Phase 6 — finish notification UX and hardening first, then execute this checklist on **staging** (or pre-production) in one or two focused sessions.

**During development (every PR that touches domain):** Run CLI runners in [tests/](../tests/PROJECT_CONTEXT.md) (`run_scoring_tests.php`, `run_retake_eligibility_tests.php`, `run_validity_tests.php`, `run_complete_attempt_tests.php`, etc.). Optional **smoke** (login → Tests → one question) after large WS/UI changes — not a substitute for this document.

**Product rules:** [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md) §2+; acceptance principles §28.

**Engineering map:** [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md) §11–12.

---

## 1. Environment

- [ ] Staging has **admin-authored** tests (React factory or REST).
- [ ] WebSocket server **restarted** after deploy ([DEVELOPMENT_RULES.md](../../../../DEVELOPMENT_RULES.md) §4).
- [ ] Test user: `gi_new_users.id_user` with **dealer Tests** access ([`DealerTestsCapability`](../../bp_contracts/)); separate admin user for factory checks if needed.

---

## 2. WebSocket & session (Phase 4)

Run in browser on personal account (Tests page or console if needed).

| # | Step | Expected |
|---|------|----------|
| 2.1 | Open Tests → list loads (`showTests`) | `displayStatus` / `canStart` sane for user with no attempts |
| 2.2 | `startTest` | `TestQuestionOnPage`; wrong answer → feedback + explanation |
| 2.3 | Submit until done → `finishTest` | `TestFinishedOnPage`; row in `gi_new_test_attempts` |
| 2.4 | Reconnect WS (same process), same test | `startTest` resumes question index |
| 2.5 | Start **other** test while one in RAM | `test_in_progress` error |
| 2.6 | After WS **process** restart | In-progress lost; `startTest` allowed per retake rules |
| 2.7 | Failed finish | Materials WS: `getTestMaterials` / `confirmTestMaterialsExamined` |

**Security spot-check:** Commands use `knowledgeTestsUserId`; no submit/finish without session.

---

## 3. Dealer UI journey (Phase 5)

| # | Step | Expected |
|---|------|----------|
| 3.1 | Nav **Tests**, pagination | §19 list; failed nav highlight when applicable (§3.2) |
| 3.2 | Start / resume in overlay (§19.2) | Open-in-place; two-step feedback (§20) |
| 3.3 | Finish pass and fail paths | Medals on list/finish; score matches spec |
| 3.4 | Fail → Materials | Checkboxes per reference; **Изучено**; retake blocked until examined |
| 3.5 | Retake after configured delay | Calendar day vs N days (§2.6) |
| 3.6 | Chat author line | Latest-result stars (§2.11), not historical best |
| 3.7 | Overlay a11y | Focus trap; Escape; keyboard **Continue** / **Далее** |

---

## 4. Notifications UX (Phase 6)

Config matrix from [IMPLEMENTATION_PLAN §10](./IMPLEMENTATION_PLAN.md#10-notifications-v1) and product §3.1–3.4.

| # | Scenario | Expected |
|---|----------|----------|
| 4.1 | New test available | Popup / dismiss limit; optional block; then bottom bar |
| 4.2 | Failed test | Red menu + bottom notification + link to materials |
| 4.3 | Prerequisites (baseline §6) | New-test notice only when prior test completed |
| 4.4 | Dismiss persistence | Rows in `gi_new_test_notifications` match **D10** decision |

---

## 5. Domain & admin (cross-cutting)

Checklist from [IMPLEMENTATION_PLAN §12](./IMPLEMENTATION_PLAN.md#12-testing--qa-checklist-v1):

- [ ] Score formula examples from spec §2.1
- [ ] Pass at Bronze; fail below
- [ ] 95% lock (§2.12)
- [ ] Failed → materials → Examined → retake
- [ ] In-progress survives reconnect; lost after **03:00** restart (§2.5)
- [ ] Critical update → outdated, achievement removed, history kept (§6)
- [ ] One-year / outdated rules §2.15
- [ ] Randomization differs per session (§2.7)
- [ ] Latest result star, not best
- [ ] Achievement recalc on settings change (§5.6)
- [ ] Admin: critical update, results §17, analytics §18, settings §16 (smoke)

---

## 6. Sign-off

| Field | Value |
|-------|--------|
| Date | |
| Environment | |
| Tester | |
| Blockers filed | (links to issues) |
| **V1 release ready** | [ ] Yes — proceed to production deploy checklist (Phase 7) |
