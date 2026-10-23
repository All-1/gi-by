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

    /** @return array{name: string, value: string}|null */
    public function findMedalById(int $medalId): ?array
    {
        $row = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'id = %d',
            'values' => [$medalId],
        ], 'name, value');
        if (!is_object($row)) {
            return null;
        }

        return ['name' => (string) $row->name, 'value' => (string) $row->value];
    }
}
