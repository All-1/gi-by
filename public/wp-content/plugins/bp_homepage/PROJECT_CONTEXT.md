# Component Context: bp_homepage

## Overview
`bp_homepage` (internal name: `bp_templates_redactor`) provides a CMS-like interface for managing content on specific dynamic pages, primarily the Home Page and Contacts page.

## Key Features
- **Home Page Editor**:
  - Manage "Classic" vs "Modern" style blocks.
  - "Materials" section (headers, images, links).
  - "Cities" grid.
  - "UTP" (Unique Selling Proposition) blocks.
  - **Slider**: Dedicated tab to manage the main hero slider (`gi_homeslider`).
- **Contacts Editor**:
  - Departments (`gi_departments`): Manage office/factory locations with coordinates for maps and images.
  - Employees (`gi_contacts`): Manage staff contact info organized by department/subsection.

## Architecture
- **Main File**: `index.php` registers the Admin Menus ("Главная страница", "Контакты").
- **Database Tables**:
  - `gi_homepage`: Singleton-like row storing homepage text/image URLs.
  - `gi_homeslider`: Slides for the main banner.
  - `gi_contacts`: Employee list.
  - `gi_departments`: Physical locations (Factory, Distributors).

## Data Flow
- Admin updates forms -> Direct SQL updates/inserts.
- Frontend likely consumes these tables via direct SQL queries in the theme template (not strictly via shortcodes in this plugin, though it prepares the data).

## Estimates & Critical Analysis

> [!TIP]
> **Complexity Level: MEDIUM**
> CMS-like functionality with multiple content sections.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **3-5 Days** | Adding preview, revision history, better UI. |
| **Rewrite (Modern Stack)** | **1-2 Weeks** | Block editor integration, structured content API. |

### Key Risks
1. **Singleton Data**: `gi_homepage` uses single-row approach. Schema locked.
2. **Direct Theme Coupling**: Theme directly queries these tables. No abstraction layer.
3. **No Versioning**: Changes overwrite immediately. No undo/history.
4. **Mixed Content Types**: Slider, contacts, departments in one plugin. Scope creep.
