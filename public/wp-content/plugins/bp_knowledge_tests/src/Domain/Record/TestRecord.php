<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain\Record;

final class TestRecord
{
    public function __construct(
        public int $id,
        public string $title,
        public int $version,
        public ?string $achievementArea,
        public string $dateCreation,
        public string $dateModified,
    ) {
    }
}
