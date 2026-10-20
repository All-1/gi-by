<?php

declare(strict_types=1);

namespace PersonalAccount\Testing\Contract;

use PersonalAccount\Testing\GradedQuestionCounts;

interface QuestionSelectionGraderInterface
{
    /**
     * @param list<int> $correctAnswerIds
     * @param list<int> $selectedAnswerIds
     */
    public function grade(array $correctAnswerIds, array $selectedAnswerIds): GradedQuestionCounts;
}
