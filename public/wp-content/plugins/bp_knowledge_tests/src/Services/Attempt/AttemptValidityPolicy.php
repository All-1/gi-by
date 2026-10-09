<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Attempt;

use BpKnowledgeTests\Domain\Record\AttemptRecord;

final class AttemptValidityPolicy
{
    /** Passed result expired per spec §2.3 (one year from date_finished; §2.15 does not shorten before that). */
    public function passedResultExpired(AttemptRecord $attempt, string $now): bool
    {
        if ($attempt->status !== 'passed') {
            return false;
        }

        $validThrough = $this->calendarDate($attempt->dateFinished)->modify('+1 year');

        return $this->calendarDate($now) > $validThrough;
    }

    private function calendarDate(string $dateTime): \DateTimeImmutable
    {
        return new \DateTimeImmutable(substr($dateTime, 0, 10));
    }
}
