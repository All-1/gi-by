# Component Context: bp_zakazi

## Overview
`bp_zakazi` ("My Orders") provides the Dealer Cabinet interface for tracking order status, viewing proformas, and monitoring shipments. It is heavily reliant on an AJAX-driven table view.

## Architecture
- **Main File**: `index.php` (contains the `[moi_zakazi]` shortcode and basic UI shell).
- **Data Source**: `temporary_<USER_ID>` tables. These tables are populated by a synchronization process (likely in `bp_contracts` or a cron job) for performance, giving each dealer a dedicated view of their orders.

### backend Logic
1.  **`ajaxtable.php`**:
    - **Purpose**: Returns the HTML rows for the orders table.
    - **Filters**:
        - `search`: LIKE query against `order_number`, `client_numer`, `point`, `status`.
        - `otgruz` ('yes'): Filters out 'Shipped' (`$lang_adm->mz_shipped`) items.
        - `date_from`/`date_to`: Filters by `shipping_date`.
    - **Pagination**: Calculated server-side, renders 20 items per page.
2.  **`dannie.php`**:
    - **Purpose**: Calculates Totals (Netto, Brutto, Volume) for *selected* rows.
    - **Logic**: Accepts an array of IDs from checkboxes, queries `temporary_$user`, sums the columns, and returns a summary HTML block.

## Key Features
- **Legacy PDF Generation**: Buttons for "Vedomost" and "Print" submit to `/docs_generate.php` (root level script).
- **Status Tracking**: Visualizes diverse states: "In Production", "Shipped", "Ready", etc.
- **Role Check**: Checks `user_role !== 'designer_architect'` to show/hide print buttons (Designers presumably just view, don't generate docs).

## Dependencies
- **DB**: `temporary_<ID>` tables are critical. If missing, the plugin fails or shows empty.
- **Global**: `$lang_adm` (Localization global).
- **Root Scripts**: `/docs_generate.php` for document outputs.

## Critical Analysis & Estimates

> [!TIP]
> **Complexity Level: MEDIUM**
> The complexity here comes from data synchronization (`temporary_` tables) rather than the UI itself.

### Critical Issues (Immediate Attention Needed)
> [!CAUTION]
> **Data Integrity Risk**: Critical Scalability Flaw.

1.  **Per-User Temporary Tables**
    - **Risk**: Creating `temporary_$USER_ID` tables (e.g., 500+ extra tables) destroys database performance/backups and breaks if sync fails.
    - **Remediation**: Consolidate to single `gi_orders` table with `user_id` index.
    - **Estimate**: 2-3 Weeks (Schema definition + migration script).

2.  **Data Duplication**
    - **Risk**: Data in temporary tables gets out of sync with source of truth (`bp_contracts` or internal ERP).
    - **Remediation**: Fetch live data or use read-through cache strategies.
    - **Estimate**: N/A (Solved by above).

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **3-5 Days** | Optimizing the `ajaxtable.php` query. Adding error checking for missing temporary tables. |
| **Rewrite (Modern Stack)** | **10-15 Days** | Moving from temporary tables to a proper `orders` table with `user_id` index. Rebuilding the grid in a JS Data Grid. |

