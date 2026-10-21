<?php

declare(strict_types=1);

namespace PersonalAccount\Testing;

use PersonalAccount\Testing\Contract\QuestionSelectionGraderInterface;

/**
 * RAM holder for in-progress knowledge tests. No scoring thresholds or pass/fail rules.
 */
final class TestController
{
    /** @var array<string, AttemptSession> */
    private array $sessions = [];

    public function __construct(
        private QuestionSelectionGraderInterface $grader,
    ) {
    }

    public function hasSession(int $userId, int $testId): bool
    {
        return isset($this->sessions[$this->sessionKey($userId, $testId)]);
    }

    /** Active in-progress test for user, or null when none (at most one session per user). */
    public function inProgressTestId(int $userId): ?int
    {
        $prefix = $userId . ':';
        foreach (array_keys($this->sessions) as $key) {
            if (!str_starts_with($key, $prefix)) {
                continue;
            }
            $testId = (int) substr($key, strlen($prefix));

            return $testId > 0 ? $testId : null;
        }

        return null;
    }

    public function getSession(int $userId, int $testId): ?AttemptSession
    {
        return $this->sessions[$this->sessionKey($userId, $testId)] ?? null;
    }

    /**
     * @param list<array{questionId: int, answerIds: list<int>, correctAnswerIds: list<int>}> $questions
     */
    public function startOrResume(int $userId, int $testId, array $questions): AttemptSession
    {
        $key = $this->sessionKey($userId, $testId);
        if (isset($this->sessions[$key])) {
            return $this->sessions[$key];
        }

        $session = AttemptSession::begin($userId, $testId, $questions);
        $this->sessions[$key] = $session;

        return $session;
    }

    /**
     * @param list<int> $selectedAnswerIds
     */
    public function submitQuestion(int $userId, int $testId, array $selectedAnswerIds): ?SubmitQuestionResult
    {
        $session = $this->getSession($userId, $testId);
        if ($session === null) {
            return null;
        }

        $questionId = $session->currentQuestionId();
        if ($questionId === null) {
            return null;
        }

        $graded = $this->grader->grade(
            $session->correctAnswerIds($questionId),
            $selectedAnswerIds,
        );
        $session->recordSubmission($questionId, $graded, $selectedAnswerIds);

        return new SubmitQuestionResult(
            $questionId,
            $graded,
            $selectedAnswerIds,
            $session->isSessionComplete(),
            $session,
        );
    }

    /**
     * @return list<array{questionId: int, rightAnswers: int, failedAnswers: int}>
     */
    public function buildFinishPayload(int $userId, int $testId): array
    {
        $session = $this->getSession($userId, $testId);
        if ($session === null) {
            return [];
        }

        return $session->finishQuestionLines();
    }

    public function clear(int $userId, ?int $testId = null): void
    {
        if ($testId !== null) {
            unset($this->sessions[$this->sessionKey($userId, $testId)]);

            return;
        }

        $prefix = $userId . ':';
        foreach (array_keys($this->sessions) as $key) {
            if (strpos($key, $prefix) === 0) {
                unset($this->sessions[$key]);
            }
        }
    }

    private function sessionKey(int $userId, int $testId): string
    {
        return $userId . ':' . $testId;
    }
}
