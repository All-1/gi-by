# Project Context: bp_kitchen_print

## Overview
This plugin handles the frontend display of the Kitchen catalog and individual Kitchen pages. It includes AJAX filtering logic and dynamic content loading.

## Architecture
- **Entry Point**: `index.php` (Shortcodes: `[catalogue_print]`, `[kitchen_print]`)
- **AJAX Handlers**: 
    - `ajax_catalogue.php`: Handles main catalog filtering.
    - `ajax_kitchen.php`: Handles single product dynamic content (facades, handles, etc.).
- **JavaScript**: `js/script.js` handles UI interactions and AJAX requests.
- **Dependencies**: `wordpress_framework` (specifically `KitchenWorker`).

## Recent Changes
- **Refactoring**: The `print_kitchen` function was refactored to use `KitchenWorker` for data retrieval.
- **Shared Assets**: `js/script.js` and `ajax_kitchen.php` are now used by `bp_wardrobe_print` and `bp_related_products` to ensure consistent behavior.
- **Script Updates**: `js/script.js` was updated to support snake_case property names (`image_href`, `thumb_href`, `model_name`) to be compatible with `gi_handle` and `gi_wardrobe_fittings` data structures.

## Key Components
### `ajax_kitchen.php`
Accepts `value`, `where`, `nameKitchen`, and optional `type` parameters. Delegates logic to `KitchenWorker::switchWhereLookFor`.

### `js/script.js`
Handles:
- Main button clicks (Facades, Handles, Fittings, etc.)
- Inner button clicks (Material types)
- AJAX requests to `ajax_kitchen.php`
- Rendering of grid items (facades, handles, etc.) with support for multiple data formats (camelCase and snake_case).

## Database Interaction
- Relies on `KitchenWorker` to query `gi_kitchen`, `gi_facades_list`, `gi_handle`, etc.

## Detailed Logic
- **Facades**: Fetched via `getFacadesMain` and `getFacadesList`.
- **Handles**: Fetched via `getHandlesMain`.
- **Tabletops**: Fetched via `getTabletopsMain`.
- **Fittings**: Fetched via `getFittingsMain`.
