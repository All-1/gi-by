<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Admin;

use BpKnowledgeTests\Plugin;

final class DevToolsMenu
{
    public function __construct(private Plugin $plugin)
    {
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'addPage']);
    }

    public function addPage(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        add_management_page(
            'Knowledge Tests (dev)',
            'Knowledge Tests (dev)',
            'manage_options',
            'bp-knowledge-tests-dev',
            [$this, 'render'],
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.'));
        }

        $catalog = $this->plugin->catalog();
        $domain = $this->plugin->domain();
        $tests = $catalog->tests->listAll();
        $config = $domain->config->read();

        echo '<div class="wrap"><h1>Knowledge Tests (dev)</h1>';
        echo '<p><strong>Not product UI.</strong> V1 dealer and admin interfaces are <strong>React</strong>; this page is temporary smoke for catalog/scoring until React admin exists. Schema option: <code>bp_knowledge_tests_schema_version</code>.</p>';
        echo '<h2>Thresholds (from config)</h2><ul>';
        printf(
            '<li>Bronze: %s%% — Silver: %s%% — Gold: %s%% — Lock: %s%%</li>',
            esc_html((string) $config->bronzeThreshold),
            esc_html((string) $config->silverThreshold),
            esc_html((string) $config->goldThreshold),
            esc_html((string) $config->lockThreshold),
        );
        echo '</ul><h2>Tests</h2>';
        if ($tests === []) {
            echo '<p>No tests yet. Create via catalog API or future admin UI.</p>';
        } else {
            echo '<table class="widefat"><thead><tr><th>ID</th><th>Title</th><th>Version</th><th>Questions</th></tr></thead><tbody>';
            foreach ($tests as $test) {
                $questions = $catalog->questions->listForTest($test->id);
                echo '<tr>';
                printf('<td>%d</td>', $test->id);
                printf('<td>%s</td>', esc_html($test->title));
                printf('<td>%d</td>', $test->version);
                printf('<td>%d</td>', count($questions));
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        echo '</div>';
    }
}
