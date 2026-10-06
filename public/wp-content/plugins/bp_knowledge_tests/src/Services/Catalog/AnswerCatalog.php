<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Catalog;

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

    /**
     * @return list<int>
     */
    public function listCorrectAnswerIdsForQuestion(int $questionId): array
    {
        return $this->answers->listCorrectAnswerIdsForQuestion($questionId);
    }

    public function maximumCorrectForTest(int $testId): int
    {
        return $this->answers->countCorrectForTest($testId);
    }

    public function create(int $questionId, string $answerText, bool $isCorrect = false): int
    {
        $id = $this->answers->create($questionId, $answerText, $isCorrect);
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

    public function setIsCorrect(int $answerId, bool $isCorrect): bool
    {
        $updated = $this->answers->setIsCorrect($answerId, $isCorrect);
        if ($updated) {
            $this->bumpTestForAnswer($answerId);
        }
        return $updated;
    }

    /**
     * @param list<int> $answerIds
     */
    public function setCorrectAnswerIdsForQuestion(int $questionId, array $answerIds): void
    {
        $this->assertAnswersBelongToQuestion($questionId, $answerIds);
        $this->answers->clearCorrectFlagsForQuestion($questionId);
        foreach ($answerIds as $answerId) {
            $this->answers->setIsCorrect((int) $answerId, true);
        }
        $this->bumpTestForQuestion($questionId);
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

    /**
     * @param list<int> $answerIds
     */
    private function assertAnswersBelongToQuestion(int $questionId, array $answerIds): void
    {
        foreach ($answerIds as $answerId) {
            $ownerQuestion = $this->answers->questionIdForAnswer((int) $answerId);
            if ($ownerQuestion !== $questionId) {
                throw new \InvalidArgumentException(
                    "Answer {$answerId} does not belong to question {$questionId}."
                );
            }
        }
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
