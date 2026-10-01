<?php

declare(strict_types=1);

namespace BpKnowledgeTests;

final class Plugin
{
    public const VERSION = '0.1.0';

    public function boot(): void
    {
        // Phase 1: runtime hooks register here in later work.
    }

    public function version(): string
    {
        return self::VERSION;
    }
}
