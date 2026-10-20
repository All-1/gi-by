# Component Context: bp_contracts/src/Testing

## Purpose

In-memory **unfinished** knowledge test sessions (`TestController`, spec §2.5). Completed attempts persist in `bp_knowledge_tests`; this folder holds **session facts only** (shuffle order, per-question counts, last selection).

Sessions die when the Ratchet process restarts (nightly ~**03:00**).

## Layout

| Path | Role |
|------|------|
| `Contract/` | Boundaries toward `bp_knowledge_tests` (interfaces only) |
| `*.php` (root) | Session RAM, DTOs, plugin bridge implementations |

## Types

| Class | Role |
|-------|------|
| `TestController` | Per-process map `userId:testId` → `AttemptSession` |
| `AttemptSession` | Shuffled question/answer ids, progress index, stats for finish payload |
| `Contract\QuestionSelectionGraderInterface` | Grade one question’s selections (implemented by plugin bridge) |
| `PluginQuestionSelectionGrader` | Calls `BpKnowledgeTests\Domain\QuestionSelectionGrader` |
| `KnowledgeTestsPluginResolver` | Resolves `Plugin` via `bp_knowledge_tests_plugin()` or `PluginBootstrap` |
| `SessionQuestionLoader` | Catalog → `TestController::startOrResume` question rows |
| `SubmitQuestionResult` / `GradedQuestionCounts` | DTOs for WS layer (Wave D+) |

## DI

Registered in `SystemConstructor` (singleton per WS process):

| Container key | Type |
|---------------|------|
| `KnowledgeTestsPluginResolver` | `KnowledgeTestsPluginResolver` |
| `SessionQuestionLoader` | `SessionQuestionLoader` |
| `TestController` | `TestController` |

WS handlers (Wave D–E) use **`KnowledgeTestsPluginResolver`** + **`TestController`** (+ **`SessionQuestionLoader`** for `startTest`). Call plugin facades via `$resolver->plugin()` for finish, retake, materials — no duplicated scoring rules in `Chat.php`. Add a multi-step orchestrator only when a handler would otherwise exceed the thin-controller rule.

## Protocol

WS titles and JSON: [bp_knowledge_tests/spec/WS_PROTOCOL.md](../../bp_knowledge_tests/spec/WS_PROTOCOL.md).

## Parent

[../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)
