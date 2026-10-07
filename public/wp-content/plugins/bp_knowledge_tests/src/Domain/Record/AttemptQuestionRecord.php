<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain\Record;

final class AttemptQuestionRecord
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $attemptId,
        public int $questionId,
        public int $rightAnswers,
        public int $failedAnswers,
        public string $dateFinished,
    ) {
    }

    /**
     * Materials rows use persisted counts only (spec §9.2, D4 — no catalog re-check).
     */
    public function qualifiesForMaterials(): bool
    {
        return $this->failedAnswers > 0 || $this->rightAnswers === 0;
    }
}
