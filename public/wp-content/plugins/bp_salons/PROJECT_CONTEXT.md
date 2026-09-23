# Component Context: bp_salons

## Overview
`bp_salons` manages the network of Dealer and Factory salons. It provides the data and UI for the "Where to buy" functionality.

## Key Features
- **Salon Management**:
  - Stores location data (City, Address, Coordinates `lat_lng`).
  - Stores contact info (Phones, Worktime).
  - Flags for "Firm" (Factory owned) and "Monobrand" salons (renders medals/icons).
  - Associates a Salon with a WordPress User (`user` column) - this links to `bp_contracts` and `bp_samples` permissions.
- **Frontend**:
  - `[salons_print]`: Lists salons, filters by city, renders Leaflet Map.
  - `[salon_card]`: Single salon view.
  - `[rec_salons_designers]`: Recommended salons list.
- **Map**: Uses Leaflet.js with OpenStreetMap tiles.

## Architecture
- **Main File**: `index.php`.
- **Database Tables**:
  - `gi_salons`: The main registry of salons.
- **AJAX**: `ajax.php` (inside plugin dir?) or inline JS logic for loading salon details on map click.

## Dependencies
- `bp_contracts` likely depends on this to link Orders to Salons.
- `bp_samples` links samples to specific Salons.

## Estimates & Critical Analysis

> [!TIP]
> **Complexity Level: MEDIUM**
> Geographic data and map integration add complexity.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **3-5 Days** | Improving map performance, adding geolocation, caching salon data. |
| **Rewrite (Modern Stack)** | **2 Weeks** | Proper geospatial queries, clustering for large datasets, mobile optimization. |

### Key Risks
1. **Map Library**: Leaflet.js bundled locally. May be outdated, security patches missing.
2. **Coordinate Data**: `lat_lng` stored as string. No validation, no spatial indexing.
3. **User Linking**: Salon-to-User association affects `bp_contracts` permissions. Critical data.
4. **Performance**: Loading all salons on map may be slow with large datasets.
