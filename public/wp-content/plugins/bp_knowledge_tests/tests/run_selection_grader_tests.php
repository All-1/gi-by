<?php

declare(strict_types=1);

/**
 * Question-level selection counting (spec §2.1.1).
 *
 * Run from plugin root: php tests/run_selection_grader_tests.php
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

use BpKnowledgeTests\Domain\QuestionSelectionGrader;

$failures = 0;

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        ++$failures;
    }
};

$grader = new QuestionSelectionGrader();

$result = $grader->grade([1, 2, 3], [1]);
$assert($result->rightAnswers === 1 && $result->failedAnswers === 0 && $result->missedCorrect === 2, 'One correct selected');

$result = $grader->grade([1, 2, 3], [1, 99]);
$assert($result->rightAnswers === 1 && $result->failedAnswers === 1 && $result->missedCorrect === 2, 'Correct + wrong selected');

$result = $grader->grade([1, 2], [99, 100]);
$assert($result->rightAnswers === 0 && $result->failedAnswers === 2 && $result->missedCorrect === 2, 'Only wrong selected');

$result = $grader->grade([1, 2], []);
$assert($result->rightAnswers === 0 && $result->failedAnswers === 0 && $result->missedCorrect === 2, 'Empty selection');

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "All selection grader tests passed.\n");
exit(0);
