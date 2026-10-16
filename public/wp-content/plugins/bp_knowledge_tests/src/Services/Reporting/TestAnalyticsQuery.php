<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Reporting;

use BpKnowledgeTests\Infrastructure\Repository\AnswerRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptAnalyticsRepository;
use BpKnowledgeTests\Infrastructure\Repository\QuestionRepository;
use BpKnowledgeTests\Services\CatalogServices;

final class TestAnalyticsQuery
{
    public function __construct(
        private AttemptAnalyticsRepository $attempts,
        private CatalogServices $catalog,
        private QuestionRepository $questions,
        private AnswerRepository $answers,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listTestSummaries(): array
    {
        $summaries = [];
        foreach ($this->catalog->tests->listAll() as $test) {
            $summaries[] = $this->buildTestPayload($test->id, $test->title, $test->version, false);
        }

        return $summaries;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function testDetail(int $testId): ?array
    {
        $test = $this->catalog->tests->find($testId);
        if ($test === null) {
            return null;
        }

        return $this->buildTestPayload($test->id, $test->title, $test->version, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTestPayload(int $testId, string $title, int $version, bool $withQuestions): array
    {
        $summary = $this->attempts->testAttemptSummary($testId) ?? [
            'completed' => 0,
            'passed' => 0,
            'failed' => 0,
            'outdated' => 0,
            'averageScore' => 0.0,
        ];
        $payload = [
            'testId' => $testId,
            'title' => $title,
            'currentVersion' => $version,
            'questionCount' => count($this->questions->listByTestId($testId)),
            'attempts' => $summary,
            'scoreDistribution' => $this->attempts->scoreDistributionForTest($testId),
        ];
        if ($withQuestions) {
            $payload['questions'] = $this->questionAnalytics($testId);
        }

        return $payload;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function questionAnalytics(int $testId): array
    {
        $correctByQuestion = $this->answers->countCorrectOptionsByTest($testId);
        $items = [];
        foreach ($this->attempts->questionAggregateRows($testId) as $row) {
            $questionId = (int) $row->question_id;
            $presented = (int) ($row->times_presented ?? 0);
            $totalRight = (int) ($row->total_right ?? 0);
            $totalFailed = (int) ($row->total_failed ?? 0);
            $maxCorrect = $correctByQuestion[$questionId] ?? 0;
            $missed = max(0, $presented * $maxCorrect - $totalRight);
            $items[] = [
                'questionId' => $questionId,
                'question' => (string) ($row->question_text ?? ''),
                'timesPresented' => $presented,
                'totalRightSelections' => $totalRight,
                'totalFailedSelections' => $totalFailed,
                'missedCorrectSelections' => $missed,
                'maxCorrectOptions' => $maxCorrect,
                'averageQuestionScorePercent' => $this->averageQuestionScore(
                    $presented,
                    $maxCorrect,
                    $totalRight,
                    $totalFailed,
                ),
            ];
        }

        return $items;
    }

    private function averageQuestionScore(
        int $presented,
        int $maxCorrect,
        int $totalRight,
        int $totalFailed,
    ): ?float {
        if ($presented === 0 || $maxCorrect === 0) {
            return null;
        }

        $net = $totalRight - $totalFailed;
        $denominator = $presented * $maxCorrect;
        $percent = max(0, 100 * $net / $denominator);

        return round($percent, 2);
    }
}
