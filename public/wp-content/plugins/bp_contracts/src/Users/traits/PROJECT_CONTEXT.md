# Component Context: bp_contracts/Users/traits

## Overview
**Capability mixins** composed into user classes. Controllers call methods on the active `UserMain` subclass without knowing every role.

## Traits Taxonomy

### 1. Capability Traits
- **`WorkWithContracts.php`**: Fetch, filter, view contracts.
- **`WorkWithOrders.php`**: Order detail and list state.
- **`WorkWithInvoice.php`**: Financial documents.
- **`WorkWithShipments.php`**: Shipment / logistics views (`ShipmentManager`).
- **`ManageContractors.php`**: Dealer-side external staff (installers, designers).
- **`ManageFactoryWorkers.php`**: Factory HR / co-worker management.

### 2. Analytics
- **`ManageAnalytics.php`**: State and API for the analytics dashboard (`Admin` only in practice).
  - **State**: `whose` (`all` \| `factory` \| `contractor` \| `dealer`), `dialoguesType`, date range, `period`, `timeMode` (`work_hours` \| `standard`), SLA minutes (first/subsequent), `idUserAnalytics`, user search string.
  - **`getAnalytics()`**: Builds params → `AnalyticsWorker` → JSON for `AnalyticsOnPage`.
  - **`getDealerDependentsAnalytics()`**: Drill-down for one dealer’s contractors (`dealer_dependents` mode).
  - **Setters**: `setAnalyticsWhose`, `setAnalyticsDialoguesType`, `setAnalyticsPeriod`, `setAnalyticsTimeMode`, `setAnalyticsSla*`, `setAnalyticsUser`, date setters, `getAnalyticsUserSearch`.
  - **Users catalog**: `UserCollector` → `factory`, `contractor`, `dealer` lists with `search` field.

See [Workers/Analytics](../../Workers/Analytics/PROJECT_CONTEXT.md).

### 3. Role Specific Traits
- **`DealerTrait.php`**: `DealerInterface` behavior.
- **`DistributorTrait.php`**: Child dealer management.

### 4. "Capsule" Traits (`/capsule`)
DTO-style property accessors: `CapsuleUserParam`, `CapsuleDealerParam`, `CapsuleDistributorParam`, `CapsuleContractorParam`, `CapsuleFactoryWorkersParam`, etc.

## Interfaces (`../interfaces`)
`UserMainInterface`, `DealerInterface`, `ContractsInterface`, `OrdersInterface`, `InvoicesInterface`, `ShipmentsInterface`, `FactoryWorkersInterface`, `WorkWithParamInterface`.

## Related
- [Users overview](../PROJECT_CONTEXT.md)
- [WebSocket analytics commands](../../PROJECT_CONTEXT.md)
