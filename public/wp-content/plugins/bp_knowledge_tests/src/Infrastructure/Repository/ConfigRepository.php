<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure\Repository;

use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Workers\DBWorker;

final class ConfigRepository
{
    private const TABLE = 'gi_new_test_config';

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
        $rows = $this->db->getRawSQL(
            'SELECT name, value FROM `' . self::TABLE . '` ORDER BY name ASC',
            []
        );
        $map = [];
        foreach ($rows ?: [] as $row) {
            $map[(string) $row->name] = (string) $row->value;
        }

        return $map;
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
