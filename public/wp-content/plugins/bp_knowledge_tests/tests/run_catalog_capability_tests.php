<?php

declare(strict_types=1);

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'BpKnowledgeTests\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $path = dirname(__DIR__) . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($path)) {
            require_once $path;
        }
    });
}

use BpKnowledgeTests\Infrastructure\Security\CatalogCapability;

$failed = 0;

$assert = static function (bool $condition, string $message) use (&$failed): void {
    if (!$condition) {
        echo "FAIL: {$message}\n";
        $failed++;
        return;
    }
    echo "OK: {$message}\n";
};

$assert(
    CatalogCapability::CAP_MANAGE_CATALOG === 'bp_manage_knowledge_tests_catalog',
    'capability slug is stable',
);
$assert(
    CatalogCapability::isFactoryCatalogRole('administrator'),
    'factory administrator role is catalog-eligible',
);
$assert(
    CatalogCapability::isFactoryCatalogRole('sales_manager'),
    'sales_manager is catalog-eligible',
);
$assert(
    !CatalogCapability::isFactoryCatalogRole('dealer'),
    'dealer is not catalog-eligible',
);

exit($failed > 0 ? 1 : 0);
