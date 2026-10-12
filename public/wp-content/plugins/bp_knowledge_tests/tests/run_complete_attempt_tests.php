<?php

declare(strict_types=1);

/**
 * CompleteAttempt integration (pass/fail, materials, achievements, notifications).
 * Wave A4 scripted lifecycle — no WordPress required.
 *
 * Run from plugin root: php tests/run_complete_attempt_tests.php
 * Requires PHP 8.0+ (bp_contracts `Container::mixed`).
 */

$pluginRoot = dirname(__DIR__);
$autoload = $pluginRoot . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class) use ($pluginRoot): void {
        $prefix = 'BpKnowledgeTests\\';
        if (strncmp($class, $prefix, strlen($prefix)) === 0) {
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

            return;
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

use BpKnowledgeTests\Domain\Record\NotificationRecord;
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
$db = $fixture->memoryDb();
$catalog = $plugin->catalog()->tests;
$complete = $plugin->attempts()->complete;
$materials = $plugin->attempts()->materials;
$notifications = $plugin->attempts()->notifications;

$seed = $fixture->seedCatalog($plugin);
$testId = $seed['testId'];
$questionId = $seed['questionId'];
$userId = 9001;

$passLines = [['questionId' => $questionId, 'rightAnswers' => 7, 'failedAnswers' => 0]];
$passed = $complete->finish($userId, $testId, $passLines);
$assert($passed->status === 'passed', 'bronze pass status');
$assert(abs($passed->score - 70.0) < 0.01, 'score 70% with 7/10 correct options');
$assert($materials->listForAttempt($userId, $passed->id) === [], 'passed attempt has no materials');
$assert(count($db->allRows('KnowledgeTestUserAchievements')) === 1, 'achievement row after pass');
$notif = $notifications->findByUserTestAndType($userId, $testId, NotificationRecord::TYPE_FAILED_TEST);
$assert($notif === null, 'no failed-test notification after pass');

$failLines = [['questionId' => $questionId, 'rightAnswers' => 0, 'failedAnswers' => 2]];
$failed = $complete->finish($userId, $testId, $failLines);
$assert($failed->status === 'failed', 'failed status');
$assert($materials->listForAttempt($userId, $failed->id) !== [], 'materials created on fail');
$assert(count($db->allRows('KnowledgeTestUserAchievements')) === 0, 'achievement removed after fail');
$failedNotif = $notifications->findByUserTestAndType($userId, $testId, NotificationRecord::TYPE_FAILED_TEST);
$assert($failedNotif !== null && $failedNotif->dismissCount === 0, 'failed-test notification reset on fail');

$recovery = $complete->finish($userId, $testId, $passLines);
$assert($recovery->status === 'passed', 'can pass again after fail');
$assert(count($db->allRows('KnowledgeTestUserAchievements')) === 1, 'achievement restored after pass');

$attemptRows = $db->allRows('KnowledgeTestAttempts');
$assert(count($attemptRows) === 3, 'three attempts persisted');
$questionRows = $db->allRows('KnowledgeTestAttemptQuestions');
$assert(count($questionRows) === 3, 'three attempt_question rows');

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All complete attempt integration tests passed.\n");
