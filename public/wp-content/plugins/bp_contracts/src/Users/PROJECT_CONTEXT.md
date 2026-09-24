# Component Context: bp_contracts/Users

## Overview
This directory implements the **Domain Models** for system actors. It uses a polymorphic class hierarchy to encapsulate the specific business rules (Permissions, Data Visibility, UI Options) for each user role.

> **Deep Dive**: See [Traits & Interfaces](./traits/PROJECT_CONTEXT.md) for Capability Mixins (`WorkWithContracts`) and Role Interfaces.

## Class Hierarchy

### Base Class: `UserMain.php`
**Role**: Abstract parent class defining the interface for all users.
- **State**: Holds `idUser`, `login`, `role`, `idWebsocket`, and `idPoints` (assigned salons).
- **Shared Logic**:
    - `getUnreadedMessagesDB()`: Fetches unread counts.
    - `updateStatusUser()`: Manages "Online/Away/Do Not Disturb" states.
    - `shutDownDialog()`: Logic for leaving a chat conversation.

---

### Dealer Ecosystem
*Users associated with specific "Points (Salons)"*

#### `Contractor.php`
- **Role**: Base class for dealer-side users with `ManageContractors`.
- **Children**: `Dealer`, `FreeDealer`.

#### `Dealer.php`
- **Role**: The primary partner (`extends Contractor`).
- **Visibility**: Sees ONLY contracts linked to their assigned `idPoints`.
- **Permissions**: Can create Contracts, place Orders, invite `SalesManager`.

#### `FreeDealer.php`
- **Role**: Limited dealer; invoice access via `InvoicesInterface`.

#### `Distributor.php`
- **Role**: Regional manager for multiple Dealers.
- **Visibility**: Cascading access. Sees all contracts of their child Dealers.

#### `SalesManager.php`
- **Role**: Staff working at a Dealer's salon.
- **Visibility**: Limited to specific orders or shared salon access (configurable).

---

### Factory Ecosystem
*Internal staff with cross-company visibility*

#### `FactoryWorker.php`
- **Role**: General factory staff (e.g., Technologists).
- **Visibility**: Can be invited to ANY contract to resolve issues.

#### `Admin.php`
- **Role**: Superuser.
- **Visibility**: Global access to all Contracts and Debugging tools.
- **Analytics**: Uses `ManageAnalytics` trait; owns `AnalyticsWorker` instance for the `[analytics]` page.

#### `ContractsWorker.php` (user role)
- **Role**: Factory staff focused on contract operations (`extends FactoryWorker`, `ContractsInterface`).
- **Note**: Name collision with `Workers\ContractsWorker` — infrastructure vs domain role.

#### `Bookkeeper.php`
- **Role**: Finance department.
- **Focus**: Read-only access to Contracts; Write access to **Invoices** (`DialogInvoice`).

#### `ShipmentManager.php`
- **Role**: Logistics.
- **Focus**: Access to `Shipment` objects and "Ready" status orders.

## Data Integration
- **`gi_new_users`**: The primary table storing User metadata (separate from WP `users` table).
- **`gi_rel_users_points`**: Maps Users <-> Salons (Many-to-Many).
