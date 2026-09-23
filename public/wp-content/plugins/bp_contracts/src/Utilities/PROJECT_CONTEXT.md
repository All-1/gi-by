# Component Context: bp_contracts/Utilities

## Overview
Utilities are stateless helper services providing shared functionality across the application. They focus on **Data Transformation**, **Query Construction**, and **User Metadata** resolution.

## Key Services

### 1. `DBUtilities.php` (SQL Query Builder)
**Role**: Dynamically generates complex SQL clauses for `DBWorker`.
**Core Methods**:
- `createConditionQueryIN($column, $param)`: Builds `WHERE id IN (1,2,3)` clauses safely.
- `prepareBetween($column, $start, $end)`: Generates date-range SQL.
- `prepareInsert($rows)`: Converts a 2D array into a bulk `INSERT INTO ... VALUES (...)` string.
- `mergeCondition()`: Intelligently combines multiple WHERE clauses with AND/OR logic.

### 2. `DataUtilities.php` (Array & Collection Manipulation)
**Role**: The "Lodash" of the project. Handles complex array sorting and restructuring.
**Core Methods**:
- `sortArray($array, $where, ...)`: A powerful multi-dimensional sorter used for grids.
- `packageContracts($contracts, $orders, ...)`: The **Critical** function that merges disparate raw DB rows (contracts, orders, points) into the nested JSON tree used by the frontend.
- `prepareOrdersForUserU()`: Formats order dates and statuses for UI display (timestamps to human-readable strings).

### 3. `UserUtilities.php` (Role & Permission Helper)
**Role**: Centralizes logic for User Metadata.
**Core Methods**:
- `getUserRole($id)`: Resolves the complex mapping of WordPress roles -> BP Roles (Dealer, Factory, etc).
- `checkUserAccess($idUser)`: Validates if a user is active/banned.
- `packageUser($sqlUsers)`: Formats raw user rows into safe "Public Types" (removing passwords/salts before sending to client).
- `getManagerForPoint($idPoint)`: Finds the responsible Sales Manager for a specific Salon.

### 4. `DialogServices.php` (Chat Logic)
**Role**: Helper for `Dialog` objects.
**Responsibilities**:
- Formatting timestamps for chat bubbles.
- Grouping messages by date.
- Preparing the "Unread Count" badges.

### 5. `Utilit.php` (Legacy / Deprecated)
> [!CAUTION]
> This file contains mixed concerns and is a target for refactoring.
**Contents**:
- Legacy date parsers.
- Direct property accessors.
- Some logic duplicating `DataUtilities`.
**Strategy**: New code should use `DataUtilities` or `SimpleUtilities` instead.
