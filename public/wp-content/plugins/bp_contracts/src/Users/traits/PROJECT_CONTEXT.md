# Component Context: bp_contracts/Users/traits

## Overview
This directory contains **Specific Behavior Mixins** (Traits) that are composed into the User Classes. This promotes code reuse across different User Roles that share similar capabilities (e.g., both `Dealer` and `FactoryWorker` implementation of `WorkWithContracts`).

## Traits Taxonomy

### 1. Capability Traits
- **`WorkWithContracts.php`**: Methods to fetch, filter, and view contracts.
- **`WorkWithOrders.php`**: Logic for accessing specific order details.
- **`WorkWithInvoice.php`**: Methods for viewing financial documents.
- **`ManageContractors.php`**: Logic for Dealers to manage their own sub-contractors (Installers, Designers).

### 2. Role Specific Traits
- **`DealerTrait.php`**: Implementation of `DealerInterface`. Logic specific to the Partner role.
- **`DistributorTrait.php`**: Logic for managing child dealers.

### 3. "Capsule" Traits (`/capsule`)
- **Role**: Data Transfer Objects (DTOs) defined as Traits.
- **Files**: `CapsuleUserParam.php`, `CapsuleDealerParam.php`.
- **Purpose**: Defines standard properties (fields) for User objects to ensure strict typing and consistency across the hierarchy.

## Interfaces (`../interfaces`)
- **Role**: Contracts defining what a User Class *must* implement.
- **Key Files**: `DealerInterface.php`, `OrdersInterface.php`, `FactoryWorkersInterface.php`.
- **Usage**: Type-hinting in Controllers (e.g., `function doSomething(DealerInterface $user)`).
