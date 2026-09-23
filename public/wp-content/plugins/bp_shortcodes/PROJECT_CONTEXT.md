# Component Context: bp_shortcodes

## Overview
`bp_shortcodes` is a utility collection of diverse frontend components, mostly for specific Landing Pages (Designers, Dealers) and Contact pages.

## Key Features
- **Maps & Contacts**:
  - `[contacts_map]`: Leaflet map for the Contact page.
  - `[print_contacts]`, `[contacts_btns]`: Layouts for contact info.
- **Designer/Dealer Landings**:
  - `[designer_register_form]`: Registration form sending data to a PHP endpoint.
  - `[designer_utp_block]`, `[dealers_utp_icons]`: Content blocks with selling points (UTP).
  - `[materials_block]`: Interactive comparison chart (Chart.js) for materials (LDSP, Acrylic, Wood, etc.).
- **Modals**:
  - `[cities_window]`: City selection popup.

## Architecture
- **Main File**: `index.php`.
- **JS Libraries**: Includes `Chart.js` for the materials chart.
- **Forms**: Some forms submit to internal endpoints like `forms/send_designer_registration.php`.

## Notes
- Acts as a "Theme Functions" plugin, keeping shortcode logic out of `functions.php`.

## Estimates & Critical Analysis

> [!NOTE]
> **Complexity Level: LOW**
> Utility collection with minimal interdependencies.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **1-2 Days** | Adding form validation, modernizing Chart.js. |
| **Rewrite (Modern Stack)** | **3-5 Days** | Block components, modern charting library. |

### Key Risks
1. **Chart.js Version**: May be outdated. Security/compatibility issues.
2. **Form Endpoints**: `send_designer_registration.php` - unknown validation/security.
3. **Leaflet Dependency**: Maps shortcodes depend on externally bundled Leaflet.
4. **Scope Creep**: "Utility" plugins tend to grow. Needs clear boundaries.
