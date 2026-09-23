<?php
require_once '../public/vendor/autoload.php';
require_once '../public/wp-load.php';

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Core\Container;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\Collector\MessageCollector;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Workers\Analytics\DialogMetrics;
use PersonalAccount\Workers\Analytics\ContractorsMetrics;
use PersonalAccount\Workers\Analytics\ManagerMetrics;
use PersonalAccount\Utilities\UserUtilities;
use PersonalAccount\Workers\Collector\AnalyticsCollector;
use PersonalAccount\Workers\Analytics\MessageMetrics;

$container = new Container();

$catcherBugs = new CatcherBugs();
$container->set('CatcherBugs', $catcherBugs);
$simpleUtilities = new SimpleUtilities($container);
$container->set('SimpleUtilities', $simpleUtilities);
$dbUtilities = new DBUtilities($container);
$container->set('DBUtilities', $dbUtilities);
$dbWorker = new DBWorker($container);
$container->set('DBWorker', $dbWorker);
$dataUtilities = new DataUtilities($container);
$container->set('DataUtilities', $dataUtilities);
$userUtilities = new UserUtilities($container);
$container->set('UserUtilities', $userUtilities);
$messageCollector = new MessageCollector($container);
$container->set('MessageCollector', $messageCollector);
$dialogMetrics = new DialogMetrics($container);
$container->set('DialogMetrics', $dialogMetrics);
$contractorsMetrics = new ContractorsMetrics($container);
$container->set('ContractorsMetrics', $contractorsMetrics);
$managerMetrics = new ManagerMetrics($container);
$container->set('ManagerMetrics', $managerMetrics);
$messageMetrics = new MessageMetrics($container);
$container->set('MessageMetrics', $messageMetrics);
$analyticsCollector = new AnalyticsCollector($container);
$container->set('AnalyticsCollector', $analyticsCollector);

$messageCollector->mergeMessages();
$dialogMetrics->createTableAnalytics();
$contractorsMetrics->createTableAnalytics();
$managerMetrics->createTableAnalytics();
$messageMetrics->createTableAnalytics();
$analytics = $analyticsCollector->getAnalytics(100000);
$analyticsCollector->writeAnalytics($analytics);

print_r("Analytics calculated !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! \n");
// $dialogMetrics->mergeDialogsAnalytics();