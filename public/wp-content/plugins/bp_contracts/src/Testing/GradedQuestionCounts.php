<?php

declare(strict_types=1);

namespace PersonalAccount\Testing;

/** Per-question selection counts from the knowledge-tests grader (no domain logic here). */
final class GradedQuestionCounts
{
    public function __construct(
        public readonly int $rightAnswers,
        public readonly int $failedAnswers,
        public readonly int $missedCorrect,
    ) {
    }
}
