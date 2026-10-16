<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Catalog;

use BpKnowledgeTests\Domain\NotificationSettings;
use BpKnowledgeTests\Infrastructure\Config\AdminConfigKeys;

final class AdminConfigValidator
{
    public function validate(string $name, string $value): ?string
    {
        if (!AdminConfigKeys::isAllowed($name)) {
            return 'Unknown config key: ' . $name;
        }

        if (in_array($name, AdminConfigKeys::THRESHOLD_KEYS, true)) {
            return $this->validateThreshold($value);
        }

        if ($name === 'retake_delay_days') {
            return $this->validateNonNegativeInt($value, 'Retake delay');
        }

        if (str_ends_with($name, '_notification_dismiss_limit')) {
            return $this->validateNonNegativeInt($value, 'Dismiss limit');
        }

        if (str_ends_with($name, '_notification_mode')) {
            return $this->validateNotificationMode($value);
        }

        if (str_starts_with($name, 'rank_name_')) {
            return trim($value) === '' ? 'Rank name cannot be empty.' : null;
        }

        return null;
    }

    private function validateThreshold(string $value): ?string
    {
        if (!is_numeric($value)) {
            return 'Threshold must be a number.';
        }
        $num = (float) $value;
        if ($num < 0 || $num > 100) {
            return 'Threshold must be between 0 and 100.';
        }

        return null;
    }

    private function validateNonNegativeInt(string $value, string $label): ?string
    {
        if (!ctype_digit($value) && $value !== '0') {
            return $label . ' must be a whole number.';
        }

        return null;
    }

    private function validateNotificationMode(string $value): ?string
    {
        if ($value !== NotificationSettings::MODE_BLOCK && $value !== NotificationSettings::MODE_BOTTOM) {
            return 'Notification mode must be block or bottom.';
        }

        return null;
    }
}
