# Component Context: bp_knowledge_tests/src/Domain

**Pure** domain logic: no WordPress, no SQL, no HTTP. Spec: [§2 Agreed Business Rules](../../spec/Testing%20System%20%E2%80%94%20Project%20Documentation.md#2-agreed-business-rules-and-clarifications).

**Parent:** [../PROJECT_CONTEXT.md](../PROJECT_CONTEXT.md)

## Child directories

| Directory | Context |
|-----------|---------|
| [Record/](./Record/PROJECT_CONTEXT.md) | Row DTOs mapped from `DBWorker` |

---

## Scoring pipeline

```text
Per question (during attempt):
  correctAnswerIds + selectedAnswerIds
      → QuestionSelectionGrader::grade()
      → QuestionSelectionCounts (right / failed selections)

At finish (whole test):
  sum right → valid_answers
  sum failed → invalid_answers
  maximumCorrect = catalog count of is_correct options
      → ScoringService::calculateScore(valid, invalid, maximum)
      → float 0..100

Pass / medal / lock:
  TestConfig from gi_new_test_config (via TestConfigReader in Services)
      → AttemptResultClassifier::classify(score, config)
      → AttemptClassification (passed, medal tier, locked)
```

**Important:** Official score applies **one** global floor at 0% and cap at 100% — not per-question clamp then sum (§2.1.1).

---

## Types

| Class | Role |
|-------|------|
| `QuestionSelectionGrader` | Checkbox-level counting |
| `QuestionSelectionCounts` | `rightAnswers`, `failedAnswers` for one question |
| `ScoringService` | Net formula on totals (in `Services/` but algorithm is spec §2.1) |
| `AttemptResultClassifier` | Bronze pass, silver/gold tiers, lock at threshold |
| `AttemptClassification` | Result DTO from classifier |
| `TestConfig` | Thresholds + notification settings snapshot |
| `NotificationSettings` | Dismiss limits and modes |
| `RetakeEligibility` | Outcome of retake policy (used by Services) |
| `AttemptValidityLabel` | Admin display: valid / expired / N/A |

Records: [Record/PROJECT_CONTEXT.md](./Record/PROJECT_CONTEXT.md).

---

## Tests

| Script | Focus |
|--------|--------|
| `tests/run_scoring_tests.php` | `ScoringService` edge cases |
| `tests/run_selection_grader_tests.php` | Multi-select counting |
| `tests/run_achievement_tier_tests.php` | Medal mapping |

Config-dependent behavior is tested with fixtures or in integration scripts under [tests/](../../tests/PROJECT_CONTEXT.md).
