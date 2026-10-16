<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Reporting;

use BpKnowledgeTests\Domain\AttemptValidityLabel;
use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Infrastructure\Mapping\RecordMapper;
use BpKnowledgeTests\Infrastructure\Repository\AttemptAdminRepository;

final class ResultsListQuery
{
    public function __construct(
        private AttemptAdminRepository $attempts,
        private RecordMapper $mapper,
    ) {
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int, page: int, perPage: int}
     */
    public function list(
        ?int $testId,
        ?int $userId,
        ?string $status,
        int $page,
        int $perPage,
    ): array {
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $offset = ($page - 1) * $perPage;
        $total = $this->attempts->countResults($testId, $userId, $status);
        $rows = $this->attempts->listResultRows($testId, $userId, $status, $perPage, $offset);
        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->presentRow($this->mapper->toAttemptRecord($row), $row);
        }

        return ['items' => $items, 'total' => $total, 'page' => $page, 'perPage' => $perPage];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentRow(AttemptRecord $attempt, object $row): array
    {
        $title = (string) ($row->test_title ?? '');
        $userName = isset($row->user_display_name) ? (string) $row->user_display_name : '';

        return [
            'id' => $attempt->id,
            'userId' => $attempt->userId,
            'userDisplayName' => $userName !== '' ? $userName : null,
            'testId' => $attempt->testId,
            'testTitle' => $title !== '' ? $title : 'Test #' . $attempt->testId,
            'testVersion' => $attempt->testVersion,
            'score' => $attempt->score,
            'status' => $attempt->status,
            'validity' => AttemptValidityLabel::forStatus($attempt->status),
            'dateFinished' => $attempt->dateFinished,
            'validAnswers' => $attempt->validAnswers,
            'invalidAnswers' => $attempt->invalidAnswers,
        ];
    }
}
