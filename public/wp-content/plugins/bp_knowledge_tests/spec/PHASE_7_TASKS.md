**Prerequisite:** Phases **1–6** **development complete** in repo ([PHASE_6_TASKS.md](./PHASE_6_TASKS.md)). Operator verification is **not** part of this phase — run [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) on staging **after** Phase 7 development.

# Phase 7 — Task breakdown (Hardening & release prep)

**Primary owner:** `bp_contracts` (WS entry: `InteractionInterface`, `KnowledgeTestsWsWorker`; transport security)  
**Domain owner:** `bp_knowledge_tests` (REST admin capabilities, `DealerTestsCapability`, plugin bootstrap / migrations)  
**Parent plan:** [IMPLEMENTATION_PLAN.md §11](./IMPLEMENTATION_PLAN.md#phase-7--hardening--release-prep-1115-weeks)  
**Site rules:** [DEVELOPMENT_RULES.md](../../../../DEVELOPMENT_RULES.md) §1 (security), §4 (WS restart / deploy notes)  
**Regression principles:** Product [§28](./Testing%20System%20%E2%80%94%20Project%20Documentation.md#28-important-current-principles) — map to CLI runners in [tests/](../tests/PROJECT_CONTEXT.md)  
**WS contract:** [WS_PROTOCOL.md](./WS_PROTOCOL.md) — validation must match locked shapes; document any new error codes here  
**Live status index:** [spec/PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md)

**Goal (development exit):** Knowledge-test WS and admin surfaces are **defense-in-depth** ready for staging: authenticated session binding, dealer access enforced server-side, strict payload validation, documented rate-limit stance, one-command domain regression, and a **production deploy checklist**. No open Phase 7 engineering checkboxes in IMPLEMENTATION_PLAN §11.

**Estimate:** ~1–1.5 weeks one developer (plan §11).

**Status:** **Not started** (task file created 2026-10-23).

---

## Readiness

| Ready | Implement / decide in Phase 7 |
|-------|-------------------------------|
| All WS titles implemented ([WS_PROTOCOL.md](./WS_PROTOCOL.md)) | **Server-side** `DealerTestsCapability` (or equivalent) on every mutating command — menu hide ≠ security |
| `knowledgeTestsUserId` from websocket session | Reject `userId` in client JSON; audit `getKnowledgeTestChatStars` batch scope |
| 13× `tests/run_*.php` scripts | Single `run_all_domain_tests.php` (or documented shell one-liner) with non-zero exit on failure |
| Phases 1–6 feature-complete | **D2** — promote interim roles doc to resolved (or explicit “V1 ships with interim”) |
| [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) §4 written | Execute MANUAL_QA **after** this phase only |

---

## Wave A — WS auth & access (D2 enforcement)

**Target:** `KnowledgeTestsWsWorker` + thin guards in `InteractionInterface` if needed (no business rules in Chat).

| Task | Done |
|------|------|
| A1 Central guard: `canAccessDealerTests($userId)` calling `bp_knowledge_tests_plugin()->dealerTestsCapability()->canAccess($wpUserId)` — map `gi_new_users.id_user` → WP user id via existing contracts resolver (same as page enqueue) | [ ] |
| A2 Apply guard to **all** client→server commands in §1.1 of [WS_PROTOCOL.md](./WS_PROTOCOL.md) (including `getKnowledgeTestNotifications`, `dismissKnowledgeTestNotification`, `getKnowledgeTestChatStars`) | [ ] |
| A3 Uniform denial payload: `ok: false`, code e.g. `forbidden` / `access_denied` — document in WS_PROTOCOL error table | [ ] |
| A4 **D2 doc:** Record final V1 role list in plugin [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) + `DealerTestsCapability`; close or update IMPLEMENTATION_PLAN §14 **D2** | [ ] |

**Manual spot-check (staging, post-dev):** [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) §2 security row — not a Phase 7 exit gate in repo.

---

## Wave B — WS validation & abuse

| Task | Done |
|------|------|
| B1 `requiredTestId` / type coercion audit — `testId`, `questionId`, `attemptId` positive ints; `notificationType` enum; `selectedAnswerIds` array of ints | [ ] |
| B2 `showTests` — cap `perPage` (already ≤100); reject negative `page` | [ ] |
| B3 `getKnowledgeTestChatStars` — max batch size (e.g. 50 `userIds`), dedupe, reject non-array body | [ ] |
| B4 Rate-limit **stance** documented (per-connection dismiss flood, optional debounce note in PROJECT_CONTEXT) — implement minimal server-side throttle only if product requires; default: document + monitor | [ ] |
| B5 Plugin unavailable / invalid JSON body — consistent `validation` / `plugin_unavailable` responses (no partial leaks) | [ ] |

---

## Wave C — Regression automation

| Task | Done |
|------|------|
| C1 Add `tests/run_all_domain_tests.php` that runs every `run_*_tests.php` in catalog order; exit code 1 on first failure | [ ] |
| C2 Document command in [tests/PROJECT_CONTEXT.md](../tests/PROJECT_CONTEXT.md) and plugin root [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md) | [ ] |
| C3 Map product §28 principles → script names (table in tests PROJECT_CONTEXT or Phase 7 PR description) | [ ] |
| C4 Optional: wire `run_all_domain_tests.php` into CI or `public/` pre-release doc — minimum: copy-paste block in deploy checklist | [ ] |

**Required scripts (minimum set):** `run_scoring_tests.php`, `run_selection_grader_tests.php`, `run_retake_eligibility_tests.php`, `run_validity_tests.php`, `run_complete_attempt_tests.php`, `run_notification_presentation_tests.php`, `run_notification_retake_tests.php`, `run_achievement_tier_tests.php`, `run_catalog_capability_tests.php`, `run_admin_config_validator_tests.php`.

---

## Wave D — Admin REST hardening (smoke)

| Task | Done |
|------|------|
| D1 Re-verify REST routes use `CatalogCapability` / admin caps — no anonymous catalog mutation | [ ] |
| D2 `run_catalog_capability_tests.php` green after any cap changes | [ ] |

---

## Wave E — Deploy & release checklist

Create **`spec/DEPLOY_V1.md`** (or section in MANUAL_QA appendix — prefer standalone deploy doc linked from plan §11).

| Checklist item | Done |
|----------------|------|
| E1 Plugin activate / migrations (`SchemaDefiner` version) on target DB | [ ] |
| E2 `bp_knowledge_tests` + `bp_contracts` deployed together (WS worker + JS) | [ ] |
| E3 **Restart WebSocket server** after protocol or `Chat` / worker changes ([DEVELOPMENT_RULES.md](../../../../DEVELOPMENT_RULES.md) §4) | [ ] |
| E4 Run `php tests/run_all_domain_tests.php` on build agent or release machine | [ ] |
| E5 Staging sign-off: [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) §1–6 complete | [ ] |
| E6 Production: CHANGELOG entry; backup DB; smoke login → Tests → one notification fetch | [ ] |

Link `DEPLOY_V1.md` from [IMPLEMENTATION_PLAN.md](./IMPLEMENTATION_PLAN.md) §11 Phase 7 and plugin [PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md).

---

## Wave F — Close Phase 7 (development)

- [ ] IMPLEMENTATION_PLAN §11 Phase 7 checkboxes
- [ ] [WS_PROTOCOL.md](./WS_PROTOCOL.md) error codes / forbidden access
- [ ] [bp_contracts/src/PROJECT_CONTEXT.md](../../bp_contracts/src/PROJECT_CONTEXT.md) — security note on knowledge-test commands
- [ ] [CHANGELOG.md](../../../../CHANGELOG.md) when behavior or deploy surface changes (User Rules §9)
- [ ] [spec/PROJECT_CONTEXT.md](./PROJECT_CONTEXT.md) phase table → Phase 7 **dev complete**

**Not in Wave F:** Executing MANUAL_QA (operators, staging).

---

## Out of scope

| Item | Where |
|------|--------|
| Full operator QA pass | [MANUAL_QA_V1.md](./MANUAL_QA_V1.md) |
| **D3** difficulty formula | Deferred (analytics only) |
| Laravel / PostgreSQL migration | Spec §24–25 future |
| Rewriting `Chat.php` or `communication_server.js` | DEVELOPMENT_RULES — extend workers only |

---

## Task checklist (copy for PRs)

- [ ] A WS dealer access guard + D2 documented
- [ ] B Payload validation + rate-limit stance
- [ ] C `run_all_domain_tests.php` + docs
- [ ] D Admin REST cap smoke
- [ ] E `DEPLOY_V1.md` + links
- [ ] F Plan §11 + PROJECT_CONTEXT sync
