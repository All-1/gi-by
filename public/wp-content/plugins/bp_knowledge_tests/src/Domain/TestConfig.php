<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain;

final class TestConfig
{
    public function __construct(
        public readonly float $bronzeThreshold,
        public readonly float $silverThreshold,
        public readonly float $goldThreshold,
        public readonly float $lockThreshold,
    ) {
    }
}
