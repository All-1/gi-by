<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Infrastructure\Repository\AchievementRepository;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\UserAchievementRepository;
use BpKnowledgeTests\Services\CatalogServices;
use BpKnowledgeTests\Services\DomainServices;

final class AttemptAchievementService
{
    public function __construct(
        private DomainServices $domain,
        private AchievementRepository $achievements,
        private UserAchievementRepository $userAchievements,
        private AttemptRepository $attempts,
        private CatalogServices $catalog,
    ) {
    }

    public function applyAfterComplete(int $userId, int $testId, float $score): void
    {
        $classification = $this->domain->classifier->classify($score, $this->domain->config->read());
        $tierValue = $this->domain->classifier->achievementTierValue($classification);
        if ($tierValue === null) {
            $this->userAchievements->deleteForUserAndTest($userId, $testId);

            return;
        }

        $medalId = $this->achievements->findIdByValue($tierValue);
        if ($medalId === null) {
            throw new \RuntimeException('Achievement tier not found: ' . $tierValue);
        }

        $this->userAchievements->upsertForUserAndTest($userId, $testId, $medalId);
    }

    public function recalculateForUserAndTest(int $userId, int $testId): void
    {
        $attempt = $this->attempts->findLatestByUserAndTest($userId, $testId);
        if ($attempt === null || $attempt->status === 'outdated') {
            return;
        }

        $this->applyAfterComplete($userId, $testId, $attempt->score);
    }

    public function recalculateAllFromLatestAttempts(): void
    {
        foreach ($this->attempts->listDistinctUserTestPairs() as $pair) {
            $this->recalculateForUserAndTest($pair['userId'], $pair['testId']);
        }
    }

    /**
     * @param list<int> $userIds
     * @return array<int, list<array<string, mixed>>>
     */
    public function chatStarsForUserIds(array $userIds): array
    {
        $result = [];
        foreach ($userIds as $rawId) {
            $userId = (int) $rawId;
            if ($userId <= 0) {
                continue;
            }
            $result[$userId] = $this->chatStarsForUser($userId);
        }

        return $result;
    }

    /** @return list<array<string, mixed>> */
    private function chatStarsForUser(int $userId): array
    {
        $stars = [];
        foreach ($this->userAchievements->listMedalRowsForUser($userId) as $pair) {
            $test = $this->catalog->tests->find($pair['testId']);
            $medal = $this->achievements->findMedalById($pair['medalId']);
            if ($test === null || $medal === null) {
                continue;
            }
            $area = $test->achievementArea !== '' ? $test->achievementArea : $test->title;
            $stars[] = [
                'testId' => $pair['testId'],
                'medalId' => $pair['medalId'],
                'achievementArea' => $area,
                'tierValue' => $medal['value'],
                'rankName' => $medal['name'],
                'testTitle' => $test->title,
            ];
        }

        return $stars;
    }
}
