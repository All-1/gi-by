<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Admin;

use BpKnowledgeTests\Infrastructure\Http\AdminRestApi;
use BpKnowledgeTests\Plugin;

final class AdminAppConfig
{
    /**
     * @return array{
     *   restUrl: string,
     *   restNonce: string,
     *   pluginVersion: string,
     *   adminUrls: array{list: string, addTest: string, settings: string}
     * }
     */
    public static function forCurrentUser(Plugin $plugin): array
    {
        return [
            'restUrl' => rest_url(AdminRestApi::NAMESPACE),
            'restNonce' => wp_create_nonce('wp_rest'),
            'pluginVersion' => $plugin->version(),
            'adminUrls' => [
                'list' => admin_url('admin.php?page=' . AdminCatalogMenu::MENU_SLUG),
                'addTest' => admin_url('admin.php?page=' . AdminCatalogMenu::SLUG_ADD),
                'settings' => admin_url('admin.php?page=' . AdminCatalogMenu::SLUG_SETTINGS),
            ],
        ];
    }
}
