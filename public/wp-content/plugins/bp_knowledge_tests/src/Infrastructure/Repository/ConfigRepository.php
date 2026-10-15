<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class ConfigRepository
{
    private const TABLE = 'KnowledgeTestConfig';

    public function __construct(
        private DBWorker $db,
        private DBUtilities $dbUtilities,
    ) {
    }

    public function getValue(string $name): ?string
    {
        $value = $this->db->selectVarSimple(self::TABLE, 'name', $name, 'value');

        return $value !== null ? (string) $value : null;
    }

    /**
     * @return array<string, string>
     */
    public function getAllAsMap(): array
    {
        $table = $this->db->getPathTable(self::TABLE);
        $rows = $this->db->getRawSQL(
            'SELECT name, value FROM `' . $table . '` ORDER BY name ASC',
            []
        );
        $map = [];
        foreach ($this->configRows($rows) as $row) {
            $map[(string) $row->name] = (string) $row->value;
        }

        return $map;
    }

    /**
     * @return list<object>
     */
    private function configRows(mixed $rows): array
    {
        if ($rows === null || $rows === []) {
            return [];
        }
        if (is_object($rows)) {
            return [$rows];
        }
        if (!is_array($rows)) {
            return [];
        }
        if (isset($rows['name']) || isset($rows['value'])) {
            return [(object) $rows];
        }

        $list = [];
        foreach ($rows as $row) {
            if (is_object($row)) {
                $list[] = $row;
            } elseif (is_array($row)) {
                $list[] = (object) $row;
            }
        }

        return $list;
    }

    public function setValue(string $name, string $value): void
    {
        if ($this->getValue($name) === null) {
            $this->db->insertAssoc(
                self::TABLE,
                $this->dbUtilities->packageWriteColumns(
                    ['name' => $name, 'value' => $value],
                    ['%s', '%s']
                )
            );

            return;
        }

        $this->db->updateAssoc(
            self::TABLE,
            $this->dbUtilities->packageWriteColumns(['value' => $value], ['%s']),
            $this->dbUtilities->packageWriteColumns(['name' => $name], ['%s'])
        );
    }
}
