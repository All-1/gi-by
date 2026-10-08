<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use PersonalAccount\Workers\DBWorker;

final class AchievementRepository
{
    private const TABLE = 'KnowledgeTestAchievements';

    public function __construct(private DBWorker $db)
    {
    }

    public function findIdByValue(string $value): ?int
    {
        $row = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'value = %s',
            'values' => [$value],
        ], 'id');

        return is_object($row) ? (int) $row->id : null;
    }
}
