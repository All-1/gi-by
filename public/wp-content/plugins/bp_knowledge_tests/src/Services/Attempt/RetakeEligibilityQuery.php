<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Domain\RetakeEligibility;
use BpKnowledgeTests\Infrastructure\ClockInterface;
use BpKnowledgeTests\Domain\Record\NotificationRecord;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Infrastructure\Repository\NotificationRepository;
use BpKnowledgeTests\Services\DomainServices;

final class RetakeEligibilityQuery
{
    public function __construct(
        private AttemptRepository $attempts,
        private MaterialsService $materials,
        private NotificationRepository $notifications,
        private NotificationRetakePolicy $notificationRetakePolicy,
        private DomainServices $domain,
        private RetakeEligibilityPolicy $policy,
        private ClockInterface $clock,
    ) {
    }

    public function evaluate(int $userId, int $testId): RetakeEligibility
    {
        $latest = $this->attempts->findLatestByUserAndTest($userId, $testId);
        $settings = $this->domain->config->notificationSettings();
        $notificationBlocks = $this->notificationRetakePolicy->blocksRetake(
            $latest,
            $this->notifications->findByUserTestAndType($userId, $testId, NotificationRecord::TYPE_NEW_TEST),
            $this->notifications->findByUserTestAndType($userId, $testId, NotificationRecord::TYPE_FAILED_TEST),
            $settings,
        );
        $context = new RetakeEligibilityContext(
            $this->materials->hasPendingMaterials($userId, $testId),
            $this->domain->config->retakeDelayDays(),
            $this->clock->now(),
            $notificationBlocks,
        );

        return $this->policy->evaluate($latest, $context, $this->domain->config->read());
    }
}
