<?php

declare(strict_types=1);

namespace PersonalAccount\Testing;

final class SubmitQuestionResult
{
    /**
     * @param list<int> $lastSelectedAnswerIds
     */
    public function __construct(
        public readonly int $questionId,
        public readonly GradedQuestionCounts $graded,
        public readonly array $lastSelectedAnswerIds,
        public readonly bool $sessionComplete,
        public readonly AttemptSession $session,
    ) {
    }
}
