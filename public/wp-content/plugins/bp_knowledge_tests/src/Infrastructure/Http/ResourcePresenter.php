<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Http;

use BpKnowledgeTests\Domain\Record\AnswerRecord;
use BpKnowledgeTests\Domain\Record\QuestionRecord;
use BpKnowledgeTests\Domain\Record\TestRecord;

final class ResourcePresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function test(TestRecord $test): array
    {
        return [
            'id' => $test->id,
            'title' => $test->title,
            'version' => $test->version,
            'achievementArea' => $test->achievementArea,
            'dateCreation' => $test->dateCreation,
            'dateModified' => $test->dateModified,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function question(QuestionRecord $question): array
    {
        return [
            'id' => $question->id,
            'testId' => $question->testId,
            'question' => $question->question,
            'explanation' => $question->explanation,
            'reference' => $question->reference,
            'dateAdded' => $question->dateAdded,
            'dateModified' => $question->dateModified,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function answer(AnswerRecord $answer): array
    {
        return [
            'id' => $answer->id,
            'questionId' => $answer->questionId,
            'answer' => $answer->answer,
            'isCorrect' => $answer->isCorrect,
            'dateAdded' => $answer->dateAdded,
            'dateModified' => $answer->dateModified,
        ];
    }
}
