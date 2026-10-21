# Knowledge Tests — WebSocket Protocol (V1)

**Status:** **Locked** (Phase 4 Wave A, 2026-10-19). Handlers live in `bp_contracts` (`KnowledgeTestsWsWorker`, Wave D).

**Transport:** Same as personal account — `title::: {json}` after `idUser` registration ([bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md)).

**User id:** `gi_new_users.id_user` for the connected session (from WS bootstrap). Request bodies use **`testId`**, **`questionId`**, **`answerIds`** — not WordPress `wp_users.ID`.

**Domain rules:** Scoring, pass/fail, retake, medals, and validity stay in **`bp_knowledge_tests`**. Payloads expose **facts** for UI (counts, ids, labels, feedback flags) — not duplicated threshold logic.

**Product reference:** [Testing System — Project Documentation.md](./Testing%20System%20%E2%80%94%20Project%20Documentation.md) §19–21, §23.

---

## 1. Message titles (D1 — finalized)

Names match [IMPLEMENTATION_PLAN §4.5](./IMPLEMENTATION_PLAN.md#45-websocket-spec-23); no renames from the original proposal.

### 1.1 Client → server

| Title | Body (JSON) | Purpose |
|-------|-------------|---------|
| `showTests` | `{ "page"?: number, "perPage"?: number }` | List tests + derived row state for current user |
| `startTest` | `{ "testId": number }` | Begin new session or **resume** RAM session for that test |
| `submitTestQuestion` | `{ "testId": number, "selectedAnswerIds": number[] }` | Submit selections for **current** question; advance session |
| `finishTest` | `{ "testId": number }` | End session → persist via `CompleteAttempt::finish` |
| `abandonTest` | `{ "testId": number }` | Drop in-memory session without persisting |
| `getTestMaterials` | `{ "testId": number }` | Load pending temporary materials for user/test |
| `confirmTestMaterialsExamined` | `{ "testId": number, "attemptId": number }` | User pressed **Examined** — delete temp explanation rows |

Empty body is allowed where all fields are optional (`showTests` with `{}`).

### 1.2 Server → client

| Title | Client handler |
|-------|----------------|
| `TestsOnPage` | `handlerTestsOnPage` |
| `TestQuestionOnPage` | `handlerTestQuestionOnPage` |
| `TestFinishedOnPage` | `handlerTestFinishedOnPage` |
| `TestMaterialsOnPage` | `handlerTestMaterialsOnPage` |

Convention: `communication_server.js` → `handler` + PascalCase(title).

---

## 2. Command → response mapping

| Client command | Typical success response | Notes |
|----------------|-------------------------|--------|
| `showTests` | `TestsOnPage` | Includes pagination when `page` / `perPage` sent |
| `startTest` | `TestQuestionOnPage` | Resume returns current question in same shape |
| `submitTestQuestion` | `TestQuestionOnPage` | Last question: may include `sessionComplete: true` (still must call `finishTest`) |
| `finishTest` | `TestFinishedOnPage` | Clears RAM session after success |
| `abandonTest` | `TestsOnPage` | Refreshed list; session cleared |
| `getTestMaterials` | `TestMaterialsOnPage` | |
| `confirmTestMaterialsExamined` | `TestMaterialsOnPage` | Empty `items` after success |

### 2.1 Errors

Use the **same response title** as the successful operation would use, with root-level:

```json
{
  "ok": false,
  "error": {
    "code": "retake_delay",
    "reasons": ["retake_delay", "pending_materials"]
  }
}
```

| `error.code` | When |
|--------------|------|
| `retake_delay` | Configured retake period not elapsed |
| `pending_materials` | Failed attempt; materials not examined |
| `test_locked` | User reached lock threshold (e.g. 95%+) |
| `notification_blocked` | Notification policy blocks start |
| `test_in_progress` | **Contracts RAM:** another test session blocks start (Wave E) |
| `no_session` | `submitTestQuestion` / `finishTest` without active session |
| `invalid_test` | Unknown or inactive `testId` |
| `validation` | Malformed body or wrong current question |

Plugin retake reasons use constants in `BpKnowledgeTests\Domain\RetakeEligibility` (`retake_delay`, `pending_materials`, `test_locked`, `notification_blocked`). `test_in_progress` is added at the gateway when `TestController` holds a session.

---

## 3. Response payloads (minimal shapes)

### 3.1 `TestsOnPage`

```json
{
  "ok": true,
  "tests": [
    {
      "testId": 1,
      "title": "PostgreSQL Fundamentals",
      "displayStatus": "not_started",
      "score": null,
      "medalId": null,
      "canStart": true,
      "canOpenMaterials": false,
      "achievementArea": "PostgreSQL"
    }
  ],
  "pagination": {
    "page": 1,
    "perPage": 20,
    "total": 3
  }
}
```

**`displayStatus`** (spec §2.14): `not_started` | `in_progress` | `failed` | `passed` | `outdated`.

**`canStart` / `canOpenMaterials`:** Derived in plugin + RAM (Phase 4 gateway); UI enables **Start** / **Materials** per spec §19.1.

### 3.2 `TestQuestionOnPage`

```json
{
  "ok": true,
  "testId": 1,
  "progress": {
    "index": 3,
    "total": 20
  },
  "question": {
    "questionId": 42,
    "text": "…",
    "answerOptions": [
      { "answerId": 101, "text": "…" }
    ]
  },
  "feedback": {
    "show": true,
    "rightAnswerIds": [101],
    "wrongAnswerIds": [102],
    "explanation": "…",
    "reference": "…"
  },
  "sessionComplete": false
}
```

- **`answerOptions`:** Shuffled order from RAM (§2.7); **never** include `is_correct` on the wire.
- **`feedback`:** Present after `submitTestQuestion` when the user may proceed (§20.1). Omitted on cold `startTest` until first submit.
- **`sessionComplete`:** `true` when all questions have submitted counts; client still calls `finishTest` to persist.

### 3.3 `TestFinishedOnPage`

```json
{
  "ok": true,
  "attempt": {
    "attemptId": 99,
    "testId": 1,
    "testVersion": 2,
    "score": 91.94,
    "status": "passed",
    "validAnswers": 60,
    "invalidAnswers": 3,
    "dateFinished": "2026-10-19 12:00:00"
  },
  "medalId": 3,
  "materialsRequired": false
}
```

Maps to `AttemptRecord` + achievement side effects from `CompleteAttempt::finish`. **`materialsRequired`:** `true` when failed and temp explanation rows exist (user should open materials).

### 3.4 `TestMaterialsOnPage`

```json
{
  "ok": true,
  "testId": 1,
  "attemptId": 99,
  "items": [
    {
      "explanationId": 5,
      "questionId": 42,
      "questionText": "…",
      "explanation": "…",
      "reference": "…"
    }
  ]
}
```

After **`confirmTestMaterialsExamined`**, success returns `"items": []` (rows deleted per spec §10.2).

---

## 4. Request details

### 4.1 `submitTestQuestion`

- **`selectedAnswerIds`:** Catalog answer option ids (`gi_new_test_answers.id`), multi-select allowed.
- Server grades via `domain()->selection->grade($correctIds, $selectedIds)` → `rightAnswers` / `failedAnswers` stored in RAM for that question.
- Immediate feedback uses grader output + question `explanation` / `reference` when `failedAnswers > 0` or missed correct (product §20.1).

### 4.2 `finishTest`

RAM must supply one line per catalog question for the test:

```php
['questionId' => int, 'rightAnswers' => int, 'failedAnswers' => int]
```

Built by `TestController` (Wave B) — see [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) §11.

---

## 5. Implementation checklist (waves B–F)

| Wave | Work |
|------|------|
| B | `TestController` / `AttemptSession` |
| C | `KnowledgeTestsSessionWorker` (plugin + RAM) |
| D–E | `KnowledgeTestsWsWorker` via `Chat.php` / `InteractionInterface` |
| F | Staging harness + QA |

**Live command index:** [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md) (must stay in sync with this file when titles change).
