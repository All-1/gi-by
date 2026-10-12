<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Domain\NotificationSettings;
use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Domain\Record\NotificationRecord;

final class NotificationRetakePolicy
{
    public function blocksRetake(
        ?AttemptRecord $latestAttempt,
        ?NotificationRecord $newTestRow,
        ?NotificationRecord $failedTestRow,
        NotificationSettings $settings,
    ): bool {
        if ($this->newTestBlocks($latestAttempt, $newTestRow, $settings)) {
            return true;
        }

        return $this->failedTestBlocks($latestAttempt, $failedTestRow, $settings);
    }

    private function newTestBlocks(
        ?AttemptRecord $latestAttempt,
        ?NotificationRecord $row,
        NotificationSettings $settings,
    ): bool {
        if ($row === null || !$this->limitReached($row, $settings->newTestDismissLimit, $settings->newTestMode)) {
            return false;
        }

        return $latestAttempt === null || $latestAttempt->status !== 'passed';
    }

    private function failedTestBlocks(
        ?AttemptRecord $latestAttempt,
        ?NotificationRecord $row,
        NotificationSettings $settings,
    ): bool {
        if ($latestAttempt === null || !$latestAttempt->isFailed()) {
            return false;
        }

        return $row !== null
            && $this->limitReached($row, $settings->failedTestDismissLimit, $settings->failedTestMode);
    }

    private function limitReached(NotificationRecord $row, int $limit, string $mode): bool
    {
        return $mode === NotificationSettings::MODE_BLOCK && $row->dismissCount >= $limit;
    }
}
