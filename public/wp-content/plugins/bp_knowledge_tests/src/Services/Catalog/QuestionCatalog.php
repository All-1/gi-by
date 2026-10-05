<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Catalog;

use BpKnowledgeTests\Domain\Record\QuestionRecord;
use BpKnowledgeTests\Infrastructure\Repository\QuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\TestRepository;

final class QuestionCatalog
{
    public function __construct(
        private QuestionRepository $questions,
        private TestRepository $tests,
    ) {
    }

    public function find(int $id): ?QuestionRecord
    {
        return $this->questions->findById($id);
    }

    /**
     * @return list<QuestionRecord>
     */
    public function listForTest(int $testId): array
    {
        return $this->questions->listByTestId($testId);
    }

    public function create(
        int $testId,
        string $question,
        string $explanation,
        string $reference,
    ): int {
        $id = $this->questions->create($testId, $question, $explanation, $reference);
        $this->tests->bumpVersionAfterContentChange($testId);

        return $id;
    }

    public function update(
        int $questionId,
        string $question,
        string $explanation,
        string $reference,
    ): bool {
        $updated = $this->questions->update($questionId, $question, $explanation, $reference);
        if ($updated) {
            $this->bumpTestForQuestion($questionId);
        }

        return $updated;
    }

    public function delete(int $questionId): bool
    {
        $testId = $this->questions->testIdForQuestion($questionId);
        $deleted = $this->questions->delete($questionId);
        if ($deleted && $testId !== null) {
            $this->tests->bumpVersionAfterContentChange($testId);
        }

        return $deleted;
    }

    private function bumpTestForQuestion(int $questionId): void
    {
        $testId = $this->questions->testIdForQuestion($questionId);
        if ($testId !== null) {
            $this->tests->bumpVersionAfterContentChange($testId);
        }
    }
}
