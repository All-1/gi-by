# Component Context: bp_materials

## Overview
`bp_materials` manages auxiliary components of the kitchen ecosystem: specific Materials and Accessories (Fittings/Furnitura).

## Key Features
- **Materials Dictionary**: Admin interface to manage the list of available kitchen materials (`gi_kitchen_material`).
- **Accessories Management**:
  - CRUD for fittings (Hinges, Lift systems, Lighting).
  - Stored in `gi_acsessories`.
  - Fields: Type, Vendor/Name, Content Link, Image.
- **Frontend Display**: `[print_acsessories_buttons]` displays a tabbed interface for users to browse accessories.

## Architecture
- **Main File**: `index.php`.
- **Database Tables**:
  - `gi_kitchen_material`: Simple ID/Name/Name_Eng table.
  - `gi_acsessories`: Detailed accessories data.
- **AJAX**: Uses `ajax_acses.php` to fetch content when clicking accessory tabs.

## Integration
- Used by `bp_kitchen_add` to populate dropdowns/checkboxes for material selection.
- Used by `bp_kitchen_print` for filtering logic.

## Estimates & Critical Analysis

> [!NOTE]
> **Complexity Level: LOW**
> Simple lookup table management with minimal business logic.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **2-3 Days** | Adding validation, improving admin UI. |
| **Rewrite (Modern Stack)** | **1 Week** | REST API, integration with centralized product management. |

### Key Risks
1. **Data Integrity**: No foreign key constraints. Deleting a material may break kitchen references.
2. **Simple Security**: Basic admin-only access, but no CSRF protection on forms.
