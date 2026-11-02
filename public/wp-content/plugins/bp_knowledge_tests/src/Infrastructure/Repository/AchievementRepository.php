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

        return SelectScalar::intId($row);
    }

    /** @return array{name: string, value: string}|null */
    public function findMedalById(int $medalId): ?array
    {
        $row = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'id = %d',
            'values' => [$medalId],
        ], '*');
        if (is_object($row)) {
            $name = $row->name ?? null;
            $value = $row->value ?? null;
        } elseif (is_array($row)) {
            $name = $row['name'] ?? null;
            $value = $row['value'] ?? null;
        } else {
            return null;
        }
        if ($name === null || $value === null) {
            return null;
        }

        return ['name' => (string) $name, 'value' => (string) $value];
    }
}
