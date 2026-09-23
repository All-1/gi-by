# Component Context: bp_contracts/Dialog

## Overview
The **Dialog Subsystem** implements the internal messenger. It is not just simple chat; it's a context-aware communication tool allowing dealers to discuss specific Orders or Invoices with the Factory.

> **Deep Dive**: See [Dialog Traits](./traits/PROJECT_CONTEXT.md) for shared Chat Logic (WorkWithMessages) and Polymorphism helpers.

## Structure

### Dialog Types (Polymorphism)
1.  **`DialogConsultation`**:
    - **Context**: General/Pre-sales questions.
    - **Participants**: Dealer + Sales Assistant.
2.  **`DialogOrder`**:
    - **Context**: Tied to a specific `Order` ID.
    - **Usage**: Technical clarifications for a specific product.
    - **Participants**: Dealer + Technologist + Manager.
3.  **`DialogComplaint`**:
    - **Context**: Warranty/Quality issues.
    - **Priority**: High visibility to Quality Control staff.
4.  **`DialogInvoice`**:
    - **Context**: Discussions regarding payment/documents.
    - **Participants**: Dealer + Bookkeeper.

## Message Entity: `Message.php`
- **Attributes**: `id`, `text`, `author_id`, `timestamp`, `is_read`.
- **Validation**: Sanitizes HTML input (strips dangerous tags, allows formatting).

## Notification Logic
- **Unread Counters**: The system calculates `unreadCount` *per user*.
- **Email Fallback**: If a user is offline (`idWebsocket` is null), high-priority messages trigger an email notification via `SenderEmailNotification`.
