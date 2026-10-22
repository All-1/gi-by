**Prerequisite:** Phase **4** **complete** in repo — WS commands live per [WS_PROTOCOL.md](./WS_PROTOCOL.md). Run Phase **4** Wave **F** staging QA on staging before dealer release ([PHASE_4_TASKS.md](./PHASE_4_TASKS.md)).

# Phase 5 — Task breakdown (Dealer UI — vanilla JS)

**Primary owner:** `bp_contracts` (personal account shell, `knowledge_tests.js`, `index.php` shortcode, open-in-place overlay, chat stars)  
**Domain owner:** `bp_knowledge_tests` (achievement/medal queries, any new read APIs — no scoring in contracts)  
**Parent plan:** [IMPLEMENTATION_PLAN.md §9 / §11](./IMPLEMENTATION_PLAN.md#9-user-ui-v1)  
**Requirements:** [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md) (§3.2 failed nav, §2.11 stars, §19–21 dealer UI, §19.2 open-in-place)  
**WS contract:** [WS_PROTOCOL.md](./WS_PROTOCOL.md) — **do not** duplicate payload shapes here; extend protocol doc if fields change  
**Live status index:** [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

**Goal (exit criterion):** A dealer with the agreed role can open **Tests** in the personal account, see the paginated list (§19), **Start** or **resume** a test in the existing additional-window pattern (§19.2), complete the **one-question + Continue** flow with immediate feedback (§20), **finish** and see results; after failure, open **Materials**, mark references reviewed, and **Examined** (§21). Chat shows **latest-result stars** per test area (§2.11). Failed-test **menu styling** applies when applicable (§3.2).

**Progress (2026-10-21):** Wave **A** **complete** — PA page + `knowledge_tests.js` (list, overlay, WS handlers). Same pattern as [orders.js](../../bp_contracts/js/orders.js) / [js/PROJECT_CONTEXT.md](../../bp_contracts/js/PROJECT_CONTEXT.md). **No React** on dealer UI (admin factory stays React).

**Estimate:** ~2 weeks one developer (matches plan §11); ~1 week **MVP** (list + in-test + finish; stars/materials polish in 5b).

---

## Readiness (can we implement now?)

| Ready | Not ready / decide first |
|-------|---------------------------|
| WS commands + payloads (`showTests`, `startTest`, `submitTestQuestion`, `finishTest`, materials) | **D2** — which `gi_new_users` / PA roles see **Tests** and may start attempts |
| Client handler names registered (`handlerTestsOnPage`, …) | **Nav link** — WP menu entry to `knowledge_tests()` page |
| `displayStatus`, `canStart`, `canOpenMaterials`, pagination | **`medalId`** on list/finish still `null` in gateway |
| Admin-authored catalog on staging | **`medalId` on list/finish** — gateway currently returns `null`; needs plugin read (user achievement per test) before UI can show medals |
| Phase 4 staging QA checklist | **Achievement hover tooltips** (§2.11) — may need small REST or WS read batch for chat |

**Verdict:** Start with **Wave A** (decisions + scaffold), then **Wave C–D** (list + in-test) in parallel once mount and WS bridge exist.

---

## Already done (skip — Phase 4)

| Area | What exists |
|------|-------------|
| WS transport | `Chat.php` → `InteractionInterface` → `KnowledgeTestsWsWorker` |
| Protocol | [WS_PROTOCOL.md](./WS_PROTOCOL.md), index in [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md) |
| Page JS | `knowledge_tests.js` — `where`, `setDefaultState`, handlers, DOM (Wave A scaffold) |
| RAM session | `TestController` — shuffle, progress, `test_in_progress` guard |
| Domain finish | `CompleteAttempt`, materials rows, retake evaluation |

**Gap:** Nav menu link, polish/QA, stars (§2.11), failed-nav class wiring, `medalId`, resume UX edge cases.

---

## Wave A — Decisions & scaffold — **complete**

**Convention (matches orders / analytics):**

| Piece | Location |
|-------|----------|
| PHP page shell | `bp_contracts/index.php` → `knowledge_tests()` + `transferUserToJS` + `communication_server.js` |
| Table markup | `bp_contracts/index.php` → `knowledge_tests()` (same as `orders_dev` / `analytics`) |
| Page logic | `bp_contracts/js/knowledge_tests.js` — global `where = 'knowledge_tests'`, `setDefaultState` → `showTests` |
| Open-in-place | `#knowledge_tests_overlay.modal_window_contracts` (§19.2) |
| Access (D2) | `DealerTestsCapability` + filter `bp_knowledge_tests_can_access_dealer_tests` |

---

## Wave A (historical notes) — **superseded by table above**

Lock conventions before large UI PRs.

### A1. Role & visibility (**D2**)

| Decision | Options | Default recommendation |
|----------|---------|------------------------|
| Who sees **Tests** menu | Dealer roles only vs all PA users | Align with existing dealer nav; document in plugin + contracts PROJECT_CONTEXT |
| Who may `startTest` | Same as menu vs stricter | Same as menu unless product says otherwise |

**Deliverable:** Capability or `UserControler` check documented; WS already binds `userId` from websocket — menu hide ≠ security (server must still enforce if needed later).

### A2. Frontend — **vanilla JS only** (not React)

Dealer UI follows `js/PROJECT_CONTEXT.md`: no bundler; `utility.js` helpers + `socket.send('command::: json')`.

### A3. Open-in-place host (§19.2)

- Reuse contracts **additional-window** mechanism (same page, not full navigation).
- Map: list row **Start** / **Materials** → open panel with test or materials React root.
- **Acceptance:** Closing overlay returns to list without full page reload; list refreshes via `showTests` after finish/abandon.

---

## Wave B — WS + DOM polish — **in progress**

### B1. Handler wiring — **done (scaffold)**

| Server title | Client handler | DOM |
|--------------|----------------|-----|
| `TestsOnPage` | `handlerTestsOnPage` | `#main_container_knowledge_tests` |
| `TestQuestionOnPage` | `handlerTestQuestionOnPage` | Overlay question + Continue |
| `TestFinishedOnPage` | `handlerTestFinishedOnPage` | Overlay result |
| `TestMaterialsOnPage` | `handlerTestMaterialsOnPage` | Overlay materials + Examined |

### B2. Outbound commands

| Command | When |
|---------|------|
| `showTests` | Mount Tests page; page change; after finish/abandon/materials confirm |
| `startTest` | **Start** on row; resume when `displayStatus === 'in_progress'` |
| `submitTestQuestion` | **Continue** after selections (multi-select checkboxes) |
| `finishTest` | When `sessionComplete === true` (after last question submit) |
| `getTestMaterials` | **Materials** button |
| `confirmTestMaterialsExamined` | **Examined** with `testId` + `attemptId` |
| `abandonTest` | Optional explicit cancel if product adds control |

**Acceptance:** No business rules in JS beyond presentation (enable/disable from `canStart`, `canOpenMaterials`, `displayStatus`).

### B3. Gateway payload gaps (coordinate with plugin)

| Field | Current | Phase 5 action |
|-------|---------|----------------|
| `medalId` on list / finish | `null` in `KnowledgeTestsWsWorker` | Add plugin query (e.g. user achievement for test) in gateway **without** copying rules |
| Error UX | `error.code` + `reasons[]` | Map to user-visible messages (retake_delay, pending_materials, test_in_progress, …) |

---

## Wave C — Tests list (§19) — **not started**

### C1. Page & nav

- Add **Tests** item to personal account navigation.
- Single page: all tests, pagination at bottom (`pagination` from `TestsOnPage`).
- Apply **failed-test** nav styling when user has relevant failed state (§3.2) — define trigger (e.g. any `displayStatus === 'failed'` with pending materials or config flag).

### C2. Row UI (§19.1)

Per row from WS:

| Element | Source |
|---------|--------|
| Title | `title` |
| Status | `displayStatus` (map to product labels) |
| Score | `score` when completed |
| Medal/star | `medalId` (after B3) |
| **Start** | `canStart` |
| **Materials** | `canOpenMaterials` |
| Achievement area | `achievementArea` (optional subtitle) |

### C3. Pagination

- Send `{ page, perPage }` on `showTests`; preserve page on return from overlay when possible.

**Acceptance:** Row states match spec §2.14; buttons disabled when flags false; starting blocked test shows server error message.

---

## Wave D — In-test flow (§20) — **not started**

### D1. Layout

- One question, shuffled `answerOptions` (multi-select).
- Progress: `progress.index` / `progress.total` (spec counter).
- **Continue** → `submitTestQuestion` with `selectedAnswerIds`.

### D2. Immediate feedback (§20.1)

- After submit, render `feedback` (right/wrong ids, explanation, reference on mistakes).
- User proceeds to next question only after Continue (second click or same button per UX choice — document in UI).
- Do not persist selected ids client-side beyond current step (spec).

### D3. Finish

- When `sessionComplete === true`, call `finishTest`; show `TestFinishedOnPage` (score, status, medal when available).
- Refresh list; if `materialsRequired`, guide user to **Materials**.

### D4. Resume & errors

- `startTest` on in-progress row resumes via `TestQuestionOnPage` without new attempt.
- Handle `test_in_progress` when starting another test while one is active.
- Handle WS disconnect copy (resume same process only — spec §23).

**Acceptance:** Full pass and full fail paths on staging; finish creates DB attempt (Phase 2 behavior).

---

## Wave E — Materials UI (§21) — **not started**

- Open from list **Materials** or post-finish prompt.
- Show all items from `TestMaterialsOnPage` (question, explanation, reference).
- Per reference: **Reviewed** checkbox (client-only until submit).
- **Examined** → `confirmTestMaterialsExamined`; close overlay; `showTests` refresh.
- **Acceptance:** After confirm, temp DB rows cleared; retake allowed per plugin rules after delay.

---

## Wave F — Stars in chat (§2.11) — **not started**

- Display **latest completed** medal per test/area next to user name in messages (not best historical).
- Multiple stars per user when multiple tests/areas apply.
- Hover: achievement detail per spec (config-driven names/thresholds).
- **Data:** Define read path (REST batch from plugin or extend WS) — avoid N+1 per message row.

**Acceptance:** Transparent/outline star when not passed for that test; passed shows configured rank styling.

---

## Wave G — Close Phase 5 — **not started**

| Task | Notes |
|------|--------|
| Dealer QA | Journey: list → start → submit all → finish → (fail) materials → examined → retake after delay |
| Accessibility | Focus trap in overlay; keyboard Continue |
| Docs | `IMPLEMENTATION_PLAN.md` §11 Phase 5 checkboxes; `bp_contracts` + plugin PROJECT_CONTEXT; §19–21 map updated |
| Out of scope reminder | Notification popup/block/bottom bar → **Phase 6** |

---

## Suggested implementation order

```text
A1 → A2 → A3 (decisions + scaffold)
  ↓
B1 → B2 (+ B3 medalId when list needs stars)
  ↓
C1 → C2 → C3 (list + nav)
  ↓
D1 → D2 → D3 → D4 (in-test)
  ↓
E (materials)
  ↓
F (chat stars — can parallelize after B3 if data API ready)
  ↓
G
```

**MVP slice for early demo:** **A**, **B**, **C**, **D** (materials **E** required before retake QA).

---

## Open decisions (resolve while coding)

| # | Topic | Where |
|---|--------|--------|
| D2 | Dealer roles for Tests menu / start | Product + `UserControler` |
| 1 | Bundle directory under `bp_contracts` | Wave A2 — log in CHANGELOG |
| 2 | Open-in-place component name | Wave A3 — link from `bp_contracts/js/PROJECT_CONTEXT.md` |
| 3 | `medalId` source API | Wave B3 — plugin service + gateway field |
| 4 | Stars data transport | Wave F — REST vs WS |

Log significant UI or protocol choices in [CHANGELOG.md](../../../../CHANGELOG.md).

---

## Out of scope (later phases)

| Phase | Work |
|-------|------|
| **6** | New-test / failed-test notification popup, block, bottom bar (§3.1, §10) |
| **7** | Hardening, full §12 / spec §28 QA matrix, rate limits |

---

## Task checklist (copy for PRs)

- [x] A1 D2 `DealerTestsCapability` + documented roles
- [x] A2 Page JS (`knowledge_tests.js`) + enqueue on Tests page only
- [x] A3 Open-in-place overlay (`modal_window_contracts`)
- [x] B1 WS handlers → DOM (scaffold)
- [x] B2 Outbound commands in `knowledge_tests.js`
- [ ] B3 `medalId` (and finish medal) from plugin
- [ ] C1 Tests nav + page shell
- [ ] C2 Row UI per §19.1
- [ ] C3 Pagination
- [ ] D1–D4 In-test + finish + resume/errors
- [ ] E Materials + Examined
- [ ] F Chat stars §2.11
- [ ] G Dealer QA + doc sync
