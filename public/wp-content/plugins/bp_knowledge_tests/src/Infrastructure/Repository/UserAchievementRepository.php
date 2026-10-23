<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class UserAchievementRepository
{
    private const TABLE = 'KnowledgeTestUserAchievements';

    public function __construct(
        private DBWorker $db,
        private DBUtilities $dbUtilities,
    ) {
    }

    public function upsertForUserAndTest(int $userId, int $testId, int $medalId): void
    {
        $existingId = $this->findRowId($userId, $testId);
        if ($existingId !== null) {
            $this->db->updateAssoc(
                self::TABLE,
                $this->dbUtilities->packageWriteColumns(['medal_id' => $medalId], ['%d']),
                $this->dbUtilities->packageWriteColumns(['id' => $existingId], ['%d']),
            );

            return;
        }

        $this->db->insertAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'user_id' => $userId,
                    'test_id' => $testId,
                    'medal_id' => $medalId,
                ],
                ['%d', '%d', '%d'],
            ),
        );
    }

    public function deleteForUserAndTest(int $userId, int $testId): void
    {
        $this->db->deleteAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                ['user_id' => $userId, 'test_id' => $testId],
                ['%d', '%d'],
            ),
        );
    }

    public function deleteAllForTest(int $testId): void
    {
        $this->db->deleteAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(['test_id' => $testId], ['%d']),
        );
    }

    public function findMedalIdForUserAndTest(int $userId, int $testId): ?int
    {
        $row = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'user_id = %d AND test_id = %d',
            'values' => [$userId, $testId],
        ], 'medal_id');

        return is_object($row) && isset($row->medal_id) ? (int) $row->medal_id : null;
    }

    /** @return list<array{testId: int, medalId: int}> */
    public function listMedalRowsForUser(int $userId): array
    {
        $rows = $this->db->selectResultsFromDBU('gi_new_test_user_achievements', 'user_id', $userId);
        if ($rows === null) {
            return [];
        }
        $pairs = [];
        foreach ($rows as $row) {
            $pairs[] = [
                'testId' => (int) $row->test_id,
                'medalId' => (int) $row->medal_id,
            ];
        }

        return $pairs;
    }

    private function findRowId(int $userId, int $testId): ?int
    {
        $row = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'user_id = %d AND test_id = %d',
            'values' => [$userId, $testId],
        ], 'id');

        return is_object($row) ? (int) $row->id : null;
    }
}
