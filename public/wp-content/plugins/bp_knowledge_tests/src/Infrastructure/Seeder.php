<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure;

final class Seeder
{
    public function __construct(private \wpdb $wpdb)
    {
    }

    public function seedDefaults(): void
    {
        $defaults = [
            'bronze_threshold' => '70',
            'silver_threshold' => '80',
            'gold_threshold' => '90',
            'lock_threshold' => '95',
            'retake_delay_days' => '1',
            'new_test_notification_dismiss_limit' => '10',
            'failed_test_notification_dismiss_limit' => '10',
            'new_test_notification_mode' => 'bottom',
            'failed_test_notification_mode' => 'bottom',
            'rank_name_gold' => 'Guru',
            'rank_name_silver' => 'Expert',
            'rank_name_bronze' => 'Specialist',
            'rank_name_failed' => 'Failed',
        ];

        foreach ($defaults as $name => $value) {
            $this->insertIgnore($name, $value, 'gi_new_test_config');
        }

        $this->seedMedalTiersIfMissing();
    }

    public function seedMedalTiersIfMissing(): void
    {
        $tiers = [
            'Bronze' => 'bronze',
            'Silver' => 'silver',
            'Gold' => 'gold',
            'Lock' => 'lock',
        ];

        foreach ($tiers as $name => $value) {
            if ($this->achievementNameExists($name)) {
                continue;
            }
            $this->insertAchievement($name, $value);
        }
    }

    public function seedRankDisplayNamesIfMissing(): void
    {
        $names = [
            'rank_name_gold' => 'Guru',
            'rank_name_silver' => 'Expert',
            'rank_name_bronze' => 'Specialist',
            'rank_name_failed' => 'Failed',
        ];

        foreach ($names as $name => $value) {
            $this->insertIgnore($name, $value, 'gi_new_test_config');
        }
    }

    private function achievementNameExists(string $name): bool
    {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                'SELECT COUNT(1) FROM gi_new_test_achievements WHERE name = %s',
                $name
            )
        );

        return (int) $count > 0;
    }

    private function insertAchievement(string $name, string $value): void
    {
        $this->wpdb->query(
            $this->wpdb->prepare(
                'INSERT INTO gi_new_test_achievements (name, value) VALUES (%s, %s)',
                $name,
                $value
            )
        );
    }

    private function insertIgnore(string $name, string $value, string $table): void
    {
        $this->wpdb->query(
            $this->wpdb->prepare(
                "INSERT IGNORE INTO `{$table}` (name, value) VALUES (%s, %s)",
                $name,
                $value
            )
        );
    }

}
