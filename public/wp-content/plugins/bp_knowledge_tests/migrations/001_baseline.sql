-- Knowledge tests V1 — baseline schema (version 1)
-- Reference only: plugin activation uses SchemaDefiner::createTables() + dbDelta.
-- Column definitions: spec/IMPLEMENTATION_PLAN.md §5
-- After manual apply: set wp_option bp_knowledge_tests_schema_version = 2 (includes FKs)

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- gi_new_tests
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gi_new_tests (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    title varchar(255) NOT NULL DEFAULT '',
    version int(10) unsigned NOT NULL DEFAULT 1,
    achievement_area varchar(255) DEFAULT NULL,
    date_creation datetime NOT NULL,
    date_modified datetime NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- gi_new_test_questions
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gi_new_test_questions (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    test_id int(10) unsigned NOT NULL,
    question text NOT NULL,
    explanation text NOT NULL,
    reference text NOT NULL,
    date_added datetime NOT NULL,
    date_modified datetime NOT NULL,
    PRIMARY KEY (id),
    KEY test_id (test_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- gi_new_test_answers
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gi_new_test_answers (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    question_id int(10) unsigned NOT NULL,
    answer text NOT NULL,
    date_added datetime NOT NULL,
    date_modified datetime NOT NULL,
    PRIMARY KEY (id),
    KEY question_id (question_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- gi_new_test_attempts (completed attempts only)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gi_new_test_attempts (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    user_id int(10) unsigned NOT NULL,
    test_id int(10) unsigned NOT NULL,
    test_version int(10) unsigned NOT NULL,
    valid_answers int(10) unsigned NOT NULL DEFAULT 0,
    invalid_answers int(10) unsigned NOT NULL DEFAULT 0,
    score decimal(6,2) NOT NULL DEFAULT 0.00,
    status varchar(20) NOT NULL DEFAULT 'failed',
    date_finished datetime NOT NULL,
    PRIMARY KEY (id),
    KEY user_test (user_id, test_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- gi_new_test_attempt_questions
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gi_new_test_attempt_questions (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    user_id int(10) unsigned NOT NULL,
    attempt_id int(10) unsigned NOT NULL,
    question_id int(10) unsigned NOT NULL,
    right_answers int(10) unsigned NOT NULL DEFAULT 0,
    failed_answers int(10) unsigned NOT NULL DEFAULT 0,
    date_finished datetime NOT NULL,
    PRIMARY KEY (id),
    KEY attempt_id (attempt_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- gi_new_finished_attempts_explanations (temporary materials)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gi_new_finished_attempts_explanations (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    attempt_id int(10) unsigned NOT NULL,
    question_id int(10) unsigned NOT NULL,
    user_id int(10) unsigned NOT NULL,
    explanation text NOT NULL,
    reference text NOT NULL,
    PRIMARY KEY (id),
    KEY attempt_id (attempt_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- gi_new_test_achievements
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gi_new_test_achievements (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    name varchar(64) NOT NULL DEFAULT '',
    value varchar(32) NOT NULL DEFAULT '',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- gi_new_test_user_achievements
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gi_new_test_user_achievements (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    user_id int(10) unsigned NOT NULL,
    test_id int(10) unsigned NOT NULL,
    medal_id int(10) unsigned NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY user_test (user_id, test_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- gi_new_test_notifications
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gi_new_test_notifications (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    user_id int(10) unsigned NOT NULL,
    test_id int(10) unsigned NOT NULL,
    dismiss_count int(10) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY user_test (user_id, test_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- gi_new_test_config
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS gi_new_test_config (
    id int(10) unsigned NOT NULL AUTO_INCREMENT,
    name varchar(128) NOT NULL DEFAULT '',
    value text NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Default config seed (same as Seeder)
-- ---------------------------------------------------------------------------
-- ---------------------------------------------------------------------------
-- Foreign keys (v2 — also applied by SchemaDefiner::installForeignKeys() on activation)
-- Requires InnoDB + gi_new_users.id_user. Fix orphan rows before running.
-- ---------------------------------------------------------------------------
ALTER TABLE gi_new_test_questions
    ADD CONSTRAINT fk_kt_questions_test FOREIGN KEY (test_id)
    REFERENCES gi_new_tests (id) ON DELETE CASCADE;

ALTER TABLE gi_new_test_answers
    ADD CONSTRAINT fk_kt_answers_question FOREIGN KEY (question_id)
    REFERENCES gi_new_test_questions (id) ON DELETE CASCADE;

ALTER TABLE gi_new_test_attempts
    ADD CONSTRAINT fk_kt_attempts_test FOREIGN KEY (test_id)
    REFERENCES gi_new_tests (id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_kt_attempts_user FOREIGN KEY (user_id)
    REFERENCES gi_new_users (id_user) ON DELETE RESTRICT;

ALTER TABLE gi_new_test_attempt_questions
    ADD CONSTRAINT fk_kt_attempt_q_attempt FOREIGN KEY (attempt_id)
    REFERENCES gi_new_test_attempts (id) ON DELETE CASCADE,
    ADD CONSTRAINT fk_kt_attempt_q_question FOREIGN KEY (question_id)
    REFERENCES gi_new_test_questions (id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_kt_attempt_q_user FOREIGN KEY (user_id)
    REFERENCES gi_new_users (id_user) ON DELETE RESTRICT;

ALTER TABLE gi_new_finished_attempts_explanations
    ADD CONSTRAINT fk_kt_explanations_attempt FOREIGN KEY (attempt_id)
    REFERENCES gi_new_test_attempts (id) ON DELETE CASCADE,
    ADD CONSTRAINT fk_kt_explanations_question FOREIGN KEY (question_id)
    REFERENCES gi_new_test_questions (id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_kt_explanations_user FOREIGN KEY (user_id)
    REFERENCES gi_new_users (id_user) ON DELETE RESTRICT;

ALTER TABLE gi_new_test_user_achievements
    ADD CONSTRAINT fk_kt_user_ach_test FOREIGN KEY (test_id)
    REFERENCES gi_new_tests (id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_kt_user_ach_medal FOREIGN KEY (medal_id)
    REFERENCES gi_new_test_achievements (id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_kt_user_ach_user FOREIGN KEY (user_id)
    REFERENCES gi_new_users (id_user) ON DELETE RESTRICT;

ALTER TABLE gi_new_test_notifications
    ADD CONSTRAINT fk_kt_notifications_test FOREIGN KEY (test_id)
    REFERENCES gi_new_tests (id) ON DELETE CASCADE,
    ADD CONSTRAINT fk_kt_notifications_user FOREIGN KEY (user_id)
    REFERENCES gi_new_users (id_user) ON DELETE CASCADE;

-- ---------------------------------------------------------------------------
-- Default config seed (same as Seeder)
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO gi_new_test_config (name, value) VALUES
    ('bronze_threshold', '75'),
    ('silver_threshold', '85'),
    ('gold_threshold', '95'),
    ('lock_threshold', '95'),
    ('retake_delay_days', '1'),
    ('new_test_notification_dismiss_limit', '10'),
    ('failed_test_notification_dismiss_limit', '10'),
    ('new_test_notification_mode', 'bottom'),
    ('failed_test_notification_mode', 'bottom');

INSERT IGNORE INTO gi_new_test_achievements (name, value) VALUES
    ('rank_name_gold', 'Guru'),
    ('rank_name_silver', 'Expert'),
    ('rank_name_bronze', 'Specialist'),
    ('rank_name_failed', 'Failed');
