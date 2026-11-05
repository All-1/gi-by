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
$standing = $plugin->attempts()->standing;
$failedNotification = static function (int $forUserId, int $forTestId) use ($db): ?object {
    foreach ($db->allRows('KnowledgeTestNotifications') as $row) {
        if ((int) $row->user_id === $forUserId
            && (int) $row->test_id === $forTestId
            && (string) $row->notification_type === NotificationRecord::TYPE_FAILED_TEST
        ) {
            return $row;
        }
    }

    return null;
};

$seed = $fixture->seedCatalog($plugin);
$testId = $seed['testId'];
$questionId = $seed['questionId'];
$userId = 9001;

$emptyStanding = $standing->forUserAndTest($userId, $testId);
$assert($emptyStanding->latest === null, 'standing has no attempt before finish');
$assert($emptyStanding->medalId === null && $emptyStanding->medalTier === null, 'standing has no medal before finish');
$assert($emptyStanding->medalRankName === null, 'standing has no rank before finish');

$passLines = [['questionId' => $questionId, 'rightAnswers' => 7, 'failedAnswers' => 0]];
$passed = $complete->finish($userId, $testId, $passLines);
$assert($passed->status === 'passed', 'bronze pass status');
$assert(abs($passed->score - 100.0) < 0.01, 'score 100% with no wrong selections (10 max options)');
$assert($materials->listForAttempt($userId, $passed->id) === [], 'locked pass stores no materials');
$lockedMedalId = (int) $db->allRows('KnowledgeTestUserAchievements')[0]->medal_id;
$lockedTier = '';
foreach ($db->allRows('KnowledgeTestAchievements') as $medal) {
    if ((int) $medal->id === $lockedMedalId) {
        $lockedTier = (string) $medal->value;
    }
}
$assert($lockedTier === 'gold', 'locked pass stores the gold medal');
$passedStanding = $standing->forUserAndTest($userId, $testId);
$assert($passedStanding->latest !== null && $passedStanding->latest->id === $passed->id, 'standing uses the finished attempt');
$assert($passedStanding->medalId === $lockedMedalId, 'standing medal id');
$assert($passedStanding->medalTier === 'gold', 'standing medal tier');
$assert($passedStanding->medalRankName === 'gold', 'standing rank falls back to the tier value');
$assert(count($db->allRows('KnowledgeTestUserAchievements')) === 1, 'achievement row after pass');
$assert($failedNotification($userId, $testId) === null, 'no failed-test notification after pass');

$failLines = [['questionId' => $questionId, 'rightAnswers' => 0, 'failedAnswers' => 4]];
$failed = $complete->finish($userId, $testId, $failLines);
$assert($failed->status === 'failed', 'failed status');
$assert(abs($failed->score - 60.0) < 0.01, 'score 60% with 4 wrong of 10 max');
$assert($materials->listForAttempt($userId, $failed->id) !== [], 'materials created on fail');
$assert(count($db->allRows('KnowledgeTestUserAchievements')) === 0, 'achievement removed after fail');
$failedStanding = $standing->forUserAndTest($userId, $testId);
$assert($failedStanding->latest !== null && $failedStanding->latest->userId === $userId, 'standing still has an attempt after fail');
$assert($failedStanding->medalId === null && $failedStanding->medalRankName === null, 'standing medal cleared after fail');
$failedNotif = $failedNotification($userId, $testId);
$assert($failedNotif !== null && (int) $failedNotif->dismiss_count === 0, 'failed-test notification reset on fail');

$recovery = $complete->finish($userId, $testId, $passLines);
$assert($recovery->status === 'passed', 'can pass again after fail');
$assert(count($db->allRows('KnowledgeTestUserAchievements')) === 1, 'achievement restored after pass');

$belowLock = $complete->finish(9002, $testId, [
    ['questionId' => $questionId, 'rightAnswers' => 9, 'failedAnswers' => 1],
]);
$assert($belowLock->status === 'passed', 'score below lock still passes');
$assert(
    $materials->listForAttempt(9002, $belowLock->id) !== [],
    'mistakes below the lock score create materials',
);

$attemptRows = $db->allRows('KnowledgeTestAttempts');
$assert(count($attemptRows) === 4, 'four attempts persisted');
$questionRows = $db->allRows('KnowledgeTestAttemptQuestions');
$assert(count($questionRows) === 4, 'four attempt_question rows');

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All complete attempt integration tests passed.\n");
