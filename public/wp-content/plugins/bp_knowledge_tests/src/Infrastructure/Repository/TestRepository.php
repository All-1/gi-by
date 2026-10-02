<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use BpKnowledgeTests\Domain\Record\TestRecord;
use BpKnowledgeTests\Infrastructure\DatabaseClock;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class TestRepository
{
    private const TABLE = 'gi_new_tests';

    public function __construct(
        private DBWorker $db,
        private DBUtilities $dbUtilities,
        private DatabaseClock $clock,
        private RecordMapper $mapper,
    ) {
    }

    public function findById(int $id): ?TestRecord
    {
        $row = $this->db->selectUni_2(self::TABLE, [
            'sql' => 'id = %d',
            'values' => [$id],
        ], '*');

        return is_object($row) ? $this->mapper->toTestRecord($row) : null;
    }

    /**
     * @return list<TestRecord>
     */
    public function listAll(): array
    {
        $rows = $this->db->getRawSQL(
            'SELECT * FROM `' . self::TABLE . '` ORDER BY title ASC',
            []
        );

        $records = [];
        foreach ($this->mapper->rows($rows) as $row) {
            $records[] = $this->mapper->toTestRecord($row);
        }

        return $records;
    }

    public function create(string $title, ?string $achievementArea): int
    {
        $now = $this->clock->now();

        return $this->db->insertAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'title' => $title,
                    'version' => 1,
                    'achievement_area' => $achievementArea,
                    'date_creation' => $now,
                    'date_modified' => $now,
                ],
                ['%s', '%d', '%s', '%s', '%s']
            )
        );
    }

    public function update(int $id, string $title, ?string $achievementArea): bool
    {
        return $this->db->updateAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(
                [
                    'title' => $title,
                    'achievement_area' => $achievementArea,
                    'date_modified' => $this->clock->now(),
                ],
                ['%s', '%s', '%s']
            ),
            $this->dbUtilities->packageWriteColumns(['id' => $id], ['%d'])
        );
    }

    public function delete(int $id): bool
    {
        return $this->db->deleteAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(['id' => $id], ['%d'])
        );
    }

    public function bumpVersionAfterContentChange(int $id): void
    {
        $this->db->updateDB(
            self::TABLE,
            'id = %d',
            'version = version + 1, date_modified = %s',
            [$this->clock->now(), $id]
        );
    }
}
