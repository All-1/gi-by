# Component Context: bp_contracts/Core

## Overview
Bootstrap and **dependency injection** when the WebSocket server starts (`SystemConstructor` from `public/websocket_server.php`).

## `Container.php`
Simple service locator: `set($name, $service)`, `get($name)`.

## `SystemConstructor.php`
1. Creates `Container`.
2. Instantiates workers, utilities, controllers, analytics helpers.
3. Passes container **by reference** into each constructor.
4. Registers each instance under a string key.

### Registered services (keys)
| Key | Class |
|:----|:------|
| `Chat` | `PersonalAccount\Chat` |
| `CatcherBugs` | `Workers\CatcherBugs` |
| `SSHRemote` | `Workers\SSHRemote` |
| `Mailer` | `Workers\Mailer` |
| `SimpleUtilities` | `Utilities\SimpleUtilities` |
| `DBUtilities` | `Utilities\DBUtilities` |
| `DBWorker` | `Workers\DBWorker` |
| `DataUtilities` | `Utilities\DataUtilities` |
| `UPWorker` | `Workers\UPWorker` |
| `ORWorker` | `Workers\ORWorker` |
| `UserUtilities` | `Utilities\UserUtilities` |
| `Factory` | `Factory\Factory` |
| `DialogServices` | `Utilities\DialogServices` |
| `UserControler` | `Controler\UserControler` |
| `ControlerObjectsRelationship` | `Controler\ControlerObjectsRelationship` |
| `NotificationWorker` | `Workers\NotificationWorker` |
| `InvoiceWorker` | `Workers\InvoiceWorker` |
| `InteractionInterface` | `Controler\InteractionInterface` |
| `MessageWorker` | `Workers\MessageWorker` |
| `ContractsWorker` | `Workers\ContractsWorker` |
| `OrdersWorker` | `Workers\OrdersWorker` |
| `ManageContractorsWorker` | `Workers\ManageContractorsWorker` |
| `MessageCollector` | `Workers\Collector\MessageCollector` |
| `DialogMetrics` | `Workers\Analytics\DialogMetrics` |
| `ManagerMetrics` | `Workers\Analytics\ManagerMetrics` |
| `ContractorsMetrics` | `Workers\Analytics\ContractorsMetrics` |
| `MessageMetrics` | `Workers\Analytics\MessageMetrics` |
| `TurnAnalyticsQueries` | `Workers\Analytics\TurnAnalyticsQueries` |
| `DialogAnalyticsQueries` | `Workers\Analytics\DialogAnalyticsQueries` |
| `OrderAnalyticsQueries` | `Workers\Analytics\OrderAnalyticsQueries` |
| `AnalyticsCollector` | `Workers\Collector\AnalyticsCollector` |
| `UserCollector` | `Workers\Collector\UserCollector` |

### Not in the container
- **`AnalyticsWorker`**: constructed inside `Users\Admin` for `ManageAnalytics` (uses container only for query services if resolved manually in constructor).

`SystemConstructor` is the main **coupling hub** (imports most of the plugin).

## Related
- [Workers](../Workers/PROJECT_CONTEXT.md)
- [Chat / WS gateway](../PROJECT_CONTEXT.md)
