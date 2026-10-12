<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use BpKnowledgeTests\Domain\Record\NotificationRecord;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class NotificationRepository
{
    private const TABLE = 'KnowledgeTestNotifications';

    public function __construct(
        private DBWorker $db,
        private DBUtilities $dbUtilities,
        private RecordMapper $mapper,
    ) {
    }

    public function findByUserTestAndType(int $userId, int $testId, string $type): ?NotificationRecord
    {
        $row = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'user_id = %d AND test_id = %d AND notification_type = %s',
            'values' => [$userId, $testId, $type],
        ], '*');

        return is_object($row) ? $this->mapper->toNotificationRecord($row) : null;
    }

    public function resetDismissCount(int $userId, int $testId, string $type): void
    {
        if ($this->findByUserTestAndType($userId, $testId, $type) !== null) {
            $this->updateDismissCount($userId, $testId, $type, 0);

            return;
        }

        $this->db->insertAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'user_id' => $userId,
                    'test_id' => $testId,
                    'notification_type' => $type,
                    'dismiss_count' => 0,
                ],
                ['%d', '%d', '%s', '%d'],
            ),
        );
    }

    public function incrementDismissCount(int $userId, int $testId, string $type): void
    {
        $row = $this->findByUserTestAndType($userId, $testId, $type);
        if ($row === null) {
            $this->resetDismissCount($userId, $testId, $type);
            $row = $this->findByUserTestAndType($userId, $testId, $type);
        }
        if ($row === null) {
            return;
        }

        $this->updateDismissCount($userId, $testId, $type, $row->dismissCount + 1);
    }

    public function deleteByUserTestAndType(int $userId, int $testId, string $type): void
    {
        $this->db->deleteAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'user_id' => $userId,
                    'test_id' => $testId,
                    'notification_type' => $type,
                ],
                ['%d', '%d', '%s'],
            ),
        );
    }

    private function updateDismissCount(int $userId, int $testId, string $type, int $count): void
    {
        $this->db->updateAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(['dismiss_count' => $count], ['%d']),
            $this->dbUtilities->packageWriteColumns(
                [
                    'user_id' => $userId,
                    'test_id' => $testId,
                    'notification_type' => $type,
                ],
                ['%d', '%d', '%s'],
            ),
        );
    }
}
