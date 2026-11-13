<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

final class ScoringService
{
    /**
     * Spec §2.1: share of questions answered exactly (full correct set, no incorrect option).
     *
     * Score = 100 × correctlyAnsweredQuestions / questionCount
     */
    public function calculateScore(int $correctlyAnsweredQuestions, int $questionCount): float
    {
        if ($questionCount <= 0) {
            throw new \InvalidArgumentException('Question count must be greater than zero.');
        }

        $percent = 100.0 * $correctlyAnsweredQuestions / $questionCount;

        return round(min(100.0, max(0.0, $percent)), 2);
    }
}
