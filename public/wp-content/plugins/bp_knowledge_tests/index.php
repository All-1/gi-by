<?php
/**
 * Plugin Name: bp_knowledge_tests
 * Description: Dealer knowledge tests (domain logic; UI via bp_contracts).
 * Version: 0.1.0
 * Author: Business Park
 * Requires Plugins: wordpress_framework
 */

declare(strict_types=1);

use BpKnowledgeTests\Bootstrap\PluginBootstrap;
use PersonalAccount\Core\Container;

if (!defined('ABSPATH')) {
    exit;
}

$siteAutoload = dirname(__DIR__, 3) . '/vendor/autoload.php';
if (is_readable($siteAutoload)) {
    require_once $siteAutoload;
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

global $wpdb;
global $servicesContainer;

$bootstrap = new PluginBootstrap($wpdb, $servicesContainer);

register_activation_hook(__FILE__, [$bootstrap, 'activate']);

$bootstrap->run();
