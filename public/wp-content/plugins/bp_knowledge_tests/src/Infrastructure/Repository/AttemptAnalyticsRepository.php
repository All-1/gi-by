<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use PersonalAccount\Workers\DBWorker;

final class AttemptAnalyticsRepository
{
    private const TABLE_ATTEMPTS = 'KnowledgeTestAttempts';

    public function __construct(
        private DBWorker $db,
        private RecordMapper $mapper,
    ) {
    }

    /**
     * @return array<string, int|float>|null
     */
    public function testAttemptSummary(int $testId): ?array
    {
        $attempts = $this->db->getPathTable(self::TABLE_ATTEMPTS);
        $rows = $this->mapper->rows($this->db->getRawSQL(
            "SELECT COUNT(1) AS completed,
                SUM(CASE WHEN status = 'passed' THEN 1 ELSE 0 END) AS passed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed,
                SUM(CASE WHEN status = 'outdated' THEN 1 ELSE 0 END) AS outdated,
                AVG(score) AS average_score
            FROM `{$attempts}` WHERE test_id = %d",
            [$testId],
        ));
        if ($rows === []) {
            return null;
        }

        $row = $rows[0];

        return [
            'completed' => (int) ($row->completed ?? 0),
            'passed' => (int) ($row->passed ?? 0),
            'failed' => (int) ($row->failed ?? 0),
            'outdated' => (int) ($row->outdated ?? 0),
            'averageScore' => round((float) ($row->average_score ?? 0), 2),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function scoreDistributionForTest(int $testId): array
    {
        $attempts = $this->db->getPathTable(self::TABLE_ATTEMPTS);
        $rows = $this->mapper->rows($this->db->getRawSQL(
            "SELECT
                SUM(CASE WHEN score < 70 THEN 1 ELSE 0 END) AS under_70,
                SUM(CASE WHEN score >= 70 AND score < 80 THEN 1 ELSE 0 END) AS from_70_to_79,
                SUM(CASE WHEN score >= 80 AND score < 90 THEN 1 ELSE 0 END) AS from_80_to_89,
                SUM(CASE WHEN score >= 90 THEN 1 ELSE 0 END) AS from_90_plus
            FROM `{$attempts}` WHERE test_id = %d",
            [$testId],
        ));
        if ($rows === []) {
            return [
                'under70' => 0,
                'from70To79' => 0,
                'from80To89' => 0,
                'from90Plus' => 0,
            ];
        }

        $row = $rows[0];

        return [
            'under70' => (int) ($row->under_70 ?? 0),
            'from70To79' => (int) ($row->from_70_to_79 ?? 0),
            'from80To89' => (int) ($row->from_80_to_89 ?? 0),
            'from90Plus' => (int) ($row->from_90_plus ?? 0),
        ];
    }

    /**
     * @return list<object>
     */
    public function questionAggregateRows(int $testId): array
    {
        $questions = $this->db->getPathTable('KnowledgeTestQuestions');
        $attemptQuestions = $this->db->getPathTable('KnowledgeTestAttemptQuestions');
        $attempts = $this->db->getPathTable(self::TABLE_ATTEMPTS);

        return $this->mapper->rows($this->db->getRawSQL(
            "SELECT q.id AS question_id, q.question AS question_text,
                COUNT(aq.id) AS times_presented,
                COALESCE(SUM(aq.right_answers), 0) AS total_right,
                COALESCE(SUM(aq.failed_answers), 0) AS total_failed
            FROM `{$questions}` q
            LEFT JOIN `{$attemptQuestions}` aq ON aq.question_id = q.id
            LEFT JOIN `{$attempts}` att ON att.id = aq.attempt_id AND att.test_id = q.test_id
            WHERE q.test_id = %d
            GROUP BY q.id, q.question
            ORDER BY q.id ASC",
            [$testId],
        ));
    }
}
