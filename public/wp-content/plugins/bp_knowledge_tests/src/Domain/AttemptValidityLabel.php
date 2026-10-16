<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain;

final class AttemptValidityLabel
{
    public static function forStatus(string $status): string
    {
        if ($status === 'passed') {
            return 'valid';
        }
        if ($status === 'outdated') {
            return 'expired';
        }

        return 'not_applicable';
    }
}
