# Component Context: bp_samples

## Overview
`bp_samples` enables Dealers to list "Exposition Samples" (Kitchen displays) for sale. It includes a Moderation workflow where Admins must approve listings.

## Key Features
- **Dealer Workflow**:
  - `[add_sample]`: Form for dealers to submit a sample. Requires a linked Salon (`gi_salons`).
  - `[my_samples_print]`: Dashboard for dealers to view status (On Modernation, Approved, Rejected) and edit/delete samples.
- **Admin Workflow**:
  - Admin Menu "Samples" -> "Moderate".
  - Email notifications sent to `info@gi.by` on submission.
- **Catalog**:
  - `[samples_print]`: Public catalog of samples filtered by City/Country.

## Architecture
- **Main File**: `index.php`.
- **Database Tables**:
  - `gi_samples`: Stores sample info, prices, dimensions, and image paths (serialized strings). Status tracked via `moderate` column ('yes', 'no', 'dorab').
- **Image Handling**: Custom PHP logic to resize and store images in `images/samples/`.

## Dependencies
- Requires `gi_salons` to link a sample to a physical location.
- Uses `wp_mail` for notifications.

## Estimates & Critical Analysis

> [!TIP]
> **Complexity Level: MEDIUM**
> Moderation workflow adds complexity beyond simple CRUD.

### Component Estimates
| Task | Effort | Notes |
| :--- | :--- | :--- |
| **Refactor / Stabilization** | **3-5 Days** | Improving moderation UI, adding notification templates. |
| **Rewrite (Modern Stack)** | **2 Weeks** | Proper workflow engine, admin dashboard, email templates. |

### Key Risks
1. **Serialized Data**: Image paths stored as serialized strings. Hard to query or migrate.
2. **Email Delivery**: Uses `wp_mail` directly. No queue, no retry, no tracking.
3. **Moderation States**: Only 3 states ('yes', 'no', 'dorab'). Need audit trail for compliance.
4. **Salon Dependency**: Requires valid `gi_salons` entry. Orphaned if salon deleted.
