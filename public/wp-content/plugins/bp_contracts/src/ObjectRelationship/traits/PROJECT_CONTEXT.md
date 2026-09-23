# Component Context: bp_contracts/ObjectRelationship/traits

## Overview
This directory contains shared logic for Business Entities (`Contract`, `Order`, `Invoice`).

## Traits

### 1. `InvoiceAndShipment.php`
- **Role**: Shared logic for "Document" type objects.
- **Usage**: Used by both `Invoice` and `Shipment` classes to handle common status updates or date formatting.

### 2. Capsules (`/capsule`)
- **Role**: Entity Definitions.
- **Files**:
    - `CapsuleContract.php`: Defines the standard fields of a Contract (`serialNumber`, `name`, `status`).
    - `CapsuleOrder.php`: Defines the fields of an Order.
- **Purpose**: These traits act as the "Schema" for the entities, keeping the getter/setter logic separate from the business logic methods in the main Class files.
