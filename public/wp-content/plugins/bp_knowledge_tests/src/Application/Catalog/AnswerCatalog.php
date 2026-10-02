<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Application\Catalog;

use BpKnowledgeTests\Domain\Record\AnswerRecord;
use BpKnowledgeTests\Infrastructure\Repository\AnswerRepository;
use BpKnowledgeTests\Infrastructure\Repository\QuestionRepository;
use BpKnowledgeTests\Infrastructure\Repository\TestRepository;

final class AnswerCatalog
{
    public function __construct(
        private AnswerRepository $answers,
        private QuestionRepository $questions,
        private TestRepository $tests,
    ) {
    }

    public function find(int $id): ?AnswerRecord
    {
        return $this->answers->findById($id);
    }

    /**
     * @return list<AnswerRecord>
     */
    public function listForQuestion(int $questionId): array
    {
        return $this->answers->listByQuestionId($questionId);
    }

    public function create(int $questionId, string $answerText): int
    {
        $id = $this->answers->create($questionId, $answerText);
        $this->bumpTestForQuestion($questionId);

        return $id;
    }

    public function update(int $answerId, string $answerText): bool
    {
        $updated = $this->answers->update($answerId, $answerText);
        if ($updated) {
            $this->bumpTestForAnswer($answerId);
        }

        return $updated;
    }

    public function delete(int $answerId): bool
    {
        $questionId = $this->answers->questionIdForAnswer($answerId);
        $deleted = $this->answers->delete($answerId);
        if ($deleted && $questionId !== null) {
            $this->bumpTestForQuestion($questionId);
        }

        return $deleted;
    }

    private function bumpTestForAnswer(int $answerId): void
    {
        $questionId = $this->answers->questionIdForAnswer($answerId);
        if ($questionId !== null) {
            $this->bumpTestForQuestion($questionId);
        }
    }

    private function bumpTestForQuestion(int $questionId): void
    {
        $testId = $this->questions->testIdForQuestion($questionId);
        if ($testId !== null) {
            $this->tests->bumpVersionAfterContentChange($testId);
        }
    }
}
