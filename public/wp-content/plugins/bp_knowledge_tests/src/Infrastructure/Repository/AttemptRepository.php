<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class AttemptRepository
{
    private const TABLE = 'KnowledgeTestAttempts';

    public function __construct(
        private DBWorker $db,
        private DBUtilities $dbUtilities,
        private RecordMapper $mapper,
    ) {
    }

    public function findById(int $id): ?AttemptRecord
    {
        $row = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'id = %d',
            'values' => [$id],
        ], '*');

        return is_object($row) ? $this->mapper->toAttemptRecord($row) : null;
    }

    public function findLatestByUserAndTest(int $userId, int $testId): ?AttemptRecord
    {
        $table = $this->db->getPathTable(self::TABLE);
        $rows = $this->db->getRawSQL(
            "SELECT * FROM `{$table}` WHERE user_id = %d AND test_id = %d ORDER BY date_finished DESC LIMIT 1",
            [$userId, $testId],
        );
        foreach ($this->mapper->rows($rows) as $row) {
            return $this->mapper->toAttemptRecord($row);
        }

        return null;
    }

    public function insert(
        int $userId,
        int $testId,
        int $testVersion,
        int $validAnswers,
        int $invalidAnswers,
        float $score,
        string $status,
        string $dateFinished,
    ): int {
        return $this->db->insertAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'user_id' => $userId,
                    'test_id' => $testId,
                    'test_version' => $testVersion,
                    'valid_answers' => $validAnswers,
                    'invalid_answers' => $invalidAnswers,
                    'score' => $score,
                    'status' => $status,
                    'date_finished' => $dateFinished,
                ],
                ['%d', '%d', '%d', '%d', '%d', '%f', '%s', '%s']
            )
        );
    }

    public function updateStatus(int $attemptId, string $status): void
    {
        $this->db->updateAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(['status' => $status], ['%s']),
            $this->dbUtilities->packageWriteColumns(['id' => $attemptId], ['%d']),
        );
    }

    public function markPassedAsOutdatedForTest(int $testId): void
    {
        $table = $this->db->getPathTable(self::TABLE);
        $this->db->getRawSQL(
            "UPDATE `{$table}` SET status = %s WHERE test_id = %d AND status = %s",
            ['outdated', $testId, 'passed'],
        );
    }

    /**
     * @return list<int>
     */
    public function listDistinctTestIdsForUser(int $userId): array
    {
        $table = $this->db->getPathTable(self::TABLE);
        $rows = $this->db->getRawSQL(
            "SELECT DISTINCT test_id FROM `{$table}` WHERE user_id = %d",
            [$userId],
        );

        $ids = [];
        foreach ($this->mapper->rows($rows) as $row) {
            $ids[] = (int) $row->test_id;
        }

        return $ids;
    }
}
