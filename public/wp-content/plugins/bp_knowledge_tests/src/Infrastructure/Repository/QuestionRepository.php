<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use BpKnowledgeTests\Domain\Record\QuestionRecord;
use BpKnowledgeTests\Infrastructure\DatabaseClock;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class QuestionRepository
{
    private const TABLE = 'KnowledgeTestQuestions';

    public function __construct(
        private DBWorker $db,
        private DBUtilities $dbUtilities,
        private DatabaseClock $clock,
        private RecordMapper $mapper,
    ) {
    }

    public function findById(int $id): ?QuestionRecord
    {
        $row = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'id = %d',
            'values' => [$id],
        ], '*');

        return is_object($row) ? $this->mapper->toQuestionRecord($row) : null;
    }

    /**
     * @return list<QuestionRecord>
     */
    public function listByTestId(int $testId): array
    {
        $rows = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'test_id = %d',
            'values' => [$testId],
            'OrderBy' => 'id ASC',
        ], '*');

        $records = [];
        foreach ($this->mapper->rows($rows) as $row) {
            $records[] = $this->mapper->toQuestionRecord($row);
        }

        return $records;
    }

    public function create(
        int $testId,
        string $question,
        string $explanation,
        string $reference,
    ): int {
        $now = $this->clock->now();

        return $this->db->insertAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'test_id' => $testId,
                    'question' => $question,
                    'explanation' => $explanation,
                    'reference' => $reference,
                    'date_added' => $now,
                    'date_modified' => $now,
                ],
                ['%d', '%s', '%s', '%s', '%s', '%s']
            )
        );
    }

    public function update(
        int $id,
        string $question,
        string $explanation,
        string $reference,
    ): bool {
        return $this->db->updateAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'question' => $question,
                    'explanation' => $explanation,
                    'reference' => $reference,
                    'date_modified' => $this->clock->now(),
                ],
                ['%s', '%s', '%s', '%s']
            ),
            $this->dbUtilities->packageWriteColumns(['id' => $id], ['%d'])
        );
    }

    public function delete(int $id): bool
    {
        return $this->db->deleteAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(['id' => $id], ['%d'])
        );
    }

    public function testIdForQuestion(int $questionId): ?int
    {
        $value = $this->db->selectVarSimple(self::TABLE, 'id', $questionId, 'test_id');

        return $value !== null ? (int) $value : null;
    }
}
