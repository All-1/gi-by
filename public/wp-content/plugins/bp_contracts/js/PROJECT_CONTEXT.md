# Component Context: bp_contracts/Frontend (JS)

## Overview
Each WordPress shortcode in `index.php` enqueues a **page-specific script** plus shared `communication_server.js`. There is no bundler: globals (`where`, `socket`, `lastSN`, `handler*` functions) coordinate UI and WebSocket I/O.

## 1. Core: `communication_server.js`
- **Ownership**: Single global **`socket`** and connect/reconnect — see [DEVELOPMENT_RULES.md](../../../../DEVELOPMENT_RULES.md) §2.5. Do not patch `openConnection` from other scripts.
- **Connection**: `WebSocket` to `ws://127.0.0.1:8080` (or production `wss://…`).
- **On open**: `idUser::: ` → `inicializationWebsocket(socket)` → `reloadDataForWindow` → **`onWebSocketSessionReady()`** (`setDefaultState(where)`, optional feature callbacks).
- **On close**: **`onWebSocketSessionClosed()`** (e.g. knowledge-tests overlay banner) before reconnect heartbeat.
- **Heartbeat / reconnect**: `startHeartbeat`, visibility-aware `intervalReconnect`.
- **Inbound routing**:
  - `paramForClient` → builds `mainUser` (`user.js`).
  - `Notification` → `NotificationWorker` (`notification.js`).
  - All other titles → `handlerRequestFromServer(title, body)` which calls `window['handler' + toCapsCase(title)]` if defined.

Example: server sends `AnalyticsOnPage::: {...}` → `handlerAnalyticsOnPage` in `analytics.js`.

## 2. Shared utilities
| File | Role |
|:-----|:-----|
| `utility.js` | DOM helpers, `invokeDataForWindow`, `choseWhereForWindow`, formatting |
| `controls_for_page.js` | Pagination, filters, `sendShowWhere(where)` → `show{Page}::: ` |
| `user.js` | `mainUser` client model |
| `status-user.js` | User status UI |
| `modal_window.js` | Large modal / form host |
| `notification.js` | Toast / notification UI |

## 3. Page modules (by shortcode)
| File | Shortcode / area |
|:-----|:-----------------|
| `contracts.js` | `contracts` |
| `orders.js` | `orders_dev` |
| `invoices.js` | `invoices` |
| `analytics.js` | `analytics` — charts (Chart.js), SLA controls, dealer drill-down |
| `partners.js` | Contractor / partner flows |
| `stuff_management.js` | `staff_management`, `co_workers` |
| `my_profile.js` | `my_profile` |
| `reg_users.js` | `reg_users` |
| `points.js` / `points_dev.js` | `points_manager_new` / `points_dev` |
| `invoice_print.js` | Invoice PDF client render |
| `integer_to_words.js` | Amount wording for documents |
| `knowledge_tests.js` | `knowledge_tests` shortcode page — `where`, list + overlay UI, WS handlers (like `orders.js`). In-test: `submitTestQuestion` → feedback → `startTest` (next) or `finishTest` (last) |
| `knowledge_tests_nav.js` | When dealer Tests cap — `requestKnowledgeTestsNavHighlight`, minimal `handlerTestsOnPage`; reconnect via `onKnowledgeTestsWebSocketOpen` / `Close` (called from `communication_server.js`) |
| `knowledge_test_chat_stars.js` | `getKnowledgeTestChatStars` batch on dialog load; `modal_window.js` appends stars to `.author_message` |

### `analytics.js` (summary)
- Global `where = 'analytics'`; on load → `showAnalytics` via `sendShowWhere`.
- Handlers: `handlerAnalyticsOnPage`, `handlerAnalyticsDealerDependentsOnPage`, `handlerAnalyticsUserSearch`.
- Sends `setAnalytics*` WS commands when filters / SLA / dates change.
- Uses `chart.umd.js` for dashboards; CSS in `css/style.css` (`.analytics_*`).

## 4. Libraries (vendor)
- `pdfmake`, `exceljs` — client-side export (see contracts / orders / invoices flows).
- `chart.umd.js` — analytics only.

## 5. Orphan / dev
- `draft_work.js` — not enqueued in `index.php` (legacy / WIP).
- `grid-template-examples.js` — examples.

## Data flow (typical)
1. User action on page JS → `socket.send('command::: body')`.
2. PHP `Chat` → `InteractionInterface` → user traits / workers.
3. Server `Title::: json` → matching `handlerTitle` → DOM update.

## Related
- [WebSocket command list](../src/PROJECT_CONTEXT.md)
- [Analytics backend](../src/Workers/Analytics/PROJECT_CONTEXT.md)
