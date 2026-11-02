<?php

declare(strict_types=1);

/**
 * AchievementRepository — findMedalById must return tier value (multi-column select).
 *
 * Run from plugin root: php tests/run_achievement_repository_tests.php
 */

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class) use ($root): void {
        $prefix = 'BpKnowledgeTests\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $path = $root . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($path)) {
            require $path;
        }
    });
}

require $root . '/tests/support/MemoryDBWorker.php';

use BpKnowledgeTests\Infrastructure\Repository\AchievementRepository;
use BpKnowledgeTests\Tests\Support\MemoryDBWorker;
use PersonalAccount\Utilities\DBUtilities;
use PersonalAccount\Utilities\SimpleUtilities;

$failures = 0;
$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        ++$failures;
    }
};

$dbUtilities = new DBUtilities(new SimpleUtilities());
$db = new MemoryDBWorker($dbUtilities);
$db->seedRow('KnowledgeTestAchievements', ['name' => 'Bronze', 'value' => 'bronze']);
$repo = new AchievementRepository($db);

$medal = $repo->findMedalById(1);
$assert($medal !== null, 'findMedalById returns row');
$assert(($medal['value'] ?? '') === 'bronze', 'tier value is bronze');

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All achievement repository tests passed.\n");
