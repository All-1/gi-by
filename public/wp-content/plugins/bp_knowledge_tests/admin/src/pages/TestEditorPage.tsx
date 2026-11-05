import { useCallback, useEffect, useState } from 'react';
import type { Question, Test } from '../api/types';
import {
  createQuestion,
  createTest,
  criticalUpdateTest,
  deleteTest,
  getTest,
  listQuestions,
  updateTest,
} from '../api/catalog';
import { CriticalUpdateConfirmDialog, CriticalUpdateDialog } from '../components/CriticalUpdateDialog';
import { QuestionEditor } from '../components/QuestionEditor';

type Props = {
  testId: number | null;
  onBack: () => void;
  onCreated: (id: number) => void;
};

export function TestEditorPage({ testId, onBack, onCreated }: Props) {
  const isNew = testId === null;
  const [test, setTest] = useState<Test | null>(null);
  const [title, setTitle] = useState('');
  const [achievementArea, setAchievementArea] = useState('');
  const [questions, setQuestions] = useState<Question[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);
  const [criticalStep, setCriticalStep] = useState<0 | 1 | 2>(0);

  const load = useCallback(async () => {
    if (testId === null) {
      setTest(null);
      setTitle('');
      setAchievementArea('');
      setQuestions([]);
      return;
    }
    setBusy(true);
    setError(null);
    try {
      const loaded = await getTest(testId);
      setTest(loaded);
      setTitle(loaded.title);
      setAchievementArea(loaded.achievementArea ?? '');
      setQuestions(await listQuestions(testId));
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Не удалось загрузить тест.');
    } finally {
      setBusy(false);
    }
  }, [testId]);

  useEffect(() => {
    load();
  }, [load]);

  const saveTest = async () => {
    const trimmed = title.trim();
    if (trimmed === '') {
      setError('Укажите название.');
      return;
    }
    setBusy(true);
    setError(null);
    try {
      if (isNew) {
        const created = await createTest(trimmed, achievementArea.trim() || undefined);
        setTest(created);
        onCreated(created.id);
      } else if (testId !== null) {
        const updated = await updateTest(testId, trimmed, achievementArea.trim() || null);
        setTest(updated);
      }
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Не удалось сохранить.');
    } finally {
      setBusy(false);
    }
  };

  const addQuestion = async () => {
    if (testId === null) {
      setError('Сначала сохраните тест, затем добавляйте вопросы.');
      return;
    }
    setBusy(true);
    try {
      const question = await createQuestion(testId, {
        question: 'Новый вопрос',
        explanation: '',
        reference: '',
      });
      setQuestions((prev) => [...prev, question]);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Не удалось добавить вопрос.');
    } finally {
      setBusy(false);
    }
  };

  const removeTest = async () => {
    if (testId === null) {
      onBack();
      return;
    }
    if (!window.confirm('Удалить этот тест и всё связанное содержимое?')) {
      return;
    }
    setBusy(true);
    try {
      await deleteTest(testId);
      onBack();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Не удалось удалить.');
      setBusy(false);
    }
  };

  const runCriticalUpdate = async () => {
    if (testId === null) {
      return;
    }
    setBusy(true);
    setError(null);
    try {
      const updated = await criticalUpdateTest(testId);
      setTest(updated);
      setCriticalStep(0);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Не удалось выполнить критическое обновление.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="bp-kt-page">
      <header className="bp-kt-page__header">
        <button type="button" className="button" onClick={onBack}>
          ← Все тесты
        </button>
        <h1>{isNew ? 'Добавить тест' : 'Редактировать тест'}</h1>
        {!isNew && test !== null && (
          <span className="bp-kt-muted">Версия {test.version}</span>
        )}
      </header>

      <section className="bp-kt-card">
        <label>
          Название
          <input
            type="text"
            className="regular-text"
            value={title}
            onChange={(e) => setTitle(e.target.value)}
          />
        </label>
        <label>
          Достижение за (область)
          <input
            type="text"
            className="regular-text"
            placeholder="например, PostgreSQL"
            value={achievementArea}
            onChange={(e) => setAchievementArea(e.target.value)}
          />
        </label>
        {isNew && (
          <p className="bp-kt-muted">
            Введите название и нажмите «Сохранить тест». Вопросы можно добавить после создания теста.
          </p>
        )}
        <div className="bp-kt-actions">
          <button type="button" className="button button-primary" onClick={saveTest} disabled={busy}>
            {isNew ? 'Создать тест' : 'Сохранить тест'}
          </button>
          {!isNew && (
            <>
              <button
                type="button"
                className="button"
                onClick={() => setCriticalStep(1)}
                disabled={busy}
              >
                Критическое обновление
              </button>
              <button type="button" className="button button-link-delete" onClick={removeTest} disabled={busy}>
                Удалить тест
              </button>
            </>
          )}
        </div>
      </section>

      {!isNew && testId !== null && (
        <section className="bp-kt-card">
          <header className="bp-kt-page__header">
            <h2>Вопросы</h2>
            <button type="button" className="button" onClick={addQuestion} disabled={busy}>
              Добавить вопрос
            </button>
          </header>
          {questions.length === 0 && <p className="bp-kt-muted">Вопросов пока нет.</p>}
          {questions.map((question, index) => (
            <details key={question.id} className="bp-kt-question-panel" open={index === 0}>
              <summary>Вопрос {index + 1}</summary>
              <QuestionEditor
                question={question}
                onRemoved={() => setQuestions((prev) => prev.filter((q) => q.id !== question.id))}
                onUpdated={(updated) =>
                  setQuestions((prev) => prev.map((q) => (q.id === updated.id ? updated : q)))
                }
              />
            </details>
          ))}
        </section>
      )}

      {error !== null && <p className="bp-kt-error">{error}</p>}

      <CriticalUpdateDialog
        open={criticalStep === 1}
        testTitle={title}
        onCancel={() => setCriticalStep(0)}
        onConfirm={() => setCriticalStep(2)}
      />
      <CriticalUpdateConfirmDialog
        open={criticalStep === 2}
        onCancel={() => setCriticalStep(1)}
        onConfirm={runCriticalUpdate}
        busy={busy}
      />
    </div>
  );
}
