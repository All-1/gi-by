# Component Context: bp_wardrobe_add

## Overview
`bp_wardrobe_add` mirrors the functionality of `bp_kitchen_add` but tailored for Wardrobes (Closets). It follows the same "Monolithic Admin Manager" pattern.

## Architecture
- **Entry Point**: `index.php`.
- **Key Functions**:
    - `add_wardrobe()`: Handles creation.
    - `redact_wardrobe()`: Handles updates.
    - `addGalleryWardrobe()`: Specific helper for gallery management.
- **Filesystem Structure**:
    - Stores images in `images/wardrobe/<model_name>/` with subfolders `avatar`, `gallery`, `facade`.
    - Handles thumbnail generation manually.

## Database & Data Model
- **Table**: `gi_wardrobe`.
- **Fields**:
    - `basic_cost`: Base price.
    - `sales`: Discount flag/percentage.
    - `material`: Comma-separated string of materials (e.g., "Wood, Plastic").
    - `fittings`/`handles`: Serialized or comma-separated references to components.
- **WordPress Integration**: Creates a `page` post type with `[wardrobe_print]` shortcode.

## Dependencies
- **Shared Tables**: Uses `gi_handle` (shared with Kitchens) and `gi_wardrobe_fittings`.
- **Logic Duplication**: Heavily copies logic from `bp_kitchen_add` (Transliteration arrays, image resizing code), indicating a need for refactoring into a shared utility library.

## Critical Analysis & Estimates

> [!NOTE]
> **Complexity Level: MEDIUM**
> While structurally identical to `bp_kitchen_add`, it has fewer moving parts (fewer accessory tables).

### Critical Issues (Immediate Attention Needed)
> [!CAUTION]
> **Security Violation**: Inherits all vulnerabilities from `bp_kitchen_add`.

1.  **Unrestricted File Uploads**
    - **Risk**: Direct `move_uploaded_file` calls allow shell uploads.
    - **Remediation**: Apply same fix as `bp_kitchen_add` (Strict validation).
    - **Estimate**: 4-6 Hours.

2.  **Code Duplication**
    - **Risk**: 90% copy-paste from `bp_kitchen_add`. Security fixes there are NOT automatically applied here.
    - **Remediation**: Refactor shared logic to `wordpress_framework`.
    - **Estimate**: 1 Week.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **3-5 Days** | Extracting shared logic (Image handling, Transliteration) into a common library used by both plugins. |
| **Rewrite (Modern Stack)** | **15-20 Days** | Reimplementing the Form UI. Could benefit significantly from a shared "Product Builder" component with Kitchens. |

