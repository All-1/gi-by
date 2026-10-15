<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Admin;

use BpKnowledgeTests\Infrastructure\Security\CatalogCapability;
use BpKnowledgeTests\Plugin;

final class AdminAppAssets
{
    private const SCRIPT_HANDLE = 'bp-knowledge-tests-admin';

    public static function enqueue(Plugin $plugin): void
    {
        $path = self::bundlePath();
        if (!CatalogCapability::canManageCatalog() || !is_readable($path)) {
            return;
        }

        $bundle = file_get_contents($path);
        $config = wp_json_encode(AdminAppConfig::forCurrentUser($plugin));
        if ($bundle === false || $bundle === '' || $config === false) {
            return;
        }

        wp_register_script(self::SCRIPT_HANDLE, false, [], (string) filemtime($path), true);
        wp_enqueue_script(self::SCRIPT_HANDLE);
        wp_add_inline_script(
            self::SCRIPT_HANDLE,
            'window.bpKnowledgeTestsAdmin=' . $config . ';' . str_replace('</script', '<\/script', $bundle),
        );
    }

    private static function bundlePath(): string
    {
        return plugin_dir_path(BP_KNOWLEDGE_TESTS_FILE) . 'assets/admin/app.js';
    }
}
