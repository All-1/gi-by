<?php

declare(strict_types=1);

/** Plugin PSR-4 autoload when Composer vendor/ is absent (CLI tests). */
$pluginRoot = dirname(__DIR__, 2);
$autoload = $pluginRoot . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require $autoload;

    return;
}

spl_autoload_register(static function (string $class) use ($pluginRoot): void {
    $prefix = 'BpKnowledgeTests\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = $pluginRoot . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_readable($path)) {
        require $path;
    }
});
