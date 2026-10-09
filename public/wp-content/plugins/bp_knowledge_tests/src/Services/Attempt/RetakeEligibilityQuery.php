<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Domain\RetakeEligibility;
use BpKnowledgeTests\Infrastructure\ClockInterface;
use BpKnowledgeTests\Infrastructure\Repository\AttemptRepository;
use BpKnowledgeTests\Services\DomainServices;

final class RetakeEligibilityQuery
{
    public function __construct(
        private AttemptRepository $attempts,
        private MaterialsService $materials,
        private DomainServices $domain,
        private RetakeEligibilityPolicy $policy,
        private ClockInterface $clock,
    ) {
    }

    public function evaluate(int $userId, int $testId): RetakeEligibility
    {
        $latest = $this->attempts->findLatestByUserAndTest($userId, $testId);
        $context = new RetakeEligibilityContext(
            $this->materials->hasPendingMaterials($userId, $testId),
            $this->domain->config->retakeDelayDays(),
            $this->clock->now(),
        );

        return $this->policy->evaluate($latest, $context, $this->domain->config->read());
    }
}
