# Component Context: bp_knowledge_tests

## Overview
WordPress plugin for **dealer knowledge tests** in the personal account. Domain logic lives here; **`bp_contracts`** provides UI shell, WebSocket transport, and in-memory `TestController` for unfinished attempts.

**Status**: Specification and implementation plan only — no runtime code yet.

## Documentation (canonical)

| Document | Purpose |
|----------|---------|
| [spec/Testing System — Project Documentation.md](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md) | Business requirements, rules, DB fields, UI (baseline §1 must not be silently changed) |
| [spec/IMPLEMENTATION_PLAN.md](./spec/IMPLEMENTATION_PLAN.md) | Architecture, phases, integration, WS proposal, engineering standards (§4.3), QA checklist |

## Integration

- [bp_contracts/PROJECT_CONTEXT.md](../bp_contracts/PROJECT_CONTEXT.md) — personal account, Ratchet, modals
- [Site PROJECT_CONTEXT.md](../../../PROJECT_CONTEXT.md) — portal map (add plugin link when implementing)

## Database (planned)

All tables use prefix `gi_new_test_*`.

- **Column lists (one place):** [spec/IMPLEMENTATION_PLAN.md §5.0](./spec/IMPLEMENTATION_PLAN.md#50-table-index) (`5.0.1` … `5.0.10`)
- **Business rules per table:** [spec/Testing System — Project Documentation.md §8–13](./spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md#8-proposed-database-tables)
