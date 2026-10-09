<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain;

final class RetakeEligibility
{
    public const REASON_RETAKE_DELAY = 'retake_delay';
    public const REASON_PENDING_MATERIALS = 'pending_materials';
    public const REASON_TEST_LOCKED = 'test_locked';

    /**
     * @param list<string> $reasons
     */
    public function __construct(
        public readonly bool $canStart,
        public readonly array $reasons,
    ) {
    }
}
