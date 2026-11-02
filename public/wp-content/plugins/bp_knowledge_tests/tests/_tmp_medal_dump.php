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

echo 'medalId=' . var_export($plugin->attempts()->findMedalIdForUserAndTest(133, 1), true) . PHP_EOL;
echo 'medalTier=' . var_export($plugin->attempts()->findMedalTierValueForUserAndTest(133, 1), true) . PHP_EOL;
$medal = $plugin->catalog()->achievements->findMedalById(1);
echo 'findMedalById=' . var_export($medal, true) . PHP_EOL;
