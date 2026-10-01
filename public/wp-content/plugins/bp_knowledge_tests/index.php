<?php
/**
 * Plugin Name: bp_knowledge_tests
 * Description: Dealer knowledge tests (domain logic; UI via bp_contracts).
 * Version: 0.1.0
 * Author: Business Park
 */

declare(strict_types=1);

use BpKnowledgeTests\Bootstrap\PluginBootstrap;

if (!defined('ABSPATH')) {
    exit;
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

global $wpdb;

$bootstrap = new PluginBootstrap($wpdb);
$plugin = $bootstrap->compose();

register_activation_hook(__FILE__, [$bootstrap, 'activate']);

add_action('plugins_loaded', [$plugin, 'boot']);
