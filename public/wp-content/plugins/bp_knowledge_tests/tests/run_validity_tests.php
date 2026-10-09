<?php

declare(strict_types=1);

/**
 * Attempt validity / outdated rules (spec §2.3, §2.15).
 *
 * Run from plugin root: php tests/run_validity_tests.php
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

use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Services\Attempt\AttemptValidityPolicy;

$failures = 0;

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        ++$failures;
    }
};

$policy = new AttemptValidityPolicy();

$passed = static function (string $finished): AttemptRecord {
    return new AttemptRecord(1, 10, 2, 1, 5, 0, 85.0, 'passed', $finished);
};

$assert(
    !$policy->passedResultExpired($passed('2026-09-16 10:00:00'), '2027-09-16 23:59:59'),
    'still valid on last day of one-year period',
);

$assert(
    $policy->passedResultExpired($passed('2026-09-16 10:00:00'), '2027-09-17 00:00:01'),
    'outdated after one-year period',
);

$failed = new AttemptRecord(1, 10, 2, 1, 0, 5, 40.0, 'failed', '2020-01-01 00:00:00');
$assert(!$policy->passedResultExpired($failed, '2030-01-01 00:00:00'), 'failed attempts are not expired by validity policy');

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All validity tests passed.\n");
