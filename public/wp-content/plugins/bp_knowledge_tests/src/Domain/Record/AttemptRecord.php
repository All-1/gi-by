<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain\Record;

final class AttemptRecord
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $testId,
        public int $testVersion,
        public int $validAnswers,
        public int $invalidAnswers,
        public float $score,
        public string $status,
        public string $dateFinished,
    ) {
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
