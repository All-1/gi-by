# Testing System

---

# 1. Original Requirements — Baseline

This section contains the original requirements translated into English.

**The baseline requirements in this section must not be silently edited, rewritten, shortened, or replaced.**

The requirements **may be modified during the project**, but any modification must be made through an explicit project decision and documented in the sections below.

The purpose of keeping this section unchanged is to preserve the original business requirements and allow us to distinguish clearly between:

- the original requirement;
- an agreed clarification;
- an architectural decision;
- a later modification of the requirement.

---

## User Mechanism

1. **Each question is displayed on a separate page with its own answer options.**

2. **We show the user only which answers they got right and which answers they got wrong.**  
   If the user did not select at least one correct answer, or selected at least one incorrect answer, we show a link to the relevant document together with a short explanation of where to find the necessary information.

3. **Each question has the same weight within the overall test.**  
   Within the question itself, we calculate how many percent of the question was completed correctly, based on the correct and incorrect answers.

4. **We introduce three different stars:**

   - Bronze — 70%
   - Silver — 80%
   - Gold — 90%

   The star is visible to everyone and is displayed next to the user's name in every message.

5. **All links to documents that the user needs to read are shown to the user at the end of the test and are also saved in the user's Tests section.**

   The user's completed test is marked accordingly, and there should be a function allowing the user not only to retake the test if it was completed poorly, but also to access all the materials they need to study.

   There should also be an option to track whether the user has reviewed the material, or at least provide a checkbox confirming that they have reviewed it.

   A checkbox should be displayed next to each link.

6. **When a new test becomes available and the user is notified about it for the first time, a notification popup appears when the user enters their account, with a link to the Tests page.**

   The notification can be closed up to 10 times.

   After that, Igor proposes making the notification persistent and blocking work in the account until the test is completed, regardless of the result.

   This notification appears only if the previous test has been completed fully.

   Once the user has already seen this message, it becomes a notification at the bottom of the screen that can be closed, similar to the browser notification-consent message.

   Igor proposes making this effectively mandatory.

7. **If the test result is poor, the Tests menu item is displayed in red.**

   A notification also appears at the bottom of the screen every time the user enters the account, informing them that they should study the relevant materials.

   The notification contains:

   - a **"Understood"** button;
   - a link to the page with the materials selected for them based on the previous test.

8. **The test remains valid and marked as passed for the user for at least one year, even if the test itself has been modified many times by the test factory.**

9. **When we display questions and answers to users in each test, we change both the order of the questions and the order of the answers within the test.**

10. **The same test can only be retaken on the following day.**

11. **If a user leaves a test unfinished, their progress is saved and the test starts from the same place when they return.**  
    *(Or for 24 hours.)*

---

## Technical Details

1. **Tests will be created in the administrative panel.**

2. **Each question must support an unlimited number of answers.**

3. **There must be an option to attach links to learning materials together with a short explanation, where we can specify what exactly should be read and what the user should pay attention to.**

4. **There must be analytics showing the difficulty of individual questions.**

5. **If the tests become more diverse, a gamification system should be introduced.**

   Examples:

   - Expert in ...
   - Specialist in ...
   - Guru in ...

   These achievements should appear when the user hovers over the star.

6. **The administrator must have a button in the administrative panel:**

   **"Critical Product Update: invalidate the results of the old test for all users and require them to take the new test."**

---

# 2. Agreed Business Rules and Clarifications

This section records decisions made during project discussion. These decisions clarify or modify the baseline requirements and therefore take precedence where an explicit conflict exists.

---

## 2.1 Scoring

The final test score is calculated from the total number of correct answers and incorrect answers selected by the user across the entire test.

The total number of correct answers available in the complete test is the **Maximum Correct Answers**.

### Formula

**Score = 100 × (Correct Answers − Incorrect Answers) / Maximum Correct Answers**

The minimum score is **0%**.

The score must never become negative.

Conceptually:

```text
Net Correct = Correct Answers − Incorrect Answers

Score = 100 × Net Correct / Maximum Correct Answers

Minimum Score = 0%
```

Example:

```text
Test:
20 questions

Maximum Correct Answers:
62

User:
60 correct answers
3 incorrect answers

Net Correct:
60 − 3 = 57

Score:
100 × 57 / 62 = 91.94%
```

Therefore, the maximum possible result is 100%.

An incorrect selected answer is a penalty and reduces the user's score.

---

## 2.2 Passed

A test is considered **Passed** when the user's completed result reaches at least the currently configured Bronze threshold.

The thresholds for:

- Bronze;
- Silver;
- Gold

must be configurable through the administration panel.

Example configuration:

```text
Bronze: 70%
Silver: 80%
Gold:   90%
```

Changing these thresholds must be possible without changing the underlying test logic.

If the user has passed the test:

- the test is considered passed for that user;
- the Tests menu does not display the failed/red indicator for that test;
- the user does not receive the failed-test notification for that test.

---

## 2.3 Test Validity

A successful test result remains valid for **one year from the date on which the user passed the test**.

Example:

```text
Test passed:
16/09/2026

Valid until:
16/09/2027
```

If the administrator changes the test on:

```text
10/11/2026
```

the user's result remains valid until:

```text
16/09/2027
```

Further changes to the test do not automatically invalidate the user's existing valid result.

The validity period is associated with the user's completed result and the date on which it was achieved.

---

## 2.4 Test and Test Version

A **Test** is a set of questions designed to help users understand what knowledge gaps they have in a particular area of the test.

A **Test Version** is the same logical test after its content has been changed, for example when:

- questions are added;
- questions are deleted;
- answers for existing questions are changed.

A separate `test_versions` table is not required for the current design.

Instead, the current version is represented by the `version` field of the test itself.

Conceptually:

```text
Test
id
title
version
date_creation
date_modified
```

When the test is changed in a way that creates a new version, its version number is increased.

Historical attempts record the version number under which the attempt was completed.

---

## 2.5 In-Progress Attempts

A user who starts a test but does not finish it is **not recorded as a completed attempt in the database**.

The unfinished attempt exists temporarily in application memory.

A distinct `TestController` class should be created in `bp_contracts` for this purpose.

Conceptually:

```text
User
  ↓
bp_contracts
  ↓
TestController
  ↓
In-memory test attempt
```

The in-memory attempt contains the current state necessary to continue the test.

If the user leaves the test or closes the application/browser while the server is still running, the unfinished attempt remains available in memory.

When the user returns, `TestController` can find the unfinished test and continue it from the current position.

The unfinished attempt is available until the regular server restart at:

**03:00 AM**

The 03:00 AM restart therefore clears unfinished in-memory test attempts.

An unfinished attempt:

- does not count as a completed attempt;
- does not consume the retake restriction;
- does not produce a result;
- does not produce a medal.

---

## 2.6 Retake Period

The interval before another completed attempt becomes available must be configurable from the administration panel.

The configuration is a simple number-of-days input field.

Examples:

```text
1
2
3
7
```

The interpretation of:

```text
1 day
```

is:

**the following day.**

Therefore the administrator can specify the required number of days directly.

An unfinished attempt does not activate the retake period.

A completed attempt does activate the configured retake period.

For a failed test, the user must also complete the required temporary learning process before taking the test again.

The relevant conditions are:

```text
Configured retake period has elapsed
AND
Required references have been reviewed
```

---

## 2.7 Randomization

The system randomizes both:

1. the order of questions;
2. the order of answers inside every question.

Example:

```text
Original:

Question 1
Question 2
Question 3
```

may be displayed as:

```text
Question 2
Question 3
Question 1
```

For Question 1:

```text
A B C D E
```

may become:

```text
D C B E A
```

For Question 2:

```text
J K L M
```

may become:

```text
L J M K
```

The randomization changes only the presentation order.

The logical identity and correctness of answers remain unchanged.

The randomized order is **not stored in the database**.

It is generated dynamically at runtime when the test data is received from the database.

---

## 2.8 Feedback and References

Learning materials themselves do **not** belong to questions.

The question stores:

- an explanation;
- a reference to where the relevant information can be found;
- information about what the user should pay particular attention to.

Conceptually:

```text
Question
├── Answers
├── Explanation
└── Reference
```

If the user makes any mistake in a question, the explanation and reference associated with that question are shown.

At the end of the test, the user can see the questions where mistakes were made together with their explanations and references.

The system does not need to preserve the user's exact selected answer after the user moves to the next question.

---

## 2.9 Temporary Learning Page — V1

V1 does not introduce a permanent materials-management system.

The system creates a temporary learning page containing the references required after a user makes mistakes.

For each problematic question, the page displays:

```text
Question
Explanation
Reference

[ ] Reviewed
```

The user must review every required reference and then press:

**"Examined"**

After confirmation, the temporary learning page and its temporary database records are deleted.

A future separate materials system/plugin will provide permanent materials management, including search and other functionality.

---

## 2.10 Temporary References in the Tests Section

The references required after mistakes are temporarily available in the user's Tests section.

The user can open the temporary materials page from the Tests interface.

The references are intended only for this temporary learning workflow.

After the user has reviewed all required references and presses **"Examined"**, the temporary records are removed.

Historical test results and attempts remain stored.

---

## 2.11 Stars and Latest Result

The visible medal/star represents the **latest completed test result** for the corresponding test.

It does not represent the user's best historical result.

Example:

```text
Previous result:
90% → Gold

Latest result:
80% → Silver

Current result:
Silver
```

Therefore a later result may replace a previously higher result.

The current values in the achievement configuration determine the medal associated with the latest result.

---

## 2.12 Test Lock at 95%

A result of **95% or higher** locks that test for the user.

Once the user achieves 95% or more:

- the test becomes disabled for that user;
- the user cannot open it again;
- the user cannot retake it.

Examples:

```text
94.99% → not locked
95.00% → locked
95.01% → locked
100%   → locked
```

The lock value should be configurable through the test configuration system.

---

## 2.13 Test States in the Database

The database stores information only about **completed attempts**.

A completed attempt can have one of three states:

```text
Failed
Passed
Outdated
```

The database does **not** store:

- Not started;
- In progress.

`In progress` exists only temporarily in application memory while the unfinished attempt is maintained by `TestController`.

`Not started` is derived by the application when the user has no completed attempt for the relevant test and no active in-progress attempt.

---

## 2.14 Test States in the Application

When tests are displayed to the user, the application can show the following states:

```text
Not started
In progress
Failed
Passed
Outdated
```

### Not started

The user has no completed attempt for the test and no active unfinished attempt.

### In progress

The user has an unfinished test attempt currently stored in application memory and the server has not yet restarted.

### Failed

The user has completed the test but did not reach the configured Bronze threshold.

### Passed

The user has completed the test and reached at least the configured Bronze threshold.

### Outdated

The user's previously passed result is no longer valid.

The application derives these display states from the persistent database records and the current in-memory state.

---

## 2.15 Outdated Logic

When the user is authorized in the application, the system retrieves information about tests from the database and compares the test's `date_modified` with the user's completed attempt's `date_finished`.

If the test was modified after the user completed the test, the system checks whether the current date is more than one year after the user's `date_finished`.

When the required validity period has expired, the result is changed to:

```text
Outdated
```

When a result becomes outdated, the corresponding achievement is removed.

If the administrator pushes the **Critical Update** button, the affected result is immediately changed to:

```text
Outdated
```

The corresponding achievement is removed at the same time.

Historical attempt information is retained.

---

# 3. Notification Logic

## 3.1 New Test

When a new test becomes available, the user must be notified.

The administrator can configure:

- how many times the notification can be dismissed;
- what happens after the dismissal limit is reached.

Possible behavior after the limit:

### Blocking

The notification blocks the user's normal account workflow until the user completes the test.

### Bottom notification

The notification becomes a small notification at the bottom of the browser/account interface.

The notification behavior must therefore be configurable from the administration panel.

The original rule that the new-test notification is shown only when the previous test has been completed remains part of the system.

---

## 3.2 Failed Test Notification

When the user fails a test:

- the Tests menu item becomes red;
- a notification is displayed;
- the notification behavior is controlled by configuration.

The administrator can configure the same main notification parameters as for a new test, including:

- number of dismissals;
- blocking behavior;
- bottom-notification behavior.

The user must review the required references before another attempt becomes available.

---

## 3.3 Materials Not Reviewed

If the user has made any mistakes in the completed test, the system provides the relevant explanations and references.

The user can open the temporary materials page and review the references.

The user marks each required reference and presses:

**"Examined"**

Until that process is completed, the user cannot take the test again.

The temporary learning state does not become a permanent test state in the database.

---

## 3.4 Materials Reviewed

After the user:

1. checks every required reference;
2. presses **"Examined"**;

the temporary learning page and its temporary database records are deleted.

The user becomes eligible for another test attempt once the configured retake period has also expired.

There is no requirement to maintain a permanent `Materials reviewed` test state for V1.

---

# 4. Question Difficulty Analytics

Question difficulty analytics are part of V1.

The system should collect enough raw information from completed attempts to calculate question difficulty later.

The raw data should allow us to determine:

- how many times a question was presented;
- how many users answered it;
- how many correct answers were selected;
- how many incorrect answers were selected;
- how many correct answers were missed;
- question-level score information;
- answer-level statistics;
- distribution of question results.

No separate `question_stats` table is currently required.

The historical attempt data in:

```text
gi_new_test_attempts
gi_new_test_attempt_questions
```

is the source of truth for question analytics.

---

## 4.1 Test-Level Analytics

Analytics must also provide a main frame at the **test level**.

Test-level analytics should provide an overall view of test performance.

Possible information includes:

- number of completed attempts;
- passed attempts;
- failed attempts;
- average score;
- score distribution;
- number of questions;
- overall performance trends.

The exact analytics presentation can be finalized separately.

---

## 4.2 Question-Level Analytics

Question-level analytics can show:

- how often the question produces mistakes;
- correct-answer statistics;
- incorrect-answer statistics;
- question score distribution;
- other indicators derived from the raw attempt data.

The exact final difficulty formula does not need to be fixed immediately.

The important requirement is:

> **V1 must collect the raw data necessary to calculate question difficulty later.**

---

# 5. Gamification

The system supports multiple stars next to one user's name.

A user may therefore have several stars representing different tests or areas.

Example:

```text
[Gold] [Silver] [Bronze] [Failed]
```

Each star corresponds to a particular test/area.

---

## 5.1 Two-Part Achievement Name

When the user hovers over a star, the system displays:

**Rank — Area**

Examples:

```text
Guru — PostgreSQL
Expert — Laravel
Specialist — Redis
Failed — Docker
```

The first part is the **Rank**.

The second part is the **Area** represented by the test.

---

## 5.2 Configurable Rank Names

Rank names must be configurable through the administration panel.

For example:

```text
Gold   → Guru
Silver → Expert
Bronze → Specialist
Failed → Failed
```

The names must not be hard-coded.

---

## 5.3 Configurable Area

The second part of the achievement name is configured individually for each test.

For example:

```text
Test: PostgreSQL Fundamentals
Area: PostgreSQL

Test: Laravel Security
Area: Laravel Security

Test: Redis
Area: Redis
```

The resulting hover text is:

```text
Guru — PostgreSQL
Expert — Laravel Security
Specialist — Redis
Failed — Docker
```

---

## 5.4 Multiple Stars

A user can have many stars.

Each star can represent a separate test and knowledge area.

Example:

```text
Gold — PostgreSQL
Silver — Laravel
Bronze — Redis
Failed — Docker
```

There is therefore no requirement for one global star representing all tests.

---

## 5.5 Failed Star

If the user has not passed a particular test, the corresponding star is displayed as a transparent star with an outline.

Hovering over it shows the relevant test/area.

Example:

```text
Failed — Docker
```

---

## 5.6 Achievement Configuration Changes

The achievement definitions are stored in the central testing configuration table.

When an achievement value changes, the system must check the affected user achievements and update the current user achievement state according to the new configuration.

---

# 6. Critical Product Update

The administrator has the ability to perform:

**"Critical Product Update: invalidate the results of the old test for all users and require them to take the new test."**

The action is available directly from the **Edit Test** interface.

The button is:

**"Critical Update"**

---

## 6.1 Critical Update Confirmation

The administrator must not be able to trigger the operation accidentally.

When the administrator clicks **"Critical Update"**:

1. a confirmation modal is displayed;
2. the modal asks the administrator to confirm that they are sure;
3. a second confirmation step is required before the operation is executed.

Only after the second confirmation is the critical update performed.

---

## 6.2 Effect of Critical Update

The critical update immediately invalidates the old passed result for all affected users.

The result becomes:

```text
Outdated
```

The corresponding achievement is removed.

The user is then required to take the new version of the test.

The historical attempt itself remains stored.

---

## 6.3 Old Result

The historical result remains in the database.

Its normal validity period is based on the date on which the user originally passed the test.

A critical update can invalidate that result before the end of its normal one-year validity period.

The result is therefore not deleted; only its current validity/status changes.

---

## 6.4 Attempt History

Historical attempts remain stored.

Every completed attempt identifies the test and the version number under which it was completed.

Example:

```text
Attempt #1 → Test 5 → Version 1
Attempt #2 → Test 5 → Version 1
Attempt #3 → Test 5 → Version 2
```

Critical Update does not delete historical attempts.

---

## 6.5 Stars

A star remains while the corresponding result is valid and passed.

When the result becomes outdated because of:

- normal expiration; or
- Critical Update;

the corresponding achievement is removed and the old successful star no longer represents a current valid achievement.

Historical attempt data remains stored.

---

## 6.6 Learning References

V1 uses only:

- references;
- explanations;
- temporary learning pages.

The system does not create a permanent learning-material database.

When the user reviews all required references and presses **"Examined"**, the temporary learning page and its temporary records are deleted.

Critical Update does not change this mechanism.

A future standalone materials system will provide the permanent materials functionality.

---

# 7. Database and Domain Model — V1

## 7.1 General Principle

The database stores persistent information about the testing system.

The current application process stores unfinished attempts temporarily in memory.

Therefore:

```text
Completed attempt
→ Database

Unfinished attempt
→ RAM / TestController
```

The completed attempt is created in the database only after the user completely finishes the test.

---

# 8. Proposed Database Tables

The current database model is:

```text
gi_new_tests
gi_new_test_questions
gi_new_test_answers

gi_new_test_attempts
gi_new_test_attempt_questions

gi_new_finished_attempts_explanations

gi_new_test_achievements
gi_new_test_user_achievements

gi_new_test_notifications

gi_new_test_config
```

A separate `gi_new_test_versions` table is not required in the current design.

A separate `gi_new_test_constants` table is also not required.

All configurable values are stored in the single `gi_new_test_config` table.

---

## 8.1 `gi_new_tests`

Represents the logical test.

The test is a set of questions designed to help users understand their knowledge gaps in a specific area.

Proposed fields:

```text
id
title
version
date_creation
date_modified
```

The `version` field identifies the current version of the logical test.

When questions are added/deleted or answers are changed, the test version is increased.

---

## 8.2 `gi_new_test_questions`

Stores questions belonging to a test.

Proposed fields:

```text
id
test_id
question
explanation
reference
date_added
date_modified
```

A question contains:

- question text;
- explanation;
- reference;
- timestamps.

The reference is only a reference to where the required information can be found.

It is not a permanent learning-material object.

---

## 8.3 `gi_new_test_answers`

Stores answers belonging to a question.

Proposed fields:

```text
id
question_id
answer
date_added
date_modified
```

Each question can contain an unlimited number of answers.

The data model must also indicate which answers are correct because the scoring system needs to identify correct and incorrect answers.

---

# 9. Completed Attempts

## 9.1 `gi_new_test_attempts`

This table stores completed test attempts.

Proposed fields:

```text
id
user_id
test_id
test_version
valid_answers
invalid_answers
score
status
date_finished
```

Possible statuses:

```text
failed
passed
outdated
```

`test_version` identifies the version of the test used for the completed attempt.

`valid_answers` contains the total number of correct answers selected.

`invalid_answers` contains the total number of incorrect answers selected.

The score is calculated using:

```text
Score = 100 × (valid_answers − invalid_answers) / Maximum Correct Answers
```

with a minimum score of 0%.

The record should also contain the information required to determine the one-year validity period.

---

## 9.2 `gi_new_test_attempt_questions`

This table stores question-level statistics for a completed attempt.

Proposed fields:

```text
user_id
test_id
question_id
right_answers
failed_answers
date_finished
```

The table stores the number of correct and incorrect answers associated with the question.

The system does not need to preserve the exact answer selections after the user moves to the next question.

The data stored here is sufficient for the required historical question analytics and for identifying questions where the user made mistakes.

---

## 9.3 No `gi_new_test_attempt_answers` Table in V1

A separate:

```text
gi_new_test_attempt_answers
```

table is not required for V1.

While answering a question, the application temporarily knows which answers the user selected and can immediately determine which were correct or incorrect.

After the user clicks the button to proceed to the next question, the exact selected-answer information is no longer required.

The persistent database stores the required result/statistical information rather than every exact answer selection.

---

# 10. Finished Attempt Explanations

## 10.1 `gi_new_finished_attempts_explanations`

This table stores the temporary explanation/reference data required for the user's learning page after a completed attempt.

Proposed fields:

```text
id
attempt_id
question_id
user_id
explanation
reference
```

Only questions for which the user made a mistake need to produce these records.

These records support the temporary Materials page.

They are not a permanent materials-management database.

---

## 10.2 Deleting Temporary Records

When the user has reviewed all required references and clicks:

**"Examined"**

the application sends the confirmation to the server.

The associated temporary explanation/reference records are then deleted from the database.

This deletion does not delete:

- the completed attempt;
- the score;
- the attempt-question statistics;
- historical test information.

---

# 11. Achievements

## 11.1 `gi_new_test_achievements`

The separate `gi_new_test_achievements` table stores the achievement definitions themselves:

```text
id
name
value
```

Example conceptual records:

```text
1 | Bronze | 75%
2 | Silver | 85%
3 | Gold   | 95%
4 | Lock   | 95%
```

These definitions represent the achievement levels.

Their configurable values are managed through the testing configuration system.

---

## 11.2 `gi_new_test_user_achievements`

This table represents the user's achievements for individual tests.

Proposed fields:

```text
user_id
test_id
medal_id
```

A user can therefore have multiple achievements for multiple tests.

Example:

```text
User 15

PostgreSQL → Gold
Laravel    → Silver
Redis      → Bronze
```

When the achievement values/configuration change, the system must check the affected user achievements against the new values.

---

# 12. Notifications

## 12.1 `gi_new_test_notifications`

This table stores notification state for the user and the relevant test.

Proposed fields:

```text
id
user_id
test_id
dismiss_count
```

`dismiss_count` records how many times the user has dismissed the notification.

The configured dismissal limit and behavior are stored in `gi_new_test_config`.

---

# 13. Configuration

## 13.1 `gi_new_test_config`

`gi_new_test_config` is the **single configuration table** for the testing system.

There is no separate `gi_new_test_constants` table.

The table stores all values that the administrator is allowed to change.

Conceptually:

```text
id
name
value
```

It may contain values such as:

```text
Bronze threshold
Silver threshold
Gold threshold
Lock threshold
Retake delay
New-test notification dismissal limit
Failed-test notification dismissal limit
New-test notification mode
Failed-test notification mode
Gold rank name
Silver rank name
Bronze rank name
Failed rank name
```

Additional configurable testing values can be added to this table as the system evolves.

The configuration table therefore acts as the central store for administrator-controlled testing parameters.

---

# 14. User States

A separate persistent user-state table is currently **not required**.

The user's current test state can be determined from:

- completed attempts;
- current test information;
- test version;
- validity period;
- temporary in-memory attempt;
- current achievement;
- temporary learning state where applicable.

The `Not started` state is derived by the application.

The `In progress` state is maintained temporarily in RAM by `TestController`.

The temporary Materials state does not need a permanent user-state record.

---

# 15. Admin Panel UI — V1

The administrative interface contains:

```text
All Tests
Add Test
Results
Analytics
Settings
```

---

## 15.1 All Tests

The **All Tests** page contains all tests.

The page includes:

- all tests;
- an **Add Test** button;
- the current test version number;
- the questions inside each test.

When an administrator opens a test, its questions can be displayed on the same page using the expandable/open-in-place mechanism already used in the existing Contracts administration interface.

The existing mechanism should also be reused for the **user representation** in the relevant administration interface.

---

## 15.2 Add Test / Edit Test

The test editing interface contains:

```text
Questions
Answers
Explanations
References
Add New
Achievement For
```

The administrator can:

- add new questions;
- edit questions;
- delete questions;
- add unlimited answers;
- edit answers;
- identify correct answers;
- define explanations;
- define references;
- define the area represented by the achievement.

The **Achievement For** field defines the Area part of the achievement name.

Example:

```text
Achievement For:
PostgreSQL
```

The Rank is provided by the global achievement configuration.

The resulting hover text becomes:

```text
Guru — PostgreSQL
```

for a Gold result.

---

## 15.3 Critical Update Button

The Edit Test interface must contain a button:

**"Critical Update"**

Clicking this button opens a confirmation modal.

The administrator must confirm the operation.

A second confirmation step is required before the critical update is executed.

The critical update then:

- invalidates old passed results;
- changes the affected result status to `Outdated`;
- removes the corresponding achievements;
- requires affected users to take the new test;
- preserves historical attempts.

---

# 16. Admin Settings

The Settings page contains configurable testing-system values.

These include at least:

### Achievement thresholds

```text
Bronze
Silver
Gold
Lock
```

### Retake period

A simple number-of-days input.

For example:

```text
3
```

### Notification settings

The administrator can configure:

- number of allowed dismissals;
- whether the notification becomes blocking;
- whether the notification becomes a bottom notification.

### Achievement rank names

The administrator can configure the Rank names.

For example:

```text
Gold   → Guru
Silver → Expert
Bronze → Specialist
Failed → Failed
```

All these values are stored in the central:

```text
gi_new_test_config
```

table.

---

# 17. Results Page

The **Results** page provides access to completed test attempts.

The administrator can see at least:

```text
User
Test
Test Version
Score
Status
Date Finished
Validity
```

Historical attempts remain distinguishable by test version.

---

# 18. Analytics Page

The **Analytics** page must provide two main levels.

## 18.1 Test Level

The test-level analytics provide an overall view of a test.

Possible information includes:

- number of completed attempts;
- passed attempts;
- failed attempts;
- average score;
- score distribution;
- number of questions;
- overall performance.

## 18.2 Question Level

The question-level analytics provide information about the difficulty and performance of individual questions.

Possible information includes:

- number of times presented;
- correct-answer statistics;
- incorrect-answer statistics;
- missed correct answers;
- question score distribution;
- other indicators derived from the stored attempt data.

The final difficulty formula can be defined later.

---

# 19. User-Side UI — V1

The personal account contains a menu item:

**Tests**

All tests are displayed on the same page.

Pagination is displayed at the bottom.

---

## 19.1 Test List

Each test contains:

- title;
- current status/result;
- **Start** button if the user can take the test;
- **Materials** button if the user needs to review references;
- score/estimation if the test has already been completed;
- corresponding medal/star.

Possible application states are:

```text
Not started
In progress
Failed
Passed
Outdated
```

---

## 19.2 Open-in-Place Interface

Tests and temporary materials open using the existing additional-window mechanism already used in the Contracts system.

They open within the same page rather than requiring a separate full-page navigation flow.

---

# 20. Test Interface

Inside the test, the user sees only:

- one question;
- its answers;
- a button for submitting/proceeding;
- a counter.

Example:

```text
Question 4 / 20

[ ] Answer A
[ ] Answer B
[ ] Answer C
[ ] Answer D

[Continue]
```

The counter shows:

- total number of questions;
- how many questions have already been completed.

Questions and answers are randomized at runtime.

---

## 20.1 Immediate Feedback

When the user answers a question, the application temporarily knows which answers were selected and can determine which were correct and which were incorrect.

This information is needed only during the current question/interaction.

The exact selected answers do not need to be stored permanently after the user proceeds to the next question.

If the user made a mistake, the question's explanation and reference are shown.

---

# 21. Materials Interface — V1

The temporary Materials page displays all questions where the user made mistakes.

For each problematic question, the user sees:

- the question;
- the explanation;
- the reference to where the information can be studied.

The user's exact original answer selections are not stored and therefore do not need to be displayed here.

Everything is shown on the same page.

For every required reference:

```text
[ ] Reviewed
```

The user then presses:

**"Examined"**

After confirmation:

- the temporary materials page is deleted;
- its temporary database records are deleted;
- the user can become eligible for a new test attempt after the configured retake period.

---

# 22. Architecture

## 22.1 Separate WordPress Plugin

The testing system is implemented as a separate WordPress plugin.

Proposed structure:

```text
WordPress
│
├── bp_contracts
│   └── Personal Account
│       └── Testing integration layer
│
└── bp_knowledge_tests
    └── Testing System
```

The testing plugin owns the testing domain and its business rules.

`bp_contracts` provides the personal-account environment and integration.

---

## 22.2 `bp_contracts`

`bp_contracts` remains responsible for the personal-account environment.

Its testing-related responsibilities include:

- displaying the Tests section;
- providing the testing interface;
- identifying the authenticated user;
- integrating testing notifications;
- providing the `TestController` for unfinished in-memory attempts;
- integrating with the existing Ratchet/WebSocket functionality.

Testing business rules should not be duplicated inside `bp_contracts`.

---

## 22.3 Testing Plugin

The separate testing plugin owns:

- tests;
- test versions represented by test version numbers;
- questions;
- answers;
- completed attempts;
- scoring;
- references;
- temporary learning workflow;
- achievements;
- analytics;
- notifications;
- validity;
- critical invalidation.

---

# 23. Ratchet / WebSocket

The current personal account uses WebSocket Ratchet and is restarted every night.

The unfinished test state is kept in application memory while the server is running.

`TestController` inside `bp_contracts` manages this temporary state.

The completed test result is persisted in the WordPress database.

The nightly restart at **03:00 AM** clears unfinished in-memory attempts.

Therefore:

```text
Unfinished test
→ RAM
→ lost at 03:00 restart

Completed test
→ Database
→ persists
```

---

# 24. Future Platform Compatibility

The new version of the website is being developed using:

```text
WordPress
    → Headless CMS + Administration

Laravel
    → Business Logic + Backend

PostgreSQL
    → Application Database

Laravel Reverb
    → WebSocket / Realtime Communication

React
    → Frontend
```

The testing system should therefore remain sufficiently separated from `bp_contracts` that the testing domain can later be moved toward the new Laravel/PostgreSQL architecture.

The testing business rules should not depend on WordPress page rendering or the specific current implementation of Ratchet.

---

# 25. API-Oriented Design Principle

Even though V1 is implemented in WordPress, the testing functionality should have a clear separation between:

```text
User Interface
      ↓
Testing Interface / API
      ↓
Testing Logic
      ↓
Persistent Storage
```

The personal account should consume the testing functionality rather than duplicate its business rules.

This also provides a cleaner future path toward the React frontend.

---

# 26. Current Core Database Model

The current conceptual database is:

```text
                    gi_new_tests
                         │
                         ├── gi_new_test_questions
                         │          │
                         │          └── gi_new_test_answers
                         │
                         └── version
                         
gi_new_test_attempts
          │
          └── gi_new_test_attempt_questions

gi_new_finished_attempts_explanations

gi_new_test_achievements
          │
          └── gi_new_test_user_achievements

gi_new_test_notifications

gi_new_test_config
```

The current architecture intentionally does not use:

```text
gi_new_test_versions
gi_new_test_attempt_answers
gi_new_test_question_stats
wp_bp_test_user_states
gi_new_test_constants
```

as separate V1 tables.

---

# 27. Persistent vs Temporary State

The system explicitly separates persistent database state from temporary application state.

## 27.1 Database

The database contains:

```text
Tests
Questions
Answers
Completed Attempts
Attempt Question Statistics
Temporary Finished-Attempt References
Achievements
User Achievements
Notifications
Configuration
```

## 27.2 Application

The application contains temporary:

```text
Not Started
In Progress
Current question
Current answer selections
Current unfinished test model
```

`Not Started` is derived rather than stored.

`In Progress` exists only while the unfinished attempt is maintained in memory.

Exact answer selections exist only during the current question/interaction and are not retained after the user proceeds.

---

# 28. Important Current Principles

1. The original requirements are preserved as the baseline and are not silently changed.
2. Explicit project decisions may modify those requirements and must be documented separately.
3. The total test score is based on the total number of correct and incorrect selected answers.
4. The score cannot be below 0%.
5. Bronze/Silver/Gold thresholds are configurable.
6. Passed means at least Bronze.
7. A successful result is normally valid for one year from the date it was achieved.
8. A test is a logical set of questions covering a particular area.
9. A new version of the same test is represented through the test's version number; a separate version table is not currently required.
10. Completed attempts retain the test version under which they were completed.
11. Unfinished attempts exist only temporarily in application memory.
12. `TestController` in `bp_contracts` manages unfinished in-memory attempts.
13. The unfinished attempt is available until the 03:00 AM server restart.
14. Unfinished attempts do not consume the retake opportunity.
15. The retake delay is configurable as a simple number of days.
16. `1 day` means the following day.
17. Questions are randomized at runtime.
18. Answers inside questions are randomized at runtime.
19. The randomized order is not stored.
20. Explanations and references belong to questions; permanent learning materials do not.
21. V1 uses a temporary learning page with references, explanations, checkboxes, and an `Examined` button.
22. If the user makes mistakes, the relevant explanations and references are shown.
23. Exact selected answers do not need to be stored after the user proceeds to the next question.
24. A failed test requires the user to complete the temporary learning process before another attempt.
25. The star represents the latest completed test result, not the historical best result.
26. A result of 95% or higher locks that test for the user.
27. The system supports multiple stars, one for each relevant test/area.
28. Achievement names consist of `Rank — Area`.
29. Rank names are configurable globally.
30. Area names are configurable per test.
31. A failed test can be represented by a transparent outlined star.
32. The database stores completed attempts with `Failed`, `Passed`, or `Outdated` status.
33. `Not started` and `In progress` are application states, not persistent completed-attempt states.
34. Question difficulty analytics use raw historical attempt data rather than a separate V1 statistics table.
35. Analytics must provide both test-level and question-level views.
36. Achievement configuration changes must cause the relevant user achievements to be checked again.
37. The testing system uses one central `gi_new_test_config` table for all administrator-configurable values.
38. Critical invalidation removes the validity of old results but does not delete historical attempts.
39. Historical attempts retain their test-version association.
40. Temporary learning pages are removed after the user has reviewed all required references and confirmed `Examined`.
41. Testing is implemented as a separate WordPress plugin.
42. `bp_contracts` provides the personal-account integration and `TestController`.
43. Ratchet is used for realtime communication and temporary application state, not as permanent storage.
44. The testing system should remain compatible with the future WordPress + Laravel + PostgreSQL + React + Reverb architecture.