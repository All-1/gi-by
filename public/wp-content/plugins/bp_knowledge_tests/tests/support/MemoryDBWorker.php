<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Tests\Support;

use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

/**
 * In-memory DBWorker subset for CompleteAttempt integration tests (no WordPress).
 */
final class MemoryDBWorker extends DBWorker
{
    /** @var array<string, list<object>> */
    private array $rows = [];

    private int $lastInsertId = 0;

    /** @var array<string, string> */
    private const TABLE_MAP = [
        'KnowledgeTests' => 'gi_new_tests',
        'KnowledgeTestQuestions' => 'gi_new_test_questions',
        'KnowledgeTestAnswers' => 'gi_new_test_answers',
        'KnowledgeTestAttempts' => 'gi_new_test_attempts',
        'KnowledgeTestAttemptQuestions' => 'gi_new_test_attempt_questions',
        'KnowledgeTestFinishedExplanations' => 'gi_new_test_finished_attempts_explanations',
        'KnowledgeTestAchievements' => 'gi_new_test_achievements',
        'KnowledgeTestUserAchievements' => 'gi_new_test_user_achievements',
        'KnowledgeTestNotifications' => 'gi_new_test_notifications',
        'KnowledgeTestConfig' => 'gi_new_test_config',
    ];

    public function __construct(DBUtilities $dbUtilities)
    {
        $this->DBUtilities = $dbUtilities;
    }

    public function getPathTable($string): string
    {
        if (isset(self::TABLE_MAP[$string])) {
            return self::TABLE_MAP[$string];
        }

        return $string;
    }

    public function updateDB($table, $whereSql, $setSql, $queryParams)
    {
        $tableKey = $this->getPathTable((string) $table);
        if ($whereSql === 'id = %d' && str_contains((string) $setSql, 'version = version + 1')) {
            $testId = (int) end($queryParams);
            $modified = (string) $queryParams[0];
            foreach ($this->rows[$tableKey] ?? [] as $row) {
                if ((int) $row->id === $testId) {
                    $row->version = (int) ($row->version ?? 1) + 1;
                    $row->date_modified = $modified;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param array{values: array<string, mixed>, formats?: list<string>|null} $package
     */
    public function insertAssoc(string $table, array $package): int
    {
        $tableKey = $this->getPathTable($table);
        $values = $package['values'];
        ++$this->lastInsertId;
        $values['id'] = $this->lastInsertId;
        $row = (object) $values;
        $this->rows[$tableKey][] = $row;

        return $this->lastInsertId;
    }

    public function selectUni_2($table, $conditions, $what = '*')
    {
        $tableKey = $this->getPathTable($table);
        $matched = $this->filterRows($tableKey, $conditions);
        if (isset($conditions['OrderBy'])) {
            $matched = $this->sortRows($matched, (string) $conditions['OrderBy']);
        }

        return $this->finalize($matched, $what);
    }

    public function selectVarSimple($table, $where = null, $value = null, $what)
    {
        $tableKey = $this->getPathTable($table);
        if ($where === null || $value === null) {
            return null;
        }

        foreach ($this->rows[$tableKey] ?? [] as $row) {
            if (isset($row->$where) && (string) $row->$where === (string) $value) {
                return $row->$what ?? null;
            }
        }

        return null;
    }

    public function getRawSQL($sql, $params = [])
    {
        $sql = $this->resolveSqlTableNames($sql);
        if (strpos($sql, 'COUNT(1) AS cnt') !== false && strpos($sql, 'is_correct = 1') !== false) {
            return $this->countCorrectAnswersForTest((int) ($params[0] ?? 0));
        }
        if (preg_match('/SELECT \* FROM `([^`]+)` WHERE user_id = %d AND test_id = %d ORDER BY date_finished DESC LIMIT 1/', $sql)) {
            return $this->latestAttempt((int) $params[0], (int) $params[1]);
        }
        if (preg_match('/SELECT e\.\* FROM/', $sql)) {
            return $this->pendingExplanations((int) $params[0], (int) $params[1]);
        }
        if (preg_match('/SELECT \* FROM `([^`]+)` ORDER BY title ASC/', $sql, $m)) {
            $tableKey = $m[1];
            $rows = $this->rows[$tableKey] ?? [];
            usort($rows, static fn (object $a, object $b): int => strcmp((string) ($a->title ?? ''), (string) ($b->title ?? '')));

            return $rows;
        }

        return [];
    }

    /**
     * @param array{values: array<string, mixed>, formats?: list<string>|null} $setPackage
     * @param array{values: array<string, mixed>, formats?: list<string>|null} $wherePackage
     */
    public function updateAssoc(string $table, array $setPackage, array $wherePackage): bool
    {
        $tableKey = $this->getPathTable($table);
        $row = $this->findFirstByWhere($tableKey, $wherePackage['values']);
        if ($row === null) {
            return false;
        }
        foreach ($setPackage['values'] as $key => $value) {
            $row->$key = $value;
        }

        return true;
    }

    /**
     * @param array{values: array<string, mixed>, formats?: list<string>|null} $wherePackage
     */
    public function deleteAssoc(string $table, array $wherePackage): bool
    {
        $tableKey = $this->getPathTable($table);
        $where = $wherePackage['values'];
        $before = count($this->rows[$tableKey] ?? []);
        $this->rows[$tableKey] = array_values(array_filter(
            $this->rows[$tableKey] ?? [],
            static function (object $row) use ($where): bool {
                foreach ($where as $column => $expected) {
                    if (!isset($row->$column) || (string) $row->$column !== (string) $expected) {
                        return true;
                    }
                }

                return false;
            }
        ));

        return count($this->rows[$tableKey] ?? []) < $before;
    }

    /**
     * @param array<string, mixed> $row
     */
    public function seedRow(string $logicalTable, array $row): void
    {
        $tableKey = $this->getPathTable($logicalTable);
        if (!isset($row['id'])) {
            ++$this->lastInsertId;
            $row['id'] = $this->lastInsertId;
        }
        $this->lastInsertId = max($this->lastInsertId, (int) $row['id']);
        $this->rows[$tableKey][] = (object) $row;
    }

    /**
     * @return list<object>
     */
    public function allRows(string $logicalTable): array
    {
        $tableKey = $this->getPathTable($logicalTable);

        return $this->rows[$tableKey] ?? [];
    }

    /**
     * @param array{sql?: string, values?: list<mixed>} $conditions
     * @return list<object>
     */
    private function filterRows(string $tableKey, array $conditions): array
    {
        $sql = $conditions['sql'] ?? '';
        $values = $conditions['values'] ?? [];
        $rows = $this->rows[$tableKey] ?? [];

        return array_values(array_filter($rows, function (object $row) use ($sql, $values): bool {
            return $this->rowMatches($row, $sql, $values);
        }));
    }

    private function rowMatches(object $row, string $sql, array $values): bool
    {
        if ($sql === 'id = %d') {
            return isset($row->id) && (int) $row->id === (int) $values[0];
        }
        if ($sql === 'test_id = %d') {
            return isset($row->test_id) && (int) $row->test_id === (int) $values[0];
        }
        if ($sql === 'question_id = %d') {
            return isset($row->question_id) && (int) $row->question_id === (int) $values[0];
        }
        if ($sql === 'attempt_id = %d') {
            return isset($row->attempt_id) && (int) $row->attempt_id === (int) $values[0];
        }
        if ($sql === 'user_id = %d AND test_id = %d') {
            return (int) ($row->user_id ?? 0) === (int) $values[0]
                && (int) ($row->test_id ?? 0) === (int) $values[1];
        }
        if ($sql === 'user_id = %d AND test_id = %d AND notification_type = %s') {
            return (int) ($row->user_id ?? 0) === (int) $values[0]
                && (int) ($row->test_id ?? 0) === (int) $values[1]
                && (string) ($row->notification_type ?? '') === (string) $values[2];
        }
        if ($sql === 'question_id = %d AND is_correct = 1') {
            return (int) ($row->question_id ?? 0) === (int) $values[0]
                && (int) ($row->is_correct ?? 0) === 1;
        }
        if ($sql === 'value = %s') {
            return (string) ($row->value ?? '') === (string) $values[0];
        }

        return false;
    }

    /**
     * @param list<object> $rows
     */
    private function sortRows(array $rows, string $orderBy): array
    {
        if (stripos($orderBy, 'id ASC') !== false) {
            usort($rows, static fn (object $a, object $b): int => (int) ($a->id ?? 0) <=> (int) ($b->id ?? 0));
        }

        return $rows;
    }

    private function finalize(array $rows, mixed $what): mixed
    {
        if ($rows === []) {
            return null;
        }
        if ($what === '*') {
            return count($rows) === 1 ? $rows[0] : $rows;
        }
        if (is_string($what) && $what !== '*') {
            if (count($rows) === 1) {
                $row = $rows[0];

                return isset($row->$what) ? (object) [$what => $row->$what] : null;
            }
            $list = [];
            foreach ($rows as $row) {
                if (isset($row->$what)) {
                    $list[] = (object) [$what => $row->$what];
                }
            }

            return $list;
        }

        return $rows;
    }

    private function resolveSqlTableNames(string $sql): string
    {
        foreach (self::TABLE_MAP as $logical => $physical) {
            $sql = str_replace('`' . $logical . '`', '`' . $physical . '`', $sql);
        }

        return $sql;
    }

    /**
     * @return list<object>
     */
    private function countCorrectAnswersForTest(int $testId): array
    {
        $questionIds = [];
        foreach ($this->rows['gi_new_test_questions'] ?? [] as $question) {
            if ((int) $question->test_id === $testId) {
                $questionIds[(int) $question->id] = true;
            }
        }
        $count = 0;
        foreach ($this->rows['gi_new_test_answers'] ?? [] as $answer) {
            if ((int) ($answer->is_correct ?? 0) === 1
                && isset($questionIds[(int) ($answer->question_id ?? 0)])) {
                ++$count;
            }
        }

        return [(object) ['cnt' => $count]];
    }

    /**
     * @return list<object>
     */
    private function latestAttempt(int $userId, int $testId): array
    {
        $matches = [];
        foreach ($this->rows['gi_new_test_attempts'] ?? [] as $row) {
            if ((int) $row->user_id === $userId && (int) $row->test_id === $testId) {
                $matches[] = $row;
            }
        }
        if ($matches === []) {
            return [];
        }
        usort($matches, static fn (object $a, object $b): int => strcmp((string) $b->date_finished, (string) $a->date_finished));

        return [$matches[0]];
    }

    /**
     * @return list<object>
     */
    private function pendingExplanations(int $userId, int $testId): array
    {
        $result = [];
        foreach ($this->rows['gi_new_test_finished_attempts_explanations'] ?? [] as $explanation) {
            if ((int) $explanation->user_id !== $userId) {
                continue;
            }
            $attemptId = (int) ($explanation->attempt_id ?? 0);
            foreach ($this->rows['gi_new_test_attempts'] ?? [] as $attempt) {
                if ((int) $attempt->id === $attemptId && (int) $attempt->test_id === $testId) {
                    $result[] = $explanation;
                }
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $where
     */
    private function findFirstByWhere(string $tableKey, array $where): ?object
    {
        foreach ($this->rows[$tableKey] ?? [] as $row) {
            $match = true;
            foreach ($where as $column => $expected) {
                if (!isset($row->$column) || (string) $row->$column !== (string) $expected) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                return $row;
            }
        }

        return null;
    }
}
