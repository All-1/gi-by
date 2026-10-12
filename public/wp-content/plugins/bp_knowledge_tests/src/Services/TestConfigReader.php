<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

use BpKnowledgeTests\Domain\NotificationSettings;
use BpKnowledgeTests\Domain\TestConfig;
use BpKnowledgeTests\Services\Catalog\ConfigCatalog;

final class TestConfigReader
{
    public function __construct(private ConfigCatalog $config)
    {
    }

    public function read(): TestConfig
    {
        return new TestConfig(
            $this->threshold('bronze_threshold', 70.0),
            $this->threshold('silver_threshold', 80.0),
            $this->threshold('gold_threshold', 90.0),
            $this->threshold('lock_threshold', 95.0),
        );
    }

    public function retakeDelayDays(): int
    {
        return max(0, $this->intConfig('retake_delay_days', 1));
    }

    public function notificationSettings(): NotificationSettings
    {
        return new NotificationSettings(
            $this->intConfig('new_test_notification_dismiss_limit', 10),
            $this->intConfig('failed_test_notification_dismiss_limit', 10),
            $this->stringConfig('new_test_notification_mode', NotificationSettings::MODE_BOTTOM),
            $this->stringConfig('failed_test_notification_mode', NotificationSettings::MODE_BOTTOM),
        );
    }

    private function intConfig(string $name, int $default): int
    {
        $raw = $this->config->get($name);
        if ($raw === null || $raw === '') {
            return $default;
        }

        return (int) $raw;
    }

    private function stringConfig(string $name, string $default): string
    {
        $raw = $this->config->get($name);
        if ($raw === null || $raw === '') {
            return $default;
        }

        return (string) $raw;
    }

    private function threshold(string $name, float $default): float
    {
        $raw = $this->config->get($name);
        if ($raw === null || $raw === '') {
            return $default;
        }

        return (float) $raw;
    }
}
