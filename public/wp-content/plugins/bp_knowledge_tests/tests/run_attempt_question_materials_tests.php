<?php

declare(strict_types=1);

/**
 * Materials question filter from persisted attempt-question counts (spec §9.2).
 *
 * Run from plugin root: php tests/run_attempt_question_materials_tests.php
 */

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'BpKnowledgeTests\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }
        $relative = substr($class, strlen($prefix));
        $path = dirname(__DIR__) . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($path)) {
            require $path;
        }
    });
}

use BpKnowledgeTests\Domain\Record\AttemptQuestionRecord;

$failures = 0;

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        ++$failures;
    }
};

$row = static function (int $right, int $failed): AttemptQuestionRecord {
    return new AttemptQuestionRecord(1, 1, 1, 1, $right, $failed, '2026-01-01 00:00:00');
};

$assert($row(2, 0)->qualifiesForMaterials() === false, 'all selected correct counts as non-materials');
$assert($row(1, 0)->qualifiesForMaterials() === false, 'partial correct without wrong pick is not materials');
$assert($row(2, 1)->qualifiesForMaterials() === true, 'wrong selection qualifies for materials');
$assert($row(0, 0)->qualifiesForMaterials() === true, 'no correct selected qualifies for materials');

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All attempt-question materials tests passed.\n");
