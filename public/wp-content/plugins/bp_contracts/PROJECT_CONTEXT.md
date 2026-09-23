# Component Context: bp_contracts

## Overview
`bp_contracts` is the central "New Personal Account" (Новый личный кабинет) module for the dealers/partners. It utilizes a **WebSocket-first architecture** where `Chat.php` acts as the main gateway for real-time interactions, delegating logic to a strictly layered controller system.

## Architecture & Data Flow

### 1. WebSocket Entry Point (`src/Chat.php`)
- Implements `Ratchet\MessageComponentInterface`.
- **Role**: Routes incoming WebSocket messages to `InteractionInterface`.
- **Message Format**: Messages are typically string-encoded with a prefix separator `::: ` (e.g., `idUser::: 123`).
- **Key Methods**:
  - `onMessage`: Parses `title` and `body`.
  - Routes like `newContract`, `showContracts`, `searchQueryContracts` calls corresponding private methods which trigger `InteractionInterface`.

### 2. WebSocket Server Bootstrap
> [!IMPORTANT]
> The server runs **outside** the plugin directory.

| Item | Value |
|:-----|:------|
| **Entry Point** | `/public/websocket_server.php` |
| **Port** | 8080 (hardcoded) |
| **Startup** | `php websocket_server.php` (requires persistent process) |
| **Dependencies** | `vendor/autoload.php` + `wp-load.php` |

### 3. Message Protocol
All messages use `title::: body` format:

| Command | Direction | Purpose |
|:--------|:----------|:--------|
| `idUser::: {id}` | Client→Server | Register connection |
| `newContract::: {json}` | Client→Server | Create contract |
| `newMessage::: {json}` | Client→Server | Send message |
| `ContractOnPage::: {json}` | Server→Client | Contract list update |
| `sendDataForContract::: {json}` | Server→Client | Contract details |
| `Notification::: {json}` | Server→Client | User notifications |
| `Ping::: GetPing` | Bidirectional | Heartbeat |


## Component Breakdown

### 0. [Core (Bootstrap)](./src/Core/PROJECT_CONTEXT.md)
- **Role**: Service Registry & Dependency Injection.
- **Key Components**: `SystemConstructor` (Bootstrapper), `Container`.

### 1. [Controllers](./src/Controler/PROJECT_CONTEXT.md)
- **Role**: Business Logic Layer & API Gateway.
- **Key Components**: `InteractionInterface` (Orchestrator), `UserControler` (Auth/State).
- **Deep Dive**: [Controller Traits](./src/Controler/traits/PROJECT_CONTEXT.md).

### 2. [Users](./src/Users/PROJECT_CONTEXT.md)
- **Role**: Domain Models for Authentication and Permissions.
- **Key Components**: `Dealer`, `FactoryWorker`, `Distributor`.
- **Deep Dive**: [Traits & Interfaces](./src/Users/traits/PROJECT_CONTEXT.md) (Capabilities & DTOs).

### 3. [Business Objects](./src/ObjectRelationship/PROJECT_CONTEXT.md)
- **Role**: Core Entities (Contracts, Orders, Invoices).
- **Key Components**: `Contract`, `Order`, `Shipment`.
- **Deep Dive**: [Object Traits](./src/ObjectRelationship/traits/PROJECT_CONTEXT.md) (Schemas).

### 4. [Factory (Instantiation)](./src/Factory/PROJECT_CONTEXT.md)
- **Role**: Central Object Creation Hub.
- **Key Components**: `Factory` (Main Class), `createDependentObjects`.

### 5. [Messaging (Dialogs)](./src/Dialog/PROJECT_CONTEXT.md)
- **Role**: Internal Chat System.
- **Key Components**: `DialogConsultation`, `DialogComplaint`.
- **Deep Dive**: [Dialog Traits](./src/Dialog/traits/PROJECT_CONTEXT.md) (Shared Logic).

### 6. [Frontend (JS)](./js/PROJECT_CONTEXT.md)
- **Role**: SPA Client with WebSocket/Event-Driven Architecture.
- **Key Components**: `communication_server.js`, `contracts.js`.

### 7. [Workers (Infrastructure)](./src/Workers/PROJECT_CONTEXT.md)
- **Role**: Database Access, External API Sync, Logging, and materialized analytics calculations.
- **Key Components**: `DBWorker` ($wpdb wrapper), `UPWorker` (ERP Sync), `Workers/Analytics` (dialogue metrics pipeline), `Workers/Collector` (reusable domain data collectors).

### 8. [Utilities (Helpers)](./src/Utilities/PROJECT_CONTEXT.md)
- **Role**: Shared Helper Classes & Query Builders.
- **Key Components**: `DBUtilities`, `UserUtilities`, `Utilit` (Legacy).

## Key Workflows

### Creating a Contract
1. **Frontend**: Sends `newContract` message via WS.
2. **Chat**: Calls `newContractFromUser` -> `InteractionInterface->regNewContract`.
3. **InteractionInterface**:
   - Finds `User` object.
   - Calls `UserControler->logicCreateContract`.
   - Determines `usersForUpdate` (who needs to see this new contract).
4. **Chat**: Broadcasts `showContracts` update to all valid `usersForUpdate`.

### Sending a Message
1. **Frontend**: Sends `newMessage`.
2. **Chat**: Calls `regNewMessage`.
3. **InteractionInterface**:
   - Registers message in DB via `UserControler`.
   - Adds message to `Contract` object via `ControlerObjectsRelationship`.
   - Triggers `NotificationWorker`.
   - Returns updated users list to `Chat`.
4. **Chat**: Broadcasts `showContract` updates to participants.

## Database Interaction
- Uses `src/Workers/DBWorker.php` as a wrapper around global `$wpdb`.
- Custom tables prefixed with `gi_new_` (e.g., `gi_new_users`, `gi_new_contract`, `gi_new_dialogues`).

## Dependencies & Helpers
- **Dependencies**: `cboden/ratchet` (WebSocket), `ReactPHP` (EventLoop).
- **Helpers**: `src/Utilities/SimpleUtilities` (Array manipulation), `src/Workers/CatcherBugs` (Logging).

## User Role Hierarchy
All roles extend `UserMain` with capability traits:

```
UserMain (Base)
├── Dealer              → WorkWithContracts, WorkWithOrders
├── FreeDealer          → Limited Dealer permissions
├── Distributor         → DistributorTrait, ManageContractors
├── FactoryWorker       → ManageFactoryWorkers
├── Contractor          → ManageContractors (23KB trait!)
├── ContractsWorker     → Contract management
├── ShipmentManager     → WorkWithShipments
├── SalesManager        → Sales operations
├── Bookkeeper          → WorkWithInvoice
└── Admin               → Full access
```

### User Interfaces (src/Users/interfaces/)
- `UserMainInterface` - Base user contract
- `DealerInterface`, `ContractorInterface` - Role-specific
- `ContractsInterface`, `OrdersInterface`, `InvoicesInterface` - Entity access
- `ShipmentsInterface`, `FactoryWorkersInterface` - Domain-specific

## Database Schema
All tables use `gi_new_` prefix:

| Table | Purpose | Notes |
|:------|:--------|:------|
| `gi_new_users` | User accounts | **Separate from WP Users!** |
| `gi_new_contract` | Contracts | Core business entity |
| `gi_new_dialogues` | Chat messages | Per-contract messaging |
| `gi_new_orders` | Orders | ERP-synced |
| `gi_new_invoice` | Invoices | ERP-synced |
| `gi_new_shipment` | Shipments | Logistics |
| `gi_new_points` | Sales points | Dealer locations |
| `gi_new_firms` | Companies | Organization entities |
| `gi_new_log_*` | Sync logs | ERP sync tracking |
| `gi_new_metrics_messages` | Merged chat messages | Analytics source stream built from message shard tables |
| `gi_new_metrics_dialogues` | Dialogue-level analytics | Time/message metrics per dialogue |
| `gi_new_metrics_managers` | Factory-side analytics | Response/waiting/message metrics per dialogue-manager pair |
| `gi_new_metrics_contractors` | Contractor-side analytics | Response/waiting/message metrics per dialogue-contractor pair |

## External Integration

### ERP Sync (`UPWorker.php`)
> [!WARNING]
> Uses direct PDO connection, bypassing `$wpdb` entirely.

- **Connection**: Via `db_connect.php` (blocked by security hook)
- **Sync Types**: Orders, Dealers, Points, Firms, Invoices
- **Trigger**: WebSocket messages like `requestUpdateOrdersUP::: {logId}`

### ERP Connector (`connector_for_UP.php`)
> [!CAUTION]
> **UNAUTHENTICATED ENDPOINT** - Anyone can trigger ERP sync via GET parameters.

```
/connector_for_UP.php?logIdOrder=123
/connector_for_UP.php?logIdDealer=456
```

**Hardcoded URL**: `wss://geosideal.ru/wss/`

## JavaScript Layer Risk
> [!WARNING]
> **No bundler, no minification, global namespace pollution.**

| File | Size | Purpose |
|:-----|:-----|:--------|
| `modal_window.js` | **64KB** | UI modals (largest) |
| `utility.js` | **38KB** | Helper functions |
| `controls_for_page.js` | **24KB** | Page controls |
| `orders.js` | **22KB** | Order management |
| `notification.js` | **13KB** | Notifications |
| `partners.js` | **12KB** | Partner management |
| `contracts.js` | **11KB** | Contract UI |

**Total Custom JS**: ~200KB+ (plus 2MB+ of libraries: pdfmake, exceljs)


## Critical Analysis & Estimates

> [!CAUTION]
> **Complexity Level: EXTREME**
> This plugin is effectively a standalone MVC application disguised as a plugin. It allows "Login" and "Business Logic" completely separate from WP Users.

### Critical Issues (Immediate Attention Needed)
> [!CAUTION]
> **Security & Architectural Risks detected.**

1.  **Unauthenticated ERP Trigger** (CRITICAL)
    - **Location**: `connector_for_UP.php`
    - **Risk**: No auth on GET params. Anyone can trigger ERP sync.
    - **Remediation**: Add nonce verification or remove public access.
    - **Estimate**: 2-4 Hours.

2.  **WebSocket Input Validation**
    - **Location**: `Chat.php` - multiple `json_decode($body)` without validation
    - **Risk**: No rate limiting, no schema validation.
    - **Remediation**: Add JSON validation, rate limiting.
    - **Estimate**: 1-2 Days.

3.  **WebSocket Architecture Fragility**
    - **Risk**: The entire "Personal Cabinet" relies on a custom PHP process (`Chat.php`). If it crashes/restarts, users lose connection.
    - **Remediation**: Setup Supervisor/Systemd for auto-restart. Queue non-critical events.
    - **Estimate**: 1-2 Days (Config/Setup).

4.  **Auth Fragmentation (`gi_new_users`)**
    - **Risk**: Custom authentication table separate from WP Users. Susceptible to session inconsistencies.
    - **Remediation**: Audit session cookie handling.
    - **Estimate**: 1-2 Days.

5.  **JavaScript Monolith**
    - **Risk**: 200KB+ of unminified, unbundled JS with global variables.
    - **Remediation**: Webpack/Vite bundling, namespace isolation.
    - **Estimate**: 3-5 Days.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **10-15 Days** | Adding Interfaces, splitting `InteractionInterface`, standardizing DB calls. |
| **Rewrite (Modern Stack)** | **50-60 Days** | Reimplementing the WS server in Node/Laravel Reverb, migrating `gi_` tables to Eloquent/Models. |
| **JS Modernization** | **3-5 Days** | Bundling, minification, namespace cleanup. |
| **Security Hardening** | **2-4 Days** | ERP connector, input validation, rate limiting. |
