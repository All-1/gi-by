<?php

declare(strict_types=1);

/**
 * Pure domain scoring tests (no WordPress bootstrap).
 *
 * Run from plugin root: php tests/run_scoring_tests.php
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
use BpKnowledgeTests\Domain\TestConfig;
use BpKnowledgeTests\Services\ScoringService;

$failures = 0;

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        ++$failures;
    }
};

$scoring = new ScoringService();
$classifier = new AttemptResultClassifier();
$config = new TestConfig(70.0, 80.0, 90.0, 95.0);

$assert(
    $scoring->calculateScore(60, 3, 62) === 91.94,
    'Spec example: 60 valid, 3 invalid, max 62 → 91.94%'
);

$assert($scoring->calculateScore(0, 10, 20) === 0.0, 'All penalties → 0%');
$assert($scoring->calculateScore(5, 10, 20) === 0.0, 'Net negative clamps to 0%');
$assert($scoring->calculateScore(100, 0, 50) === 100.0, 'Score caps at 100%');

try {
    $scoring->calculateScore(1, 0, 0);
    $assert(false, 'Zero maximum should throw');
} catch (\InvalidArgumentException $e) {
    $assert(true, 'Zero maximum throws InvalidArgumentException');
}

$passed = $classifier->classify(70.0, $config);
$assert($passed->passed && $passed->medal === AttemptResultClassifier::MEDAL_BRONZE, '70% passes at bronze');

$failed = $classifier->classify(69.99, $config);
$assert(!$failed->passed && $failed->medal === AttemptResultClassifier::MEDAL_FAILED, 'Below bronze fails');

$locked = $classifier->classify(95.0, $config);
$assert($locked->locked, '95% locks test');

$notLocked = $classifier->classify(94.99, $config);
$assert(!$notLocked->locked, '94.99% does not lock');

$gold = $classifier->classify(90.0, $config);
$assert($gold->medal === AttemptResultClassifier::MEDAL_GOLD, '90% is gold medal');

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All scoring tests passed.\n");
exit(0);
