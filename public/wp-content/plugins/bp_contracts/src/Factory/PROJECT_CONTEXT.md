# Component Context: bp_contracts/Factory

## Overview
The `Factory` component acts as the **Central Instantiation Hub** for the application. It implements the Factory Method pattern to decouple Business Logic from Object Creation.

## Core Component: `Factory.php`

### 1. `createUser($idUser, $closures)`
- **Role**: Converting a UserID + Database Role into a specific concrete Class.
- **Mapping**:
    - `distributor` -> `Distributor`
    - `dealer` -> `Dealer`
    - `manager` -> `ContractsWorker`
    - `administrator` -> `Admin`
    - ... and so on for all 10+ roles.

### 2. `createDependentObjects($type, ...)`
- **Role**: Creating core business entities.
- **Responsibility**: Instantiates objects like `Contract`, `Invoice`, `DialogOrder` and injects necessary dependencies (closures, parent references).
- **Design**: Uses a simple `switch` statement to route the request to the correct class constructor.

## Dependencies
- **Container**: The Factory is heavily dependent on the DI Container to inject services (`UserUtilities`, `CatcherBugs`) into the objects it creates.
