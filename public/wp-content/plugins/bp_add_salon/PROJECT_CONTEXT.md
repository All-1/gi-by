# Component Context: bp_add_salon

## Overview
`bp_add_salon` enables dealers to add and manage their salon locations on a map. It includes frontend forms for submission and admin/moderation capabilities.

## Key Features
- **Map Integration**: Uses Leaflet.js (OpenStreetMap) for location selection (`lat`, `long`).
- **Salon Management**: Form to input details: Name, City, Address, Phones, Working Hours, Social Links.
- **Image Handling**: Uploads and resizes store avatars/photos using PHP GD library.
- **Database**: Stores data in `gi_salons`.
- **User Roles**: Logic to restrict access or auto-fill data based on current user (`admin` vs `dealer`).

## Architecture
- **Main File**: `index.php` handles shortcode registration (`[add_salon_shortcode]`, `[my_salons]`), asset enqueueing, and form processing.
- **Frontend Assets**:
  - `css/salonadd.css`
  - `js/scripts.js`
  - Leaflet libraries (`../osm/add_place/...`).
- **Data Flow**: POST request to self -> Validation -> SQL INSERT/UPDATE.

## Shortcodes
- `[add_salon_shortcode]`: Renders the "Add New Salon" form with Leaflet map.
- `[my_salons]`: (Mentioned but logic might be separate or embedded) List user's salons.

## Dependencies
- **External**: Leaflet.js (local copy in `../osm/`).
- **Internal**: `gi_kitchen` table (for selecting samples/samples available).

## Estimates & Critical Analysis

> [!NOTE]
> **Complexity Level: LOW-MEDIUM**
> Form-based submission with map integration.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **2-3 Days** | Adding validation, improving map UX, auto-geocoding. |
| **Rewrite (Modern Stack)** | **1 Week** | Address autocomplete, image optimization, moderation workflow. |

### Critical Issues (Immediate Attention Needed)
> [!CAUTION]
> **Data Integrity & Security**.

1.  **Image Handling Vulnerabilities**
    - **Risk**: Custom GD resize logic may be vulnerable to memory exhaustion DoS or malicious images.
    - **Remediation**: Offload to `wp_handle_upload` + ImageMagick.
    - **Estimate**: 4-6 Hours.

2.  **No Input Validation**
    - **Risk**: Addresses/Coordinates are trusted blindly. XSS potential in output.
    - **Remediation**: Implement `sanitize_text_field()` and `floatval()` on all inputs.
    - **Estimate**: 1 Day.
