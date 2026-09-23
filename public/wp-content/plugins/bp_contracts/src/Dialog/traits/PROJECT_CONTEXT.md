# Component Context: bp_contracts/Dialog/traits

## Overview
Reusing chat logic across different Dialog Types.

## Traits

### 1. `ConsultationOrComplaint.php`
- **Role**: Shared logic for "Issue" based dialogs.
- **Used By**: `DialogConsultation`, `DialogComplaint`.
- **Logic**: Handles status updates (Open/Closed) and assigning a "Responsible Person".

### 2. `WorkWithMessages.php`
- **Role**: Core Messaging capabilities.
- **Used By**: All Dialog classes.
- **Logic**:
    - `addMessage()`: Appends a Message object to the dialog.
    - `getMessages()`: Retrieves message history.
    - `unreadCount()`: Calculates unread messages for a specific user.
