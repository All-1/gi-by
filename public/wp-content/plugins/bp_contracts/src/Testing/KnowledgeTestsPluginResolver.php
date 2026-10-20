<?php

declare(strict_types=1);

namespace PersonalAccount\Testing;

use BpKnowledgeTests\Bootstrap\PluginBootstrap;
use BpKnowledgeTests\Plugin;
use PersonalAccount\Core\Container;

/**
 * Resolves the bp_knowledge_tests runtime Plugin for WS / contracts (Phase 4 Wave C).
 */
final class KnowledgeTestsPluginResolver
{
    private ?Plugin $cached = null;

    public function isAvailable(): bool
    {
        try {
            return $this->resolve() instanceof Plugin;
        } catch (\Throwable) {
            return false;
        }
    }

    public function plugin(): Plugin
    {
        $plugin = $this->resolve();
        if (!$plugin instanceof Plugin) {
            throw new \RuntimeException('bp_knowledge_tests is not available.');
        }

        return $plugin;
    }

    private function resolve(): ?Plugin
    {
        if ($this->cached instanceof Plugin) {
            return $this->cached;
        }

        if (function_exists('bp_knowledge_tests_plugin')) {
            $plugin = bp_knowledge_tests_plugin();
            if ($plugin instanceof Plugin) {
                $this->cached = $plugin;

                return $plugin;
            }

            return null;
        }

        if (!class_exists(PluginBootstrap::class)) {
            return null;
        }

        global $wpdb, $servicesContainer;
        if (!$servicesContainer instanceof Container) {
            return null;
        }

        $this->cached = (new PluginBootstrap($wpdb, $servicesContainer))->compose();

        return $this->cached;
    }
}
