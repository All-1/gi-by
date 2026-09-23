<?php
/*
Plugin Name: wordpress_framework
Description: Плагин для работы с WordPress с помощью custom objects based on WP MVC
Version: 1.1
file: index.php
Author: Abbasov Ruslan
*/

use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\SimpleUtilities;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\DataUtilities;
use PersonalAccount\Utilities\UserUtilities;
use WPFramework\Workers\KitchenWorker;

require_once __DIR__ . '/../../../vendor/autoload.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . 'vendor/autoload.php';
// require_once $SERVER['DOCUMENT_ROOT'] . '/wp-load.php';

global $servicesContainer;
$servicesContainer = new Container();
$linkContainer = &$servicesContainer;

$simpleUtilities = new SimpleUtilities($linkContainer);
$servicesContainer->set('SimpleUtilities', $simpleUtilities);

$dbUtilities = new DBUtilities($linkContainer);
$servicesContainer->set('DBUtilities', $dbUtilities);

$dbWorker = new DBWorker($linkContainer);
$servicesContainer->set('DBWorker', $dbWorker);

$dataUtilities = new DataUtilities($linkContainer);
$servicesContainer->set('DataUtilities', $dataUtilities);

$userUtilities = new UserUtilities($linkContainer);
$servicesContainer->set('UserUtilities', $userUtilities);

$kitchenWorker = new KitchenWorker($linkContainer);
$servicesContainer->set('KitchenWorker', $kitchenWorker);