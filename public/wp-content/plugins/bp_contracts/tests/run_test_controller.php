<?php

declare(strict_types=1);

/**
 * Isolated TestController checks (no WordPress). Run from repo: php tests/run_test_controller.php
 */

$autoload = dirname(__DIR__, 4) . '/vendor/autoload.php';
if (!is_readable($autoload)) {
    fwrite(STDERR, "Missing autoload: {$autoload}\n");
    exit(1);
}
require_once $autoload;

use PersonalAccount\Testing\AttemptSession;
use PersonalAccount\Testing\GradedQuestionCounts;
use PersonalAccount\Testing\Contract\QuestionSelectionGraderInterface;
use PersonalAccount\Testing\TestController;

final class StubGrader implements QuestionSelectionGraderInterface
{
    public function grade(array $correctAnswerIds, array $selectedAnswerIds): GradedQuestionCounts
    {
        $correct = [];
        foreach ($correctAnswerIds as $id) {
            if ((int) $id > 0) {
                $correct[(int) $id] = true;
            }
        }
        $right = 0;
        $failed = 0;
        foreach ($selectedAnswerIds as $id) {
            $id = (int) $id;
            if (isset($correct[$id])) {
                ++$right;
            } else {
                ++$failed;
            }
        }

        return new GradedQuestionCounts($right, $failed, count($correct) - $right);
    }
}

function assertTrue(bool $cond, string $message): void
{
    if (!$cond) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$controller = new TestController(new StubGrader());
$userId = 10;
$testId = 1;
$questions = [
    ['questionId' => 1, 'answerIds' => [101, 102], 'correctAnswerIds' => [101]],
    ['questionId' => 2, 'answerIds' => [201, 202, 203], 'correctAnswerIds' => [201, 203]],
];

$session = $controller->startOrResume($userId, $testId, $questions);
assertTrue($controller->hasSession($userId, $testId), 'hasSession after start');
assertTrue($session->currentQuestionId() !== null, 'has current question');

$qid = $session->currentQuestionId();
$result = $controller->submitQuestion($userId, $testId, $session->correctAnswerIds($qid));
assertTrue($result !== null, 'submit ok');

$resumed = $controller->startOrResume($userId, $testId, $questions);
assertTrue($resumed === $session, 'resume returns same session');

while ($controller->getSession($userId, $testId)?->currentQuestionId() !== null) {
    $qid = $controller->getSession($userId, $testId)->currentQuestionId();
    $correct = $controller->getSession($userId, $testId)->correctAnswerIds($qid);
    $controller->submitQuestion($userId, $testId, $correct);
}

$payload = $controller->buildFinishPayload($userId, $testId);
assertTrue(count($payload) === 2, 'finish payload line count');
$controller->clear($userId, $testId);
assertTrue(!$controller->hasSession($userId, $testId), 'cleared');

echo "OK: TestController smoke tests passed.\n";
