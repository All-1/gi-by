# Component Context: bp_contracts/Workers

## Overview
The `Workers` directory is the **Infrastructure Layer**: database, ERP sync, email, notifications, domain-specific SQL helpers, and analytics materialization. Services are registered in [SystemConstructor](../Core/PROJECT_CONTEXT.md) and consumed by controllers and `UserMain`.

## Core Infrastructure

### 1. `DBWorker.php` (Database Facade)
**Role**: Wrapper around WordPress `$wpdb`.
- **Automatic Escaping**: Input arrays sanitized before queries.
- **Selectors**: `selectUni()`, `selectInvoices()` (pagination, firms, dates).
- **Batch**: `insertMultipleDB` for ERP / analytics bulk writes.
- **CRUD**: `updateDBU`, `insertDBU` with `CatcherBugs` logging.

### 2. `UPWorker.php` (ERP "Universal Platform" Sync)
**Role**: Bridge to internal factory ERP (PDO, not `$wpdb`).
- **Trigger**: `requestUpdate*UP` WebSocket messages / connector.
- **Entities**: Orders, dealers, points, firms, invoices (`prepareInvoice_NEW`, etc.).
- **Logging**: `logId` cursor to avoid duplicate imports.

### 3. `InvoiceWorker.php`
Currency mapping, `printInvoice()` tree for client PDF, `getInvoicesSum()`.

### 4. `NotificationWorker.php`
WebSocket push via `Chat` when online; `Mailer` when offline. Participant join notifications.

### 5. `ManageContractorsWorker.php`
External staff: bind/unbind, filter by role/status.

### 6. `ORWorker.php`
Object-relationship cleanup (`deleteOR` for Contract and related types). Used by `ControlerObjectsRelationship`.

### 7. `MessageWorker.php`
Message persistence helpers (dialog tables, read state). Injected into `UserMain`.

### 8. `ContractsWorker.php` (infrastructure)
**Not** `Users\ContractsWorker` (role class). SQL/list helpers for contract grids.

### 9. `OrdersWorker.php`
Order list logic, filters, ERP-related order operations for the orders UI.

## Subdirectories

### [Analytics/](./Analytics/PROJECT_CONTEXT.md)
Materialization calculators (`DialogMetrics`, `ManagerMetrics`, `ContractorsMetrics`, `MessageMetrics`), dashboard query layer (`TurnAnalyticsQueries`, `DialogAnalyticsQueries`, `OrderAnalyticsQueries`, `AnalyticsWorker`).

### [Collector/](./Collector/PROJECT_CONTEXT.md)
`MessageCollector`, `AnalyticsCollector`, `UserCollector`.

## Support & misc
- **`Mailer.php`**: `wp_mail()` + templates (preferred in-app path).
- **`CatcherBugs.php`**: Logs under `wp-content/uploads/logs/`.
- **`SSHRemote.php`**: Rare remote shell helper.
- **`YandexDisk.php`**: Guzzle stub for Yandex Disk API — **not** wired in `SystemConstructor`; unused placeholder.

## Related
- [sender HTTP mail](../../sender/PROJECT_CONTEXT.md)
- [WebSocket commands](../PROJECT_CONTEXT.md)
