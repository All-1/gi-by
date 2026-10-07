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
}
