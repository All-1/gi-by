<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Config;

final class AdminConfigKeys
{
    /** @var list<string> */
    public const THRESHOLD_KEYS = [
        'bronze_threshold',
        'silver_threshold',
        'gold_threshold',
        'lock_threshold',
    ];

    /** @var list<string> */
    public const ALL = [
        'bronze_threshold',
        'silver_threshold',
        'gold_threshold',
        'lock_threshold',
        'retake_delay_days',
        'new_test_notification_dismiss_limit',
        'failed_test_notification_dismiss_limit',
        'new_test_notification_mode',
        'failed_test_notification_mode',
        'rank_name_gold',
        'rank_name_silver',
        'rank_name_bronze',
        'rank_name_failed',
    ];

    public static function isAllowed(string $name): bool
    {
        return in_array($name, self::ALL, true);
    }

    public static function triggersAchievementRecalc(string $name): bool
    {
        return in_array($name, self::THRESHOLD_KEYS, true);
    }
}
