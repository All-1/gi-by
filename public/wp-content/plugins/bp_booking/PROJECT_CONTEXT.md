# Component Context: bp_booking

## Overview
`bp_booking` provides a strict reservation/ordering system ("Бронирование") for dealers. It assigns sequential order numbers to requests.

## Key Features
- **Order Numbering**: Auto-increments `order_number` based on `MAX(gi_booking.order_number)`.
- **Table View**: Lists current user's bookings with status and date.
- **Integration**:
  - Uses `gi_booking` table.
  - Updates `usermeta` (`last_login`) to manage session/data freshness.
- **Restricted Access**: Displays "Technical Works" message for non-designer/architect roles (configurable).

## Architecture
- **Main File**: `index.php`.
- **AJAX Handlers**:
  - `dannie.php`: Handles new booking creation (POST).
  - `ajaxtable.php`: Handles pagination and filtering of the booking list.
- **Shortcodes**: `[booking]`.

## Data Flow
1. User clicks "Забронировать".
2. JS captures `model_name`.
3. AJAX POST to `dannie.php`.
4. Backend inserts new row into `gi_booking`.
5. Page reloads/updates table.

## Estimates & Critical Analysis

> [!NOTE]
> **Complexity Level: LOW-MEDIUM**
> Simple reservation system with sequential numbering.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **2-3 Days** | Adding validation, improving table UI, status tracking. |
| **Rewrite (Modern Stack)** | **1 Week** | REST API, calendar view, conflict detection. |

### Key Risks
1. **Order Number Race Condition**: `MAX(order_number)` approach may cause duplicates under concurrent load.
2. **Role Check**: Hard-coded role checks for `designer_architect`. Inflexible.
3. **Session Handling**: Updates `usermeta` for session tracking. Unconventional approach.
4. **No Expiry**: Bookings don't expire. May hold inventory indefinitely.
