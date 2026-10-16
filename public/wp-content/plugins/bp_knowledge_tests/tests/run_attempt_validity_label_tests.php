<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use BpKnowledgeTests\Domain\AttemptValidityLabel;

$failures = 0;

function assertSame(string $expected, string $actual, string $label): void
{
    global $failures;
    if ($expected !== $actual) {
        echo "FAIL: {$label} — expected {$expected}, got {$actual}\n";
        $failures++;
    }
}

assertSame('valid', AttemptValidityLabel::forStatus('passed'), 'passed');
assertSame('expired', AttemptValidityLabel::forStatus('outdated'), 'outdated');
assertSame('not_applicable', AttemptValidityLabel::forStatus('failed'), 'failed');

if ($failures > 0) {
    echo "{$failures} test(s) failed.\n";
    exit(1);
}

echo "All attempt validity label tests passed.\n";
