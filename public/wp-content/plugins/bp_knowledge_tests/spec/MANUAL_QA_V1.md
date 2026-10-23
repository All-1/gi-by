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

**Code:** `bp_contracts/js/knowledge_test_notifications.js` (loads when dealer has Tests nav). **WS:** `getKnowledgeTestNotifications`, `dismissKnowledgeTestNotification` → `KnowledgeTestNotificationsOnPage` ([WS_PROTOCOL.md](./WS_PROTOCOL.md)). **Domain:** `NotificationUiQuery` in plugin (no scoring in contracts).

**Product / plan:** [IMPLEMENTATION_PLAN §10](./IMPLEMENTATION_PLAN.md#10-notifications-v1), product §3.1–3.4, prerequisite baseline §6. **D10:** one row per `(user_id, test_id, notification_type)` — types `new_test` | `failed_test`.

### 4.0 Setup (do once per session)

| Step | Action |
|------|--------|
| S1 | In admin React **Settings → Notifications**, note **New test — max dismissals**, **after limit** (`bottom` \| `block`), and the same pair for **Failed test**. For block/bottom transitions, temporarily set **max dismissals** to **1** or **2** so you do not need ten clicks. |
| S2 | Publish at least **two** tests **A** then **B** in catalog order (factory list order = prerequisite order). |
| S3 | Use a dealer user with Tests nav (`.knowledge_tests_nav_link` on PA pages). Open any PA page **except** `/tests/` first when testing **account block** (block overlay is suppressed on Tests page). |
| S4 | WS connected (`onWebSocketSessionReady` triggers `getKnowledgeTestNotifications`). After deploy, restart WS ([DEVELOPMENT_RULES.md](../../../../DEVELOPMENT_RULES.md) §4). |

**CLI (optional, before browser):** `php wp-content/plugins/bp_knowledge_tests/tests/run_notification_presentation_tests.php` and `run_notification_retake_tests.php`.

### 4.1 New test available (§3.1)

| # | Step | Expected |
|---|------|----------|
| 4.1.1 | User **completed test A**; test **B** has **no** attempt for this user. | — |
| 4.1.2 | Log in → land on e.g. **Contracts** (not Tests). | Full-screen popup: title **«Доступен новый тест»**, body mentions test **B**, buttons **Перейти к тестам** / **Закрыть**. |
| 4.1.3 | Click **Закрыть** repeatedly until `dismissCount` reaches **max dismissals** (watch popup reappear after each dismiss until limit). | Each dismiss sends `dismissKnowledgeTestNotification` with `notificationType: "new_test"`, `testId` = B. |
| 4.1.4 | After limit, if mode = **bottom** | Bottom bar with text + **Тесты** + **Понятно** (no full-screen popup). **Понятно** dismisses again (increments count). |
| 4.1.5 | After limit, if mode = **block** | On non-Tests pages: blocking overlay **«Требуется пройти тест»** + link to `/tests/`. Open **/tests/** → overlay hidden (`where === "knowledge_tests"`). |
| 4.1.6 | Complete test **B** (any result). | New-test UI for B disappears; `new_test` row cleared or no longer applies. |

### 4.2 Failed test (§3.2)

| # | Step | Expected |
|---|------|----------|
| 4.2.1 | Fail test **B** (score below bronze). Finish via dealer overlay. | Tests nav **failed highlight** (Phase 5); pending materials if configured. |
| 4.2.2 | Reload PA (any page with Tests nav). | Popup **«Не сдан тест»** and/or bottom bar: study materials copy; **Материалы** link when `materialsAvailable` is true. |
| 4.2.3 | Click **Понятно** on bottom bar (or **Закрыть** on popup). | `dismissKnowledgeTestNotification` with `notificationType: "failed_test"`. |
| 4.2.4 | Open **/tests/** → **Materials** for B, complete **Изучено**. | Failed notification may clear after pass or policy change; retake rules per §3. |

### 4.3 Prerequisites (baseline §6)

| # | Step | Expected |
|---|------|----------|
| 4.3.1 | User with **no** attempt on test **A**, test **B** published. | **No** new-test popup for **B** (prior test A not completed). |
| 4.3.2 | User completes **A**, still no attempt on **B**. | New-test notification for **B** appears on next `getKnowledgeTestNotifications` (page load / WS open). |

### 4.4 Dismiss persistence (D10)

| # | Step | Expected |
|---|------|----------|
| 4.4.1 | After 4.1.3, inspect `gi_new_test_notifications` for `(user_id, test_B_id, new_test)`. | Single row; `dismiss_count` matches UI dismisses; unique key per **D10**. |
| 4.4.2 | After failed finish (4.2.1), row for `failed_test` on same test. | Row exists; passing attempt removes failed notification applicability (see `CompleteAttempt` side effects). |
| 4.4.3 | DevTools → WS: send `getKnowledgeTestNotifications::: {}` | Response `ok: true`, `items[]` with `presentation` `popup` \| `bottom` \| `block`, `dismissCount`, `dismissLimit`, `accountBlocked` true when any item has `presentation: "block"`. |

### 4.5 Retake block (notification policy)

| # | Step | Expected |
|---|------|----------|
| 4.5.1 | With active **block** presentation for `new_test`, try **startTest** on WS before completing the test. | `startTest` may return `notification_blocked` when `NotificationRetakePolicy` applies (align with `run_notification_retake_tests.php`). |

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
