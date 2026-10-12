<?php

declare(strict_types=1);

/**
 * Notification block vs retake (spec §3.1–§3.2; plan §5.4).
 *
 * Run from plugin root: php tests/run_notification_retake_tests.php
 */

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'BpKnowledgeTests\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $path = dirname(__DIR__) . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($path)) {
            require $path;
        }
    });
}

use BpKnowledgeTests\Domain\NotificationSettings;
use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Domain\Record\NotificationRecord;
use BpKnowledgeTests\Services\Attempt\NotificationRetakePolicy;

$failures = 0;

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        ++$failures;
    }
};

$policy = new NotificationRetakePolicy();
$blockSettings = new NotificationSettings(10, 10, NotificationSettings::MODE_BLOCK, NotificationSettings::MODE_BLOCK);
$bottomSettings = new NotificationSettings(10, 10, NotificationSettings::MODE_BOTTOM, NotificationSettings::MODE_BOTTOM);

$failed = new AttemptRecord(1, 1, 2, 1, 0, 5, 40.0, 'failed', '2026-01-01');
$passed = new AttemptRecord(2, 1, 2, 1, 5, 0, 80.0, 'passed', '2026-01-01');

$failedRow = new NotificationRecord(1, 1, 2, NotificationRecord::TYPE_FAILED_TEST, 10);

$assert(
    $policy->blocksRetake($failed, null, $failedRow, $blockSettings),
    'failed test blocks retake when dismiss limit reached in block mode',
);

$assert(
    !$policy->blocksRetake($failed, null, $failedRow, $bottomSettings),
    'bottom mode does not block retake',
);

$assert(
    !$policy->blocksRetake($passed, null, $failedRow, $blockSettings),
    'passed latest attempt ignores failed-test notification block',
);

$newRow = new NotificationRecord(2, 1, 2, NotificationRecord::TYPE_NEW_TEST, 10);
$assert(
    $policy->blocksRetake(null, $newRow, null, $blockSettings),
    'new-test notification blocks when no passed attempt yet',
);

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All notification retake tests passed.\n");
