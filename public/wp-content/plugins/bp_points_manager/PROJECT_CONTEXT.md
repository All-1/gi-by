# Component Context: bp_points_manager

## Overview
`bp_points_manager` is a utility for Sales Managers to manage their "Points" (Dealer Locations/Salons). It allows assigning specific sales points to manager users.

## Key Features
- **Manager Dashboard**: `[points_manager]` shortcode renders the interface.
- **Point Assignment**:
  - Lists all users with role `manager`.
  - Checkbox interface to assign/unassign points to managers.
  - Supports "Temporary" assignments (`ManagerIdTemp_gi`).
- **Search**: Client-side filtering of points data.

## Architecture
- **Main File**: `index.php`.
- **Database Tables**:
  - `gi_points`: Stores point data (`PKId_gi`, `PointName_gi`, `ManagerId_gi`).
- **JS**: `js/points_script.js` handles the checkbox logic and saving (AJAX likely).

## Dependencies
- Requires users with `manager` role in WordPress.

## Estimates & Critical Analysis

> [!NOTE]
> **Complexity Level: LOW**
> Simple assignment interface with checkbox-based logic.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **1-2 Days** | Adding confirmation dialogs, improving search. |
| **Rewrite (Modern Stack)** | **3-5 Days** | React-based assignment interface, bulk operations. |

### Key Risks
1. **Role Dependency**: Hard-coded check for `manager` role. Role name changes break the plugin.
2. **Temporary Assignments**: `ManagerIdTemp_gi` logic is undocumented. Unclear cleanup mechanism.
3. **No Audit Log**: No history of who assigned what point to which manager.
