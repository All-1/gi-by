-- Schema v4 — correct option flag on gi_new_test_answers (D6)
-- Applied by PluginBootstrap when bp_knowledge_tests_schema_version < 4.
-- Migrates data from gi_new_test_correct_answer_options if that v3 experiment table exists.

ALTER TABLE gi_new_test_answers
    ADD COLUMN is_correct tinyint(1) NOT NULL DEFAULT 0 AFTER answer;

-- If junction table exists from an earlier v3 activation:
-- UPDATE gi_new_test_answers a
--   INNER JOIN gi_new_test_correct_answer_options c ON c.answer_id = a.id
--   SET a.is_correct = 1;
-- DROP TABLE gi_new_test_correct_answer_options;
