# Component Context: bp_contracts/Workers

## Overview
The `Workers` directory represents the **Infrastructure Layer** of the application. These classes are responsible for all external system interactions (Database, ERP, Email, Filesystem) and data-heavy background calculations. They are stateless and designed to be injected into Business Controllers or Domain Models.

## Core Infrastructure

### 1. `DBWorker.php` (Database Facade)
**Role**: A secure abstraction wrapper around the global WordPress `$wpdb` object.
**Key Features**:
- **Automatic Escaping**: All input arrays are sanitized before query construction.
- **Universal Selectors**:
    - `selectUni($table, $condition)`: Flexible generic SELECT.
    - `selectInvoices($filters)`: Specialized complex query for listing invoices with pagination, firm filtering, and date ranges.
- **Batch Operations**: `insertMultipleDB` allows high-performance bulk inserts (used during ERP sync).
- **Safe Modifications**: `updateDBU` and `insertDBU` handle common CRUD tasks with error logging via `CatcherBugs`.

### 2. `UPWorker.php` (ERP "Universal Platform" Sync)
**Role**: The bridge between the Web Server and the Internal Factory ERP.
**Sync Workflow**:
1.  **Polling/Trigger**: `requestUpdateOrdersUP($logId)` is called to check for changes since a specific transaction ID.
2.  **Mapping**: Reads raw ERP statuses (JSON/XML) and maps them to internal `gi_orders` columns (`proforma_date`, `shipping_date`, etc.).
3.  **Entity Management**:
    - `preparePoint()`: Syncs new Dealer Salons.
    - `prepareInvoice_NEW()`: Imports massive deep-nested Invoice objects ($Firm -> $Invoice -> $Orders).
4.  **Logging**: Tracks the `logId` of the last successful sync to prevent data duplication.

### 3. `InvoiceWorker.php` (PDF & Calculation Engine)
**Role**: Handles specific logic for financial documents.
**Responsibilities**:
- **Currency Resolution**: `chooseCurrency($currId)` maps internal IDs to EUR/BYN/RUB.
- **PDF Data Prep**: `printInvoice($id)` fetches the deep object tree required by the Frontend's `pdfmake` library (Firm Details, Order List, Totals).
- **Aggregates**: `getInvoicesSum()` calculates totals on the fly for dashboard widgets.

### 4. `NotificationWorker.php` (Messaging Bus)
**Role**: Manages multi-channel user alerts.
**Channels**:
- **WebSocket**: Direct push via `Chat.php` if user is online.
- **Email**: Fallback via `Mailer.php` if user is offline/away.
**Logic**:
- `prepareDataNotification_2()`: Formats the payload.
- `addParticipiant()`: Handles the "Join Chat" logic, notifying existing participants that a new user (e.g., Technologist) has entered the room.

### 5. `ManageContractorsWorker.php` (External Staff)
**Role**: Manages auxiliary users like Installers, Designers, and Delivery drivers.
**Logic**:
- **Binding**: `bindContractors()` links a specific temporary contractor to a permanent Dealer/Order.
- **Role Sorting**: `sortedByRole()` and `sortedByStatus()` allow for complex filtering of available labor resources.

## Worker Subdirectories

### 1. `Analytics/` (Dialogue Metrics Pipeline)
**Role**: Calculates, stores, and reads analytics for the new dialogue metrics tables. This package separates analytics calculation from regular page rendering so expensive metrics can be materialized into `gi_new_metrics_*` tables and later queried quickly by the analytics page.

**Main Classes**:
- **`AnalyticsWorker.php`**: Read/query facade used by `Users\traits\ManageAnalytics`. It builds filtered SQL against the materialized metrics tables for the analytics page. Supports `whose = all|factory|contractor`, date range filters, dialogue type filters, selected manager/contractor filters, and period grouping (`all`, `week`, `month`, `quarter`, `year`).
- **`AnalyticsCollector.php`**: Orchestrates metric recalculation. It loads dialogues, merged message history, and related order dates, builds a shared statistics context, delegates calculations to the metric classes, and writes the resulting batches to the metrics tables.
- **`AnalyticsBase.php`**: Shared base class for metric calculators. It resolves common services from the container and owns reusable helpers for response/waiting-time transitions, work-hours calculations, counting messages before proforma/confirmation, and batch writing.
- **`DialogMetrics.php`**: Calculates dialogue-level metrics and owns the schema for `gi_new_metrics_dialogues`: first message date, proforma/confirmation dates, time to proforma/confirmation, work-hours variants, message counts, iteration count, and calculation timestamp.
- **`ManagerMetrics.php`**: Calculates factory-side participant metrics and owns the schema for `gi_new_metrics_managers`: manager message counts, messages before proforma/confirmation, total response time, waiting time, first response time, and work-hours variants per dialogue/manager pair.
- **`ContractorsMetrics.php`**: Calculates contractor/dealer-side participant metrics and owns the schema for `gi_new_metrics_contractors`: contractor message counts, messages before proforma/confirmation, waiting time, total response time, and work-hours variants per dialogue/contractor pair.
- **`MessageCollector.php`**: Normalizes historical per-point/per-year message shard tables into `gi_new_metrics_messages`. It scans `x_gi_new_messages_contract_{year}_{point}` tables, unions existing shards, removes source IDs, and bulk inserts the merged stream for analytics processing.

**Data Flow**:
1. `MessageCollector->mergeMessages()` creates/rebuilds `gi_new_metrics_messages` from historical message shard tables.
2. `AnalyticsCollector->getAnalytics()` or `getAnalyticsNightly()` loads dialogues and their message streams.
3. `DialogMetrics`, `ManagerMetrics`, and `ContractorsMetrics` calculate independent metric blocks from the same dialogue context.
4. `AnalyticsCollector->writeAnalytics()` or `writeAnalyticsNightly()` persists calculated data into `gi_new_metrics_dialogues`, `gi_new_metrics_managers`, and `gi_new_metrics_contractors`.
5. `AnalyticsWorker` reads those tables for `ManageAnalytics->getAnalytics()`, which sends the prepared result to the frontend `analyticsOnPage` handler.

**Important Notes**:
- The calculation path distinguishes client roles (`dealer`, `free_dealer`, `distributor`, `designer_dealer`) from factory roles (`manager`, `consultant`, `complaint_handler`, `specialist`, `sales_manager`) when measuring response and waiting intervals.
- The "nightly" write path replaces existing metric rows for the same dialogue/user pair before inserting recalculated rows.
- The analytics page does not calculate raw metrics directly; it queries already materialized metrics through `AnalyticsWorker`.

### 2. `Collector/` (Reusable Data Collectors)
**Role**: Contains focused data collectors that assemble reusable domain datasets for workers and user traits. These classes sit between low-level utilities/DB access and higher-level workflows.

**Main Classes**:
- **`UserCollector.php`**: Builds searchable user lists for analytics filters and user selection workflows. It is registered in `SystemConstructor` and injected through `UserMain`, where traits such as `ManageAnalytics` use it to populate manager/contractor search options.

**UserCollector Responsibilities**:
- **Factory users**: `getFactoryUsers()` loads factory-side users through `UserUtilities`, enriches each item with translated role labels and display names, and creates a combined `search` string for client-side filtering.
- **Contractor users**: `getContractorUsers()` loads contractor-side roles and packages role-specific metadata:
  - dealers/free dealers: firm name, dealer name, and point names;
  - designer dealers: point name;
  - distributors: distributor name.
- **Search packaging**: Private helpers flatten relevant user metadata into a single searchable text field while preserving structured fields for UI display.

**Integration Points**:
- `SystemConstructor` registers `UserCollector` in the dependency container.
- `UserMain` stores it as `$this->userCollector`.
- `ManageAnalytics->setAnalyticsWhose()` uses it to prepare the selectable user list when analytics are filtered by factory or contractor users.

## Support Workers
- **`Mailer.php`**: Wraps `wp_mail()` with HTML templates and logging.
- **`CatcherBugs.php`**: Writes structured error logs to `wp-content/uploads/logs/`.
- **`SSHRemote.php`**: (Legacy/Rare) Executes remote shell commands if needed for file operations.
