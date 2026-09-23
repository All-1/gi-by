# Component Context: bp_contracts/Core

## Overview
The `Core` directory acts as the **Bootstrap & Service Registry** for the application. It handles the wiring of all dependencies when the WebSocket server starts.

## Components

### 1. `Container.php` (Dependency Injection)
- **Role**: A simple Service Locator / DI Container.
- **Mechanism**:
    - Stores singleton instances of services in an array `$services`.
    - `set($name, $service)`: Registers a service.
    - `get($name)`: Retrieves a service by key.

### 2. `SystemConstructor.php` (The Bootstrapper)
- **Role**: The main setup class called by `server-ws.php` (or the entry point).
- **Responsibility**:
    1.  Instantiates the `Container`.
    2.  Instantiates **ALL** Workers, Utilities, and Controllers.
    3.  Injects the `Container` (by reference) into every service.
    4.  Registers every service back into the `Container`.
- **Key Flow**:
    ```php
    $this->container = new Container();
    $DBWorker = new DBWorker($linkContainer);
    $this->container->set('DBWorker', $DBWorker);
    ```
- **Dependencies**: This file has `use` statements for *almost every class in the system*, making it the central coupling point.
