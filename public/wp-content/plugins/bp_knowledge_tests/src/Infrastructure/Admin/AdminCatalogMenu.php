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
            __('Тесты знаний', 'bp_knowledge_tests'),
            __('Тесты знаний', 'bp_knowledge_tests'),
            $cap,
            self::MENU_SLUG,
            fn () => $this->renderMount('list'),
            'dashicons-welcome-learn-more',
            58,
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Все тесты', 'bp_knowledge_tests'),
            __('Все тесты', 'bp_knowledge_tests'),
            $cap,
            self::MENU_SLUG,
            fn () => $this->renderMount('list'),
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Добавить тест', 'bp_knowledge_tests'),
            __('Добавить тест', 'bp_knowledge_tests'),
            $cap,
            self::SLUG_ADD,
            fn () => $this->renderMount($this->addScreenView()),
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Настройки', 'bp_knowledge_tests'),
            __('Настройки', 'bp_knowledge_tests'),
            $cap,
            self::SLUG_SETTINGS,
            fn () => $this->renderMount('settings'),
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Результаты', 'bp_knowledge_tests'),
            __('Результаты', 'bp_knowledge_tests'),
            $cap,
            self::SLUG_RESULTS,
            fn () => $this->renderMount('results'),
        );
        add_submenu_page(
            self::MENU_SLUG,
            __('Аналитика', 'bp_knowledge_tests'),
            __('Аналитика', 'bp_knowledge_tests'),
            $cap,
            self::SLUG_ANALYTICS,
            fn () => $this->renderMount('analytics'),
        );
    }

    private function renderMount(string $initialView): void
    {
        if (!CatalogCapability::canManageCatalog()) {
            wp_die(esc_html__('Недостаточно прав для управления тестами знаний.', 'bp_knowledge_tests'));
        }

        $bundle = plugin_dir_path(BP_KNOWLEDGE_TESTS_FILE) . 'assets/admin/app.js';
        echo '<div class="wrap">';
        if (!is_readable($bundle)) {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Не найден файл сборки админки. В папке admin плагина выполните npm run build.', 'bp_knowledge_tests');
            echo '</p></div></div>';

            return;
        }

        printf(
            '<div id="bp-knowledge-tests-admin-root" data-initial-view="%s"></div>',
            esc_attr($initialView),
        );
        echo '</div>';
    }

    private function addScreenView(): string
    {
        $testId = isset($_GET['test_id']) ? (int) $_GET['test_id'] : 0;

        return $testId > 0 ? 'edit-' . $testId : 'edit-new';
    }

}
