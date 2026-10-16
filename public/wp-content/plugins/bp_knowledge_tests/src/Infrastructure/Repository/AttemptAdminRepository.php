<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use PersonalAccount\Workers\DBWorker;

final class AttemptAdminRepository
{
    private const TABLE_ATTEMPTS = 'KnowledgeTestAttempts';

    private const TABLE_TESTS = 'KnowledgeTests';

    public function __construct(
        private DBWorker $db,
        private RecordMapper $mapper,
    ) {
    }

    /**
     * @return list<object>
     */
    public function listResultRows(
        ?int $testId,
        ?int $userId,
        ?string $status,
        int $limit,
        int $offset,
    ): array {
        [$sql, $values] = $this->resultsWhere($testId, $userId, $status);
        $attempts = $this->db->getPathTable(self::TABLE_ATTEMPTS);
        $tests = $this->db->getPathTable(self::TABLE_TESTS);
        $users = 'gi_new_users';
        $query = "SELECT a.*, t.title AS test_title, u.user_name AS user_display_name
            FROM `{$attempts}` a
            INNER JOIN `{$tests}` t ON t.id = a.test_id
            LEFT JOIN `{$users}` u ON u.id_user = a.user_id
            WHERE {$sql}
            ORDER BY a.date_finished DESC
            LIMIT %d OFFSET %d";
        $values[] = $limit;
        $values[] = $offset;

        return $this->mapper->rows($this->db->getRawSQL($query, $values));
    }

    public function countResults(?int $testId, ?int $userId, ?string $status): int
    {
        [$sql, $values] = $this->resultsWhere($testId, $userId, $status);
        $attempts = $this->db->getPathTable(self::TABLE_ATTEMPTS);
        $query = "SELECT COUNT(1) AS cnt FROM `{$attempts}` a WHERE {$sql}";
        $rows = $this->mapper->rows($this->db->getRawSQL($query, $values));
        if ($rows === []) {
            return 0;
        }

        return (int) ($rows[0]->cnt ?? 0);
    }

    /**
     * @return array{0: string, 1: list<int|float|string>}
     */
    private function resultsWhere(?int $testId, ?int $userId, ?string $status): array
    {
        $parts = ['1 = 1'];
        $values = [];
        if ($testId !== null && $testId > 0) {
            $parts[] = 'a.test_id = %d';
            $values[] = $testId;
        }
        if ($userId !== null && $userId > 0) {
            $parts[] = 'a.user_id = %d';
            $values[] = $userId;
        }
        if ($status !== null && $status !== '') {
            $parts[] = 'a.status = %s';
            $values[] = $status;
        }

        return [implode(' AND ', $parts), $values];
    }
}
