<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure;

interface ClockInterface
{
    public function now(): string;
}
