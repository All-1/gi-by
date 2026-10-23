<?php

declare(strict_types=1);

/**
 * Notification presentation phases (spec §3.1–§3.2).
 *
 * Run from plugin root: php tests/run_notification_presentation_tests.php
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

use BpKnowledgeTests\Domain\NotificationPresentation;
use BpKnowledgeTests\Domain\NotificationSettings;

$failures = 0;

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        ++$failures;
    }
};

$assert(
    NotificationPresentation::resolve(0, 10, NotificationSettings::MODE_BLOCK) === NotificationPresentation::POPUP,
    'below limit → popup',
);
$assert(
    NotificationPresentation::resolve(10, 10, NotificationSettings::MODE_BLOCK) === NotificationPresentation::BLOCK,
    'at limit block mode → block',
);
$assert(
    NotificationPresentation::resolve(10, 10, NotificationSettings::MODE_BOTTOM) === NotificationPresentation::BOTTOM,
    'at limit bottom mode → bottom',
);

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All notification presentation tests passed.\n");
