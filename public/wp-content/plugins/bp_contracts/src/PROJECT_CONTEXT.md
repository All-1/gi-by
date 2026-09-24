# Component Context: bp_contracts/src (WebSocket Gateway)

## Overview
`src/Chat.php` is the **Ratchet WebSocket gateway** for the personal account. Every browser page loads `js/communication_server.js`, connects to the WS process, sends `idUser::: {wpOrGiUserId}`, and then exchanges `title::: body` messages. `Chat.php` parses the title and delegates to `InteractionInterface`.

Other directories under `src/` are documented separately; this file is the **authoritative command list** for `Chat.php`.

## Connection Bootstrap (Server → Client)
After `idUser`, the server typically sends:

| Title | Purpose |
|:------|:--------|
| `idUser` | Echo / confirm user id |
| `idWebsocket` | Internal connection id |
| `paramForClient` | JSON user bootstrap (`mainUser` on the client) |
| `Notification` | Unread / alert payload |

## Client → Server Commands (`Chat::onMessage`)

### Session & grid state
| Command | Body | Purpose |
|:--------|:-----|:--------|
| `idUser` | User id | Register connection |
| `perPageContracts` / `perPageOrders` / `perPageInvoices` / `perPageContractors` | Number | Page size |
| `currentPageContracts` / `currentPageOrders` / `currentPageInvoices` / `currentPageContractors` | Number | Current page |
| `setFilterContracts` | JSON | Contract list filters |
| `searchQueryContracts` / `searchQueryOrders` / `searchQueryInvoices` / `searchQueryContractors` | String | Search text |
| `setConditionForSearchOrders` | JSON | Order search mode |
| `setFilterOrders` | JSON | Order filters |
| `setStartDateOrders` / `setEndDateOrders` | Date string | Order date range |
| `showContracts` / `showOrders` / `showInvoices` / `showContractors` | (often empty) | Refresh list for current user |

### Contracts & chat
| Command | Body | Purpose |
|:--------|:-----|:--------|
| `newContract` | JSON | Create contract |
| `getDataForContract` | Serial / id | Open contract detail |
| `newMessage` | JSON | Post chat message |
| `readedMessage` | JSON | Mark read |
| `deleteEmptyContracts` | JSON | Remove empty contracts |
| `addParticipiant` / `addMeParticipiant` | JSON | Add chat participant |
| `quitDialog` | JSON | Leave dialogue |
| `getPotencialParticipiant` | Contract SN | Users that can be invited |
| `changeNameContract` | JSON | Rename contract |
| `closeContracts` | JSON | Close contract scope |
| `setReplacementForUser` | JSON | Temporary user replacement |
| `setStatusUser` | JSON | Online / away / DND |
| `setEmailNotification` | JSON | Email notification prefs |

### Orders & exports
| Command | Body | Purpose |
|:--------|:-----|:--------|
| `packageInfoOrders` | JSON | Package info for Excel export |
| `printReportOrders` | JSON | Order report for Excel |
| `createInvoiceOrders` | — | Stub (no handler body) |
| `createShipmentOrders` | — | Stub (no handler body) |
| `savePoints` | JSON | Persist salon/point selection |

### Contractors (external staff)
| Command | Body | Purpose |
|:--------|:-----|:--------|
| `setFilterRoleContractors` / `setFilterStatusContractors` | JSON | List filters |
| `searchForBindContractors` | String | Search bind targets |
| `bindContractors` / `unbindContractors` | JSON | Link / unlink |
| `changeStatusContractors` / `changeUserNameContractors` | JSON | CRUD on contractor rows |

### ERP sync (triggers `UPWorker`)
| Command | Body | Purpose |
|:--------|:-----|:--------|
| `requestUpdateOrdersUP` | log id | Sync orders |
| `requestUpdatePointUP` | log id | Sync points |
| `requestUpdateDealerUP` | log id | Sync dealers |
| `requestUpdateFirmUP` | log id | Sync firms |
| `requestUpdateInvoiceUP` | log id | Sync invoices |

### Invoices
| Command | Body | Purpose |
|:--------|:-----|:--------|
| `printInvoice` | Id | PDF payload for client `pdfmake` |

### Analytics (Admin / `ManageAnalytics`)
| Command | Body | Purpose |
|:--------|:-----|:--------|
| `showAnalytics` | JSON (optional) | Load dashboard data |
| `setAnalyticsWhose` | `all` \| `factory` \| `contractor` \| `dealer` | Audience filter |
| `setAnalyticsDialoguesType` | `consultation` \| `order` \| `complaint` \| `` | Dialogue type |
| `setAnalyticsPeriod` | `all` \| `week` \| `month` \| `quarter` \| `year` | Grouping |
| `setAnalyticsTimeMode` | `work_hours` \| `standard` | Duration basis |
| `setAnalyticsSlaFirstResponse` | Minutes | First-response SLA threshold |
| `setAnalyticsSlaSubsequentResponse` | Minutes | Subsequent-response SLA |
| `setAnalyticsUser` | User id or empty | Filter by manager/contractor/dealer |
| `getAnalyticsUserSearch` | Search string | Autocomplete list |
| `setStartDateAnalytics` / `setEndDateAnalytics` | Date | Analytics range |
| `getDealerDependentsAnalytics` | JSON `{ dealerUserId }` | Drill-down: dealer’s contractors |

See [Workers/Analytics](./Workers/Analytics/PROJECT_CONTEXT.md) and [Users/traits/ManageAnalytics](./Users/traits/PROJECT_CONTEXT.md).

## Server → Client Responses (selected)
| Title | Handler on client (`handler` + Title in PascalCase) |
|:------|:------------------------------------------------------|
| `ContractOnPage` | `handlerContractOnPage` |
| `sendDataForContract` | `handlerSendDataForContract` |
| `OrdersOnPage` | `handlerOrdersOnPage` |
| `InvoicesOnPage` | `handlerInvoicesOnPage` |
| `ContractorsOnPage` | `handlerContractorsOnPage` |
| `potencialParticipiant` | `handlerPotencialParticipiant` |
| `showForBindContractors` | `handlerShowForBindContractors` |
| `packageInfoOrdersXLSX` / `printReportOrdersXLSX` | Excel export handlers |
| `printInvoicePDF` | `handlerPrintInvoicePDF` |
| `AnalyticsOnPage` | `handlerAnalyticsOnPage` |
| `AnalyticsDealerDependentsOnPage` | `handlerAnalyticsDealerDependentsOnPage` |
| `analyticsUserSearch` | `handlerAnalyticsUserSearch` |
| `Notification` | `handlerNotification` (special case in `communication_server.js`) |
| `paramForClient` | `handlerParamForClient` + page bootstrap |

Convention: `communication_server.js` → `handlerRequestFromServer(caseTitle, data)` → `window['handler' + toCapsCase(caseTitle)]`.

## Related
- [Core (DI bootstrap)](./Core/PROJECT_CONTEXT.md)
- [Controllers](./Controler/PROJECT_CONTEXT.md)
- [Frontend JS](../js/PROJECT_CONTEXT.md)
