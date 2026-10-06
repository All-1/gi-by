<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain;

final class QuestionSelectionCounts
{
    public function __construct(
        public readonly int $rightAnswers,
        public readonly int $failedAnswers,
        public readonly int $missedCorrect,
    ) {
    }
}
