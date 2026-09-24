# Component Context: bp_contracts/Frontend (JS)

## Overview
Each WordPress shortcode in `index.php` enqueues a **page-specific script** plus shared `communication_server.js`. There is no bundler: globals (`where`, `socket`, `lastSN`, `handler*` functions) coordinate UI and WebSocket I/O.

## 1. Core: `communication_server.js`
- **Connection**: `WebSocket` to `ws://127.0.0.1:8080` (or production `wss://…`).
- **Bootstrap**: On open → `idUser::: ` + `setDefaultState(where)` + `reloadDataForWindow`.
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
