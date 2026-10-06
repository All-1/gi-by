<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain\Record;

/**
 * One row in gi_new_test_answers — an answer **option** (choice text) for a question, not a user's attempt selection.
 */
final class AnswerRecord
{
    public function __construct(
        public int $id,
        public int $questionId,
        public string $answer,
        public bool $isCorrect,
        public string $dateAdded,
        public string $dateModified,
    ) {
    }
}
