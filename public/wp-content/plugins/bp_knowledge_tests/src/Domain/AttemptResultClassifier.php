<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Domain;

final class AttemptResultClassifier
{
    public const MEDAL_GOLD = 'gold';
    public const MEDAL_SILVER = 'silver';
    public const MEDAL_BRONZE = 'bronze';
    public const MEDAL_FAILED = 'failed';

    public function classify(float $score, TestConfig $config): AttemptClassification
    {
        $passed = $score >= $config->bronzeThreshold;

        return new AttemptClassification(
            $score,
            $passed,
            $this->medalFor($score, $config, $passed),
            $score >= $config->lockThreshold,
        );
    }

    private function medalFor(float $score, TestConfig $config, bool $passed): string
    {
        if (!$passed) {
            return self::MEDAL_FAILED;
        }
        if ($score >= $config->goldThreshold) {
            return self::MEDAL_GOLD;
        }
        if ($score >= $config->silverThreshold) {
            return self::MEDAL_SILVER;
        }

        return self::MEDAL_BRONZE;
    }
}
