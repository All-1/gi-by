<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use BpKnowledgeTests\Domain\Record\AttemptQuestionRecord;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class AttemptQuestionRepository
{
    private const TABLE = 'KnowledgeTestAttemptQuestions';

    public function __construct(
        private DBWorker $db,
        private DBUtilities $dbUtilities,
        private RecordMapper $mapper,
    ) {
    }

    /**
     * @return list<AttemptQuestionRecord>
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
            $records[] = $this->mapper->toAttemptQuestionRecord($row);
        }

        return $records;
    }

    public function insert(
        int $userId,
        int $attemptId,
        int $questionId,
        int $rightAnswers,
        int $failedAnswers,
        string $dateFinished,
    ): int {
        return $this->db->insertAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'user_id' => $userId,
                    'attempt_id' => $attemptId,
                    'question_id' => $questionId,
                    'right_answers' => $rightAnswers,
                    'failed_answers' => $failedAnswers,
                    'date_finished' => $dateFinished,
                ],
                ['%d', '%d', '%d', '%d', '%d', '%s']
            )
        );
    }
}
