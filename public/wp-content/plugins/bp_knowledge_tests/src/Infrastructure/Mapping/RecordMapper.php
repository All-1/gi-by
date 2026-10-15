<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Mapping;

use BpKnowledgeTests\Domain\Record\AnswerRecord;
use BpKnowledgeTests\Domain\Record\AttemptQuestionRecord;
use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Domain\Record\FinishedExplanationRecord;
use BpKnowledgeTests\Domain\Record\NotificationRecord;
use BpKnowledgeTests\Domain\Record\QuestionRecord;
use BpKnowledgeTests\Domain\Record\TestRecord;

final class RecordMapper
{
    /**
     * DBWorker returns either stdClass rows or camelCase arrays, and unwraps a single row.
     *
     * @return list<object>
     */
    public function rows(mixed $result): array
    {
        if ($result === null || $result === false || $result === []) {
            return [];
        }

        if (is_object($result)) {
            return [$result];
        }

        if (!is_array($result)) {
            return [];
        }

        if ($this->isAssociativeRow($result)) {
            return [$this->toRowObject($result)];
        }

        $rows = [];
        foreach ($result as $item) {
            if (is_object($item)) {
                $rows[] = $item;
                continue;
            }
            if (is_array($item)) {
                $rows[] = $this->toRowObject($item);
            }
        }

        return $rows;
    }

    /**
     * @param array<mixed> $row
     */
    private function isAssociativeRow(array $row): bool
    {
        return array_keys($row) !== range(0, count($row) - 1);
    }

    /**
     * @param array<mixed> $row
     */
    private function toRowObject(array $row): object
    {
        $snake = [];
        foreach ($row as $key => $value) {
            $name = is_string($key) ? $this->camelToSnake($key) : (string) $key;
            $snake[$name] = $value;
        }

        return (object) $snake;
    }

    private function camelToSnake(string $key): string
    {
        return strtolower((string) preg_replace('/[A-Z]/', '_$0', $key));
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
            (bool) (int) ($row->is_correct ?? 0),
            (string) $row->date_added,
            (string) $row->date_modified,
        );
    }

    public function toAttemptRecord(object $row): AttemptRecord
    {
        return new AttemptRecord(
            (int) $row->id,
            (int) $row->user_id,
            (int) $row->test_id,
            (int) $row->test_version,
            (int) $row->valid_answers,
            (int) $row->invalid_answers,
            (float) $row->score,
            (string) $row->status,
            (string) $row->date_finished,
        );
    }

    public function toAttemptQuestionRecord(object $row): AttemptQuestionRecord
    {
        return new AttemptQuestionRecord(
            (int) $row->id,
            (int) $row->user_id,
            (int) $row->attempt_id,
            (int) $row->question_id,
            (int) $row->right_answers,
            (int) $row->failed_answers,
            (string) $row->date_finished,
        );
    }

    public function toFinishedExplanationRecord(object $row): FinishedExplanationRecord
    {
        return new FinishedExplanationRecord(
            (int) $row->id,
            (int) $row->attempt_id,
            (int) $row->question_id,
            (int) $row->user_id,
            (string) $row->explanation,
            (string) $row->reference,
        );
    }

    public function toNotificationRecord(object $row): NotificationRecord
    {
        return new NotificationRecord(
            (int) $row->id,
            (int) $row->user_id,
            (int) $row->test_id,
            (string) ($row->notification_type ?? ''),
            (int) $row->dismiss_count,
        );
    }
}
