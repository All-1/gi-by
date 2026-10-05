<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain;

final class AttemptClassification
{
    public function __construct(
        public readonly float $score,
        public readonly bool $passed,
        public readonly string $medal,
        public readonly bool $locked,
    ) {
    }
}
