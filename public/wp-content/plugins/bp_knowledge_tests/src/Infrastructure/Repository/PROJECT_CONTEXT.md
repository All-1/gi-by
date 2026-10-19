# Component Context: bp_knowledge_tests/src/Infrastructure/Repository

SQL via `DBWorker` — reads use `selectUni_2` + [RecordMapper](../Mapping/PROJECT_CONTEXT.md); writes use `DBUtilities::packageWriteColumns`.

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

| Repository | Entity |
|------------|--------|
| `TestRepository` | `gi_new_tests` |
| `QuestionRepository` | `gi_new_test_questions` |
| `AnswerRepository` | `gi_new_test_answers` (`is_correct`) |
| `ConfigRepository` | `gi_new_test_config` |
| `AttemptRepository` | `gi_new_test_attempts` |
| `AttemptQuestionRepository` | `gi_new_test_attempt_questions` |
| `FinishedExplanationRepository` | `gi_new_test_finished_attempts_explanations` |
| `AchievementRepository` | `gi_new_test_achievements` |
| `UserAchievementRepository` | `gi_new_test_user_achievements` |
| `NotificationRepository` | `gi_new_test_notifications` |
| `AttemptAdminRepository` | Results list joins |
| `AttemptAnalyticsRepository` | Analytics aggregates |

Table key names (e.g. `KnowledgeTestAnswers`) map in [bp_contracts DBWorker](../../../bp_contracts/src/Workers/DBWorker.php).
