<?php

declare(strict_types=1);

namespace PersonalAccount\Testing;

use PersonalAccount\Testing\Contract\QuestionSelectionGraderInterface;
use BpKnowledgeTests\Bootstrap\PluginBootstrap;
use BpKnowledgeTests\Plugin;

/**
 * Thin bridge to bp_knowledge_tests (Wave B); gateway may wrap this in Wave C.
 */
final class PluginQuestionSelectionGrader implements QuestionSelectionGraderInterface
{
    public function grade(array $correctAnswerIds, array $selectedAnswerIds): GradedQuestionCounts
    {
        $counts = $this->plugin()->domain()->selection->grade($correctAnswerIds, $selectedAnswerIds);

        return new GradedQuestionCounts(
            $counts->rightAnswers,
            $counts->failedAnswers,
            $counts->missedCorrect,
        );
    }

    private function plugin(): Plugin
    {
        static $instance = null;
        if ($instance instanceof Plugin) {
            return $instance;
        }

        global $wpdb, $servicesContainer;
        $instance = (new PluginBootstrap($wpdb, $servicesContainer))->compose();

        return $instance;
    }
}
