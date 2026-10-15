<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Admin;

use BpKnowledgeTests\Infrastructure\Security\CatalogCapability;
use BpKnowledgeTests\Plugin;

final class AdminAppMount
{
    public const SHORTCODE = 'knowledge_tests_admin';

    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        add_shortcode(self::SHORTCODE, [$this, 'renderShortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueWhenNeeded']);
    }

    public function enqueueWhenNeeded(): void
    {
        if (!is_singular()) {
            return;
        }

        $post = get_post();
        if ($post === null || !has_shortcode((string) $post->post_content, self::SHORTCODE)) {
            return;
        }

        AdminAppAssets::enqueue($this->plugin);
    }

    public function renderShortcode(): string
    {
        if (!CatalogCapability::canManageCatalog()) {
            return '<p>' . esc_html__('You do not have permission to manage knowledge tests.', 'bp_knowledge_tests') . '</p>';
        }

        AdminAppAssets::enqueue($this->plugin);

        return '<div id="bp-knowledge-tests-admin-root" class="bp-knowledge-tests-admin-root"></div>';
    }
}
