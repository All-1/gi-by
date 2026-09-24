# Component Context: bp_contracts/Workers/Analytics

## Overview
Two complementary layers:

1. **Materialization** — batch jobs write summary rows into `gi_new_metrics_*` tables (`DialogMetrics`, `ManagerMetrics`, `ContractorsMetrics`, `MessageMetrics`), orchestrated by [Collector/AnalyticsCollector](../Collector/PROJECT_CONTEXT.md).
2. **Interactive dashboard** — `AnalyticsWorker` (used by `ManageAnalytics` on `Admin`) runs SQL via query classes for charts, percentiles, SLA %, and order volume. The UI is `[analytics]` shortcode + `js/analytics.js`.

`AnalyticsWorker` is **not** registered in `SystemConstructor`; `Admin` constructs it directly. Query helpers **are** in the container.

## Tables

| Table | Writer | Reader |
|:------|:-------|:-------|
| `gi_new_metrics_messages` | `MessageCollector` | `AnalyticsCollector` |
| `gi_new_metrics_dialogues` | `DialogMetrics` | `DialogAnalyticsQueries` |
| `gi_new_metrics_managers` | `ManagerMetrics` | (aggregated in collectors / legacy paths) |
| `gi_new_metrics_contractors` | `ContractorsMetrics` | (aggregated in collectors / legacy paths) |
| `gi_new_metrics_dialog_turns` | `MessageMetrics` | `TurnAnalyticsQueries` |

### Message shards (source, not metrics)
Historical messages live in per-point, per-year tables: `x_gi_new_messages_contract_{year}_{point}`. `MessageCollector::mergeMessages()` merges them into `gi_new_metrics_messages`.

## Materialization classes

### `AnalyticsBase.php`
Shared math: work-hours vs calendar time, response/waiting transitions, message counts before proforma/confirmation, batch insert helpers.

### `DialogMetrics.php`
Schema and calculator for `gi_new_metrics_dialogues` (durations, message counts, proforma/confirmation timing, `calculated_at`).

### `ManagerMetrics.php`
Per dialogue × factory user: `gi_new_metrics_managers`.

### `ContractorsMetrics.php`
Per dialogue × contractor/dealer side: `gi_new_metrics_contractors`.

### `MessageMetrics.php`
Per **turn** between messages: `gi_new_metrics_dialog_turns` (`started_at`, `ended_at`, `time_from_previous_turn`, `time_before_next_turn`, work-hour variants, `is_first_response`, message id range). Used heavily for SLA and percentile charts.

## Query layer (dashboard)

### `AnalyticsQueriesBase.php`
Period expressions, user filters, normalization of SQL result sets.

### `TurnAnalyticsQueries.php`
Reads `gi_new_metrics_dialog_turns`. `queryTimeResponseStats()` — p50/p90/p95 via `CUME_DIST()`. `querySlaStats()` — share of turns under SLA threshold. `queryTurnCounts()` — volume by period. `durationColumn()` respects `timeMode` (`work_hours` vs `standard`).

### `DialogAnalyticsQueries.php`
Joins `gi_new_dialogues` + `gi_new_metrics_dialogues`. Opened/active dialog stats, duration/message percentiles, dialogue-type breakdown. `queryDealerDependentsCounts()` powers dealer drill-down links in the UI.

### `OrderAnalyticsQueries.php`
`queryOrderStats()` on `gi_new_orders` by `point_id` and period (`receiption_date`). Excludes orders whose names match `%б1%`…`%б9%` (internal “B-order” pattern).

### `AnalyticsWorker.php`
Facade composed in `Admin`:

| Method | `whose` / mode | Returns (high level) |
|:-------|:---------------|:---------------------|
| `getAnalyticsAll` | `all` | Factory turn percentiles + `dataDialogs` |
| `getAnalyticsFactory` | `factory` | Turn metrics + dialog stats |
| `getAnalyticsContractor` | `contractor`, `dealer`, `dealer_dependents` | Manager/contractor response percentiles, SLA series, dialogs, turns, orders (contractor view) |
| `getDealerDependentsCounts` | `dealer` list | Map dealer user id → dependent contractor count |

Params from `ManageAnalytics`: `dateFrom`, `dateTo`, `period`, `dialoguesType`, `idUser` / `filterUserId`, `timeMode`, `slaFirstResponseSeconds`, `slaSubsequentResponseSeconds`.

## Role semantics (metrics calculation)
**Client side** (waiting on factory): `dealer`, `free_dealer`, `distributor`, `designer_dealer`.  
**Factory side**: `manager`, `consultant`, `complaint_handler`, `specialist`, `sales_manager`.

## Nightly vs full rebuild
`AnalyticsCollector::getAnalytics()` / `writeAnalytics()` — full recalculation paths.  
`getAnalyticsNightly()` / `writeAnalyticsNightly()` — incremental by `date_last_activity`; replaces rows for the same dialogue/user keys before insert.

## WebSocket surface
Documented in [src/PROJECT_CONTEXT.md](../../PROJECT_CONTEXT.md) (analytics commands). Server payloads: `AnalyticsOnPage`, `AnalyticsDealerDependentsOnPage`, `analyticsUserSearch`.

## Related
- [Collector](../Collector/PROJECT_CONTEXT.md) — `AnalyticsCollector`, `MessageCollector`, `UserCollector`
- [Users/traits/ManageAnalytics](../../Users/traits/PROJECT_CONTEXT.md)
- [js/analytics.js](../../../js/PROJECT_CONTEXT.md)
