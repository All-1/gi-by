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

define('BP_KNOWLEDGE_TESTS_FILE', __FILE__);

$siteAutoload = dirname(__DIR__, 3) . '/vendor/autoload.php';
if (is_readable($siteAutoload)) {
    require_once $siteAutoload;
}

$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

if (!class_exists(PluginBootstrap::class, false)) {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'BpKnowledgeTests\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $path = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($path)) {
            require_once $path;
        }
    });
}

register_activation_hook(__FILE__, 'bp_knowledge_tests_activate');

add_action('plugins_loaded', 'bp_knowledge_tests_boot', 11);

function bp_knowledge_tests_resolve_container(): ?Container
{
    global $servicesContainer;

    if ($servicesContainer instanceof Container) {
        return $servicesContainer;
    }

    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $frameworkMain = 'wordpress_framework/index.php';
    if (is_plugin_active($frameworkMain)) {
        require_once WP_PLUGIN_DIR . '/' . $frameworkMain;
    }

    return $servicesContainer instanceof Container ? $servicesContainer : null;
}

function bp_knowledge_tests_missing_framework_notice(): void
{
    if (!current_user_can('activate_plugins')) {
        return;
    }

    echo '<div class="notice notice-error"><p>';
    echo esc_html__(
        'bp_knowledge_tests is inactive: activate the wordpress_framework plugin.',
        'bp_knowledge_tests',
    );
    echo '</p></div>';
}

function bp_knowledge_tests_activate(): void
{
    global $wpdb;

    $container = bp_knowledge_tests_resolve_container();
    if ($container === null) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(
            esc_html__(
                'bp_knowledge_tests requires the wordpress_framework plugin to be active.',
                'bp_knowledge_tests',
            ),
        );
    }

    (new PluginBootstrap($wpdb, $container))->activate();
}

function bp_knowledge_tests_boot(): void
{
    global $wpdb;

    $container = bp_knowledge_tests_resolve_container();
    if ($container === null) {
        add_action('admin_notices', 'bp_knowledge_tests_missing_framework_notice');

        return;
    }

    (new PluginBootstrap($wpdb, $container))->run();
}
