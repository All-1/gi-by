<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain;

/**
 * Per-question selection counting (spec §2.1.1).
 */
final class QuestionSelectionGrader
{
    /**
     * @param list<int> $correctAnswerIds
     * @param list<int> $selectedAnswerIds
     */
    public function grade(array $correctAnswerIds, array $selectedAnswerIds): QuestionSelectionCounts
    {
        $correct = $this->uniquePositiveIds($correctAnswerIds);
        $selected = $this->uniquePositiveIds($selectedAnswerIds);

        $right = 0;
        $failed = 0;
        foreach ($selected as $answerId) {
            if (isset($correct[$answerId])) {
                ++$right;
            } else {
                ++$failed;
            }
        }

        return new QuestionSelectionCounts($right, $failed, count($correct) - $right);
    }

    /**
     * @param list<int> $ids
     *
     * @return array<int, true>
     */
    private function uniquePositiveIds(array $ids): array
    {
        $set = [];
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $set[$id] = true;
            }
        }

        return $set;
    }
}
