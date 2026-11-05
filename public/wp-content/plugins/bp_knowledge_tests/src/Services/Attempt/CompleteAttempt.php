<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Domain\Record\NotificationRecord;
use BpKnowledgeTests\Domain\Record\QuestionRecord;
use BpKnowledgeTests\Infrastructure\ClockInterface;
use BpKnowledgeTests\Infrastructure\Repository\AttemptQuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\NotificationRepository;
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
        private NotificationRepository $notifications,
        private ClockInterface $clock,
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

        if ($outcome['status'] === 'failed' || !$outcome['locked']) {
            $this->materials->populateFromFailedAttempt($attempt->id);
        }
        if ($outcome['status'] === 'failed') {
            $this->notifications->resetDismissCount($userId, $testId, NotificationRecord::TYPE_FAILED_TEST);
        } else {
            $this->notifications->deleteByUserTestAndType($userId, $testId, NotificationRecord::TYPE_FAILED_TEST);
            $this->notifications->deleteByUserTestAndType($userId, $testId, NotificationRecord::TYPE_NEW_TEST);
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
        $this->assertQuestionLinesBelongToTest($this->catalog->questions->listForTest($testId), $lines);

        return $lines;
    }

    /**
     * @param list<array{questionId: int, rightAnswers: int, failedAnswers: int}> $lines
     * @return array{validAnswers: int, invalidAnswers: int, score: float, status: string, locked: bool}
     */
    private function outcomeForLines(int $testId, array $lines): array
    {
        $valid = 0;
        $invalid = 0;
        foreach ($lines as $line) {
            $valid += $line['rightAnswers'];
            $invalid += $line['failedAnswers'];
        }

        $questionIds = array_values(array_unique(array_column($lines, 'questionId')));
        $maximumCorrect = $this->catalog->answers->maximumCorrectForQuestions($questionIds);
        if ($maximumCorrect <= 0) {
            throw new \InvalidArgumentException('Maximum correct answers must be greater than zero.');
        }

        $score = $this->domain->scoring->calculateScore(
            $invalid,
            $maximumCorrect,
        );
        $classification = $this->domain->classifier->classify($score, $this->domain->config->read());

        return [
            'validAnswers' => $valid,
            'invalidAnswers' => $invalid,
            'score' => $score,
            'status' => $classification->passed ? 'passed' : 'failed',
            'locked' => $classification->locked,
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
        if ($attemptId <= 0) {
            throw new \RuntimeException('Attempt insert failed.');
        }

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
        if ($attempt !== null) {
            return $attempt;
        }

        return new AttemptRecord(
            $attemptId,
            $attemptRow['userId'],
            $attemptRow['testId'],
            $attemptRow['testVersion'],
            $outcome['validAnswers'],
            $outcome['invalidAnswers'],
            $outcome['score'],
            $outcome['status'],
            $attemptRow['dateFinished'],
        );
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
    private function assertQuestionLinesBelongToTest(array $catalogQuestions, array $lines): void
    {
        if ($catalogQuestions === []) {
            throw new \InvalidArgumentException('Test has no questions.');
        }

        $catalogIds = [];
        foreach ($catalogQuestions as $question) {
            $catalogIds[$question->id] = true;
        }

        $providedIds = array_column($lines, 'questionId');
        if (count($providedIds) !== count(array_unique($providedIds))) {
            throw new \InvalidArgumentException('Duplicate question in attempt payload.');
        }

        foreach ($providedIds as $questionId) {
            if (!isset($catalogIds[(int) $questionId])) {
                throw new \InvalidArgumentException(
                    "Question {$questionId} does not belong to this test."
                );
            }
        }
    }
}
