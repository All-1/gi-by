<?php
require_once 'vendor/autoload.php';
require_once 'wp-load.php';

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Core\Container;
use PersonalAccount\Workers\CatcherBugs;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\AnalyticsCollector;

$container = new Container();

$catcherBugs = new CatcherBugs();
$simpleUtilities = new SimpleUtilities($container);
$container->set('SimpleUtilities', $simpleUtilities);
$dbUtilities = new DBUtilities($container);
$container->set('DBUtilities', $dbUtilities);
$dbWorker = new DBWorker($container);
$container->set('DBWorker', $dbWorker);
$analyticsCollector = new AnalyticsCollector($container);
$container->set('AnalyticsCollector', $analyticsCollector);

$analyticsCollector->createTableAnalytics();
$points = $dbWorker->selectSimple(table: 'gi_new_points', what: 'id_point');

print_r($points);