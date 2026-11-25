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

$assert($scoring->calculateScore(4, 5) === 80.0, '4 of 5 questions correct → 80%');
$assert($scoring->calculateScore(2, 4) === 50.0, '2 of 4 questions correct → 50%');
$assert($scoring->calculateScore(1, 3) === 33.33, '1 of 3 rounds to 33.33%');
$assert($scoring->calculateScore(0, 20) === 0.0, 'No question fully correct → 0%');
$assert($scoring->calculateScore(20, 20) === 100.0, 'Every question correct → 100%');
$assert($scoring->calculateScore(25, 20) === 100.0, 'Over-count clamps to 100%');
$assert($scoring->calculateScore(-1, 4) === 0.0, 'Negative count clamps to 0%');

try {
    $scoring->calculateScore(1, 0);
    $assert(false, 'Zero question count should throw');
} catch (\InvalidArgumentException $e) {
    $assert(true, 'Zero question count throws InvalidArgumentException');
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
