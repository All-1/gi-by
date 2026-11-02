<?php

declare(strict_types=1);

/**
 * Attempt score uses maximum correct options for questions in the finish payload only.
 *
 * Run: php tests/run_outcome_maximum_scope_tests.php
 */

$pluginRoot = dirname(__DIR__);
$autoload = $pluginRoot . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class) use ($pluginRoot): void {
        $prefix = 'BpKnowledgeTests\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $testsPrefix = 'Tests\\';
        if (strncmp($relative, $testsPrefix, strlen($testsPrefix)) === 0) {
            $path = $pluginRoot . '/tests/' . str_replace('\\', '/', substr($relative, strlen($testsPrefix))) . '.php';
            if (is_readable($path)) {
                require $path;
            }

            return;
        }
        $path = $pluginRoot . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($path)) {
            require $path;
        }
    });
}

$contractsRoot = dirname($pluginRoot) . '/bp_contracts';
$contractsAutoload = $contractsRoot . '/vendor/autoload.php';
if (is_readable($contractsAutoload)) {
    require $contractsAutoload;
} else {
    spl_autoload_register(static function (string $class) use ($contractsRoot): void {
        $prefix = 'PersonalAccount\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $path = $contractsRoot . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($path)) {
            require $path;
        }
    });
}

use BpKnowledgeTests\Tests\Support\CompleteAttemptTestFixture;

$failures = 0;
$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        ++$failures;
    }
};

$fixture = new CompleteAttemptTestFixture();
$plugin = $fixture->createPlugin();
$complete = $plugin->attempts()->complete;

$testId = $plugin->catalog()->tests->create('Max scope', null);
$q1 = $plugin->catalog()->questions->create($testId, 'Q1?', '', '');
$q2 = $plugin->catalog()->questions->create($testId, 'Q2?', '', '');
$extra = $plugin->catalog()->questions->create($testId, 'Extra?', '', '');

foreach ([$q1, $q2] as $questionId) {
    $aid = $plugin->catalog()->answers->create($questionId, 'OK', true);
    $plugin->catalog()->answers->setCorrectAnswerIdsForQuestion($questionId, [$aid]);
}

$extraAid = $plugin->catalog()->answers->create($extra, 'Extra OK', true);
$plugin->catalog()->answers->setCorrectAnswerIdsForQuestion($extra, [$extraAid]);
for ($i = 0; $i < 4; ++$i) {
    $plugin->catalog()->answers->create($extra, 'Extra alt ' . $i, true);
}

$userId = 8801;
$lines = [
    ['questionId' => $q1, 'rightAnswers' => 1, 'failedAnswers' => 0],
    ['questionId' => $q2, 'rightAnswers' => 1, 'failedAnswers' => 0],
];

$attempt = $complete->finish($userId, $testId, $lines);
$assert(abs($attempt->score - 100.0) < 0.01, 'Score 100% when max is attempt questions only, not extra catalog question');
$assert($plugin->catalog()->answers->maximumCorrectForTest($testId) > 2, 'Catalog max includes extra question');

if ($failures > 0) {
    exit(1);
}

fwrite(STDOUT, "OK: outcome maximum scope tests passed.\n");
