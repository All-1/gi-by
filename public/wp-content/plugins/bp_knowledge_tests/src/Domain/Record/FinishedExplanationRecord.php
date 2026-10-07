<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain\Record;

final class FinishedExplanationRecord
{
    public function __construct(
        public int $id,
        public int $attemptId,
        public int $questionId,
        public int $userId,
        public string $explanation,
        public string $reference,
    ) {
    }
}
