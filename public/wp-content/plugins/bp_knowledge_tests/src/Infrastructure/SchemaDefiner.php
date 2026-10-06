<?php

declare(strict_types=1);

namespace BpKnowledgeTests\Infrastructure;

use PersonalAccount\Core\Container;
use PersonalAccount\Workers\DBWorker;

final class SchemaDefiner
{
    private DBWorker $db;

    public function __construct(
        private \wpdb $wpdb,
        Container $servicesContainer,
    ) {
        $this->db = $servicesContainer->get('DBWorker');
    }

    public function createTables(): void
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ($this->createTableStatements() as $sql) {
            dbDelta($sql);
        }
    }

    public function upgradeAnswersIsCorrectColumn(): void
    {
        if (!$this->db->columnExists('KnowledgeTestAnswers', 'is_correct')) {
            $this->wpdb->query(
                'ALTER TABLE gi_new_test_answers
                ADD COLUMN is_correct tinyint(1) NOT NULL DEFAULT 0 AFTER answer'
            );
        }

        if ($this->db->tableExists('KnowledgeTestCorrectAnswerOptions')) {
            $this->wpdb->query(
                'UPDATE gi_new_test_answers a
                INNER JOIN gi_new_test_correct_answer_options c ON c.answer_id = a.id
                SET a.is_correct = 1'
            );
            $this->dropCorrectAnswerOptionsTable();
        }
    }

    public function installForeignKeys(): void
    {
        foreach ($this->foreignKeyConstraints() as $definition) {
            $table = $definition['table'];
            if ($this->foreignKeyExists($table, $definition['name'])) {
                continue;
            }
            $this->wpdb->query("ALTER TABLE `{$table}` {$definition['sql']}");
        }
    }

    /**
     * @return list<string>
     */
    private function createTableStatements(): array
    {
        $charset = $this->wpdb->get_charset_collate();

        return [
            "CREATE TABLE gi_new_tests (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL DEFAULT '',
                version int(10) unsigned NOT NULL DEFAULT 1,
                achievement_area varchar(255) DEFAULT NULL,
                date_creation datetime NOT NULL,
                date_modified datetime NOT NULL,
                PRIMARY KEY  (id)
            ) $charset;",
            "CREATE TABLE gi_new_test_questions (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                test_id int(10) unsigned NOT NULL,
                question text NOT NULL,
                explanation text NOT NULL,
                reference text NOT NULL,
                date_added datetime NOT NULL,
                date_modified datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY test_id (test_id)
            ) $charset;",
            "CREATE TABLE gi_new_test_answers (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                question_id int(10) unsigned NOT NULL,
                answer text NOT NULL,
                is_correct tinyint(1) NOT NULL DEFAULT 0,
                date_added datetime NOT NULL,
                date_modified datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY question_id (question_id)
            ) $charset;",
            "CREATE TABLE gi_new_test_attempts (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                user_id int(10) unsigned NOT NULL,
                test_id int(10) unsigned NOT NULL,
                test_version int(10) unsigned NOT NULL,
                valid_answers int(10) unsigned NOT NULL DEFAULT 0,
                invalid_answers int(10) unsigned NOT NULL DEFAULT 0,
                score decimal(6,2) NOT NULL DEFAULT 0.00,
                status varchar(20) NOT NULL DEFAULT 'failed',
                date_finished datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY user_test (user_id, test_id)
            ) $charset;",
            "CREATE TABLE gi_new_test_attempt_questions (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                user_id int(10) unsigned NOT NULL,
                attempt_id int(10) unsigned NOT NULL,
                question_id int(10) unsigned NOT NULL,
                right_answers int(10) unsigned NOT NULL DEFAULT 0,
                failed_answers int(10) unsigned NOT NULL DEFAULT 0,
                date_finished datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY attempt_id (attempt_id)
            ) $charset;",
            "CREATE TABLE gi_new_test_finished_attempts_explanations (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                attempt_id int(10) unsigned NOT NULL,
                question_id int(10) unsigned NOT NULL,
                user_id int(10) unsigned NOT NULL,
                explanation text NOT NULL,
                reference text NOT NULL,
                PRIMARY KEY  (id),
                KEY attempt_id (attempt_id)
            ) $charset;",
            "CREATE TABLE gi_new_test_achievements (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(64) NOT NULL DEFAULT '',
                value varchar(32) NOT NULL DEFAULT '',
                PRIMARY KEY  (id)
            ) $charset;",
            "CREATE TABLE gi_new_test_user_achievements (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                user_id int(10) unsigned NOT NULL,
                test_id int(10) unsigned NOT NULL,
                medal_id int(10) unsigned NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY user_test (user_id, test_id)
            ) $charset;",
            "CREATE TABLE gi_new_test_notifications (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                user_id int(10) unsigned NOT NULL,
                test_id int(10) unsigned NOT NULL,
                notification_type varchar(128) NOT NULL DEFAULT '',
                dismiss_count int(10) unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY  (id),
                KEY user_test (user_id, test_id)
            ) $charset;",
            "CREATE TABLE gi_new_test_config (
                id int(10) unsigned NOT NULL AUTO_INCREMENT,
                name varchar(128) NOT NULL DEFAULT '',
                value text NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY name (name)
            ) $charset;",
        ];
    }

    /**
     * @return list<array{table: string, name: string, sql: string}>
     */
    private function foreignKeyConstraints(): array
    {
        return [
            [
                'table' => 'gi_new_test_questions',
                'name' => 'fk_kt_questions_test',
                'sql' => 'ADD CONSTRAINT fk_kt_questions_test FOREIGN KEY (test_id)
                    REFERENCES gi_new_tests (id) ON DELETE CASCADE',
            ],
            [
                'table' => 'gi_new_test_answers',
                'name' => 'fk_kt_answers_question',
                'sql' => 'ADD CONSTRAINT fk_kt_answers_question FOREIGN KEY (question_id)
                    REFERENCES gi_new_test_questions (id) ON DELETE CASCADE',
            ],
            [
                'table' => 'gi_new_test_attempts',
                'name' => 'fk_kt_attempts_test',
                'sql' => 'ADD CONSTRAINT fk_kt_attempts_test FOREIGN KEY (test_id)
                    REFERENCES gi_new_tests (id) ON DELETE RESTRICT',
            ],
            [
                'table' => 'gi_new_test_attempts',
                'name' => 'fk_kt_attempts_user',
                'sql' => 'ADD CONSTRAINT fk_kt_attempts_user FOREIGN KEY (user_id)
                    REFERENCES gi_new_users (id_user) ON DELETE RESTRICT',
            ],
            [
                'table' => 'gi_new_test_attempt_questions',
                'name' => 'fk_kt_attempt_q_attempt',
                'sql' => 'ADD CONSTRAINT fk_kt_attempt_q_attempt FOREIGN KEY (attempt_id)
                    REFERENCES gi_new_test_attempts (id) ON DELETE CASCADE',
            ],
            [
                'table' => 'gi_new_test_attempt_questions',
                'name' => 'fk_kt_attempt_q_question',
                'sql' => 'ADD CONSTRAINT fk_kt_attempt_q_question FOREIGN KEY (question_id)
                    REFERENCES gi_new_test_questions (id) ON DELETE RESTRICT',
            ],
            [
                'table' => 'gi_new_test_attempt_questions',
                'name' => 'fk_kt_attempt_q_user',
                'sql' => 'ADD CONSTRAINT fk_kt_attempt_q_user FOREIGN KEY (user_id)
                    REFERENCES gi_new_users (id_user) ON DELETE RESTRICT',
            ],
            [
                'table' => 'gi_new_test_finished_attempts_explanations',
                'name' => 'fk_kt_explanations_attempt',
                'sql' => 'ADD CONSTRAINT fk_kt_explanations_attempt FOREIGN KEY (attempt_id)
                    REFERENCES gi_new_test_attempts (id) ON DELETE CASCADE',
            ],
            [
                'table' => 'gi_new_test_finished_attempts_explanations',
                'name' => 'fk_kt_explanations_question',
                'sql' => 'ADD CONSTRAINT fk_kt_explanations_question FOREIGN KEY (question_id)
                    REFERENCES gi_new_test_questions (id) ON DELETE RESTRICT',
            ],
            [
                'table' => 'gi_new_test_finished_attempts_explanations',
                'name' => 'fk_kt_explanations_user',
                'sql' => 'ADD CONSTRAINT fk_kt_explanations_user FOREIGN KEY (user_id)
                    REFERENCES gi_new_users (id_user) ON DELETE RESTRICT',
            ],
            [
                'table' => 'gi_new_test_user_achievements',
                'name' => 'fk_kt_user_ach_test',
                'sql' => 'ADD CONSTRAINT fk_kt_user_ach_test FOREIGN KEY (test_id)
                    REFERENCES gi_new_tests (id) ON DELETE RESTRICT',
            ],
            [
                'table' => 'gi_new_test_user_achievements',
                'name' => 'fk_kt_user_ach_medal',
                'sql' => 'ADD CONSTRAINT fk_kt_user_ach_medal FOREIGN KEY (medal_id)
                    REFERENCES gi_new_test_achievements (id) ON DELETE RESTRICT',
            ],
            [
                'table' => 'gi_new_test_user_achievements',
                'name' => 'fk_kt_user_ach_user',
                'sql' => 'ADD CONSTRAINT fk_kt_user_ach_user FOREIGN KEY (user_id)
                    REFERENCES gi_new_users (id_user) ON DELETE RESTRICT',
            ],
            [
                'table' => 'gi_new_test_notifications',
                'name' => 'fk_kt_notifications_test',
                'sql' => 'ADD CONSTRAINT fk_kt_notifications_test FOREIGN KEY (test_id)
                    REFERENCES gi_new_tests (id) ON DELETE CASCADE',
            ],
            [
                'table' => 'gi_new_test_notifications',
                'name' => 'fk_kt_notifications_user',
                'sql' => 'ADD CONSTRAINT fk_kt_notifications_user FOREIGN KEY (user_id)
                    REFERENCES gi_new_users (id_user) ON DELETE CASCADE',
            ],
        ];
    }

    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $found = $this->wpdb->get_var(
            $this->wpdb->prepare(
                'SELECT COUNT(1) FROM information_schema.TABLE_CONSTRAINTS
                 WHERE CONSTRAINT_SCHEMA = %s AND TABLE_NAME = %s AND CONSTRAINT_NAME = %s',
                DB_NAME,
                $table,
                $constraintName
            )
        );

        return (int) $found > 0;
    }

    private function dropCorrectAnswerOptionsTable(): void
    {
        foreach (['fk_kt_correct_q_answer', 'fk_kt_correct_q_question'] as $constraint) {
            if ($this->foreignKeyExists('gi_new_test_correct_answer_options', $constraint)) {
                $this->wpdb->query(
                    "ALTER TABLE gi_new_test_correct_answer_options DROP FOREIGN KEY `{$constraint}`"
                );
            }
        }
        $this->wpdb->query('DROP TABLE IF EXISTS gi_new_test_correct_answer_options');
    }
}
