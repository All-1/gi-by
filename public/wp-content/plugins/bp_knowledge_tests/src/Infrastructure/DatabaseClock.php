<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure;

final class DatabaseClock
{
    public function now(): string
    {
        if (function_exists('current_time')) {
            return current_time('mysql');
        }

        return gmdate('Y-m-d H:i:s');
    }
}
