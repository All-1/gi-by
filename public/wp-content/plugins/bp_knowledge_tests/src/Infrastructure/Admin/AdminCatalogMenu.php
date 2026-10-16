<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Admin;

use BpKnowledgeTests\Infrastructure\Security\CatalogCapability;
use BpKnowledgeTests\Plugin;

final class AdminCatalogMenu
{
    public const MENU_SLUG = 'bp-knowledge-tests';

    public const SLUG_ADD = 'bp-knowledge-tests-add';

    public const SLUG_SETTINGS = 'bp-knowledge-tests-settings';

    public const SLUG_RESULTS = 'bp-knowledge-tests-results';

    public const SLUG_ANALYTICS = 'bp-knowledge-tests-analytics';

    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addMenus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueBundle']);
    }

    public function enqueueBundle(): void
    {
        $page = isset($_GET['page']) ? (string) $_GET['page'] : '';
        $pages = [
            self::MENU_SLUG,
            self::SLUG_ADD,
            self::SLUG_SETTINGS,
            self::SLUG_RESULTS,
            self::SLUG_ANALYTICS,
        ];
        if (!in_array($page, $pages, true)) {
            return;
        }

        AdminAppAssets::enqueue($this->plugin);
    }

    public function addMenus(): void
    {
        if (!CatalogCapability::canManageCatalog()) {
            return;
        }

        $cap = CatalogCapability::CAP_MANAGE_CATALOG;
        add_menu_page(
            __('Knowledge Tests', 'bp_knowledge_tests'),
            __('Knowledge Tests', 'bp_knowledge_tests'),
            $cap,
            self::MENU_SLUG,
            fn () => $this->renderMount('list'),
            'dashicons-welcome-learn-more',
            58,
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('All Tests', 'bp_knowledge_tests'),
            __('All Tests', 'bp_knowledge_tests'),
            $cap,
            self::MENU_SLUG,
            fn () => $this->renderMount('list'),
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Add Test', 'bp_knowledge_tests'),
            __('Add Test', 'bp_knowledge_tests'),
            $cap,
            self::SLUG_ADD,
            fn () => $this->renderMount($this->addScreenView()),
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Settings', 'bp_knowledge_tests'),
            __('Settings', 'bp_knowledge_tests'),
            $cap,
            self::SLUG_SETTINGS,
            fn () => $this->renderMount('settings'),
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Results', 'bp_knowledge_tests'),
            __('Results', 'bp_knowledge_tests'),
            $cap,
            self::SLUG_RESULTS,
            fn () => $this->renderMount('results'),
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Analytics', 'bp_knowledge_tests'),
            __('Analytics', 'bp_knowledge_tests'),
            $cap,
            self::SLUG_ANALYTICS,
            fn () => $this->renderMount('analytics'),
        );
    }

    private function renderMount(string $initialView): void
    {
        if (!CatalogCapability::canManageCatalog()) {
            wp_die(esc_html__('You do not have permission to manage knowledge tests.', 'bp_knowledge_tests'));
        }

        $bundle = plugin_dir_path(BP_KNOWLEDGE_TESTS_FILE) . 'assets/admin/app.js';
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Knowledge Tests', 'bp_knowledge_tests') . '</h1>';
        if (!is_readable($bundle)) {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Admin bundle is missing. From the plugin admin folder run npm install and npm run build.', 'bp_knowledge_tests');
            echo '</p></div></div>';

            return;
        }

        printf(
            '<div id="bp-knowledge-tests-admin-root" data-initial-view="%s"><p id="bp-knowledge-tests-admin-fallback">%s</p></div>',
            esc_attr($initialView),
            esc_html__('Loading test factory…', 'bp_knowledge_tests'),
        );
        echo '</div>';
    }

    private function addScreenView(): string
    {
        $testId = isset($_GET['test_id']) ? (int) $_GET['test_id'] : 0;

        return $testId > 0 ? 'edit-' . $testId : 'edit-new';
    }

}
