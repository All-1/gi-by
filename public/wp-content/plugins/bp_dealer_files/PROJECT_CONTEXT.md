# Component Context: bp_dealer_files

## Overview
`bp_dealer_files` acts as a resource hub for dealers and designers. It manages file downloads (instructions, software, catalogs) and texture/model galleries.

## Key Features
- **File Repository**: Categorized lists (Software, Instructions, Tech Info, Ads).
- **Designer Resources**: Special section for 3D models and textures linked to specific Kitchen models.
- **FAQ System**: Admin-editable FAQ section for designers (`gi_designer_faq`).
- **Texture Management**: Admin interface to upload textures, generate thumbnails, and tag them by type (e.g., MDF, Enamel) and Kitchen model.

## Architecture
- **Main File**: `index.php`.
- **Database Tables**:
  - `gi_dealers_files`: General files.
  - `gi_designer_architect`: 3D models/descriptions.
  - `gi_designer_textures`: Texture images.
  - `gi_designer_faq`: Q&A.
- **Shortcodes**:
  - `[user_files]`: Main file list UI.
  - `[print_designers_files]`: Gallery of materials/kitchens for designers.

## Admin Features
- Custom Admin Menu: "Сотрудничество" -> "Файлы дилеров", "Файлы дизайнеров", "Текстуры".
- AJAX-powered editing/deleting of files and FAQ items (`redact_designer_faq.php`, `delete_textures_data.php`).

## Estimates & Critical Analysis

> [!TIP]
> **Complexity Level: MEDIUM**
> Multiple content types (files, textures, 3D models, FAQ) increase scope.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **3-5 Days** | Consolidating file upload logic, adding access controls per category. |
| **Rewrite (Modern Stack)** | **1-2 Weeks** | Media library integration, CDN support, proper file type validation. |

### Critical Issues (Immediate Attention Needed)
> [!CAUTION]
> **Security Vulnerabilities**.

1.  **Unrestricted File Uploads**
    - **Risk**: Direct filesystem writes.
    - **Remediation**: Use `wp_handle_upload`. Validate MIME types.
    - **Estimate**: 4-6 Hours.

2.  **Data Fragmentation**
    - **Risk**: 4 separate tables make querying difficult.
    - **Remediation**: Consolidate into a polymorphic 'Resources' table or WP Post Types.
    - **Estimate**: 1 Week.
