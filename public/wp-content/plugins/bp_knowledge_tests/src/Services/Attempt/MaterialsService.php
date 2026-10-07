<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Domain\Record\FinishedExplanationRecord;
use BpKnowledgeTests\Infrastructure\Repository\AttemptQuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\FinishedExplanationRepository;
use BpKnowledgeTests\Infrastructure\Repository\QuestionRepository;

final class MaterialsService
{
    public function __construct(
        private AttemptRepository $attempts,
        private AttemptQuestionRepository $attemptQuestions,
        private QuestionRepository $questions,
        private FinishedExplanationRepository $explanations,
    ) {
    }

    /**
     * @return list<FinishedExplanationRecord>
     */
    public function listForAttempt(int $userId, int $attemptId): array
    {
        $attempt = $this->attempts->findById($attemptId);
        if ($attempt === null || $attempt->userId !== $userId) {
            return [];
        }

        return $this->explanations->listByAttemptId($attemptId);
    }

    /**
     * @return list<FinishedExplanationRecord>
     */
    public function listPendingForUserAndTest(int $userId, int $testId): array
    {
        return $this->explanations->listPendingForUserAndTest($userId, $testId);
    }

    public function hasPendingMaterials(int $userId, int $testId): bool
    {
        return $this->explanations->hasPendingForUserAndTest($userId, $testId);
    }

    /**
     * Copy mistake explanations into temporary materials (spec §10.1). Idempotent per attempt.
     */
    public function populateFromFailedAttempt(int $attemptId): int
    {
        $attempt = $this->attempts->findById($attemptId);
        if ($attempt === null || !$attempt->isFailed()) {
            return 0;
        }

        $this->explanations->deleteByAttemptId($attemptId);

        $created = 0;
        foreach ($this->attemptQuestions->listByAttemptId($attemptId) as $attemptQuestion) {
            if (!$attemptQuestion->qualifiesForMaterials()) {
                continue;
            }
            $question = $this->questions->findById($attemptQuestion->questionId);
            if ($question === null) {
                continue;
            }
            $this->explanations->insert(
                $attemptId,
                $question->id,
                $attempt->userId,
                $question->explanation,
                $question->reference,
            );
            ++$created;
        }

        return $created;
    }

    /**
     * User confirmed "Examined" — remove temporary rows only (spec §10.2).
     */
    public function confirmExamined(int $userId, int $attemptId): bool
    {
        $attempt = $this->attempts->findById($attemptId);
        if ($attempt === null || $attempt->userId !== $userId) {
            return false;
        }

        $this->explanations->deleteByAttemptId($attemptId);

        return true;
    }
}
