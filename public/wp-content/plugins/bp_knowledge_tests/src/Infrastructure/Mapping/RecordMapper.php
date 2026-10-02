<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Mapping;

use BpKnowledgeTests\Domain\Record\AnswerRecord;
use BpKnowledgeTests\Domain\Record\QuestionRecord;
use BpKnowledgeTests\Domain\Record\TestRecord;

final class RecordMapper
{
    /**
     * @return list<object>
     */
    public function rows(mixed $result): array
    {
        if ($result === null) {
            return [];
        }

        return is_array($result) ? $result : [$result];
    }

    public function toTestRecord(object $row): TestRecord
    {
        return new TestRecord(
            (int) $row->id,
            (string) $row->title,
            (int) $row->version,
            $row->achievement_area !== null ? (string) $row->achievement_area : null,
            (string) $row->date_creation,
            (string) $row->date_modified,
        );
    }

    public function toQuestionRecord(object $row): QuestionRecord
    {
        return new QuestionRecord(
            (int) $row->id,
            (int) $row->test_id,
            (string) $row->question,
            (string) $row->explanation,
            (string) $row->reference,
            (string) $row->date_added,
            (string) $row->date_modified,
        );
    }

    public function toAnswerRecord(object $row): AnswerRecord
    {
        return new AnswerRecord(
            (int) $row->id,
            (int) $row->question_id,
            (string) $row->answer,
            (string) $row->date_added,
            (string) $row->date_modified,
        );
    }
}
