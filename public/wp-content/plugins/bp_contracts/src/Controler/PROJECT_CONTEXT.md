# Component Context: bp_contracts/Controler

## Overview
The `Controler` directory contains the **Business Logic Layer**, orchestrating data flow between WebSockets, Models, and the Database. It follows a strict separation of concerns where `InteractionInterface` acts as the API Gateway.

> **Deep Dive**: See [Controller Traits](./traits/PROJECT_CONTEXT.md) for shared DTOs and Helper Logic.

## 1. `InteractionInterface.php` (API Gateway)
**Role**: The central hub for all incoming WebSocket events. It maps "Titles" (Commands) from `Chat.php` to specific business logic.

### Key Responsibilities
- **Routing**: Dispatches commands like `newContract`, `sendMessage`, `searchQuery`.
- **Response Formatting**: Prepares JSON payloads for the frontend.
- **Broadcast Management**: Calculates `usersForUpdate` - the list of WebSocket IDs that need to receive state changes (e.g., when a new message is sent, only participants of that contract see it).

### key Methods
- `regNewUser($userId, $idWebsocket)`: Authenticates a connection & bootstraps the user session.
- `showContracts($idWebsocket)`: Fetches and formats the main "Contract Grid" for a user.
- `regNewMessage($message)`: Validates input, saves to DB (via `ControlerObjectsRelationship`), and triggers notifications.
- `getPotencialParticipiant`: Logic for adding new users (e.g., a Factory Technologist) to a chat context.

### Analytics routing
Filter/state commands delegate to `UserControler`, which forwards to the connected user object via `getFromUserStuff()`:

- `showAnalytics`, `getDealerDependentsAnalytics`
- `setAnalyticsWhose`, `setAnalyticsDialoguesType`, `setAnalyticsPeriod`, `setAnalyticsTimeMode`
- `setAnalyticsSlaFirstResponse`, `setAnalyticsSlaSubsequentResponse`
- `setAnalyticsUser`, `getAnalyticsUserSearch`, `setStartDateAnalytics`, `setEndDateAnalytics`

Implementation lives on `Admin` through [ManageAnalytics](../Users/traits/PROJECT_CONTEXT.md). `Chat.php` wraps responses as `AnalyticsOnPage`, `AnalyticsDealerDependentsOnPage`, `analyticsUserSearch`.

Command list: [src/PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md).

---

## 2. `UserControler.php` (User Manager)
**Role**: Manages the lifecycle, state, and permissions of `User` objects.

### Core Logic
- **Factory Pattern**: Dynamically instantiates specific User classes (`Dealer`, `FactoryWorker`, `Distributor`) based on the DB role.
- **Session Management**: Maps `idWebsocket` (Connection) to `idUser` (Database ID).
- **Online Tracking**: `findUserOnline($idUser)` helps determine if a user should receive a live push or an email notification.

### Critical Methods
- `logicCreateContract($idUser, $name)`: Validates permissions before allowing a user to create a new contract scope.
- `checkRightsDoChanges($who, $whom)`: Security gate - ensures a Dealer cannot modify a Factory Admin's state.
- `findUsersByPoints($idPoints)`: Resolves which users belong to a specific physical salon/point (used for visibility scoping).

---

## 3. `ControlerObjectsRelationship.php` (Entity Manager)
**Role**: Managing the complex hierarchy of business objects (Contracts -> Orders -> Invoices).

### Core Logic
- **State Synchronization**: "Hydrates" `Contract` objects with data from `DBWorker`.
- **Access Control List (ACL)**: Determines visibility.
    - *Example*: A Contract created by "Dealer A" is visible to "Dealer A", "Distributor B" (Parent), and "Factory Admin", but NOT "Dealer C".
- **CRUD Operations**: Centralizes all creation/deletion/updates for business objects to ensure data integrity.

### Critical Methods
- `instantiateContract($serialNumber)`: Loads a full Contract tree from the DB into memory.
- `setUsersHaveAccess($idPoint)`: The core ACL algorithm.
- `requestUpdateOrdersCOR($logId)`: Handles synchronization events when the ERP system updates an order status (e.g., "In Production" -> "Shipped").

## Dependencies
- **Data Access**: Heavily relies on `DBWorker` for raw SQL execution.
- **Utilities**: Uses `UserUtilities` for role resolution.
