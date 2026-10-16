# Phase 3 — Task breakdown (Admin UI / test factory)

**Plugin (domain):** `bp_knowledge_tests`  
**Shell (React mount, nav):** `bp_contracts` or shared personal-account admin host (same pattern as other admin screens)  
**Parent plan:** [IMPLEMENTATION_PLAN.md §8 / §11](./IMPLEMENTATION_PLAN.md#8-admin-ui-v1)  
**Requirements:** [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md) (§15–18, §16 settings)  
**Live status index:** [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

**Goal (exit criterion):** Content/admin users can **create and edit tests** (questions, answers, `is_correct`, explanations, references), adjust **settings** (§16), run **Critical Update**, and retire **Tools → Knowledge Tests (dev)** for catalog work. **Results (§17)** and **Analytics (§18)** may ship in Phase 3b or early Phase 5 if timeboxed — minimum exit is **catalog + settings + critical update**.

**Progress (2026-10-16):** Waves **A–E** done. **Next:** Wave F (retire dev Tools, doc sync).

**Estimate:** ~2–2.5 weeks one developer (full §15–18); ~1–1.5 weeks for **MVP** (§15 + §16 + critical update only).

---

## Readiness (can we implement now?)

| Ready | Not ready yet |
|-------|----------------|
| Domain **catalog** CRUD (`catalog()->tests`, `questions`, `answers`, `config`) | **No HTTP/REST/AJAX application API** for React (plugin is in-process PHP only) |
| **Critical update** use case (`attempts()->criticalUpdate->execute`) | **No React app** or route mount for knowledge-tests admin in repo |
| Config thresholds / notification keys in DB + `TestConfigReader` | **D2** — which WP/`gi_new_users` roles access test factory (product decision) |
| Schema **v3**, `is_correct` on answers | **Results / analytics** read models not exposed as application services (§17–18) |
| Dev smoke: **Tools → Knowledge Tests (dev)** (read-only list) | Admin **capability checks** not implemented on a public boundary |
| Automated domain tests (Phases 1–2) | Pattern for “contracts-admin React” must be chosen (bundle location, build, enqueue) |

**Verdict:** **Start Phase 3 with Wave A (admin API + auth), not with React screens.** Backend services for catalog are ready; **UI integration surface is not**. MVP admin in ~1–2 weeks after Wave A–B land.

---

## Already done (skip)

| Area | What exists |
|------|-------------|
| Catalog writes | `TestCatalog`, `QuestionCatalog`, `AnswerCatalog`, `setCorrectAnswerIdsForQuestion`, etc. |
| Settings | `catalog()->config` get/set |
| Critical update | `attempts()->criticalUpdate` (service layer) |
| Dev listing | `DevToolsMenu` — temporary; remove when React lists catalog |

---

## Wave A — Application API (HTTP) — **done**

Thin controllers in `bp_knowledge_tests` (or documented `admin-ajax` / REST namespace) — **no business logic in handlers**.

| Area | Endpoints (indicative) | Delegates to |
|------|------------------------|--------------|
| Tests | list, get, create, update, delete | `catalog()->tests` |
| Questions | list for test, CRUD | `catalog()->questions` |
| Answers | list, CRUD, set correct ids | `catalog()->answers` |
| Config | get all / set key | `catalog()->config` |
| Critical update | POST by test id | `attempts()->criticalUpdate->execute` |

**Security:** `current_user_can` or project capability; nonce; no anonymous catalog writes (User Rules §1).

**Deliverable:** Request/response shapes documented in [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) (canonical for React).

---

## Wave B — Capabilities & mount (D2) — **done**

- **D2 interim:** capability `bp_manage_knowledge_tests_catalog` on factory WP roles `administrator`, `sales_manager`; `manage_options` fallback; filter `bp_knowledge_tests_can_manage_catalog`.
- Shortcode **`[knowledge_tests_admin]`** — React mount `#bp-knowledge-tests-admin-root`, `wp_localize_script` → `bpKnowledgeTestsAdmin` (`restUrl`, `restNonce`, `pluginVersion`).
- Bundle: `admin/` (Vite) → `assets/admin/app.js`.

**Acceptance:** Unauthorized users cannot hit Wave A endpoints (REST uses `CatalogCapability`).

**Setup:** Create a personal-account page with `[knowledge_tests_admin]`; see `admin/README.md`.

---

## Wave C — React: All Tests + editor (§15) — **done**

- **All Tests** — list, version, expandable question previews (`admin/src/pages/AllTestsPage.tsx`).
- **Add / Edit Test** — title, achievement area, questions, answers, correct flags, explanation, reference (`TestEditorPage`, `QuestionEditor`).
- **Critical Update** — two-step confirm modal → `POST /tests/{id}/critical-update`.

**Acceptance:** New test with multi-select question can be created entirely in UI; correct options via `correct-answer-ids` / `is_correct`.

---

## Wave D — Settings (§16) — **done**

- React **Settings** screen + WP submenu; subnav between All tests / Settings.
- Bronze/silver/gold/lock thresholds, `retake_delay_days`, notification dismiss limits and modes, rank display names.
- REST `/config` PUT: allowlisted keys + validation; **achievement recalc** when threshold keys change (`AttemptAchievementService::recalculateAllFromLatestAttempts`).

---

## Wave E — Results & analytics (§17–18) — **done**

- REST: `GET /results` (filters: `testId`, `userId`, `status`, pagination).
- REST: `GET /analytics/tests`, `GET /analytics/tests/{id}` (test + question aggregates from attempt tables).
- React: **Results** and **Analytics** admin screens + WP submenu entries.
- Services: `ReportingServices` (`ResultsListQuery`, `TestAnalyticsQuery`).

---

## Wave F — Close Phase 3 — **not started**

| Task | Notes |
|------|--------|
| Remove or gate dev Tools page | Plan §11 Phase 1 — when React admin lists catalog |
| Docs | `IMPLEMENTATION_PLAN` §3, §11 Phase 3 checkboxes |
| CHANGELOG | Significant API + capability decisions |

---

## Suggested implementation order

```text
A (API) → B (auth + mount) → C (catalog UI) → D (settings) → E (optional) → F
```

**Minimum before Phase 4 (WS):** **A**, **B**, **C** (at least one complete test authored in UI).

---

## Open decisions

| # | Topic | Where |
|---|--------|--------|
| D2 | Roles for admin vs dealer Tests | Product + `UserControler` |
| 1 | REST vs `admin-ajax` vs WS for admin | Prefer REST or existing site admin transport pattern |
| 2 | Bundle ownership | `bp_knowledge_tests` assets vs `bp_contracts` admin package |

---

## Task checklist (copy for PRs)

- [x] A Admin HTTP API + documented contracts
- [x] B Capabilities + React mount
- [x] C All Tests + Add/Edit + Critical Update UI
- [x] D Settings UI
- [x] E Results/Analytics
- [ ] F Dev Tools retired + doc sync
