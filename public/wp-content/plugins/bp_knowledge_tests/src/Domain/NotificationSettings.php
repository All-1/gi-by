<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain;

final class NotificationSettings
{
    public const MODE_BLOCK = 'block';
    public const MODE_BOTTOM = 'bottom';

    public function __construct(
        public readonly int $newTestDismissLimit,
        public readonly int $failedTestDismissLimit,
        public readonly string $newTestMode,
        public readonly string $failedTestMode,
    ) {
    }
}
