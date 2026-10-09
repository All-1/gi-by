<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

final class RetakeEligibilityContext
{
    public function __construct(
        public readonly bool $hasPendingMaterials,
        public readonly int $retakeDelayDays,
        public readonly string $now,
    ) {
    }
}
