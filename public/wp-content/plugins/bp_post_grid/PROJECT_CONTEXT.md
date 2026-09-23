# Component Context: bp_post_grid (bp_byers_grid)

## Overview
`bp_post_grid` (internally `bp_byers_grid`) manages various News and Information Grids across the site (Buyers, Dealers, Factory News).

## Key Features
- **News Management**:
  - **Factory News**: CRUD for news items visible to dealers (`gi_userpanel_news`). Supports Country filtering (BY, RU, UA, KZ).
  - **Info Grid**: External link management for "Buyers" section (`gi_info_grid`, `gi_news_grid`).
- **Shortcodes**:
  - `[byers_grid rubric='...']`: Displays WP Posts from a specific category.
  - `[news_print]`: Displays Factory News for the logged-in user (filtered by country).
  - `[kd_news]`: filtered news list.

## Architecture
- **Main File**: `index.php` registers multiple Admin Pages.
- **Database Tables**:
  - `gi_userpanel_news`: Factory news items.
  - `gi_info_grid`: Links/Rankings for general info.
  - `gi_news_grid`: Similar to info grid.

## Notes
- "Byers" is likely a typo for "Buyers".
- Heavy use of raw SQL for CRUD operations.

## Estimates & Critical Analysis

> [!NOTE]
> **Complexity Level: LOW**
> Standard news/grid functionality with country filtering.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **1-2 Days** | Improving query efficiency, adding caching. |
| **Rewrite (Modern Stack)** | **3-5 Days** | WP REST API, proper post type integration. |

### Key Risks
1. **Multiple Tables**: 3 separate tables for similar content. Schema normalization needed.
2. **Typo in Name**: "Byers" vs "Buyers" causes confusion.
3. **Raw SQL**: No prepared statements in some queries. SQL injection risk.
