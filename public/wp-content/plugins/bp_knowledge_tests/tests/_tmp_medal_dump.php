<?php

require dirname(__DIR__, 4) . '/wp-load.php';

global $wpdb;

echo "ACHIEVEMENTS\n";
$rows = $wpdb->get_results('SELECT id, name, value FROM gi_new_test_achievements');
foreach ($rows as $row) {
    echo $row->id . ' | ' . $row->name . ' | ' . $row->value . PHP_EOL;
}

echo "USER_ACHIEVEMENTS\n";
$rows = $wpdb->get_results('SELECT user_id, test_id, medal_id FROM gi_new_test_user_achievements');
foreach ($rows as $row) {
    echo $row->user_id . ' test ' . $row->test_id . ' medal ' . $row->medal_id . PHP_EOL;
}

$plugin = function_exists('bp_knowledge_tests_plugin') ? bp_knowledge_tests_plugin() : null;
if ($plugin === null) {
    echo "PLUGIN_NULL\n";
    exit(0);
}

$standing = $plugin->attempts()->standing->forUserAndTest(133, 1);
echo 'medalId=' . var_export($standing->medalId, true) . PHP_EOL;
echo 'medalTier=' . var_export($standing->medalTier, true) . PHP_EOL;
$container = bp_knowledge_tests_resolve_container();
$db = $container !== null ? $container->get('DBWorker') : null;
$medal = $db instanceof PersonalAccount\Workers\DBWorker
    ? (new BpKnowledgeTests\Infrastructure\Repository\AchievementRepository($db))->findMedalById(1)
    : null;
echo 'findMedalById=' . var_export($medal, true) . PHP_EOL;
