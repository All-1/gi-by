<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Infrastructure\Repository\AchievementRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\UserAchievementRepository;
use BpKnowledgeTests\Services\TestConfigReader;

final class UserTestStandingQuery
{
    public function __construct(
        private AttemptRepository $attempts,
        private UserAchievementRepository $userAchievements,
        private AchievementRepository $achievements,
        private TestConfigReader $config,
    ) {
    }

    public function forUserAndTest(int $userId, int $testId): UserTestStanding
    {
        $latest = $this->attempts->findLatestByUserAndTest($userId, $testId);
        $medalId = $this->userAchievements->findMedalIdForUserAndTest($userId, $testId);
        $tier = $this->tierValue($medalId);

        return new UserTestStanding($latest, $medalId, $tier, $this->rankName($tier));
    }

    private function tierValue(?int $medalId): ?string
    {
        if ($medalId === null) {
            return null;
        }

        $medal = $this->achievements->findMedalById($medalId);

        return $medal !== null ? $medal['value'] : null;
    }

    private function rankName(?string $tier): ?string
    {
        if ($tier === null || $tier === '') {
            return null;
        }

        return $this->config->rankLabel($tier);
    }
}
