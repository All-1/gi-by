<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Services\Catalog;

use BpKnowledgeTests\Infrastructure\Config\AdminConfigKeys;
use BpKnowledgeTests\Services\Attempt\AttemptAchievementService;

final class ConfigSaveService
{
    public function __construct(
        private ConfigCatalog $config,
        private AdminConfigValidator $validator,
        private AttemptAchievementService $achievements,
    ) {
    }

    /**
     * @param array<string, scalar> $settings
     * @return array{settings: array<string, string>, achievementsRecalculated: bool, error?: string}
     */
    public function apply(array $settings): array
    {
        $recalc = false;
        foreach ($settings as $name => $value) {
            if (!is_string($name)) {
                return $this->error('settings must use string keys.');
            }
            $stringValue = (string) $value;
            $validationError = $this->validator->validate($name, $stringValue);
            if ($validationError !== null) {
                return $this->error($validationError);
            }
            $this->config->set($name, $stringValue);
            if (AdminConfigKeys::triggersAchievementRecalc($name)) {
                $recalc = true;
            }
        }

        if ($recalc) {
            $this->achievements->recalculateAllFromLatestAttempts();
        }

        return [
            'settings' => $this->config->all(),
            'achievementsRecalculated' => $recalc,
        ];
    }

    /**
     * @return array{settings: array<string, string>, achievementsRecalculated: bool, error?: string}
     */
    private function error(string $message): array
    {
        return ['settings' => $this->config->all(), 'achievementsRecalculated' => false, 'error' => $message];
    }
}
