# Component Context: bp_contracts/sender

## Overview
Legacy **HTTP email helpers** separate from `Workers/Mailer.php` (used inside the WebSocket stack). These scripts accept POST and call PHP `mail()`.

## Files

### `senderEmailNotification.php`
- **Input**: POST `email`, `message`, `subject`.
- **From header**: `gi@gi.by`.
- **Use**: Client or cron posts notification content when not using `Mailer` + `NotificationWorker`.

### `newSender.php`
- Bootstraps `wp-load.php` + plugin `Mailer` / `CatcherBugs` (standalone HTTP entry, not WS).
- Used when notification flow must send mail outside the Ratchet process.

## Security note
> [!WARNING]
> No authentication is visible in `senderEmailNotification.php`. Any caller who can POST to this URL can send mail through the server. Prefer `Mailer` + WP hooks for new work, or protect this endpoint.

## Related
- [Dialog notification flow](../src/Dialog/PROJECT_CONTEXT.md)
- [Workers/Mailer](../src/Workers/PROJECT_CONTEXT.md)
