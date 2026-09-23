# Component Context: bp_facades_list

## Overview
`bp_facades_list` is a specialized plugin for managing kitchen facades (doors/fronts). It focuses on the Admin backend to curate a catalog of facades with properties like "New", "Antibac", "Silver Defence".

## Key Features
- **Facet Management**: CRUD operations for Facade Materials (e.g., "Massiv", "MDF").
- **Visual Catalog**: Uploads and displays facade images with associated metadata (Texture type, Kitchen model linkage).
- **Properties**: Tracks special attributes like Antibacterial coating or Silver defense ions.
- **Database**:
  - `gi_facade_material`: Lookup table for material types.
  - `gi_facades_list`: Main registry of facade images and attributes.

## Architecture
- **Main File**: `index.php`.
- **Admin UI**:
  - "Фасады new" menu item.
  - Custom grid layout for editing facade properties.
- **Dependencies**:
  - Relies on `bp_contracts/js/utility.js` for some utilities (Tight coupling with `bp_contracts`).
  - Uses `gi_kitchen` table for filtering/tagging.

## Data Flow
- Admin uploads image -> Script generates thumbnail -> Saves to `gi_facades_list`.
- AJAX used for "Live search" in admin panel (`search_filtres()`).

## Estimates & Critical Analysis

> [!NOTE]
> **Complexity Level: LOW-MEDIUM**
> Admin-focused plugin with straightforward CRUD operations.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **2-3 Days** | Adding input validation, securing image uploads. |
| **Rewrite (Modern Stack)** | **1 Week** | REST API for facade data, proper image library integration. |

### Key Risks
1. **Cross-Plugin Coupling**: Relies on `bp_contracts/js/utility.js` for utilities.
2. **Image Management**: Direct filesystem writes for thumbnails. No cleanup on delete.
3. **No Validation**: Properties like "Antibac" or "Silver Defence" flags may be inconsistently set.
