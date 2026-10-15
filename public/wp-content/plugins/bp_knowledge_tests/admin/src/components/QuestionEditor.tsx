import { useCallback, useEffect, useState } from 'react';
import type { Answer, Question } from '../api/types';
import {
  createAnswer,
  deleteAnswer,
  deleteQuestion,
  listAnswers,
  setCorrectAnswerIds,
  updateAnswer,
  updateQuestion,
} from '../api/catalog';

type Props = {
  question: Question;
  onRemoved: () => void;
  onUpdated: (question: Question) => void;
};

export function QuestionEditor({ question, onRemoved, onUpdated }: Props) {
  const [text, setText] = useState(question.question);
  const [explanation, setExplanation] = useState(question.explanation);
  const [reference, setReference] = useState(question.reference);
  const [answers, setAnswers] = useState<Answer[]>([]);
  const [correctIds, setCorrectIds] = useState<number[]>([]);
  const [newAnswer, setNewAnswer] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  const loadAnswers = useCallback(async () => {
    const payload = await listAnswers(question.id);
    setAnswers(payload.answers);
    setCorrectIds(payload.correctAnswerIds);
  }, [question.id]);

  useEffect(() => {
    loadAnswers().catch((err: unknown) => {
      setError(err instanceof Error ? err.message : 'Failed to load answers.');
    });
  }, [loadAnswers]);

  const saveQuestion = async () => {
    setBusy(true);
    setError(null);
    try {
      const updated = await updateQuestion(question.id, {
        question: text,
        explanation,
        reference,
      });
      onUpdated(updated);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Save failed.');
    } finally {
      setBusy(false);
    }
  };

  const removeQuestion = async () => {
    if (!window.confirm('Delete this question and all its answers?')) {
      return;
    }
    setBusy(true);
    try {
      await deleteQuestion(question.id);
      onRemoved();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Delete failed.');
      setBusy(false);
    }
  };

  const addAnswer = async () => {
    const label = newAnswer.trim();
    if (label === '') {
      return;
    }
    setBusy(true);
    setError(null);
    try {
      await createAnswer(question.id, label, false);
      setNewAnswer('');
      await loadAnswers();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Could not add answer.');
    } finally {
      setBusy(false);
    }
  };

  const toggleCorrect = async (answerId: number, checked: boolean) => {
    const nextIds = checked
      ? [...correctIds, answerId]
      : correctIds.filter((id) => id !== answerId);
    setCorrectIds(nextIds);
    try {
      await setCorrectAnswerIds(question.id, nextIds);
      await loadAnswers();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Could not update correct answers.');
      await loadAnswers();
    }
  };

  const saveAnswerText = async (answer: Answer, value: string) => {
    try {
      await updateAnswer(answer.id, value, answer.isCorrect);
      await loadAnswers();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Could not save answer.');
    }
  };

  const removeAnswer = async (answerId: number) => {
    if (!window.confirm('Delete this answer option?')) {
      return;
    }
    try {
      await deleteAnswer(answerId);
      await loadAnswers();
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Could not delete answer.');
    }
  };

  return (
    <div className="bp-kt-question">
      <div className="bp-kt-question__fields">
        <label>
          Question
          <textarea value={text} onChange={(e) => setText(e.target.value)} rows={3} />
        </label>
        <label>
          Explanation
          <textarea
            value={explanation}
            onChange={(e) => setExplanation(e.target.value)}
            rows={2}
          />
        </label>
        <label>
          Reference
          <textarea value={reference} onChange={(e) => setReference(e.target.value)} rows={2} />
        </label>
        <button type="button" className="button button-primary" onClick={saveQuestion} disabled={busy}>
          Save question
        </button>
        <button type="button" className="button button-link-delete" onClick={removeQuestion} disabled={busy}>
          Delete question
        </button>
      </div>

      <div className="bp-kt-answers">
        <h4>Answers</h4>
        <ul className="bp-kt-answers__list">
          {answers.map((answer) => (
            <li key={answer.id}>
              <label className="bp-kt-answers__correct">
                <input
                  type="checkbox"
                  checked={correctIds.includes(answer.id)}
                  onChange={(e) => toggleCorrect(answer.id, e.target.checked)}
                />
                Correct
              </label>
              <input
                type="text"
                className="regular-text"
                value={answer.answer}
                onChange={(e) => {
                  const value = e.target.value;
                  setAnswers((prev) =>
                    prev.map((row) => (row.id === answer.id ? { ...row, answer: value } : row)),
                  );
                }}
                onBlur={(e) => saveAnswerText(answer, e.target.value)}
              />
              <button type="button" className="button button-small" onClick={() => removeAnswer(answer.id)}>
                Remove
              </button>
            </li>
          ))}
        </ul>
        <div className="bp-kt-answers__add">
          <input
            type="text"
            className="regular-text"
            placeholder="New answer option"
            value={newAnswer}
            onChange={(e) => setNewAnswer(e.target.value)}
          />
          <button type="button" className="button" onClick={addAnswer} disabled={busy}>
            Add answer
          </button>
        </div>
      </div>

      {error !== null && <p className="bp-kt-error">{error}</p>}
    </div>
  );
}
