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

    /**
     * `gi_new_test_achievements.value` for a passed attempt (spec §2.11–§2.12). Null = no star row (failed).
     */
    public function achievementTierValue(AttemptClassification $classification): ?string
    {
        if (!$classification->passed) {
            return null;
        }
        if ($classification->locked) {
            return 'lock';
        }

        return $classification->medal;
    }
}
