<?php

declare(strict_types=1);

/**
 * Achievement tier mapping from score (spec §2.11–§2.12).
 *
 * Run from plugin root: php tests/run_achievement_tier_tests.php
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

$failures = 0;

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        ++$failures;
    }
};

$classifier = new AttemptResultClassifier();
$config = new TestConfig(70.0, 80.0, 90.0, 95.0);

$failed = $classifier->classify(50.0, $config);
$assert($classifier->achievementTierValue($failed) === null, 'failed attempt has no tier');

$bronze = $classifier->classify(75.0, $config);
$assert($classifier->achievementTierValue($bronze) === 'bronze', 'bronze tier');

$locked = $classifier->classify(96.0, $config);
$assert($locked->locked === true, 'score at lock threshold');
$assert($classifier->achievementTierValue($locked) === 'lock', 'locked uses lock tier not gold');

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All achievement tier tests passed.\n");
