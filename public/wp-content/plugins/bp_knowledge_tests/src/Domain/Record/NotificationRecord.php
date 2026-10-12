<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain\Record;

final class NotificationRecord
{
    public const TYPE_NEW_TEST = 'new_test';
    public const TYPE_FAILED_TEST = 'failed_test';

    public function __construct(
        public int $id,
        public int $userId,
        public int $testId,
        public string $notificationType,
        public int $dismissCount,
    ) {
    }
}
