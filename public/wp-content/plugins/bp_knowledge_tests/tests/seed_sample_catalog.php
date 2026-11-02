<?php

declare(strict_types=1);

/**
 * One-shot sample catalog: 15 tests × 15 questions × 5 answers.
 * Correct answers include the word "right". Run: php tests/seed_sample_catalog.php
 */

$wpLoad = dirname(__DIR__, 4) . '/wp-load.php';
if (!is_readable($wpLoad)) {
    fwrite(STDERR, "wp-load.php not found\n");
    exit(1);
}

require $wpLoad;

global $wpdb;

$marker = 'SEED-KT';
$existing = (int) $wpdb->get_var(
    $wpdb->prepare(
        'SELECT COUNT(*) FROM gi_new_tests WHERE title LIKE %s',
        $marker . '%'
    )
);
if ($existing > 0) {
    fwrite(STDOUT, "Sample catalog already present ({$existing} tests). Nothing inserted.\n");
    exit(0);
}

$now = current_time('mysql');
$areas = [
    'Кухни',
    'Фасады',
    'Фурнитура',
    'Столешницы',
    'Замеры',
    'Монтаж',
    'Материалы',
    'Комплектация',
    'Гарантия',
    'Логистика',
    'Салоны',
    'Договоры',
    'Претензии',
    'Витрины',
    'Сервис',
];

$tests = 15;
$questionsPerTest = 15;
$answersPerQuestion = 5;
$insertedTests = 0;
$insertedQuestions = 0;
$insertedAnswers = 0;

$wpdb->query('START TRANSACTION');

for ($t = 1; $t <= $tests; $t++) {
    $area = $areas[$t - 1];
    $title = sprintf('%s %02d. %s', $marker, $t, $area);
    $ok = $wpdb->insert(
        'gi_new_tests',
        [
            'title' => $title,
            'version' => 1,
            'achievement_area' => $area,
            'date_creation' => $now,
            'date_modified' => $now,
        ],
        ['%s', '%d', '%s', '%s', '%s']
    );
    if ($ok === false) {
        $wpdb->query('ROLLBACK');
        fwrite(STDERR, 'Test insert failed: ' . $wpdb->last_error . "\n");
        exit(1);
    }
    $testId = (int) $wpdb->insert_id;
    $insertedTests++;

    for ($q = 1; $q <= $questionsPerTest; $q++) {
        $question = sprintf('Тест %02d, вопрос %02d. Что относится к теме «%s»?', $t, $q, $area);
        $explanation = sprintf(
            'Пояснение к вопросу %02d теста «%s»: верные варианты помечены словом right. Тема — %s.',
            $q,
            $title,
            $area
        );
        $reference = sprintf('https://dev-local.gi.by/tests/materials/%02d/%02d', $t, $q);
        $ok = $wpdb->insert(
            'gi_new_test_questions',
            [
                'test_id' => $testId,
                'question' => $question,
                'explanation' => $explanation,
                'reference' => $reference,
                'date_added' => $now,
                'date_modified' => $now,
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s']
        );
        if ($ok === false) {
            $wpdb->query('ROLLBACK');
            fwrite(STDERR, 'Question insert failed: ' . $wpdb->last_error . "\n");
            exit(1);
        }
        $questionId = (int) $wpdb->insert_id;
        $insertedQuestions++;

        $correctCount = ($q % 5 === 0) ? 2 : 1;
        for ($a = 1; $a <= $answersPerQuestion; $a++) {
            $isCorrect = $a <= $correctCount ? 1 : 0;
            $answer = $isCorrect
                ? sprintf('right — верный ответ %d (вопрос %02d)', $a, $q)
                : sprintf('Неверный вариант %d для вопроса %02d', $a, $q);
            $ok = $wpdb->insert(
                'gi_new_test_answers',
                [
                    'question_id' => $questionId,
                    'answer' => $answer,
                    'is_correct' => $isCorrect,
                    'date_added' => $now,
                    'date_modified' => $now,
                ],
                ['%d', '%s', '%d', '%s', '%s']
            );
            if ($ok === false) {
                $wpdb->query('ROLLBACK');
                fwrite(STDERR, 'Answer insert failed: ' . $wpdb->last_error . "\n");
                exit(1);
            }
            $insertedAnswers++;
        }
    }
}

$wpdb->query('COMMIT');

fwrite(
    STDOUT,
    "Inserted tests={$insertedTests} questions={$insertedQuestions} answers={$insertedAnswers}\n"
);
