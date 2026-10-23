<?php

declare(strict_types=1);

require_once __DIR__ . '/support/require_plugin_autoload.php';

use BpKnowledgeTests\Services\Catalog\AdminConfigValidator;

$validator = new AdminConfigValidator();

assert($validator->validate('bronze_threshold', '70') === null);
assert($validator->validate('bronze_threshold', '101') !== null);
assert($validator->validate('unknown_key', '1') !== null);
assert($validator->validate('new_test_notification_mode', 'block') === null);
assert($validator->validate('new_test_notification_mode', 'popup') !== null);
assert($validator->validate('rank_name_gold', 'Guru') === null);
assert($validator->validate('rank_name_gold', '   ') !== null);

echo "run_admin_config_validator_tests: OK\n";
