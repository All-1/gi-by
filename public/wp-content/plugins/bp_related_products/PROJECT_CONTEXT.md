# Component Context: bp_related_products

## Overview
`bp_related_products` manages "Commodes" (Living Rooms / Гостинные). Despite the name related_products, it primarily functions as a standalone catalog for Living Room furniture that complements the Kitchens.

## Key Features
- **Living Room CRUD**:
  - Add/Edit "Commodes" (Living Room sets).
  - Auto-creates WP Pages (`post_type=page`, parent=44) with shortcode `[some_commode_print]`.
  - Image Gallery management with thumbnail generation.
- **Kitchen Integration**:
  - Links a Living Room to a parent "Kitchen" model.
  - Inherits/Displays technical info from the parent Kitchen (Style, Material, Wood, etc.).
  - Pulls available Facades (`gi_facades_list`) and Handles (`gi_handle`) based on validity for the parent Kitchen.
- **Frontend**:
  - `[commode_print]`: Catalog grid.
  - `[some_commode_print]`: Single Product Card. Refactored to use `KitchenWorker` and shared `bp_kitchen_print` JS for visualization (Slider + AJAX Tabs for Facades/Handles).

## Architecture
- **Main File**: `index.php`.
- **Database Tables**:
  - `gi_commode`: Product data (`kitchen` foreign key by name, `folder` path).
  - `gi_commode_visualisation`: Gallery images.
- **Dependencies**:
  - `wordpress_framework`: Uses `KitchenWorker` to fetch associated Kitchen materials (Facades, Handles).
  - `bp_kitchen_print`: Uses shared JS assets for rendering tabs.
  - `gi_kitchen`: Parent product data.
  - `gi_facades_list`, `gi_handle`: Components.

## Notes
- Uses `bp_contracts/js/utility.js`.
- File system manipulations (mkdir, unlink) for galleries.

## Estimates & Critical Analysis

> [!TIP]
> **Complexity Level: MEDIUM**
> Simpler than kitchen plugins but inherits parent kitchen's complexity.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **3-5 Days** | Separating gallery logic, adding validation for parent kitchen linkage. |
| **Rewrite (Modern Stack)** | **2 Weeks** | Integrating into unified product system with proper relations. |

### Key Risks
1. **Orphan Products**: If parent Kitchen is deleted, Living Room data becomes orphaned.
2. **Filesystem Ops**: Same `mkdir`/`unlink` security risks as other catalog plugins.
3. **Cross-Plugin Dependency**: Uses `bp_contracts/js/utility.js` - tight coupling. Now also coupled with `bp_kitchen_print`.
