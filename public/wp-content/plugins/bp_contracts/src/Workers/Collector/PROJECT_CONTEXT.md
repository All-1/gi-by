# Component Context: bp_contracts/Workers/Collector

## Overview
Focused **data assembly** classes used by analytics batch jobs and by user traits. Registered in `SystemConstructor` and exposed on `UserMain` as `$this->userCollector` (and container keys for collectors).

## `MessageCollector.php`
**Role**: Build the unified analytics message stream.

- Scans shard tables `x_gi_new_messages_contract_{year}_{point}`.
- `mergeMessages()` rebuilds `gi_new_metrics_messages` (dedupe by source ids, bulk insert).
- Feeds [AnalyticsCollector](#analyticscollectorphp) message timelines per `id_dialog`.

## `AnalyticsCollector.php`
**Role**: Orchestrate **offline** metrics materialization.

1. Load dialogues (`getDialog` / `getDialogNightly`).
2. Load messages from `gi_new_metrics_messages` per dialogue.
3. Build shared context (order dates, participants).
4. Delegate to `DialogMetrics`, `ManagerMetrics`, `ContractorsMetrics`, `MessageMetrics`.
5. `writeAnalytics()` / `writeAnalyticsNightly()` persist to:
   - `gi_new_metrics_dialogues`
   - `gi_new_metrics_managers`
   - `gi_new_metrics_contractors`
   - `gi_new_metrics_dialog_turns`

Does **not** power the live dashboard directly; the dashboard uses [Analytics/AnalyticsWorker](../Analytics/PROJECT_CONTEXT.md) query classes on the same tables.

## `UserCollector.php`
**Role**: Searchable user directories for analytics filters and admin pickers.

| Method | Audience | Payload |
|:-------|:---------|:--------|
| `getFactoryUsers()` | Factory roles | `search` string + translated role + display name |
| `getContractorUsers()` | Dealers, free dealers, designers, distributors | Firm/dealer/point metadata + `search` |
| `getDealerUsers()` | Dealer user accounts | Dealer name, firm, points — used when `whose = dealer` |

Used by `ManageAnalytics::ensureAnalyticsUsersCollected()` and `getAnalyticsUserSearch()`.

## Container keys
`MessageCollector`, `AnalyticsCollector`, `UserCollector` — see [Core/PROJECT_CONTEXT.md](../../Core/PROJECT_CONTEXT.md).
