<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Domain\Record\QuestionRecord;
use BpKnowledgeTests\Infrastructure\DatabaseClock;
use BpKnowledgeTests\Infrastructure\Repository\AttemptQuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Services\CatalogServices;
use BpKnowledgeTests\Services\DomainServices;

final class CompleteAttempt
{
    public function __construct(
        private CatalogServices $catalog,
        private DomainServices $domain,
        private AttemptRepository $attempts,
        private AttemptQuestionRepository $attemptQuestions,
        private MaterialsService $materials,
        private AttemptAchievementService $achievements,
        private DatabaseClock $clock,
    ) {
    }

    /**
     * @param list<array{questionId: int, rightAnswers: int, failedAnswers: int}> $questionLines
     */
    public function finish(int $userId, int $testId, array $questionLines): AttemptRecord
    {
        $test = $this->catalog->tests->find($testId);
        if ($test === null) {
            throw new \InvalidArgumentException('Test not found.');
        }

        $lines = $this->validatedLinesForTest($testId, $questionLines);
        $outcome = $this->outcomeForLines($testId, $lines);
        $attemptRow = [
            'userId' => $userId,
            'testId' => $testId,
            'testVersion' => $test->version,
            'dateFinished' => $this->clock->now(),
        ];
        $attempt = $this->persist($attemptRow, $lines, $outcome);

        if ($outcome['status'] === 'failed') {
            $this->materials->populateFromFailedAttempt($attempt->id);
        }

        $this->achievements->applyAfterComplete($userId, $testId, $attempt->score);

        return $attempt;
    }

    /**
     * @param list<array<string, mixed>> $rawLines
     * @return list<array{questionId: int, rightAnswers: int, failedAnswers: int}>
     */
    private function validatedLinesForTest(int $testId, array $rawLines): array
    {
        $lines = $this->parseQuestionLines($rawLines);
        $this->assertQuestionLinesMatchCatalog($this->catalog->questions->listForTest($testId), $lines);

        return $lines;
    }

    /**
     * @param list<array{questionId: int, rightAnswers: int, failedAnswers: int}> $lines
     * @return array{validAnswers: int, invalidAnswers: int, score: float, status: string}
     */
    private function outcomeForLines(int $testId, array $lines): array
    {
        $valid = 0;
        $invalid = 0;
        foreach ($lines as $line) {
            $valid += $line['rightAnswers'];
            $invalid += $line['failedAnswers'];
        }

        $score = $this->domain->scoring->calculateScore(
            $valid,
            $invalid,
            $this->catalog->answers->maximumCorrectForTest($testId),
        );
        $passed = $this->domain->classifier->classify($score, $this->domain->config->read())->passed;

        return [
            'validAnswers' => $valid,
            'invalidAnswers' => $invalid,
            'score' => $score,
            'status' => $passed ? 'passed' : 'failed',
        ];
    }

    /**
     * @param array{userId: int, testId: int, testVersion: int, dateFinished: string} $attemptRow
     * @param list<array{questionId: int, rightAnswers: int, failedAnswers: int}> $lines
     * @param array{validAnswers: int, invalidAnswers: int, score: float, status: string} $outcome
     */
    private function persist(array $attemptRow, array $lines, array $outcome): AttemptRecord
    {
        $attemptId = $this->attempts->insert(
            $attemptRow['userId'],
            $attemptRow['testId'],
            $attemptRow['testVersion'],
            $outcome['validAnswers'],
            $outcome['invalidAnswers'],
            $outcome['score'],
            $outcome['status'],
            $attemptRow['dateFinished'],
        );

        foreach ($lines as $line) {
            $this->attemptQuestions->insert(
                $attemptRow['userId'],
                $attemptId,
                $line['questionId'],
                $line['rightAnswers'],
                $line['failedAnswers'],
                $attemptRow['dateFinished'],
            );
        }

        $attempt = $this->attempts->findById($attemptId);

        return $attempt ?? throw new \RuntimeException('Attempt was not persisted.');
    }

    /**
     * @param list<array<string, mixed>> $questionLines
     * @return list<array{questionId: int, rightAnswers: int, failedAnswers: int}>
     */
    private function parseQuestionLines(array $questionLines): array
    {
        if ($questionLines === []) {
            throw new \InvalidArgumentException('Attempt must include at least one question.');
        }

        $parsed = [];
        foreach ($questionLines as $line) {
            if (!is_array($line) || !isset($line['questionId'], $line['rightAnswers'], $line['failedAnswers'])) {
                throw new \InvalidArgumentException('Each question line requires questionId, rightAnswers, failedAnswers.');
            }
            $right = (int) $line['rightAnswers'];
            $failed = (int) $line['failedAnswers'];
            if ($right < 0 || $failed < 0) {
                throw new \InvalidArgumentException('Answer counts cannot be negative.');
            }
            $parsed[] = [
                'questionId' => (int) $line['questionId'],
                'rightAnswers' => $right,
                'failedAnswers' => $failed,
            ];
        }

        return $parsed;
    }

    /**
     * @param list<QuestionRecord> $catalogQuestions
     * @param list<array{questionId: int, rightAnswers: int, failedAnswers: int}> $lines
     */
    private function assertQuestionLinesMatchCatalog(array $catalogQuestions, array $lines): void
    {
        if ($catalogQuestions === []) {
            throw new \InvalidArgumentException('Test has no questions.');
        }

        $expectedIds = array_map(static fn (QuestionRecord $q): int => $q->id, $catalogQuestions);
        sort($expectedIds);

        $providedIds = array_column($lines, 'questionId');
        $uniqueProvided = array_values(array_unique($providedIds));
        sort($uniqueProvided);

        if (count($providedIds) !== count($uniqueProvided)) {
            throw new \InvalidArgumentException('Duplicate question in attempt payload.');
        }
        if ($expectedIds !== $uniqueProvided) {
            throw new \InvalidArgumentException('Question set does not match test catalog.');
        }
    }
}
