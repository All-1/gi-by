<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use BpKnowledgeTests\Domain\Record\AnswerRecord;
use BpKnowledgeTests\Infrastructure\DatabaseClock;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class AnswerRepository
{
    private const TABLE = 'gi_new_test_answers';

    public function __construct(
        private DBWorker $db,
        private DBUtilities $dbUtilities,
        private DatabaseClock $clock,
        private RecordMapper $mapper,
    ) {
    }

    public function findById(int $id): ?AnswerRecord
    {
        $row = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'id = %d',
            'values' => [$id],
        ], '*');

        return is_object($row) ? $this->mapper->toAnswerRecord($row) : null;
    }

    /**
     * @return list<AnswerRecord>
     */
    public function listByQuestionId(int $questionId): array
    {
        $rows = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'question_id = %d',
            'values' => [$questionId],
            'OrderBy' => 'id ASC',
        ], '*');

        $records = [];
        foreach ($this->mapper->rows($rows) as $row) {
            $records[] = $this->mapper->toAnswerRecord($row);
        }

        return $records;
    }

    public function create(int $questionId, string $answerText): int
    {
        $now = $this->clock->now();

        return $this->db->insertAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'question_id' => $questionId,
                    'answer' => $answerText,
                    'date_added' => $now,
                    'date_modified' => $now,
                ],
                ['%d', '%s', '%s', '%s']
            )
        );
    }

    public function update(int $id, string $answerText): bool
    {
        return $this->db->updateAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'answer' => $answerText,
                    'date_modified' => $this->clock->now(),
                ],
                ['%s', '%s']
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

    public function questionIdForAnswer(int $answerId): ?int
    {
        $value = $this->db->selectVarSimple(self::TABLE, 'id', $answerId, 'question_id');

        return $value !== null ? (int) $value : null;
    }
}
