# Component Context: bp_contracts/src/Testing

## Purpose

In-memory **unfinished** knowledge test sessions (`TestController`, spec §2.5). Completed attempts persist in `bp_knowledge_tests`; this folder holds **session facts only** (shuffle order, per-question counts, last selection).

Sessions die when the Ratchet process restarts (nightly ~**03:00**).

## Layout

| Path | Role |
|------|------|
| `Contract/` | Grader interface toward `bp_knowledge_tests` |
| `*.php` (root) | Session RAM + DTOs |

## Types (this folder)

| Class | Role |
|-------|------|
| `TestController` | Per-process map `userId:testId` → `AttemptSession`; **one** in-progress test per user (`inProgressTestId`) |
| `AttemptSession` | Shuffled question/answer ids, progress index, stats for finish payload |
| `Contract\QuestionSelectionGraderInterface` | Grade one question’s selections |
| `SubmitQuestionResult` / `GradedQuestionCounts` | DTOs for WS layer |

## Workers (container)

Integration and WS commands live in **`Workers/`** (same pattern as `OrdersWorker`):

| Container key | Class |
|---------------|--------|
| `KnowledgeTestsSessionWorker` | Plugin resolve, grading bridge, `TestController`, session question rows |
| `KnowledgeTestsWsWorker` | WS command → JSON payloads |

`Chat.php` → `InteractionInterface` → `KnowledgeTestsWsWorker`. Domain rules stay in **`bp_knowledge_tests`**.

## Protocol

WS titles and JSON: [bp_knowledge_tests/spec/WS_PROTOCOL.md](../../bp_knowledge_tests/spec/WS_PROTOCOL.md).

## Parent

[../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)
