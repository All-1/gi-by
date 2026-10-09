<?php

declare(strict_types=1);

/**
 * Retake eligibility rules (spec §2.6, §2.12; plan §5.4).
 *
 * Run from plugin root: php tests/run_retake_eligibility_tests.php
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

use BpKnowledgeTests\Domain\AttemptResultClassifier;
use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Domain\RetakeEligibility;
use BpKnowledgeTests\Domain\TestConfig;
use BpKnowledgeTests\Services\Attempt\RetakeEligibilityContext;
use BpKnowledgeTests\Services\Attempt\RetakeEligibilityPolicy;

$failures = 0;

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        ++$failures;
    }
};

$policy = new RetakeEligibilityPolicy(new AttemptResultClassifier());
$config = new TestConfig(70.0, 80.0, 90.0, 95.0);

$context = static fn (bool $pending, int $delayDays, string $now): RetakeEligibilityContext => new RetakeEligibilityContext(
    $pending,
    $delayDays,
    $now,
);

$none = $policy->evaluate(null, $context(false, 1, '2026-10-08 12:00:00'), $config);
$assert($none->canStart === true && $none->reasons === [], 'no attempt allows start');

$attempt = static function (
    float $score,
    string $status,
    string $finished,
): AttemptRecord {
    return new AttemptRecord(1, 10, 2, 1, 5, 0, $score, $status, $finished);
};

$delayBlocked = $policy->evaluate(
    $attempt(80.0, 'passed', '2026-10-07 23:59:59'),
    $context(false, 1, '2026-10-07 10:00:00'),
    $config,
);
$assert(
    !$delayBlocked->canStart && in_array(RetakeEligibility::REASON_RETAKE_DELAY, $delayBlocked->reasons, true),
    'same calendar day blocks retake when delay is 1',
);

$delayOk = $policy->evaluate(
    $attempt(80.0, 'passed', '2026-10-07 08:00:00'),
    $context(false, 1, '2026-10-08 00:00:01'),
    $config,
);
$assert($delayOk->canStart === true, 'following calendar day allows retake');

$materials = $policy->evaluate(
    $attempt(50.0, 'failed', '2026-10-01 12:00:00'),
    $context(true, 1, '2026-10-10 12:00:00'),
    $config,
);
$assert(
    in_array(RetakeEligibility::REASON_PENDING_MATERIALS, $materials->reasons, true),
    'failed with pending materials blocks',
);

$locked = $policy->evaluate(
    $attempt(96.0, 'passed', '2026-10-01 12:00:00'),
    $context(false, 1, '2026-10-10 12:00:00'),
    $config,
);
$assert(
    !$locked->canStart && in_array(RetakeEligibility::REASON_TEST_LOCKED, $locked->reasons, true),
    'score at lock threshold blocks retake',
);

$outdatedLock = $policy->evaluate(
    $attempt(96.0, 'outdated', '2026-10-01 12:00:00'),
    $context(false, 1, '2026-10-10 12:00:00'),
    $config,
);
$assert(
    !in_array(RetakeEligibility::REASON_TEST_LOCKED, $outdatedLock->reasons, true),
    'outdated attempt does not apply lock block',
);

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All retake eligibility tests passed.\n");
