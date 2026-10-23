<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Domain\NotificationPresentation;
use BpKnowledgeTests\Domain\NotificationSettings;
use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Domain\Record\NotificationRecord;
use BpKnowledgeTests\Domain\Record\TestRecord;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\NotificationRepository;
use BpKnowledgeTests\Services\CatalogServices;
use BpKnowledgeTests\Services\DomainServices;

/** PA notification list/dismiss + lazy `new_test` rows (spec §3.1–§3.2). */
final class NotificationUiQuery
{
    public function __construct(
        private CatalogServices $catalog,
        private AttemptRepository $attempts,
        private NotificationRepository $notifications,
        private DomainServices $domain,
        private MaterialsService $materials,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listActive(int $userId): array
    {
        $this->syncNewTestRowsForUser($userId);
        $settings = $this->domain->config->notificationSettings();
        $items = [];
        foreach ($this->catalog->tests->listAll() as $test) {
            $newItem = $this->buildItem($userId, $test, NotificationRecord::TYPE_NEW_TEST, $settings);
            if ($newItem !== null) {
                $items[] = $newItem;
            }
            $failedItem = $this->buildItem($userId, $test, NotificationRecord::TYPE_FAILED_TEST, $settings);
            if ($failedItem !== null) {
                $items[] = $failedItem;
            }
        }

        return $items;
    }

    public function dismiss(int $userId, int $testId, string $notificationType): bool
    {
        if (!$this->isKnownType($notificationType)) {
            return false;
        }
        $this->notifications->incrementDismissCount($userId, $testId, $notificationType);

        return true;
    }

    private function syncNewTestRowsForUser(int $userId): void
    {
        $ordered = $this->catalog->tests->listAll();
        foreach ($ordered as $test) {
            if (!$this->previousTestsCompleted($userId, $test, $ordered)) {
                continue;
            }
            if ($this->attempts->findLatestByUserAndTest($userId, $test->id) !== null) {
                continue;
            }
            $this->notifications->resetDismissCount(
                $userId,
                $test->id,
                NotificationRecord::TYPE_NEW_TEST,
            );
        }
    }

    /**
     * @param list<TestRecord> $ordered
     */
    private function previousTestsCompleted(int $userId, TestRecord $test, array $ordered): bool
    {
        foreach ($ordered as $prior) {
            if ($prior->id === $test->id) {
                return true;
            }
            if ($this->attempts->findLatestByUserAndTest($userId, $prior->id) === null) {
                return false;
            }
        }

        return false;
    }

    private function buildItem(
        int $userId,
        TestRecord $test,
        string $notificationType,
        NotificationSettings $settings,
    ): ?array {
        $latest = $this->attempts->findLatestByUserAndTest($userId, $test->id);
        if (!$this->typeApplies($notificationType, $latest)) {
            return null;
        }

        $row = $this->notifications->findByUserTestAndType($userId, $test->id, $notificationType);
        if ($row === null) {
            return null;
        }

        return $this->itemPayload($userId, $test, $notificationType, $settings, $row->dismissCount);
    }

    /** @return array<string, mixed> */
    private function itemPayload(
        int $userId,
        TestRecord $test,
        string $notificationType,
        NotificationSettings $settings,
        int $dismissCount,
    ): array {
        [$limit, $mode] = $this->limitAndMode($notificationType, $settings);

        return [
            'testId' => $test->id,
            'testTitle' => $test->title,
            'notificationType' => $notificationType,
            'presentation' => NotificationPresentation::resolve($dismissCount, $limit, $mode),
            'dismissCount' => $dismissCount,
            'dismissLimit' => $limit,
            'materialsAvailable' => $notificationType === NotificationRecord::TYPE_FAILED_TEST
                && $this->materials->hasPendingMaterials($userId, $test->id),
        ];
    }

    /** @return array{0: int, 1: string} */
    private function limitAndMode(string $notificationType, NotificationSettings $settings): array
    {
        if ($notificationType === NotificationRecord::TYPE_NEW_TEST) {
            return [$settings->newTestDismissLimit, $settings->newTestMode];
        }

        return [$settings->failedTestDismissLimit, $settings->failedTestMode];
    }

    private function typeApplies(string $notificationType, ?AttemptRecord $latest): bool
    {
        if ($notificationType === NotificationRecord::TYPE_NEW_TEST) {
            return $latest === null;
        }

        return $latest !== null && $latest->isFailed();
    }

    private function isKnownType(string $notificationType): bool
    {
        return in_array(
            $notificationType,
            [NotificationRecord::TYPE_NEW_TEST, NotificationRecord::TYPE_FAILED_TEST],
            true,
        );
    }
}
