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
    private const TABLE = 'KnowledgeTestAnswers';

    private const TABLE_QUESTIONS = 'KnowledgeTestQuestions';

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

    public function create(int $questionId, string $answerText, bool $isCorrect = false): int
    {
        $now = $this->clock->now();

        return $this->db->insertAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'question_id' => $questionId,
                    'answer' => $answerText,
                    'is_correct' => $isCorrect ? 1 : 0,
                    'date_added' => $now,
                    'date_modified' => $now,
                ],
                ['%d', '%s', '%d', '%s', '%s']
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

    public function setIsCorrect(int $answerId, bool $isCorrect): bool
    {
        return $this->db->updateAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'is_correct' => $isCorrect ? 1 : 0,
                    'date_modified' => $this->clock->now(),
                ],
                ['%d', '%s']
            ),
            $this->dbUtilities->packageWriteColumns(['id' => $answerId], ['%d'])
        );
    }

    public function clearCorrectFlagsForQuestion(int $questionId): void
    {
        $this->db->updateAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'is_correct' => 0,
                    'date_modified' => $this->clock->now(),
                ],
                ['%d', '%s']
            ),
            $this->dbUtilities->packageWriteColumns(['question_id' => $questionId], ['%d'])
        );
    }

    /**
     * @return list<int>
     */
    public function listCorrectAnswerIdsForQuestion(int $questionId): array
    {
        $rows = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'question_id = %d AND is_correct = 1',
            'values' => [$questionId],
            'OrderBy' => 'id ASC',
        ], 'id');

        $ids = [];
        foreach ($this->mapper->rows($rows) as $row) {
            $ids[] = (int) $row->id;
        }

        return $ids;
    }

    public function countCorrectForTest(int $testId): int
    {
        $answers = $this->db->getPathTable(self::TABLE);
        $questions = $this->db->getPathTable(self::TABLE_QUESTIONS);
        $rows = $this->db->getRawSQL(
            "SELECT COUNT(1) AS cnt FROM `{$answers}` a
            INNER JOIN `{$questions}` q ON q.id = a.question_id
            WHERE q.test_id = %d AND a.is_correct = 1",
            [$testId]
        );
        if (is_array($rows) && isset($rows[0]) && is_object($rows[0]) && isset($rows[0]->cnt)) {
            return (int) $rows[0]->cnt;
        }

        return 0;
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
