<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Domain\AttemptResultClassifier;
use BpKnowledgeTests\Domain\Record\AttemptRecord;
use BpKnowledgeTests\Domain\RetakeEligibility;
use BpKnowledgeTests\Domain\TestConfig;

final class RetakeEligibilityPolicy
{
    public function __construct(private AttemptResultClassifier $classifier)
    {
    }

    public function evaluate(
        ?AttemptRecord $latestAttempt,
        RetakeEligibilityContext $context,
        TestConfig $config,
    ): RetakeEligibility {
        if ($latestAttempt === null) {
            return new RetakeEligibility(true, []);
        }

        $reasons = $this->blockingReasons($latestAttempt, $context, $config);

        return new RetakeEligibility($reasons === [], $reasons);
    }

    /**
     * @return list<string>
     */
    private function blockingReasons(
        AttemptRecord $attempt,
        RetakeEligibilityContext $context,
        TestConfig $config,
    ): array {
        $reasons = [];
        if ($this->isLockedForRetake($attempt, $config)) {
            $reasons[] = RetakeEligibility::REASON_TEST_LOCKED;
        }
        if (!$this->retakeDelayElapsed($attempt->dateFinished, $context->retakeDelayDays, $context->now)) {
            $reasons[] = RetakeEligibility::REASON_RETAKE_DELAY;
        }
        if ($attempt->isFailed() && $context->hasPendingMaterials) {
            $reasons[] = RetakeEligibility::REASON_PENDING_MATERIALS;
        }

        return $reasons;
    }

    private function isLockedForRetake(AttemptRecord $attempt, TestConfig $config): bool
    {
        if ($attempt->status === 'outdated') {
            return false;
        }

        return $this->classifier->classify($attempt->score, $config)->locked;
    }

    private function retakeDelayElapsed(string $dateFinished, int $retakeDelayDays, string $now): bool
    {
        $finishedDate = $this->calendarDate($dateFinished);
        $today = $this->calendarDate($now);
        $eligibleFrom = $finishedDate->modify(sprintf('+%d days', $retakeDelayDays));

        return $today >= $eligibleFrom;
    }

    private function calendarDate(string $dateTime): \DateTimeImmutable
    {
        $datePart = substr($dateTime, 0, 10);

        return new \DateTimeImmutable($datePart);
    }
}
