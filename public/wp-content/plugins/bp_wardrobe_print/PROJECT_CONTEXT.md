# Component Context: bp_wardrobe_print

## Overview
`bp_wardrobe_print` is the **Frontend Catalog Rendering Engine** for Wardrobes. It mirrors the logic of `bp_kitchen_print`.

## Architecture
- **Main File**: `index.php` (Refactored to use `wordpress_framework`).
- **AJAX Handler**: `ajax_catalogue.php` (Legacy filtering).
- **Shortcodes**:
    - `[catalogue_wardrobe_print]`: Main catalog.
    - `[wardrobe_print]`: Single wardrobe page.

## Key Features
- **Refactored Visualization**:
    - `[wardrobe_print]` now uses the unified `KitchenWorker` and `bp_kitchen_print` JS assets for displaying Facades, Handles, and Fittings.
    - Uses AJAX filtering tabs similar to Kitchens.
- **Legacy JS**: Still uses inline `jQuery` for the main catalog filter (`[catalogue_wardrobe_print]`).
- **Filtering Logic**:
    - Constructs complex SQL queries dynamically based on `$_POST` params from the frontend.
    - Filters by `gi_wardrobe_filtres` (Config, Style, Color).

## Dependencies
- **Framework**: Uses `WPFramework\Workers\KitchenWorker` for data retrieval (Facades, Fittings, Handles).
- **Shared Assets**: Depends on `bp_kitchen_print/js/script.js` and `bp_kitchen_print/ajax_kitchen.php`.
- **Tables**: `gi_wardrobe`, `gi_kitchen_material` (Note: reuses Kitchen materials table!), `gi_wardrobe_filtres`.

## Estimates & Critical Analysis

> [!TIP]
> **Complexity Level: MEDIUM**
> Mirror of `bp_kitchen_print` with wardrobe-specific logic.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **3-5 Days** | Extract shared filter logic with `bp_kitchen_print` to framework. |
| **Rewrite (Modern Stack)** | **2 Weeks** | Consolidate into unified catalog frontend with configurable product types. |

### Critical Issues (Immediate Attention Needed)
> [!CAUTION]
> **Security & Stability Risk**.

1.  **Dynamic SQL / Injection Risk**
    - **Risk**: Dynamic query construction poses significant SQLi risk in `ajax_catalogue.php`.
    - **Remediation**: Audit all `ajax_catalogue.php` inputs. Use `$wpdb->prepare`.
    - **Estimate**: 1-2 Days.

2.  **Monolithic Architecture**
    - **Risk**: Legacy `index.php` parts still large.
    - **Remediation**: Rewrite frontend to consume a proper REST API.
    - **Estimate**: 2-3 Weeks (Rewrite).
