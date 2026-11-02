<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

final class ScoringService
{
    /**
     * Spec §2.1 (project): score from wrong selections only — full correct-option pool minus penalties.
     *
     * Score = 100 × (maximumCorrect − invalidSelections) / maximumCorrect
     */
    public function calculateScore(int $invalidSelections, int $maximumCorrect): float
    {
        if ($maximumCorrect <= 0) {
            throw new \InvalidArgumentException('Maximum correct answers must be greater than zero.');
        }

        $net = $maximumCorrect - $invalidSelections;
        $percent = 100.0 * $net / $maximumCorrect;

        return round(min(100.0, max(0.0, $percent)), 2);
    }
}
