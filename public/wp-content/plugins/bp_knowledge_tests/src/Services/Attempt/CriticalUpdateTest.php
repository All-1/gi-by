<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\TestRepository;
use BpKnowledgeTests\Infrastructure\Repository\UserAchievementRepository;

final class CriticalUpdateTest
{
    public function __construct(
        private TestRepository $tests,
        private AttemptRepository $attempts,
        private UserAchievementRepository $userAchievements,
    ) {
    }

    public function execute(int $testId): void
    {
        if ($this->tests->findById($testId) === null) {
            throw new \InvalidArgumentException('Test not found.');
        }

        $this->attempts->markPassedAsOutdatedForTest($testId);
        $this->userAchievements->deleteAllForTest($testId);
        $this->tests->bumpVersionAfterContentChange($testId);
    }
}
