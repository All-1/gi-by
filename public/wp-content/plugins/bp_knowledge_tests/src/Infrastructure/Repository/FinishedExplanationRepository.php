<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use BpKnowledgeTests\Domain\Record\FinishedExplanationRecord;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class FinishedExplanationRepository
{
    private const TABLE = 'KnowledgeTestFinishedExplanations';

    private const TABLE_ATTEMPTS = 'KnowledgeTestAttempts';

    public function __construct(
        private DBWorker $db,
        private DBUtilities $dbUtilities,
        private RecordMapper $mapper,
    ) {
    }

    /**
     * @return list<FinishedExplanationRecord>
     */
    public function listByAttemptId(int $attemptId): array
    {
        $rows = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'attempt_id = %d',
            'values' => [$attemptId],
            'OrderBy' => 'id ASC',
        ], '*');

        $records = [];
        foreach ($this->mapper->rows($rows) as $row) {
            $records[] = $this->mapper->toFinishedExplanationRecord($row);
        }

        return $records;
    }

    /**
     * @return list<FinishedExplanationRecord>
     */
    public function listPendingForUserAndTest(int $userId, int $testId): array
    {
        $explanations = $this->db->getPathTable(self::TABLE);
        $attempts = $this->db->getPathTable(self::TABLE_ATTEMPTS);
        $rows = $this->db->getRawSQL(
            "SELECT e.* FROM `{$explanations}` e
            INNER JOIN `{$attempts}` a ON a.id = e.attempt_id
            WHERE e.user_id = %d AND a.test_id = %d
            ORDER BY e.id ASC",
            [$userId, $testId]
        );

        $records = [];
        foreach ($this->mapper->rows($rows) as $row) {
            $records[] = $this->mapper->toFinishedExplanationRecord($row);
        }

        return $records;
    }

    public function hasPendingForUserAndTest(int $userId, int $testId): bool
    {
        return $this->listPendingForUserAndTest($userId, $testId) !== [];
    }

    public function insert(
        int $attemptId,
        int $questionId,
        int $userId,
        string $explanation,
        string $reference,
    ): int {
        return $this->db->insertAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'attempt_id' => $attemptId,
                    'question_id' => $questionId,
                    'user_id' => $userId,
                    'explanation' => $explanation,
                    'reference' => $reference,
                ],
                ['%d', '%d', '%d', '%s', '%s']
            )
        );
    }

    public function deleteByAttemptId(int $attemptId): void
    {
        $this->db->deleteAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(['attempt_id' => $attemptId], ['%d'])
        );
    }
}
