<?php

declare(strict_types=1);

namespace PersonalAccount\Testing;

/**
 * In-memory unfinished attempt (spec §2.5). Cleared when the WS process restarts (~03:00).
 */
final class AttemptSession
{
    /** @param list<int> $questionIds */
    /** @param array<int, list<int>> $shuffledAnswerIdsByQuestion */
    /** @param array<int, list<int>> $correctAnswerIdsByQuestion */
    /** @param array<int, array{rightAnswers: int, failedAnswers: int}> $statsByQuestionId */
    private function __construct(
        public readonly int $userId,
        public readonly int $testId,
        private array $questionIds,
        private array $shuffledAnswerIdsByQuestion,
        private array $correctAnswerIdsByQuestion,
        private int $currentIndex,
        private array $statsByQuestionId,
        /** @var list<int> */
        private array $lastSelectedAnswerIds,
    ) {
    }

    /**
     * @param list<array{questionId: int, answerIds: list<int>, correctAnswerIds: list<int>}> $questions
     */
    public static function begin(int $userId, int $testId, array $questions): self
    {
        $questionIds = [];
        $answers = [];
        $correct = [];
        foreach ($questions as $row) {
            $qid = (int) $row['questionId'];
            $questionIds[] = $qid;
            $answerIds = array_map('intval', $row['answerIds']);
            self::shuffleInPlace($answerIds);
            $answers[$qid] = $answerIds;
            $correct[$qid] = array_map('intval', $row['correctAnswerIds']);
        }
        self::shuffleInPlace($questionIds);

        return new self($userId, $testId, $questionIds, $answers, $correct, 0, [], []);
    }

    public function currentQuestionId(): ?int
    {
        if ($this->currentIndex >= count($this->questionIds)) {
            return null;
        }

        return $this->questionIds[$this->currentIndex];
    }

    /** @return list<int> */
    public function shuffledAnswerIds(int $questionId): array
    {
        return $this->shuffledAnswerIdsByQuestion[$questionId] ?? [];
    }

    /** @return list<int> */
    public function correctAnswerIds(int $questionId): array
    {
        return $this->correctAnswerIdsByQuestion[$questionId] ?? [];
    }

    /** @return array{index: int, total: int} */
    public function progress(): array
    {
        $total = count($this->questionIds);
        $index = min($this->currentIndex + 1, $total > 0 ? $total : 1);

        return ['index' => $index, 'total' => $total];
    }

    public function isSessionComplete(): bool
    {
        return $this->currentIndex >= count($this->questionIds);
    }

    /** @return list<int> */
    public function allQuestionIds(): array
    {
        return $this->questionIds;
    }

    /** @return list<int> */
    public function lastSelectedAnswerIds(): array
    {
        return $this->lastSelectedAnswerIds;
    }

    /**
     * @param list<int> $selectedAnswerIds
     */
    public function recordSubmission(
        int $questionId,
        GradedQuestionCounts $graded,
        array $selectedAnswerIds,
    ): void {
        if ($this->currentQuestionId() !== $questionId) {
            throw new \InvalidArgumentException('submit_not_current_question');
        }
        if (isset($this->statsByQuestionId[$questionId])) {
            throw new \InvalidArgumentException('submit_already_answered');
        }

        $this->statsByQuestionId[$questionId] = [
            'rightAnswers' => $graded->rightAnswers,
            'failedAnswers' => $graded->failedAnswers,
        ];
        $this->lastSelectedAnswerIds = $selectedAnswerIds;
        ++$this->currentIndex;
    }

    /**
     * @return list<array{questionId: int, rightAnswers: int, failedAnswers: int}>
     */
    public function finishQuestionLines(): array
    {
        $lines = [];
        foreach ($this->questionIds as $questionId) {
            $stats = $this->statsByQuestionId[$questionId] ?? ['rightAnswers' => 0, 'failedAnswers' => 0];
            $lines[] = [
                'questionId' => $questionId,
                'rightAnswers' => $stats['rightAnswers'],
                'failedAnswers' => $stats['failedAnswers'],
            ];
        }

        return $lines;
    }

    /** @param list<int> $items */
    private static function shuffleInPlace(array &$items): void
    {
        $count = count($items);
        for ($i = $count - 1; $i > 0; --$i) {
            $j = random_int(0, $i);
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
        }
    }
}
