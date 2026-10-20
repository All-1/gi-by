<?php

declare(strict_types=1);

namespace PersonalAccount\Testing;

use BpKnowledgeTests\Services\CatalogServices;

/**
 * Builds TestController session rows from catalog (shuffle happens in AttemptSession).
 */
final class SessionQuestionLoader
{
    public function __construct(private KnowledgeTestsPluginResolver $resolver)
    {
    }

    /**
     * @return list<array{questionId: int, answerIds: list<int>, correctAnswerIds: list<int>}>
     */
    public function loadForTest(int $testId): array
    {
        $plugin = $this->resolver->plugin();
        if ($plugin->catalog()->tests->find($testId) === null) {
            throw new \InvalidArgumentException('Test not found.');
        }

        $catalog = $plugin->catalog();
        $rows = [];
        foreach ($catalog->questions->listForTest($testId) as $question) {
            $rows[] = $this->rowForQuestion($catalog, $question->id);
        }

        return $rows;
    }

    private function rowForQuestion(CatalogServices $catalog, int $questionId): array
    {
        $answerIds = [];
        foreach ($catalog->answers->listForQuestion($questionId) as $answer) {
            $answerIds[] = $answer->id;
        }

        return [
            'questionId' => $questionId,
            'answerIds' => $answerIds,
            'correctAnswerIds' => $catalog->answers->listCorrectAnswerIdsForQuestion($questionId),
        ];
    }
}
