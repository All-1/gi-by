<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services;

final class ScoringService
{
    public function calculateScore(int $validSelections, int $invalidSelections, int $maximumCorrect): float
    {
        if ($maximumCorrect <= 0) {
            throw new \InvalidArgumentException('Maximum correct answers must be greater than zero.');
        }

        $net = $validSelections - $invalidSelections;
        $percent = 100.0 * $net / $maximumCorrect;

        return round(min(100.0, max(0.0, $percent)), 2);
    }
}
