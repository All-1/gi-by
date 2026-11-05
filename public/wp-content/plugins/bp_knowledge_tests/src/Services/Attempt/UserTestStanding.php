<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Domain\Record\AttemptRecord;

final class UserTestStanding
{
    public function __construct(
        public ?AttemptRecord $latest,
        public ?int $medalId,
        public ?string $medalTier,
        public ?string $medalRankName,
    ) {
    }
}
