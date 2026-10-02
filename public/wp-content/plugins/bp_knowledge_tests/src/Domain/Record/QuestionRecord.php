<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain\Record;

final class QuestionRecord
{
    public function __construct(
        public int $id,
        public int $testId,
        public string $question,
        public string $explanation,
        public string $reference,
        public string $dateAdded,
        public string $dateModified,
    ) {
    }
}
