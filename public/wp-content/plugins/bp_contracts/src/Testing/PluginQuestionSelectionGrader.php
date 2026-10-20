<?php

declare(strict_types=1);

namespace PersonalAccount\Testing;

use PersonalAccount\Testing\Contract\QuestionSelectionGraderInterface;
/**
 * Thin bridge to bp_knowledge_tests grading (Wave B); used by TestController.
 */
final class PluginQuestionSelectionGrader implements QuestionSelectionGraderInterface
{
    public function __construct(private KnowledgeTestsPluginResolver $resolver)
    {
    }

    public function grade(array $correctAnswerIds, array $selectedAnswerIds): GradedQuestionCounts
    {
        $counts = $this->resolver->plugin()->domain()->selection->grade($correctAnswerIds, $selectedAnswerIds);

        return new GradedQuestionCounts(
            $counts->rightAnswers,
            $counts->failedAnswers,
            $counts->missedCorrect,
        );
    }
}
