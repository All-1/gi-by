**Prerequisite:** Phase **3** **complete** — author tests via WP **Knowledge Tests** React admin (or REST) before WS work.

# Phase 4 — Task breakdown (TestController & WebSocket)

**Primary owner:** `bp_contracts` (RAM session + WS transport)  
**Domain owner:** `bp_knowledge_tests` (grading, finish, retake, materials — no duplicated rules)  
**Parent plan:** [IMPLEMENTATION_PLAN.md §11](./IMPLEMENTATION_PLAN.md#11-implementation-phases)  
**Architecture:** [IMPLEMENTATION_PLAN.md §4.5 / §7](./IMPLEMENTATION_PLAN.md#45-websocket-spec-23)  
**Requirements:** [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md) (§23 in-progress, §2.7 randomization, §9–10 finish counts, §20–21 flows via WS payloads)  
**Contracts WS index:** [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md)  
**Live status index:** [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

**Goal (exit criterion):** A dealer (or tester) can **start → answer questions → finish** a knowledge test **over WebSocket** on staging; unfinished state lives in **RAM** only; **finish** persists via `attempts()->complete->finish` with the Phase 2 payload contract. **No React product UI required** for exit (manual WS client or temporary harness is enough).

**Progress (2026-10-20):** Waves **A–D** complete; **E** core flow implemented in `KnowledgeTestsWsWorker`. Wave **F** pending.

**Estimate:** ~1.5–2 weeks one developer (matches plan §11).

---

## Already done (skip — Phase 2)

| Area | What exists |
|------|-------------|
| Finish persistence | `CompleteAttempt::finish`, materials / achievements / notifications hooks |
| Finish payload | `['questionId', 'rightAnswers', 'failedAnswers']` per catalog question — [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) |
| Grading | `domain()->selection->grade`, `domain()->scoring`, `domain()->classifier` |
| Retake (DB) | `attempts()->retake->evaluate` — **in-progress RAM not wired yet** |
| Materials (DB) | `attempts()->materials` — WS wrappers in Phase 4 |
| Integration tests | `tests/run_complete_attempt_tests.php` (no WS) |

**Gap:** `TestController`, WS titles/handlers, `InteractionInterface` glue, shuffle + per-question feedback over WS.

---

## Wave A — Protocol & documentation (D1) — **complete**

Finalize names before merging handlers (plan §4.5 proposals).

**Canonical doc:** [WS_PROTOCOL.md](./WS_PROTOCOL.md) (titles, payloads, error codes). Live index: [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md).

### A1. Client → server commands

| Proposed title | Purpose | Notes |
|----------------|---------|--------|
| `showTests` | List tests + derived row state for user | Pagination can mirror contracts patterns later (Phase 5) |
| `startTest` | Begin or **resume** in-memory session | Must call `attempts()->retake->evaluate` before new start |
| `submitTestQuestion` | Selections for current question | Returns immediate feedback (§20.1); updates RAM counts |
| `finishTest` | End session → `CompleteAttempt` | Builds full question line set |
| `abandonTest` | Drop RAM session | Optional but recommended |
| `getTestMaterials` | Materials for user/test | Thin read via plugin |
| `confirmTestMaterialsExamined` | Mark materials examined | Enables retake path after fail |

**Deliverable:** Locked title list in [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md) (incoming + outgoing tables). Log renames in [CHANGELOG.md](../../../../CHANGELOG.md) if changed from §4.5.

### A2. Server → client responses

| Proposed title | Client handler (convention) |
|----------------|----------------------------|
| `TestsOnPage` | `handlerTestsOnPage` |
| `TestQuestionOnPage` | `handlerTestQuestionOnPage` |
| `TestFinishedOnPage` | `handlerTestFinishedOnPage` |
| `TestMaterialsOnPage` | `handlerTestMaterialsOnPage` |

**Acceptance:** Documented JSON field shapes (minimal) in contracts PROJECT_CONTEXT or linked plugin doc — no scoring rules in payload docs beyond what UI needs.

---

## Wave B — Session model & `TestController` — **complete**

**Code:** `bp_contracts/src/Testing/` — smoke test `bp_contracts/tests/run_test_controller.php`.

**Location:** `bp_contracts/src/Testing/TestController.php` (+ small session type(s) in same folder).

### B1. `AttemptSession` (RAM)

Hold per user (and active `testId`):

- Shuffled `questionIds` and per-question shuffled `answerIds` (§2.7 — not persisted).
- Current question index / id.
- Per-question running **`rightAnswers` / `failedAnswers`** (counts only, spec §9.2–9.3).
- Optional: last selected answer ids for feedback display only.

**Hard rule:** No pass/fail, medal, or validity logic here — only session facts.

### B2. `TestController` API

| Method | Behavior |
|--------|----------|
| `hasSession(int $userId, int $testId): bool` | For retake / start guards |
| `startOrResume(...)` | Create or return existing session |
| `submitQuestion(...)` | Store selections; update counts via **plugin** grader |
| `buildFinishPayload(): array` | Lines for `CompleteAttempt::finish` |
| `clear(int $userId, ?int $testId): void` | After finish or abandon |

**Acceptance:** Unit-level or isolated tests in `bp_contracts` if practical; otherwise covered by Wave F staging script.

### B3. Lifecycle wiring

- Register `TestController` in `SystemConstructor` (singleton per WS process).
- Document: session cleared on WS process restart (~03:00) — spec accepted behavior.

---

## Wave C — Plugin bridge (no business logic in contracts) — **complete**

### C1. Access `bp_knowledge_tests` from WS layer

- Resolve `Plugin` (or narrow facade) from existing bootstrap — same pattern as other cross-plugin use; document entry in [bp_knowledge_tests/PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md).
- **Forbidden:** copy scoring thresholds, retake rules, or achievement mapping into `bp_contracts`.

### C2. Contracts integration surface (no proxy façade)

| Piece | Role |
|-------|------|
| `KnowledgeTestsSessionWorker` | Plugin resolve, grading, `TestController`, session question rows |
| `KnowledgeTestsWsWorker` | WS payloads; uses session worker via container |

WS worker calls `$sessionWorker->plugin()->attempts()` / `catalog()` — **no** extra proxy types.

### C3. Optional plugin use cases (only if handlers grow)

Plan §6 names `TestCatalogQuery`, `StartTestSession`, `SubmitQuestion` — add **only when** `InteractionInterface` would otherwise exceed thin-controller rule. Prefer a **single** multi-step orchestrator in `bp_contracts` (e.g. `startTest` flow) over many proxy methods.

**Acceptance:** `Chat.php` / `InteractionInterface` methods stay short; domain stays in plugin `src/`.

---

## Wave D — WebSocket implementation — **complete**

### D1. `InteractionInterface`

- One method per incoming command (or grouped private helpers).
- Auth: same user id as existing WS (`idUser` / `gi_new_users` mapping per site rules).

### D2. `Chat.php`

- Route new titles to interface methods.
- Reply with `Title::: {json}` per existing convention.

### D3. Client stubs (Phase 4 minimum)

- Register handlers in `communication_server.js` (can no-op until Phase 5 React).
- **Exit:** provable round-trip via browser console or documented manual steps on staging.

**Out of scope:** Full React Tests page (Phase 5).

---

## Wave E — Behavior wiring — **mostly complete** (in `KnowledgeTestsWsWorker`)

### E1. `startTest`

1. `attempts()->retake->evaluate($userId, $testId)` — block with reason codes if not `canStart`.
2. If RAM session exists for user+test → resume snapshot.
3. Else load catalog questions/answers; shuffle; init counts to zero.

### E2. `submitTestQuestion`

1. Load correct option ids from `catalog()->answers` (D6).
2. `domain()->selection->grade($correctIds, $selectedIds)`.
3. Map grader result to question line increments (§5.1.1); store in session.
4. Response: right/wrong/missed feedback for UI (§20.1); explanation/reference on mistakes.

### E3. `finishTest`

1. `buildFinishPayload()` — **all** catalog question ids for test.
2. `attempts()->complete->finish($userId, $testId, $lines)`.
3. Clear RAM session.
4. Response: score, status, medal/lock flags (from `AttemptRecord` / classifier — plugin).

### E4. Retake + in-progress

- Extend retake path: if `TestController::hasSession($userId, $testId)` → treat as **cannot start new attempt** (reason code e.g. `test_in_progress`) — align with [PHASE_2_TASKS.md](./PHASE_2_TASKS.md) C1 note.
- Plugin change: optional flag on `RetakeEligibilityContext` or separate check in gateway before `startTest`.

### E5. Materials commands (if in Phase 4 scope)

- `getTestMaterials` → `materials->listPendingForUserAndTest` (or attempt-scoped list).
- `confirmTestMaterialsExamined` → `materials->confirmExamined`.

**Acceptance:** Failed attempt → materials rows in DB; finish does not skip Wave E notification hooks (already in `CompleteAttempt`).

---

## Wave F — Close Phase 3 — **not started**

| Task | Notes |
|------|--------|
| Staging QA | Checklist: start, submit (pass/fail question), finish, reconnect resume, restart WS loses RAM |
| Security | Auth on commands; no cross-user session access (DEVELOPMENT_RULES §1) |
| Docs | `IMPLEMENTATION_PLAN.md` §3 + §11 Phase 4 checkboxes; `bp_contracts` + plugin PROJECT_CONTEXT |
| WS deploy | Restart Ratchet/WS after protocol change (DEVELOPMENT_RULES §4) |

---

## Suggested implementation order

```text
A1 → A2 (lock protocol)
  ↓
B1 → B2 → B3 (TestController)
  ↓
C1 → C2 (gateway)
  ↓
E1 → E2 → E3 (core test flow)
  ↓
D1 → D2 → D3 (expose on WS)
  ↓
E4 → E5 (retake + materials WS)
  ↓
F
```

**Minimum before Phase 5 (dealer React):** **A**, **B**, **C**, **E1–E3**, **D** for `showTests` / `startTest` / `submitTestQuestion` / `finishTest`. Materials WS (**E5**) can ship with Phase 5 if list UI needs it — but retake after fail requires materials path before dealer QA.

---

## Open decisions (resolve while coding)

| # | Topic | Where |
|---|--------|--------|
| D1 | Exact WS command/response names | **Resolved** — [WS_PROTOCOL.md](./WS_PROTOCOL.md) |
| D2 | Which roles see Tests / start tests | Product + `UserControler` / capabilities |
| 1 | `user_id` on WS | `gi_new_users.id_user` — same as Phase 2 |
| 2 | Resume after reconnect | Same WS process only; new process = lost session (spec) |
| 3 | `showTests` payload | Minimal id/title/status vs full §19 row — document contract |

Log significant protocol or product choices in [CHANGELOG.md](../../../../CHANGELOG.md).

---

## Out of scope (later phases)

| Phase | Work |
|-------|------|
| **5** | React Tests list, in-test UI, materials UI, nav styling |
| **6** | Notification popup / block / bottom bar UX |
| **7** | Hardening, full §12 QA matrix |

---

## Task checklist (copy for PRs)

- [x] A1 Client → server titles documented (live list in bp_contracts PROJECT_CONTEXT)
- [x] A2 Server → client titles + handler names documented
- [x] B1 `AttemptSession` RAM model
- [x] B2 `TestController` API + finish payload builder
- [x] B3 Registered in `SystemConstructor`
- [x] C1 Plugin bootstrap access from WS layer
- [x] C2 `KnowledgeTestsSessionWorker` + `KnowledgeTestsWsWorker` (container)
- [x] D1–D3 WS routing + client handler registration
- [x] E1 `startTest` (retake + shuffle + resume)
- [x] E2 `submitTestQuestion` (plugin grader + feedback payload)
- [x] E3 `finishTest` → `CompleteAttempt` + clear session
- [ ] E4 In-progress blocks retake / duplicate start (cross-test `test_in_progress` TBD)
- [x] E5 Materials WS commands (or explicit defer + CHANGELOG)
- [ ] F Staging QA + doc sync + IMPLEMENTATION_PLAN §11
