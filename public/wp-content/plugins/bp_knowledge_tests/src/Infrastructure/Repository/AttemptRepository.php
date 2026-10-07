<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use PersonalAccount\Workers\DBWorker;

final class AttemptRepository
{
    private const TABLE = 'KnowledgeTestAttempts';

    public function __construct(
        private DBWorker $db,
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
}
