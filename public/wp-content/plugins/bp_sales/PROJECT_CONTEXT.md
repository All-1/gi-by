# Component Context: bp_sales

## Overview
`bp_sales` is a simple plugin to manage "On Sale" status for Kitchens.

## Key Features
- **Admin Interface**:
  - Grid view of all Kitchens (`gi_kitchen`).
  - Input field to set a percentage discount.
  - "Go" button applies updates in bulk.
- **Frontend**:
  - `[sales_print]`: Displays a specific grid of Kitchens that have a discount (`sales != 'no'`).

## Architecture
- **Main File**: `index.php`.
- **Database Tables**:
  - `gi_kitchen`: Updates the `sales` column directly.

## Data Flow
- `index.php` -> POST request -> `UPDATE gi_kitchen SET sales = '...'`.
- `[sales_print]` -> `SELECT * FROM gi_kitchen WHERE sales != 'no'`.

## Estimates & Critical Analysis

> [!NOTE]
> **Complexity Level: LOW**
> Simple discount management with minimal logic.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **1-2 Days** | Adding bulk update confirmation, validation. |
| **Rewrite (Modern Stack)** | **3-5 Days** | REST API, scheduled sales, date ranges. |

### Key Risks
1. **String-Based Flag**: `sales` column stores string values ('no', percentages). Type inconsistency.
2. **No History**: No audit trail of when sales were applied or removed.
3. **Direct SQL**: Bulk updates without transaction safety.
