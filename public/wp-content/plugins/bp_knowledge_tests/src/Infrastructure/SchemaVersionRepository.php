<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure;

final class SchemaVersionRepository
{
    private const OPTION_KEY = 'bp_knowledge_tests_schema_version';

    public const TARGET_VERSION = 4;

    public function current(): int
    {
        return (int) get_option(self::OPTION_KEY, 0);
    }

    public function markUpToDate(): void
    {
        update_option(self::OPTION_KEY, self::TARGET_VERSION, false);
    }
}
