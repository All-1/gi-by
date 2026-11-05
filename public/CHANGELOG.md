# Project Changelog

This document tracks changes to the project. Entry format is defined in **Cursor User Rules** (CHANGELOG section); global rules are not duplicated in [DEVELOPMENT_RULES.md](./DEVELOPMENT_RULES.md).

---

## Log

### [2026-11-05] Knowledge tests: Russian admin and no loading placeholder
**Author**: AI Assistant
**Logic**:
- The test-factory admin screens and WordPress menu were in English. Factory users work in Russian.
- The All Tests page showed «Loading test factory…» inside the Knowledge Tests block before and beside the React app. That placeholder is removed. An error line appears only if the bundle does not start.
**Changes**:
- `admin/src`: screen labels, errors, and dialogs in Russian
- `AdminCatalogMenu.php`, `AdminAppMount.php`: menu and page title in Russian; loading paragraph removed
- `assets/admin/app.js`: rebuilt bundle

### [2026-11-05] Knowledge tests: admin npm packages install into vendor
**Author**: AI Assistant
**Logic**:
- The test-factory build dependencies install in `public/vendor/node_modules`, outside the plugin. The Vite build loads React and Vite from there.
**Changes**:
- `admin/package.json`, `admin/vite.config.ts`: build uses `public/vendor/node_modules`
- `public/vendor/.gitignore`: ignore that install
- `admin/PROJECT_CONTEXT.md`, `admin/README.md`

### [2026-11-03] Knowledge tests: one standing read for attempt and medal
**Author**: AI Assistant
**Logic**:
- `AttemptServices` both wired the attempt use cases and answered latest-attempt and medal lookups. The websocket list and finish payloads called those lookups separately, including the medal id twice.
- The facade now only wires use cases. `UserTestStandingQuery` returns the latest attempt, medal id, tier, and rank name in one call. `NotificationRepository` is no longer a public property. `CompleteAttempt` takes `ClockInterface`, same as retake and validity.
**Changes**:
- `AttemptServices.php`: wiring only; public `standing`
- `UserTestStanding.php`, `UserTestStandingQuery.php`: [NEW] list and finish read model
- `CompleteAttempt.php`: `ClockInterface`
- `KnowledgeTestsWsWorker.php`: finish and test-list rows use `standing`
- `run_complete_attempt_tests.php`: standing cases; notifications read from the memory rows

### [2026-11-02] Knowledge tests: retake waits for materials and retake_delay_days
**Author**: AI Assistant
**Logic**:
- Spec §2.6 and §3.3–§3.4: another attempt needs the materials marked examined and the configured retake delay. A pass below lock was skipping both, so Начать stayed clickable.
- Pending materials now block every completed attempt. The delay applies again. Начать is shown disabled until both are clear. A locked score still has no start button.
**Changes**:
- `RetakeEligibilityPolicy.php`, `run_retake_eligibility_tests.php`
- `KnowledgeTestsWsWorker.php`: list rows include `retakeReasons`
- `knowledge_tests.js`: disabled Начать while waiting

### [2026-11-02] Knowledge tests: retake a pass below the lock score the same day
**Author**: AI Assistant
**Logic**:
- A pass below the lock score stayed blocked by the next-day retake delay, so Начать never appeared after the materials were reviewed.
- That pass can be started again the same day. A failed attempt still waits. A locked score still cannot be retaken.
**Changes**:
- `RetakeEligibilityPolicy.php`
- `run_retake_eligibility_tests.php`

### [2026-11-02] Knowledge tests: materials below lock, gold at lock
**Author**: AI Assistant
**Logic**:
- A pass below the lock score still has mistakes to study. Those questions get the same temporary materials page as a failed attempt, and the test can be retaken.
- A score at or above the lock threshold cannot be retaken and stores the gold medal.
**Changes**:
- `CompleteAttempt.php`, `MaterialsService.php`: write mistake materials unless the score is locked
- `AttemptResultClassifier.php`: locked pass tier is `gold`
- `KnowledgeTestsWsWorker.php`: finish payload `materialsRequired` follows pending materials
- `run_achievement_tier_tests.php`

### [2026-11-02] Knowledge tests: hide «Понятно» after the dismiss limit
**Author**: AI Assistant
**Logic**:
- After the configured dismiss limit, the bottom bar and the block notice stay up and can no longer be closed.
**Changes**:
- `knowledge_test_notifications.js`: omit «Понятно» when `dismissCount` reaches `dismissLimit`; the tests link no longer dismisses in that state

### [2026-11-02] Knowledge tests: «Понятно» on block notice and button hover
**Author**: AI Assistant
**Logic**:
- The block overlay had only a tests link, so «Понятно» was missing.
- Consent-bar buttons had no hover; account buttons turn sand `#dea993` with navy text.
**Changes**:
- `knowledge_test_notifications.js`: block overlay «Понятно» snoozes like the bottom bar
- `style.css`: hover on notification buttons

### [2026-11-02] Knowledge tests: block mode no longer uses the bottom bar
**Author**: AI Assistant
**Logic**:
- «Block account» still rendered a bottom bar until dismiss count reached the limit. Failed tests never opened a popup, so they stayed on the bottom bar for the whole limit.
- Block mode now opens the full-screen notice (a new test still opens once as a popup). Bottom mode stays a bottom bar. The bar reuses the account consent-bar markup.
**Changes**:
- `NotificationPresentation.php`, `run_notification_presentation_tests.php`
- `knowledge_test_notifications.js`, `style.css`: consent bar and denied-dialog buttons
- `SettingsPage.tsx`: hint for the two modes

### [2026-11-02] Knowledge tests: notification close and block timing
**Author**: AI Assistant
**Logic**:
- Spec points 6–7: popup «Закрыть» ends that visit; the bottom bar is the next entry (next day or WebSocket reconnect), not the same response.
- Bottom and popup keep two actions. A failed test uses «Материалы» and «Понятно», not a third «Тесты» button.
- Block mode is the state after the dismiss limit, shown on the next day or after the WebSocket reconnects, not in the visit that reached the limit.
**Changes**:
- `knowledge_test_notifications.js`: drop same-response bottom bypass; snooze block the same way; two buttons

### [2026-11-02] Knowledge tests: clickable reference and list pagination
**Author**: AI Assistant
**Logic**:
- Wrong-answer explanations printed the reference as text, so the link could not be opened.
- The tests list had no account-style page numbers and no “Выводить по” control. Default page size 20 hid paging for 15 tests.
**Changes**:
- `knowledge_tests.js`: reference rendered as a link; pagination and per-page selector (default 10)
- `index.php`: tests footer uses the same pagination and per-page markup as other account lists
- `style.css`: reference link color

### [2026-11-02] Knowledge tests: sample catalog seed
**Author**: AI Assistant
**Logic**:
- Local dealer testing needs a full catalog: tests, questions, explanations, reference links, and marked answers.
**Changes**:
- `bp_knowledge_tests/tests/seed_sample_catalog.php`: [NEW] inserts 15 tests if titles `SEED-KT` are absent
- Database: 15 tests, 225 questions, 1125 answers (correct rows contain the word `right`)

### [2026-11-02] Knowledge tests: match account table and notification styles
**Author**: AI Assistant
**Logic**:
- Tests list and in-test overlay used a separate flex layout and a text close control; notifications used tighter padding than the account consent bar and denied dialog.
- Align both with the existing account chrome: orders/contracts table, navy/sand buttons, bottom consent bar, denied-dialog buttons, modal close image.
**Changes**:
- `bp_contracts/css/style.css`: tests table, overlay, action buttons, notification popup and bottom bar
- `bp_contracts/index.php`: tests overlay close uses the theme close image

### [2026-10-30] Knowledge tests: show notification again after WS drop or logout
**Author**: AI Assistant
**Logic**:
- Calendar-day snooze in localStorage hid the bar after «Понятно»/«Закрыть» even when the WebSocket reconnected on the same page or the user logged out.
- Browser reload and moving to another PA page must keep the snooze (previous bug: every navigation looked like a WS reload).
**Changes**:
- `knowledge_test_notifications.js`: clear snooze on same-document socket drop and on logout link click
- `communication_server.js`: notify notification module on socket close

### [2026-10-30] Knowledge tests: notification snooze survives reload (fix WS epoch)
**Author**: AI Assistant
**Logic**:
- WS epoch bumped on every page load (race with `isPageReload`), clearing snooze so bottom bar reappeared on reload/navigation.
- Snooze is calendar-day only in `localStorage`; popup «Закрыть» snoozes for the day but still allows one bottom render in the same response.
**Changes**:
- `knowledge_test_notifications.js`: remove WS epoch snooze; legacy snooze map migration
- `communication_server.js`: remove reconnect pending hook

### [2026-10-30] Knowledge tests: notification snooze until next day or WS reconnect
**Author**: AI Assistant
**Logic**:
- Bottom «Понятно» should not reappear on every PA page the same day; only after calendar day change or WebSocket reconnect (not full page navigation).
- Block overlay after dismiss limit has no dismiss buttons by spec; clarify in admin + block hint text.
**Changes**:
- `knowledge_test_notifications.js`: localStorage snooze (date + WS epoch); epoch bumps on reconnect after drop
- `communication_server.js`: pending reconnect flag on WS close
- `SettingsPage.tsx`: admin hint for block mode

### [2026-10-30] Knowledge tests: notification re-show after dismiss (fix sessionStorage)
**Author**: AI Assistant
**Logic**:
- `sessionStorage` kept dismissed ids for the whole browser tab, so notifications never returned after reload, WS reconnect, or re-login in the same tab.
- Spec: bottom bar shows again on each account entry (new PA page load / WS fetch); popup «Закрыть» only moves to bottom, not permanent hide.
**Changes**:
- `knowledge_test_notifications.js`: in-memory hide for bottom «Понятно» only until next `getKnowledgeTestNotifications`; clear legacy sessionStorage key on fetch

### [2026-10-30] Knowledge tests: notification UX (presentation + tests page)
**Author**: AI Assistant
**Logic**:
- Spec §3.1: first view popup, then bottom until dismiss limit; at limit use admin `block`/`bottom`. Failed tests start as bottom.
- Old rule `dismissCount < limit → popup` re-showed popup after every «Закрыть».
- No notification chrome on `/tests/`; bottom «Понятно» hides until next fetch (not sessionStorage).
**Changes**:
- `NotificationPresentation.php`, `NotificationUiQuery.php`, `run_notification_presentation_tests.php`
- `knowledge_test_notifications.js`

### [2026-10-30] Knowledge tests: notifications on all PA pages + dismiss refresh
**Author**: AI Assistant
**Logic**:
- Notifications were only requested from `knowledge_tests.js` (loaded on `/tests/` only), so other account pages never fetched or showed UX.
- Dismiss cleared DOM before WS response; failed dismiss looked like dead buttons.
**Changes**:
- `communication_server.js`: `requestKnowledgeTestNotifications()` on WS ready (with nav highlight)
- `knowledge_test_notifications.js`: dismiss waits for `KnowledgeTestNotificationsOnPage` to re-render; «Тесты» dismisses on all pages
- `knowledge_tests.js`: overlay reconnect only in `onKnowledgeTestsWebSocketOpen`

### [2026-10-30] Knowledge tests: medal hover + author name row (chat, list, finish)
**Author**: AI Assistant
**Logic**:
- Spec §5.1: medals with hover `{rank} — {area}`; chat shows medals inline after dealer name.
**Changes**:
- `knowledge_tests_medals.js`: `createKnowledgeTestsMedalBadge`, hover block
- `knowledge_test_chat_stars.js`, `modal_window.js`: `knowledge_tests_author_with_medals`
- `knowledge_tests.js`: finish screen badge; `style.css`: hover + layout
- `KnowledgeTestsWsWorker.php`, `AttemptServices.php`: `medalRankName` on list/finish

### [2026-10-30] Knowledge tests: chat medal hover uses configured rank name
**Author**: AI Assistant
**Logic**:
- Spec §5.1 hover is `{rank from config} — {area}`, not the achievement row name (`Bronze`).
**Changes**:
- `TestConfigReader.php`: `rankLabel`
- `AttemptAchievementService.php`: chat star `rankName` from `rank_name_*`

### [2026-10-29] Knowledge tests: list medal icons missing (findMedalById)
**Author**: AI Assistant
**Logic**:
- `selectUni_2(..., 'name, value')` + `whatExist` dropped multi-column rows, so `medalTier` was always empty and the UI could not show Bronze/Silver/Gold pictures.
**Changes**:
- `AchievementRepository.php`: load medal row with `*` and map `name`/`value`
- `tests/run_achievement_repository_tests.php`: regression check

### [2026-10-29] Knowledge tests: medal icons from wp-diary theme
**Author**: AI Assistant
**Logic**:
- Dealer list and finish screen showed numeric medal id; product uses Bronze/Silver/Gold SVGs in the theme.
**Changes**:
- `AttemptServices.php`: `findMedalTierValueForUserAndTest`
- `KnowledgeTestsWsWorker.php`: `medalTier` on list and finish payloads
- `knowledge_tests_medals.js`: tier → CSS class; SVG paths in `style.css` as `background-image`
- `index.php`: enqueue medals.js before chat stars when plugin active
- `knowledge_tests.js`: list + finish medal columns use shared helpers
- `style.css`: `.knowledge_tests_medal_icon`

### [2026-10-29] Knowledge tests: scoring formula (max − invalid) / max
**Author**: AI Assistant
**Logic**:
- Product requested official score **not** use selected-correct minus invalid; use **(all correct options in attempt − wrong selections) / all correct options** so 4 right and 1 wrong on a 5-option test → 80%.
**Changes**:
- `ScoringService.php`: `calculateScore(invalid, maximumCorrect)`
- `CompleteAttempt.php`: caller updated
- `run_scoring_tests.php`, `run_complete_attempt_tests.php`: expectations
- `knowledge_tests.js`: finish breakdown text

### [2026-10-29] Knowledge tests: score denominator scoped to attempt questions
**Author**: AI Assistant
**Logic**:
- Finish used `maximumCorrectForTest` (entire catalog). Extra/unanswered questions inflated the denominator so 4 correct and 1 wrong could show ~30% instead of ~60%, or fail when the user expected to pass.
- Official score (spec §2.1) still uses `(valid − invalid) / maximumCorrect` for **questions in this attempt**.
**Changes**:
- `AnswerCatalog.php`: `maximumCorrectForQuestions`
- `CompleteAttempt.php`: denominator from finish line question ids
- `knowledge_tests.js`: score breakdown on finish screen
- `tests/run_outcome_maximum_scope_tests.php`: [NEW] regression

### [2026-10-28] Knowledge tests: notification dismiss stuck on /tests/
**Author**: AI Assistant
**Logic**:
- `syncNewTestRowsForUser` called `resetDismissCount` on every list fetch, zeroing dismiss count after the user closed the popup.
- On `/tests/`, popup blocked interaction; «Перейти к тестам» reloaded the same page without dismissing.
**Changes**:
- `NotificationRepository.php`: `ensureRowExists` (insert only)
- `NotificationUiQuery.php`: sync uses ensure, not reset
- `knowledge_test_notifications.js`: no popup on tests page; link dismisses when already there; optimistic UI clear

### [2026-10-28] Knowledge tests: finish persist without reload + medal scalar reads
**Author**: AI Assistant
**Logic**:
- After INSERT, `findById` could still fail on some DBWorker shapes; persist now returns an `AttemptRecord` built from the insert payload when reload misses.
- `selectUni_2` with one column returns a bare scalar — achievement/medal lookups returned null or skipped tiers incorrectly.
**Changes**:
- `CompleteAttempt.php`: validate insert id; synthesize record on reload miss
- `SelectScalar.php`: [NEW] parse single-column selects
- `AchievementRepository.php`, `UserAchievementRepository.php`: use `SelectScalar`
- `KnowledgeTestsWsWorker.php`: single try around finish + response; `attempt_insert_failed` reason
- `knowledge_tests.js`: clearer message when DB insert fails

### [2026-10-28] Knowledge tests: AttemptRepository findById after insert
**Author**: AI Assistant
**Logic**:
- `selectUni_2` + `convertDataFromDB` returns a camelCase **array** for one row; `findById` only accepted `stdClass`, so reload after insert returned null → `Attempt was not persisted` → `server_error (finish_failed)`.
**Changes**:
- `AttemptRepository.php`, `NotificationRepository.php`: map reads through `RecordMapper::rows` like other repositories

### [2026-10-28] Knowledge tests: finishTest WS drop and disconnect banner
**Author**: AI Assistant
**Logic**:
- Uncaught errors during `finishTest` (e.g. missing achievement tier, JSON encode) could kill the WS handler; the overlay showed the generic disconnect banner instead of a result or error.
- While finish was in flight, any socket close looked like mid-test disconnect.
**Changes**:
- `KnowledgeTestsWsWorker.php`, `Chat.php`: catch failures; always respond with `TestFinishedOnPage`
- `AttemptAchievementService.php`: log missing tier instead of throwing
- `knowledge_tests.js`: finish pending state, retry finish on reconnect, clearer banners
- `knowledge_test_notifications.js`: drop duplicate `onKnowledgeTestsWebSocketOpen` (tests page owns hook)

### [2026-10-28] Knowledge tests: fix maximumCorrect count from DBWorker
**Author**: AI Assistant
**Logic**:
- `AnswerRepository::countCorrectForTest` expected an array row; `DBWorker::getRawSQL` unwraps a single COUNT row to an object, so `maximumCorrectForTest` was always 0 and finish failed with `test_not_scorable`.
**Changes**:
- `AnswerRepository.php`: parse count via `RecordMapper::rows`
- `KnowledgeTestsWsWorker.php`: block `startTest` when test has no scorable correct options

### [2026-10-28] Knowledge tests: finish attempt validation vs session snapshot
**Author**: AI Assistant
**Logic**:
- `CompleteAttempt` required finish lines to match the **current** full catalog; if the catalog changed after `startTest`, `finishTest` failed with generic `validation`.
- `/tests/` WS reconnect looked up `handlerTests` (pathname) but only `handlerTestsOnPage` existed.
**Changes**:
- `bp_knowledge_tests/src/Services/Attempt/CompleteAttempt.php`: validate question membership in test, not full catalog equality
- `bp_contracts/src/Workers/KnowledgeTestsWsWorker.php`: require completed session; map scorable-test errors
- `bp_contracts/js/knowledge_tests.js`: add `handlerTests` for page init
- `bp_knowledge_tests/spec/WS_PROTOCOL.md`: finish payload = session questions

### [2026-10-28] bp_contracts: createTagHtml multi-class strings
**Author**: AI Assistant
**Logic**:
- `classList.add()` accepts one token; knowledge tests list cells pass `"td_div_knowledge_tests kt-col-title"`, which broke `renderKnowledgeTestsList` on `TestsOnPage`.
**Changes**:
- `bp_contracts/js/utility.js`: split `htmlClass` on whitespace before `classList.add`

### [2026-10-23] Knowledge tests: fix CLI tests and selection grader iteration
**Author**: AI Assistant
**Logic**:
- `QuestionSelectionGrader` iterated set values (`true`) instead of answer ids (keys), breaking §2.1.1 counts when wrong options were selected.
- Two runners required `vendor/autoload.php`; fixture omitted `DealerTestsCapability` on `Plugin`.
**Changes**:
- `Domain/QuestionSelectionGrader.php`: foreach over id => value map
- `tests/support/require_plugin_autoload.php`: [NEW] fallback autoload
- `run_admin_config_validator_tests.php`, `run_attempt_validity_label_tests.php`: use fallback
- `CompleteAttemptTestFixture.php`: fifth `Plugin` dependency

### [2026-10-23] Knowledge tests: Phase 7 task breakdown
**Author**: AI Assistant
**Logic**:
- Phase 7 work (WS hardening, regression runner, deploy checklist) needs the same wave/checklist structure as Phases 4–6 before implementation starts.
**Changes**:
- `bp_knowledge_tests/spec/PHASE_7_TASKS.md`: [NEW] waves A–F (auth, validation, `run_all_domain_tests`, `DEPLOY_V1.md`, close)
- `IMPLEMENTATION_PLAN.md`, `spec/PROJECT_CONTEXT.md`, plugin + `bp_contracts` PROJECT_CONTEXT: link Phase 7 tasks

### [2026-10-23] Knowledge tests: Phase 6 close — operator steps in MANUAL_QA only
**Author**: AI Assistant
**Logic**:
- Phase 6 development exit: manual verification belongs in the consolidated QA doc, not open checkboxes on the phase task list.
- Synced stale spec index (§3) with repo state (Phases 1–6 dev complete).
**Changes**:
- `bp_knowledge_tests/spec/MANUAL_QA_V1.md`: §4 expanded (setup, new/failed/prerequisite/D10/retake-block steps)
- `PHASE_6_TASKS.md`: Wave D complete; status **development complete**
- `spec/PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md` §3 + delivery sequence
- `bp_contracts/PROJECT_CONTEXT.md`, `bp_contracts/js/PROJECT_CONTEXT.md`: Phase 6 complete note + `knowledge_test_notifications.js`

### [2026-10-23] Knowledge tests: Phase 6 notifications UX (WS + PA client)
**Author**: AI Assistant
**Logic**:
- Product §3.1–§3.2 requires dismiss limits, then block or bottom bar; domain already persisted rows and retake blocks in Phase 2.
- **D10:** one notification row per `(user_id, test_id, notification_type)` matches baseline DDL and `NotificationRepository`.
**Changes**:
- `bp_knowledge_tests`: `NotificationUiQuery`, `NotificationPresentation`; `attempts()->notificationUi`; pass attempt clears `new_test` row
- `bp_contracts`: WS commands + `knowledge_test_notifications.js`; styles; enqueue with dealer nav
- `WS_PROTOCOL.md`, `IMPLEMENTATION_PLAN.md` §11 Phase 6, `PHASE_6_TASKS.md`, `PROJECT_CONTEXT.md` files
- `tests/run_notification_presentation_tests.php`: [NEW] presentation phase unit checks

### [2026-10-23] Knowledge tests: dev-first delivery — Phase 6 next, manual QA after Phase 7
**Author**: AI Assistant
**Logic**:
- Team chose to finish Phases **6–7** development before consolidated staging QA, instead of blocking Phase 6 on Phase 4–5 operator checklists.
- Single operator script avoids duplicate QA passes and matches vanilla JS dealer UI (not React) already in repo.
**Changes**:
- `bp_knowledge_tests/spec/MANUAL_QA_V1.md`: [NEW] consolidated staging checklist (Phases 4–6 + §12)
- `bp_knowledge_tests/spec/PHASE_6_TASKS.md`: [NEW] notifications task breakdown
- `bp_knowledge_tests/spec/IMPLEMENTATION_PLAN.md`: delivery sequence, D7 UI split, phase exits, §14.1 gates
- `bp_knowledge_tests/spec/PROJECT_CONTEXT.md`, `bp_knowledge_tests/PROJECT_CONTEXT.md`, `PHASE_4_TASKS.md`, `PHASE_5_TASKS.md`, `bp_contracts/PROJECT_CONTEXT.md`, `admin/PROJECT_CONTEXT.md`

### [2026-10-23] Knowledge tests: Phase 5 B3/E/F — medals, materials, chat stars
**Author**: AI Assistant
**Logic**:
- List/finish medals and chat stars need plugin reads without duplicating scoring rules in `bp_contracts`.
- Materials flow requires per-reference acknowledgment before **Изучено**; empty `items` after confirm closes overlay and refreshes list.
**Changes**:
- `bp_knowledge_tests`: `UserAchievementRepository` / `AchievementRepository` read helpers; `AttemptAchievementService` medal + chat star batch
- `KnowledgeTestsWsWorker`: `medalId` on list/finish; `chatStarsForUsers`
- `Chat.php` / `InteractionInterface`: `getKnowledgeTestChatStars` → `KnowledgeTestChatStarsOnPage`
- `bp_contracts/js/knowledge_test_chat_stars.js`: [NEW] batch fetch + author line stars
- `knowledge_tests.js`: materials reviewed checkboxes; overlay focus + Escape
- `modal_window.js`: request stars on dialog load; append to `.author_message`
- `style.css`: material blocks + chat star tier colors
- `WS_PROTOCOL.md`, `PHASE_5_TASKS.md`, `IMPLEMENTATION_PLAN.md` §11, plugin + contracts `PROJECT_CONTEXT.md`

### [2026-10-23] bp_contracts: onWebSocketSessionReady in communication_server (no openConnection patch)
**Author**: AI Assistant
**Logic**:
- The PA client has one `socket`; session bootstrap belongs in the connection owner, not monkey-patched wrappers.
**Changes**:
- `communication_server.js`: `onWebSocketSessionReady` / `onWebSocketSessionClosed` (`setDefaultState`, optional knowledge-tests globals)
- Removed `ws_open_extensions.js`
- `DEVELOPMENT_RULES.md` §2.5, `js/PROJECT_CONTEXT.md`: socket ownership and hook contract

### [2026-10-23] DEVELOPMENT_RULES: avoid editing communication_server.js
**Author**: AI Assistant
**Logic**:
- Shared WS bootstrap is high-risk; feature hooks belong in page/feature scripts unless there is no practical alternative.
**Changes**:
- `DEVELOPMENT_RULES.md` §2.5: `communication_server.js` stability rule (v2.6)
- Reverted knowledge-tests hooks from `communication_server.js`; bridge lives in `knowledge_tests_nav.js`
- `bp_contracts/js/PROJECT_CONTEXT.md`: pointer to §2.5

### [2026-10-23] Knowledge tests: Phase 5 Wave D — in-test flow (§20)
**Author**: AI Assistant
**Logic**:
- Spec requires immediate feedback, then explicit continue before the next question; RAM session advances on submit, so the client resumes via `startTest` for the next step.
**Changes**:
- `bp_contracts/js/knowledge_tests.js`: two-step answer/feedback UI, finish + materials CTA, reconnect hooks
- `bp_contracts/js/knowledge_tests_nav.js`: WS bridge for nav + in-test reconnect (no `communication_server.js` hooks)
- `bp_contracts/css/style.css`: answer feedback + disconnect banner styles
- `WS_PROTOCOL.md`, `PHASE_5_TASKS.md`: Wave D client flow documented

### [2026-10-22] Knowledge tests: Phase 5 Wave C — Tests list nav and UI
**Author**: AI Assistant
**Logic**:
- Dealers need a **Tests** menu entry, §19 list rows, pagination, and §3.2 failed-state nav styling driven from WS list payload.
**Changes**:
- `wp-diary/myaccount-page.php`: **Тесты** nav (`/tests/`) when `DealerTestsCapability` allows
- `lang-admin.php`: `m_knowledge_tests`
- `bp_contracts/js/knowledge_tests.js`: RU list UI, medal column placeholder, disabled actions, nav sync
- `bp_contracts/js/knowledge_tests_nav.js`: [NEW] nav highlight on other PA pages
- `bp_contracts/js/utility.js`, `communication_server.js`: `updateKnowledgeTestsNavFromList`, WS bootstrap
- `KnowledgeTestsWsWorker.php`: `navFailedHighlight` on `TestsOnPage`
- `WS_PROTOCOL.md`, `PHASE_5_TASKS.md`: Wave C status

### [2026-10-22] Knowledge tests: dealer page markup in bp_contracts
**Author**: AI Assistant
**Logic**:
- Personal account HTML belongs in `bp_contracts/index.php` shortcodes, not plugin PHP string markup.
**Changes**:
- `bp_contracts/index.php`: `knowledge_tests()` shell + `add_shortcode('knowledge_tests')`
- Removed `DealerTestsMount.php`; `Plugin` exposes `dealerTestsCapability()` for access check only

### [2026-10-21] Knowledge tests: dealer UI — vanilla JS (not React)
**Author**: AI Assistant
**Logic**:
- Personal account modules use page-specific JS + `communication_server.js`, not a React bundle; align Tests UI with orders/analytics.
**Changes**:
- `bp_contracts/js/knowledge_tests.js`: list, overlay, WS handlers; removed React dispatch bridge
- `bp_contracts/index.php`: `knowledge_tests()` page; dropped global enqueue of `knowledge_tests.js`
- `DealerTestsMount.php`: HTML shell only; removed React asset enqueue classes
- Removed `bp_contracts/dealer/` React scaffold; CSS in `css/style.css`
- `PHASE_5_TASKS.md`: dealer UI = vanilla JS

### [2026-10-21] Knowledge tests: Phase 5 task breakdown
**Author**: AI Assistant
**Logic**:
- Dealer React work needs the same wave/checklist structure as Phases 3–4 before implementation starts.
**Changes**:
- `bp_knowledge_tests/spec/PHASE_5_TASKS.md`: [NEW] waves A–G, readiness, PR checklist
- `spec/PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md`, `bp_contracts/PROJECT_CONTEXT.md`: links to Phase 5 tasks

### [2026-10-21] Knowledge tests: Phase 4 complete — cross-test in-progress guard
**Author**: AI Assistant
**Logic**:
- WS protocol requires `test_in_progress` when RAM holds another test; list rows must not offer Start on other tests.
**Changes**:
- `bp_contracts/src/Testing/TestController.php`: `inProgressTestId()`
- `bp_contracts/src/Workers/KnowledgeTestsWsWorker.php`: block `startTest`, `canStart` on cross-test session
- `bp_contracts/tests/run_test_controller.php`: cover `inProgressTestId`
- Phase 4 / IMPLEMENTATION_PLAN / PROJECT_CONTEXT docs: Phase 4 closed

### [2026-10-21] Knowledge tests: consolidate contracts workers
**Author**: AI Assistant
**Logic**:
- Too many small KnowledgeTests* classes; align with bp_contracts container style (`OrdersWorker` pattern).
**Changes**:
- `bp_contracts/src/Workers/KnowledgeTestsSessionWorker.php`, `KnowledgeTestsWsWorker.php`: [NEW] two container services
- Removed resolver, payload helpers, `KnowledgeTestsWsCommands` from `src/Testing/`
- `SystemConstructor.php`, `InteractionInterface.php`, context docs updated

### [2026-10-20] Knowledge tests: Phase 4 Wave D — WS routing + commands
**Author**: AI Assistant
**Logic**:
- Dealers need locked protocol titles routed through Chat/InteractionInterface with responses matching WS_PROTOCOL.md.
**Changes**:
- `bp_contracts/src/Chat.php`, `Controler/InteractionInterface.php`: knowledge-test command dispatch
- `bp_contracts/src/Testing/KnowledgeTestsWsCommands.php` (+ payload helpers): command handling
- `bp_contracts/js/communication_server.js`: stub handlers for TestsOnPage / TestQuestionOnPage / …
- `bp_knowledge_tests/src/Services/AttemptServices.php`: `findLatestAttempt()` for test list rows
- Phase 4 docs: Wave D complete; E largely implemented

### [2026-10-20] Knowledge tests: Phase 4 Wave C — plugin resolver (no proxy gateway)
**Author**: AI Assistant
**Logic**:
- WS layer needs a single plugin entry point without duplicating scoring rules; a façade of one-line proxy methods violates project anti-patterns (§13.2).
**Changes**:
- `bp_knowledge_tests/index.php`: `bp_knowledge_tests_plugin()` shared runtime accessor
- `bp_contracts/src/Testing/KnowledgeTestsPluginResolver.php`, `SessionQuestionLoader.php`: [NEW] resolver + catalog→session rows
- `bp_contracts/src/Testing/PluginQuestionSelectionGrader.php`, `SystemConstructor.php`: wire resolver into DI
- Phase 4 task/plan/context docs: Wave C complete

### [2026-10-19] Knowledge tests: Phase 4 Wave B — TestController RAM session
**Author**: AI Assistant
**Logic**:
- Unfinished attempts must live in bp_contracts RAM per spec §2.5 before WS handlers call finish in the plugin.
**Changes**:
- `bp_contracts/src/Testing/*`: [NEW] `TestController`, `AttemptSession`, grader interface + plugin bridge
- `bp_contracts/src/Core/SystemConstructor.php`: register `TestController`
- `bp_contracts/tests/run_test_controller.php`: [NEW] isolated smoke test
- Phase 4 task/plan context files updated (Wave B complete)

### [2026-10-19] Knowledge tests: Phase 4 Wave A — lock WS protocol (D1)
**Author**: AI Assistant
**Logic**:
- Handlers must not merge until client/server message titles and minimal JSON contracts are agreed; IMPLEMENTATION_PLAN §4.5 proposals are adopted unchanged.
**Changes**:
- `bp_knowledge_tests/spec/WS_PROTOCOL.md`: [NEW] canonical titles, payloads, error codes, command→response map
- `bp_contracts/src/PROJECT_CONTEXT.md`: knowledge-tests command tables (handlers still pending)
- `bp_knowledge_tests/spec/PHASE_4_TASKS.md`, `IMPLEMENTATION_PLAN.md`, plugin `PROJECT_CONTEXT.md`, `spec/PROJECT_CONTEXT.md`: Wave A status and D1 resolved

### [2026-10-16] Knowledge tests: expand PROJECT_CONTEXT narrative
**Author**: AI Assistant
**Logic**:
- Index-only context files were hard to onboard from; the root and key folders need purpose, architecture, state model, and flow diagrams without duplicating the full product spec.
**Changes**:
- Enriched [PROJECT_CONTEXT.md](./wp-content/plugins/bp_knowledge_tests/PROJECT_CONTEXT.md) (system diagram, layering, scoring summary, phases, directory map)
- Expanded `src/`, `spec/`, `Domain/`, `Services/Attempt/`, `admin/`, `Bootstrap/`, `Infrastructure/`, `tests/` context files

### [2026-10-16] Knowledge tests: nested PROJECT_CONTEXT map
**Author**: AI Assistant
**Logic**:
- Plugin root context was a monolith; per-directory context files match site conventions (`bp_contracts`) and keep facts in one canonical place.
**Changes**:
- `bp_knowledge_tests/**/PROJECT_CONTEXT.md` under `src/`, `admin/`, `spec/`, `migrations/`, `tests/`, `assets/`
- Root [PROJECT_CONTEXT.md](./wp-content/plugins/bp_knowledge_tests/PROJECT_CONTEXT.md) is now an index with links; REST route table lives under `src/Infrastructure/Http/`

### [2026-10-16] Knowledge tests: Phase 3 Wave F — close admin phase
**Author**: AI Assistant
**Logic**:
- React admin covers catalog, settings, results, and analytics; the temporary **Tools → Knowledge Tests (dev)** smoke page duplicated listing and is no longer needed.
**Changes**:
- Removed `DevToolsMenu` and its registration from `Plugin::boot()`
- `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md`, `PHASE_3_TASKS.md`, `PHASE_4_TASKS.md` — Phase 3 marked complete; next Phase 4

### [2026-10-16] Knowledge tests: Phase 3 Wave E results and analytics
**Author**: AI Assistant
**Logic**:
- Admins need §17 completed-attempt listing and §18 test/question analytics from existing attempt tables without duplicating domain rules in React.
**Changes**:
- `ReportingServices`, `ResultsListQuery`, `TestAnalyticsQuery`; `AttemptAdminRepository`, `AttemptAnalyticsRepository`
- REST `GET /results`, `GET /analytics/tests`, `GET /analytics/tests/{id}`; WP admin **Results** / **Analytics** menus
- `admin/src/pages/ResultsPage.tsx`, `AnalyticsPage.tsx`; rebuilt `assets/admin/app.js`
- `tests/run_attempt_validity_label_tests.php`; `PHASE_3_TASKS.md`, `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md`

### [2026-10-15] Knowledge tests: correct-answer checkbox IDs from REST
**Author**: AI Assistant
**Logic**:
- Listing correct answer IDs used a single-column `id` select. DBWorker unwraps that to plain integers, so `RecordMapper::rows()` produced no rows and REST always returned `correctAnswerIds: []`, undoing the checkbox after reload.
**Changes**:
- `AnswerRepository::listCorrectAnswerIdsForQuestion()`: select full rows (`*`) before mapping IDs

### [2026-10-14] Knowledge tests: question/answer findById row mapping
**Author**: AI Assistant
**Logic**:
- `DBWorker::selectUni_2` can return camelCase arrays, not only objects. Question and answer `findById` ignored those rows, so REST create returned 500 and answer routes reported “Question not found.”
**Changes**:
- `QuestionRepository.php`, `AnswerRepository.php`: use `RecordMapper::rows()` like `TestRepository::findById`

### [2026-10-14] Knowledge tests: React catalog UI only
**Author**: AI Assistant
**Logic**:
- PHP forms duplicated the list and never opened the question editor. The admin screens should be the React app.
**Changes**:
- `AdminCatalogMenu` renders only the React mount; bundle prints in `admin_footer` after that node
- Removed `AdminTestEditor`; rebuilt `assets/admin/app.js`

### [2026-10-14] Knowledge tests: PHP test editor
**Author**: AI Assistant
**Logic**:
- React never mounted, so Edit showed the catalog list twice plus “Loading test factory…”. Editing must work without that bundle.
**Changes**:
- `AdminCatalogMenu`: one All Tests table; Add Test is create-only
- `AdminTestEditor`: [NEW] save test, questions, answers, correct flags

### [2026-10-14] Knowledge tests: map DBWorker rows before catalog list
**Author**: AI Assistant
**Logic**:
- A single `DBWorker` result is an unwrapped camelCase array. Listing treated the numeric id as a row and crashed `toTestRecord()`.
**Changes**:
- `RecordMapper::rows()` normalizes objects and camelCase arrays; `TestRepository::listAll()` queries `gi_new_tests`

### [2026-10-14] Knowledge tests: fix test list query (empty admin / REST)
**Author**: AI Assistant
**Logic**:
- `TestRepository::listAll()` used raw SQL on DBWorker key `KnowledgeTests` instead of `gi_new_tests`, so list/API returned no rows while create still worked.
**Changes**:
- `TestRepository.php`, `ConfigRepository.php`; PHP catalog table on admin list/edit screens

### [2026-10-14] Knowledge tests: Phase 3 Wave C admin catalog UI
**Author**: AI Assistant
**Logic**:
- Content admins need §15 test factory in React (list, editor, critical update), not read-only shell.
**Changes**:
- `admin/src`: `AllTestsPage`, `TestEditorPage`, `QuestionEditor`, API client, styles; rebuild `assets/admin/app.js`
- `PHASE_3_TASKS.md`, `IMPLEMENTATION_PLAN.md`, `PROJECT_CONTEXT.md`

### [2026-10-14] Knowledge tests: schema on boot + WP Admin menu
**Author**: AI Assistant
**Logic**:
- Activation could skip schema when `$servicesContainer` was missing; boot exited silently with no UI.
**Changes**:
- Load `wordpress_framework` when active but container unset; admin notice if framework missing
- `installSchemaIfNeeded()` on activate and every boot; WP Admin **Knowledge Tests** menu
- `CatalogCapability::registerCaps` grants catalog cap to roles with `manage_options`

### [2026-10-13] Knowledge tests: plugin autoload on activate
**Author**: AI Assistant
**Logic**:
- Activation failed when neither site nor plugin `vendor/autoload.php` mapped `BpKnowledgeTests\`.
**Changes**:
- `bp_knowledge_tests/index.php`: PSR-4 fallback autoloader; `PROJECT_CONTEXT.md` entry note

### [2026-10-13] Knowledge tests: defer boot until services container exists
**Author**: AI Assistant
**Logic**:
- Plugin `index.php` ran before `wordpress_framework` set `$servicesContainer`, causing a TypeError on construct.
**Changes**:
- `index.php`: `plugins_loaded` (priority 11) for `run()`; activation hook resolves container after dependencies load

### [2026-10-13] Knowledge tests: Phase 3 Wave B capabilities + React mount
**Author**: AI Assistant
**Logic**:
- Admin UI needs a stable capability boundary and a personal-account mount with REST nonce/config before Wave C screens.
**Changes**:
- `bp_knowledge_tests/src/Infrastructure/Security/CatalogCapability.php`, `Admin/AdminAppMount.php`, `AdminAppConfig.php`; REST `RestPermission` delegates to capability
- `admin/` Vite React shell → `assets/admin/app.js`; shortcode `[knowledge_tests_admin]`
- `tests/run_catalog_capability_tests.php`; `PHASE_3_TASKS.md`, `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md`

### [2026-10-12] Knowledge tests: Phase 3 Wave A admin REST API
**Author**: AI Assistant
**Logic**:
- React admin (Wave C) needs a capability-checked HTTP surface; delegates to existing catalog/attempt services only.
**Changes**:
- `bp_knowledge_tests/src/Infrastructure/Http/*` — `bp-knowledge-tests/v1` routes; `Plugin::boot()` registers `AdminRestApi`
- `PROJECT_CONTEXT.md`: route table; `PHASE_3_TASKS.md`, `IMPLEMENTATION_PLAN.md` §11

### [2026-10-12] Knowledge tests: reorder phases (admin before WS)
**Author**: AI Assistant
**Logic**:
- Phase **3** = React admin (former Phase 6); Phases **4–6** = former 3–5 (WS, dealer UI, notifications); Phase **7** unchanged.
**Changes**:
- `spec/IMPLEMENTATION_PLAN.md` §11, §3, §14.1; `PHASE_3_TASKS.md` (admin), `PHASE_4_TASKS.md` (WS); `PROJECT_CONTEXT.md`, `bp_contracts/PROJECT_CONTEXT.md`

### [2026-10-12] Knowledge tests: Phase 3 task breakdown
**Author**: AI Assistant
**Logic**:
- Phase 3 spans `bp_contracts` + `bp_knowledge_tests`; execution backlog lives beside Phase 2 pattern.
**Changes**:
- `bp_knowledge_tests/spec/PHASE_3_TASKS.md`: [NEW] waves A–F, WS protocol, checklist
- `spec/IMPLEMENTATION_PLAN.md`, `PROJECT_CONTEXT.md`: links to Phase 3 tasks

### [2026-10-12] Knowledge tests: Phase 2 exit (complete attempt integration)
**Author**: AI Assistant
**Logic**:
- Close Wave F / A4: scripted `CompleteAttempt` lifecycle verifiable without WordPress; Phase 3 can start.
**Changes**:
- `bp_knowledge_tests/tests/run_complete_attempt_tests.php`, `run_complete_attempt_script.php`, `tests/support/MemoryDBWorker.php`, `CompleteAttemptTestFixture.php`
- `spec/PHASE_2_TASKS.md`, `spec/IMPLEMENTATION_PLAN.md`, `PROJECT_CONTEXT.md`, `migrations/README.md`: Phase 2 complete

### [2026-10-12] Knowledge tests: remove is_correct schema upgrade (v4)
**Author**: AI Assistant
**Logic**:
- Greenfield installs: `is_correct` is in initial `CREATE TABLE` (`001_baseline.sql`, `SchemaDefiner`); no legacy junction migration.
**Changes**:
- Removed `upgradeAnswersIsCorrectColumn()` and activate step; deleted `004_is_correct_on_answers.sql`; schema target **3**.

### [2026-10-12] Knowledge tests: remove notification_type schema upgrade (v5)
**Author**: AI Assistant
**Logic**:
- Plugin not deployed yet; `notification_type` belongs in initial `CREATE TABLE` only, not a follow-up `ALTER`.
**Changes**:
- Removed `upgradeNotificationsNotificationType()` and activate step; schema target back to **4**.

### [2026-10-12] Knowledge tests: drop 005_notifications_type.sql
**Author**: AI Assistant
**Logic**:
- v5 upgrade lives in `SchemaDefiner::upgradeNotificationsNotificationType()` on activate; baseline already defines `notification_type` for new installs.
**Changes**:
- Removed `migrations/005_notifications_type.sql`; updated `migrations/README.md`, `PHASE_2_TASKS.md`.

### [2026-10-12] Knowledge tests: notification_type in baseline DDL
**Author**: AI Assistant
**Logic**:
- `notification_type` belongs in canonical schema for new installs; v5 upgrade remains for older DBs only.
**Changes**:
- `migrations/001_baseline.sql`, `SchemaDefiner` notifications `CREATE`: `notification_type`, `UNIQUE user_test_type`; `005_notifications_type.sql` comment clarified.

### [2026-10-12] Knowledge tests: Wave E notifications (Phase 2)
**Author**: AI Assistant
**Logic**:
- `gi_new_test_notifications` rows per `(user, test, type)` with `dismiss_count`; failed complete resets `failed_test` row (§3.2); pass clears it.
- Retake blocked when config mode is `block` and dismiss limit reached (`notification_blocked`; §3.1–§3.2).
- Schema v5 adds `notification_type` on existing installs.
**Changes**:
- `NotificationRepository`, `NotificationService`, `NotificationRetakePolicy`; hook in `CompleteAttempt`; `RetakeEligibilityContext` + reason code.
- `TestConfigReader::notificationSettings`; `tests/run_notification_retake_tests.php`; migrations `005_*`, target schema **5**.

### [2026-10-09] Knowledge tests: Wave D validity and critical update (Phase 2)
**Author**: AI Assistant
**Logic**:
- Latest `passed` attempt outdates after one calendar year from `date_finished` (§2.3); achievement row removed (§2.15).
- Critical update marks all `passed` attempts for a test `outdated`, clears test achievements, bumps test `version` (§6); history kept.
**Changes**:
- `AttemptValidityPolicy`, `ValidityMaintenance`, `CriticalUpdateTest`; `AttemptRepository` status/bulk updates; `UserAchievementRepository::deleteAllForTest`.
- `AttemptServices::$validity`, `$criticalUpdate`; `tests/run_validity_tests.php`; phase docs updated.

### [2026-10-08] Knowledge tests: Wave C retake eligibility (Phase 2)
**Author**: AI Assistant
**Logic**:
- Retake gate for Phase 3 / React: calendar-day `retake_delay_days` (§2.6), pending materials after fail, lock at configurable threshold (§2.12); outdated attempts do not apply lock.
- In-progress RAM and notification block deferred to Phase 3 / Wave E.
**Changes**:
- `RetakeEligibility`, `RetakeEligibilityPolicy`, `RetakeEligibilityQuery`; `TestConfigReader::retakeDelayDays`; `ClockInterface` on `DatabaseClock`.
- `AttemptServices::$retake`; `tests/run_retake_eligibility_tests.php`; `PROJECT_CONTEXT.md`, `PHASE_2_TASKS.md`.

### [2026-10-07] Knowledge tests: Wave B achievements (Phase 2)
**Author**: AI Assistant
**Logic**:
- Latest completed attempt drives `gi_new_test_user_achievements` (§2.11); failed latest removes the row; 95%+ uses `lock` tier (§2.12).
- Config threshold changes re-run via `recalculateForUserAndTest` (§5.6).
**Changes**:
- `AchievementRepository`, `UserAchievementRepository`, `AttemptAchievementService`; `AttemptResultClassifier::achievementTierValue`; hook in `CompleteAttempt`.
- `AttemptRepository::findLatestByUserAndTest`; `tests/run_achievement_tier_tests.php`.

### [2026-10-07] Knowledge tests: Phase 2 doc sync (Wave A complete)
**Author**: AI Assistant
**Logic**:
- PHASE_2_TASKS breakdown and CompleteAttempt are done; plan §3 and checklists should match repo before waves B–E.
**Changes**:
- `PHASE_2_TASKS.md`, `IMPLEMENTATION_PLAN.md` §3/status, `PROJECT_CONTEXT.md` services line.

### [2026-10-07] Knowledge tests: CompleteAttempt persistence (Phase 2 wave A)
**Author**: AI Assistant
**Logic**:
- Completed attempts belong in MySQL via plugin use case; in-progress stays in bp_contracts RAM (Phase 3).
- On fail, reuse existing materials workflow after attempt rows exist.
**Changes**:
- `CompleteAttempt`, `AttemptRepository`/`AttemptQuestionRepository` inserts; `AttemptServices::$complete`.
- `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md`, `PHASE_2_TASKS.md`.

### [2026-10-07] Knowledge tests: Phase 2 task breakdown doc
**Author**: AI Assistant
**Logic**:
- Phase 2 scope is large; a dedicated checklist avoids duplicating waves in IMPLEMENTATION_PLAN and tracks exit criteria for scripted lifecycle.
**Changes**:
- `bp_knowledge_tests/spec/PHASE_2_TASKS.md`: [NEW] waves A–F, checklist, open decisions.
- `spec/IMPLEMENTATION_PLAN.md`, `PROJECT_CONTEXT.md`: links to Phase 2 tasks.

### [2026-10-07] Knowledge tests: materials table `gi_new_test_finished_attempts_explanations`
**Author**: AI Assistant
**Logic**:
- Canonical name uses `gi_new_test_*` prefix in DDL, DBWorker, and spec; no rename migration (greenfield / no legacy data).
**Changes**:
- `DBWorker.php`, `SchemaDefiner.php`, `001_baseline.sql`, spec docs; removed `alignFinishedExplanationsTableName` and v5/v6 migration artifacts.

### [2026-10-07] Knowledge tests: materials filter from attempt-question counts only
**Author**: AI Assistant
**Logic**:
- Align materials question selection with D4 / §9.2: use persisted `right_answers` / `failed_answers` only; drop catalog-based `QuestionMistakeEvaluator`.
**Changes**:
- `AttemptQuestionRecord::qualifiesForMaterials()`, `MaterialsService`; removed `QuestionMistakeEvaluator`; `tests/run_attempt_question_materials_tests.php`.

### [2026-10-06] Knowledge tests: Phase 2 materials workflow (schema v5)
**Author**: AI Assistant
**Logic**:
- Implement temporary learning materials lifecycle (spec §10): populate mistake rows after failed attempts, list pending, confirm Examined deletes rows only.
- Fix explanations table name to `gi_new_finished_attempts_explanations` (aligned with spec, migrations, DBWorker).
**Changes**:
- `MaterialsService`, `AttemptServices`, attempt/explanation repositories, `QuestionMistakeEvaluator`; `Plugin::attempts()`; schema v5 migration; `tests/run_materials_mistake_tests.php`; `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md`.

### [2026-10-06] DBWorker: knowledge test table keys + getPathTable usage
**Author**: AI Assistant
**Logic**:
- Register `gi_new_test_*` tables on DBWorker properties; repositories and schema checks use keys (`KnowledgeTestAnswers`, etc.) so `tableExists` / `columnExists` / `selectUni_2` resolve consistently.
**Changes**:
- `bp_contracts/src/Workers/DBWorker.php`, `bp_knowledge_tests` repositories, `SchemaDefiner.php`, `PROJECT_CONTEXT.md`.

### [2026-10-05] Knowledge tests: D6 = is_correct on answers (schema v4)
**Author**: AI Assistant
**Logic**:
- Project decision: correct options on `gi_new_test_answers.is_correct`; remove junction table; v4 activation migrates junction rows if present.
**Changes**:
- `AnswerRecord`, `AnswerRepository`, `AnswerCatalog`, `SchemaDefiner`, `PluginBootstrap`; docs; `migrations/004_is_correct_on_answers.sql`.

### [2026-10-05] Knowledge tests: D6 correct options + selection grader (schema v3)
**Author**: AI Assistant
**Logic**:
- Persist correct answer option keys in junction table (not `is_correct` on answers); catalog API + `QuestionSelectionGrader`; fix medal vs rank-name seeding (D9).
**Changes**:
- `SchemaDefiner`, `SchemaVersionRepository`, `PluginBootstrap`, `Seeder`, `CorrectAnswerRepository`, `CorrectAnswerCatalog`, `Domain/QuestionSelectionGrader*`, `CatalogServices`, `DomainServices`.
- `migrations/002_correct_answer_options.sql`, `001_baseline.sql`, `tests/run_selection_grader_tests.php`, docs.

### [2026-10-05] Knowledge tests: D4 scoring clarifications + Phase 0 doc sync
**Author**: AI Assistant
**Logic**:
- Document agreed multi-select counting (global valid/invalid, penalties, no per-question clamp for official score) and catalog-vs-RAM correctness (D6 still open on storage shape).
- Sync implementation plan repository state, Phase 0 checkboxes, gates before Phase 2 (D9/D10); align bp_contracts React wording.
**Changes**:
- `bp_knowledge_tests/spec/Testing System — Project Documentation.md`: §2.1.1, §2.1.2.
- `bp_knowledge_tests/spec/IMPLEMENTATION_PLAN.md`: §3, §5.1.1–5.1.2, §11 Phase 0, §13–14.
- `bp_knowledge_tests/PROJECT_CONTEXT.md`, `bp_contracts/PROJECT_CONTEXT.md`.

### [2026-10-05] Knowledge tests: ban is_correct; drop v4 migration code
**Author**: AI Assistant
**Logic**:
- Document prohibition (PROJECT_CONTEXT, IMPLEMENTATION_PLAN D8); remove activation cleanup for `is_correct`; schema target back to v2.
**Changes**:
- `SchemaDefiner.php`, `PluginBootstrap.php`, `SchemaVersionRepository.php`, `migrations/README.md`, `PROJECT_CONTEXT.md`, `spec/IMPLEMENTATION_PLAN.md`.

### [2026-10-05] Knowledge tests: src layout — Services directory
**Author**: AI Assistant
**Logic**:
- Group application entry points under `src/Services/`; keep domain value objects/rules in `Domain/`, persistence in `Infrastructure/`.
**Changes**:
- Moved `CatalogServices`, `DomainServices`, `ScoringService`, `TestConfigReader`, `Catalog/*` from `Application/` to `Services/`.
- Updated `Plugin`, `PluginBootstrap`, scoring tests, `PROJECT_CONTEXT.md`.

### [2026-10-05] Knowledge tests: remove is_correct (align spec §8.3)
**Author**: AI Assistant
**Logic**:
- Product spec §8.3 lists answer columns only; `is_correct` was an mistaken implementation add-on, not specification. Schema v4 drops the column if present; catalog/API reverted.
**Changes**:
- `AnswerRecord`, repositories, catalogs, `SchemaDefiner`, baseline SQL, migrations README.
- `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md` §5.0.3, D6 open again.

### [2026-10-05] Knowledge tests: V1 UI = React (plan D7)
**Author**: AI Assistant
**Logic**:
- Product dealer + admin UIs are React; plugin stays domain/API-only. Phase 4–6 and bp_contracts integration docs updated; PHP Tools page remains dev smoke only.
**Changes**:
- `spec/IMPLEMENTATION_PLAN.md` §2, §7–9, phases 4–6, D7.
- `PROJECT_CONTEXT.md`, `DevToolsMenu.php` disclaimer.

### [2026-10-05] Knowledge tests: Phase 1 complete (scoring + is_correct)
**Author**: AI Assistant
**Logic**:
- Close Phase 1: correct-option persistence (D6), pure domain scoring/pass/lock, mandatory scoring tests, dev Tools smoke page.
- Maximum Correct Answers = count of options with `is_correct = 1` per test (D4).
**Changes**:
- Schema v3: `is_correct` on `gi_new_test_answers`; `AnswerRecord` / catalog API extended.
- `Domain\ScoringService`, `TestConfigReader`, `AttemptResultClassifier`; `Application\DomainServices`; `Plugin::domain()`.
- `tests/run_scoring_tests.php`, `Infrastructure\Admin\DevToolsMenu`.
- `PROJECT_CONTEXT.md`, `spec/IMPLEMENTATION_PLAN.md`, `migrations/README.md`, `001_baseline.sql`.

### [2026-10-02] Knowledge tests: revert container service registration
**Author**: AI Assistant
**Logic**:
- Removed `PluginServiceRegistration` / `ContainerRequire` / container keys; restored explicit wiring in `CatalogServices` and constructor-injected repos/catalogs.
**Changes**:
- Deleted registration helpers; restored `Plugin`, `PluginBootstrap::compose()`, repos, catalogs, `PROJECT_CONTEXT.md`.

### [2026-10-02] Knowledge tests: entry boot without add_action
**Author**: AI Assistant
**Logic**:
- Slim `index.php`: `Requires Plugins: wordpress_framework` so the container exists before this file runs; runtime via `PluginBootstrap::run()`.
**Changes**:
- `index.php`, `PluginBootstrap.php`, `PROJECT_CONTEXT.md`.

### [2026-10-02] Knowledge tests: compose via services container
**Author**: AI Assistant
**Logic**:
- Pass `PersonalAccount\Core\Container` into `compose()` / `CatalogServices` instead of resolving globals and wiring each service in bootstrap.
**Changes**:
- `CatalogServices.php`, `PluginBootstrap.php`, `index.php`, `PROJECT_CONTEXT.md`.

### [2026-10-01] Knowledge tests: remove is_correct; clarify answer options
**Author**: AI Assistant
**Logic**:
- `gi_new_test_answers` holds question **options**, not user selections; premature `is_correct` column confused the model. Correct-option storage deferred to scoring phase (IMPLEMENTATION_PLAN D6).
**Changes**:
- Removed `is_correct` from DDL/migration v3, repositories, `AnswerRecord`, catalog API.
- `PROJECT_CONTEXT.md`, `IMPLEMENTATION_PLAN.md` §5.0.3, `migrations/README.md`.

### [2026-10-01] Knowledge tests: catalog persistence via DBWorker
**Author**: AI Assistant
**Logic**:
- One shared DB access style across the portal; avoid duplicated SELECT strings in plugin repositories.
**Changes**:
- `DBUtilities::packageWriteColumns` (optional helper); `DBWorker` packaged `insertAssoc` / `updateAssoc` / `deleteAssoc`.
- `bp_knowledge_tests` repositories + `index.php` (`plugins_loaded`, site autoload); `PROJECT_CONTEXT.md`.

### [2026-10-01] Knowledge tests: simplify catalog layer
**Author**: AI Assistant
**Logic**:
- Replace one-class-per-operation use cases with four catalog services; keep version-bump rules on question/answer writes only.
**Changes**:
- `Application/Catalog/*`, `CatalogServices.php`; removed proxy use-case classes and `KnowledgeTestsContext`.
- `Plugin.php`, `PluginBootstrap.php`, `PROJECT_CONTEXT.md`.

### [2026-10-01] Knowledge tests: catalog CRUD use cases
**Author**: AI Assistant
**Logic**:
- Phase 1 needs persistence APIs before admin UI; keep bootstrap flat and expose use cases through `Plugin::context()`.
- Align live DDL with baseline SQL by adding `is_correct` on answers (schema version 3).
**Changes**:
- `bp_knowledge_tests/src/Application/`, `Domain/Record/`, `Infrastructure/Repository/`: tests, questions, answers, config CRUD.
- `Plugin.php`, `PluginBootstrap.php`: wire `KnowledgeTestsContext`.
- `SchemaDefiner.php`, `SchemaVersionRepository.php`: `is_correct` column migration (v3).
- `bp_knowledge_tests/PROJECT_CONTEXT.md`, `migrations/README.md`: runtime and schema status.

### [2026-10-01] Development Rules v2.5 (new-plugin bootstrap)
**Author**: AI Assistant
**Logic**:
- Codify lessons from bp_knowledge_tests scaffolding: flat bootstrap, no premature layers, instance hooks—without duplicating User Rules YAGNI/simplicity text.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: §2.8 new `bp_*` plugin bootstrap, §2.9 WP boundary vs static services; PR checklist note.

### [2026-10-01] Knowledge tests: minimal plugin tree
**Author**: AI Assistant
**Logic**:
- Keep only activation/bootstrap code until features are built; remove unused domain, repositories, and CLI scoring tests.
**Changes**:
- Removed `src/Domain/*`, `src/Infrastructure/Repository/*`, `tests/run_scoring_tests.php`.
- `PROJECT_CONTEXT.md`, `spec/IMPLEMENTATION_PLAN.md`: status and Phase 1 checklist.

### [2026-10-01] Knowledge tests: expose activation on PluginBootstrap
**Author**: AI Assistant
**Logic**:
- Remove ComposedPlugin / PluginActivation indirection; activation sequence lives on `PluginBootstrap::activate()`.
**Changes**:
- `PluginBootstrap.php`, `index.php`: direct `activate($wpdb)` hook.
- Deleted `ComposedPlugin.php`, `Infrastructure/Activation/PluginActivation.php`.

### [2026-10-01] Knowledge tests: SchemaDefiner + Seeder consolidation
**Author**: AI Assistant
**Logic**:
- Reduce activation over-engineering: one class for DDL/FKs, one for seed data; keep `PluginBootstrap::compose()` flow unchanged.
**Changes**:
- `SchemaDefiner.php`, `Seeder.php`: replace step wrappers, catalogs, and separate installers.
- Removed `SchemaTableDefinitions`, `ConfigSeeder`, `ForeignKeyCatalog`, `ForeignKeyInstaller`, `Activation/*Step.php`.
- `PluginActivation` calls `SchemaDefiner` / `Seeder` methods directly.

### [2026-09-30] Knowledge tests: flat bootstrap + activation pipeline
**Author**: AI Assistant
**Logic**:
- Avoid nesting Plugin inside factory-wired SchemaInstaller; compose services in sequence and return `ComposedPlugin`.
**Changes**:
- `Bootstrap/PluginBootstrap.php`, `ComposedPlugin.php`: compose steps, return plugin + activation.
- `Infrastructure/Activation/*`, `SchemaVersionRepository.php`: replace `SchemaInstaller` / `PluginFactory`.
- `Plugin.php`: runtime only (`boot()`); activation lives on `ComposedPlugin`.

### [2026-09-30] Knowledge tests: instance-based plugin bootstrap
**Author**: AI Assistant
**Logic**:
- Replace static schema/FK installers with constructor-injected services and `PluginFactory` wiring per composition rules.
**Changes**:
- `Plugin.php`, `PluginFactory.php`: instance `boot()` / `activate()`.
- `SchemaInstaller`, `ForeignKeyInstaller`, `SchemaTableDefinitions`, `ForeignKeyCatalog`, `ConfigSeeder`: non-static collaborators.
- `index.php`: wires hooks to plugin instance.

### [2026-09-29] Knowledge tests schema: foreign keys (v2)
**Author**: AI Assistant
**Logic**:
- Enforce referential integrity between test tables and `gi_new_users.id_user`; dbDelta does not reliably add FKs, so version 2 runs ALTER constraints on activation.
**Changes**:
- `ForeignKeyInstaller.php`: [NEW] constraint definitions.
- `SchemaInstaller.php`: schema version 2; delegates FK step.
- `migrations/001_baseline.sql`, `migrations/README.md`, `PROJECT_CONTEXT.md`, `spec/IMPLEMENTATION_PLAN.md`: FK documentation.

### [2026-09-29] Knowledge tests schema: int IDs
**Author**: AI Assistant
**Logic**:
- Align DDL with IMPLEMENTATION_PLAN §5 (`INT UNSIGNED`) instead of `bigint` for keys and FKs.
**Changes**:
- `bp_knowledge_tests/src/Infrastructure/SchemaInstaller.php`, `migrations/001_baseline.sql`: `int(10) unsigned` for integer columns.

### [2026-09-29] Knowledge tests Phase 0 close + Phase 1 skeleton
**Author**: AI Assistant
**Logic**:
- Close documentation/schema phase; align plan with User Rules and DEVELOPMENT_RULES v2.4; start plugin code without duplicating domain logic in bp_contracts.
- D4 multi-select scoring documented as sum of correct options per question (spec §2.1).
**Changes**:
- `public/wp-content/plugins/bp_knowledge_tests/`: Plugin bootstrap, `SchemaInstaller`, repositories, `ScoringService` / `TestConfigReader`, scoring test script, migrations README.
- `public/wp-content/plugins/bp_knowledge_tests/PROJECT_CONTEXT.md`: Runtime and schema status.
- `public/wp-content/plugins/bp_knowledge_tests/spec/IMPLEMENTATION_PLAN.md`: Phase 0/1 progress, D4 resolved, `is_correct` in §5.0.3.
- `public/wp-content/plugins/bp_contracts/PROJECT_CONTEXT.md`, `src/PROJECT_CONTEXT.md`: Planned Tests integration and WS stubs.
- `public/PROJECT_CONTEXT.md`: bp_knowledge_tests link path fix.

### [2026-09-25] Site context: link to DEVELOPMENT_RULES
**Author**: AI Assistant
**Logic**:
- Restore navigation from the portal map to project rules without duplicating rule text in PROJECT_CONTEXT.
**Changes**:
- `public/PROJECT_CONTEXT.md`: Related docs line with link to DEVELOPMENT_RULES.md.

### [2026-09-25] Development Rules v2.4 (project-only file)
**Author**: AI Assistant
**Logic**:
- Global rules live only in Cursor Settings; remove Part A and duplicate pointers (CHANGELOG/debugging/code size) from the repo file.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: WordPress / GeoS Ideal sections only (§0–§5); renumbered §3–§5 doc/workflow/debug.
- `public/CHANGELOG.md`: Point format to User Rules.

### [2026-09-24] Development Rules v2.3 (correct Part A / Part B split)
**Author**: AI Assistant
**Logic**:
- Part A = existing universal rules (GRASP, SOLID, composition, docs, CHANGELOG, debugging, code size, security baseline); Part B = WordPress / GeoS Ideal only. Removed abbreviated G1–G8-only Part A from v2.2.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Restructured v2.3; GRASP/SOLID/§9/§10/§11 moved to Part A only.

### [2026-09-24] Development Rules v2.2 (Part A / Part B split)
**Author**: AI Assistant
**Logic**:
- Separate **global** rules (Cursor Settings → Rules) from **GeoS Ideal project** rules in one file to avoid duplicating Part B in Settings; Part A includes G8 activation to read Part B + PROJECT_CONTEXT.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Part A (copy block G1–G8) + Part B (§0–§11 project rules).

### [2026-09-24] Development Rules v2.1 (clarity & precedence)
**Author**: AI Assistant
**Logic**:
- Remove ambiguity around canonical sources (this file vs PROJECT_CONTEXT vs Cursor User Rules), conflict precedence, restricted-file edge cases, CHANGELOG “significant”, PHP line counting, legacy containment, and when tests are expected.
- Keep security and architecture intent from v2.0; align trait/base-class gates with enforceable thresholds.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Rewritten to v2.1 (§0 precedence, §1.1–1.2 clarifications, §9 significance table, §11 counting/tiers, §8.3 tests).

### [2026-09-24] Site context: single source for development rules
**Author**: AI Assistant
**Logic**:
- Keep strict development rules in `DEVELOPMENT_RULES.md` (and Cursor User Rules) only; avoid duplicating the mandatory-read callout in the portal map.
**Changes**:
- `public/PROJECT_CONTEXT.md`: Removed DEVELOPMENT_RULES block from Overview.

### [2026-09-24] Knowledge tests implementation plan
**Author**: AI Assistant
**Logic**:
- Consolidate architecture, phases, DB summary, bp_contracts integration, and QA into one engineering document next to the product spec.
- Add plugin PROJECT_CONTEXT index and site plugin list entry for discoverability.
**Changes**:
- `public/wp-content/plugins/bp_knowledge_tests/spec/IMPLEMENTATION_PLAN.md`: [NEW] Full implementation plan and project context.
- `public/wp-content/plugins/bp_knowledge_tests/PROJECT_CONTEXT.md`: [NEW] Plugin doc index linking spec + plan.
- `public/PROJECT_CONTEXT.md`: Link to bp_knowledge_tests.

### [2026-09-24] Development Rules v2.0 (giby-2.0 adaptation)
**Author**: AI Assistant
**Logic**:
- Align site rules with giby-2.0 `DEVELOPMENT_RULES.md` (GRASP, composition, documentation hierarchy, code size, enforcement) while scoping to this WordPress-only repo (`public/`).
- Document `bp_contracts`, WebSocket, and `PROJECT_CONTEXT` nesting; remove Laravel-only references; add pragmatic legacy exceptions (`bp_zakazi`, traits).
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Rewritten to v2.0 structure with local paths and related-doc index.

### [2026-01-15] Implemented Strict Development Rules
**Author**: AI Assistant
**Logic**: 
- Validated project security requirements (`security_hook.php`) and architectural patterns (`wordpress_framework`).
- Established a single source of truth for rules to prevent legacy anti-patterns (e.g., per-user tables, god files).
- Created a mechanism to track code duplication issues between `bp_kitchen` and `bp_wardrobe`.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: [NEW] Created file with strict rules for Security, Architecture, Workflow, and Code Duplication.
- `public/PROJECT_CONTEXT.md`: Added "Primary Directive" alert linking to the new Rules file.

### [2026-01-16] Unified Changelog Format Definition
**Author**: AI Assistant
**Logic**: 
- Moved the Changelog format definition from `CHANGELOG.md` to `DEVELOPMENT_RULES.md`.
- Ensures a single source of truth for the format and reduces redundancy.
- Prevents the template in the changelog file from becoming outdated.
**Changes**:
- `public/CHANGELOG.md`: Removed "Format" section, replaced with link to Rules.
- `public/DEVELOPMENT_RULES.md`: Added "Format" definition to Section 5.

### [2026-01-16] Added SOLID Principles
**Author**: AI Assistant
**Logic**: 
- User requested explicit inclusion of SOLID principles to ensure code quality and prevent future complaints/issues.
- Strengthens the "Architecture & Code Standards" section.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Added Section 2.5 "SOLID Principles".

### [2026-01-16] Added Debugging Workflow
**Author**: AI Assistant
**Logic**: 
- User suggested using the Changelog as a debugging tool for root cause analysis.
- Formalizes a process to trace bugs by date and component using the **Changes** and **Logic** sections.
**Changes**:
- `public/DEVELOPMENT_RULES.md`: Added Section 6 "Debugging Workflow".
