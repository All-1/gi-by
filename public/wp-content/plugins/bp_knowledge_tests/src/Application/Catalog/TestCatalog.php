<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Application\Catalog;

use BpKnowledgeTests\Domain\Record\TestRecord;
use BpKnowledgeTests\Infrastructure\Repository\TestRepository;

final class TestCatalog
{
    public function __construct(private TestRepository $tests)
    {
    }

    public function find(int $id): ?TestRecord
    {
        return $this->tests->findById($id);
    }

    /**
     * @return list<TestRecord>
     */
    public function listAll(): array
    {
        return $this->tests->listAll();
    }

    public function create(string $title, ?string $achievementArea = null): int
    {
        return $this->tests->create($title, $achievementArea);
    }

    public function update(int $id, string $title, ?string $achievementArea = null): bool
    {
        return $this->tests->update($id, $title, $achievementArea);
    }

    public function delete(int $id): bool
    {
        return $this->tests->delete($id);
    }
}
