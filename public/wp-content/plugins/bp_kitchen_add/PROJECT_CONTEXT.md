# Component Context: bp_kitchen_add

## Overview
`bp_kitchen_add` is a **monolithic admin plugin** (90KB single file `index.php`) responsible for the entire "Create/Edit Kitchen" workflow. It handles form rendering, data validation, database insertion, image processing, and WordPress page generation.

## Architecture
- **Entry Point**: `index.php`.
- **Functions**:
    - `add_menu_page`/`add_submenu_page`: Registers Admin UI.
    - `add_kitchen()`: The giant function handling both Form Display and POST processing for adding.
    - `redact_kitchen()`: Handles editing logic.
    - `kitchen_catalogue()`: Listing existing kitchens in Admin.
- **Data Flow**:
    1.  **Input**: Admin fills generic fields (Name, Cost), Selects Facades/Tabletops/Handles (Checkboxes).
    2.  **Processing**:
        - Creates directory structure: `.../images/kitchen/<eng_name>/`.
        - **Image Resizing**: Custom PHP GD logic to create thumbnails (200px width) and optimize images.
        - **WordPress Page**: calls `wp_insert_post()` to create a Page with `[kitchen_print]` content.
    3.  **Storage**: Inserts into `gi_kitchen` table.

## Key Features
- **Dynamic File Management**: Directly uses `mkdir`, `move_uploaded_file`, `scandir` to manage an asset hierarchy on the disk.
- **Relational Data**: Links Kitchens to:
    - `gi_facade` (Facades)
    - `gi_tabletop` (Countertops)
    - `gi_handle` (Handles)
    - `gi_kitchen_filtres` (Styles, Colors)
- **Hardcoded Logic**: Has extensive arrays for transliteration (Cyrillic -> Latin) for URL/Folder generation.

## Dependencies
- **Filesystem Permissions**: Must have write access to `wp-content/plugins/bp_kitchen_add/images/`.
- **Tables**: `gi_kitchen` (Master), plus all accessory tables.

## Critical Analysis & Estimates

> [!WARNING]
> **Complexity Level: HIGH (Legacy)**
> This is a classic "God File" implementation (`index.php`). It mixes View, Business Logic, and Direct Filesystem IO.

### Critical Issues (Immediate Attention Needed)
> [!CAUTION]
> **Security Violation**: This plugin contains high-risk vulnerabilities.

1.  **Unrestricted File Uploads (`index.php`)**
    - **Risk**: Uses `move_uploaded_file` with weak or no validation. Allows potential RCE/Shell uploads.
    - **Remediation**: Use `wp_handle_upload` or enforce strict MIME/Extension allow-lists immediately.
    - **Estimate**: 4-6 Hours.

2.  **Monolithic Architecture**
    - **Risk**: `index.php` is a single failure point. Logic/View mix makes stable hotfixes impossible.
    - **Remediation**: Extract File logic to `ImageManager` class. Extract View to templates.
    - **Estimate**: 8-12 Hours (Stabilization).

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **5-8 Days** | separating Logic from View. Implementing an `ImageManager` class to wrap unsafe FS calls. |
| **Rewrite (Modern Stack)** | **25-35 Days** | Rebuilding the complex "Configurator" UI in React/Vue. Migrating data to a structured relationship model. |

