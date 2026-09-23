# Component Context: wordpress_framework

## Overview
`wordpress_framework` is the **Core Foundation** of the custom application. It is not a standard plugin but a library that provides the **Dependency Injection (DI) Container** and a suite of **Worker Classes** used by all other `bp_*` plugins.

## Architecture
- **Namespace**: `WPFramework`, `PersonalAccount` (Legacy namespace).
- **Entry Point**: `index.php` initializes the global `$servicesContainer`.
- **Global Variable**: `$servicesContainer` is available globally, allowing legacy plugins to resolve dependencies without constructor injection.

## Core Components

### 1. Dependency Injection Container
- **Class**: `PersonalAccount\Core\Container`
- **Role**: Registry for all shared services (Workers and Utilities).
- **Usage**: `$servicesContainer->get('DBWorker')`

### 2. Workers (Business Logic)
- **`KitchenWorker`** (`src/Workers/KitchenWorker.php`):
    - **Role**: Backend logic for the Kitchen, Wardrobe, and Commode Configurator/Display.
    - **Methods**: `getFacadesMain()`, `getTabletopsMain()`, `getHandlesMain()`, `getFittingsMain()`, `switchWhereLookFor()`.
    - **Logic**: Abstracts the complexity of querying materials tables. Supports `type` parameter ('kitchen', 'wardrobe', 'commode') to handle specific logic (e.g., resolving parent kitchen for commodes/wardrobes).
- **`DBWorker`**: Wrapper around `$wpdb` for standardized queries.

### 3. Utilities
- **`SimpleUtilities`**: Array and string manipulation helpers.
- **`DBUtilities`**: Query building helpers (WHERE clause generation).
- **`DataUtilities`**: Data formatting.
- **`UserUtilities`**: User role and permission helpers.

## Dependencies
- **Composer**: Uses `vendor/autoload.php`.
- **WordPress**: Depends on `$wpdb`.

## Critical Notes
> [!IMPORTANT]
> **Refactoring Target**: This framework is the logical place to centralize code that is currently duplicated across `bp_kitchen_add` and `bp_wardrobe_add`.

## Estimates & Critical Analysis

> [!NOTE]
> **Complexity Level: HIGH**
> This is the foundational layer. Changes here ripple across all `bp_*` plugins.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **5-7 Days** | Adding proper interfaces, improving Container API, documenting Workers. |
| **Rewrite (Modern Stack)** | **2-3 Weeks** | Migrating to PSR-11 Container, splitting into dedicated packages. |

### Critical Issues (Immediate Attention Needed)
> [!CAUTION]
> **Foundation Weakness**: Issues here affect the entire platform.

1.  **Global State Reliance (`$servicesContainer`)**
    - **Risk**: Hard dependency on a global variable makes unit testing impossible and creates hidden coupling.
    - **Remediation**: Refactor to proper Constructor Injection or `get_instance()` singleton pattern.
    - **Estimate**: 1-2 Weeks.

2.  **Tight Coupling**
    - **Risk**: Any change to `DBWorker` or `KitchenWorker` breaks 15+ plugins. No versioning.
    - **Remediation**: Introduce Interfaces for all Workers. SemVer tagging.
    - **Estimate**: 1 Week.

3.  **Missing Autoloading**
    - **Risk**: Manual `include/require` calls lead to class not found errors and memory bloat.
    - **Remediation**: Implement PSR-4 via Composer.
    - **Estimate**: 2-3 Days.
