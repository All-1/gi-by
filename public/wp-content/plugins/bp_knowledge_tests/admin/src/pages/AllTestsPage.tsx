import { Fragment, useCallback, useEffect, useState } from 'react';
import type { Question, Test } from '../api/types';
import { listQuestions, listTests } from '../api/catalog';
import { AdminSubnav } from '../components/AdminSubnav';

type Props = {
  onAddTest: () => void;
  onEditTest: (id: number) => void;
  onOpenSettings: () => void;
};

export function AllTestsPage({ onAddTest, onEditTest, onOpenSettings }: Props) {
  const [tests, setTests] = useState<Test[]>([]);
  const [expandedId, setExpandedId] = useState<number | null>(null);
  const [questionsByTest, setQuestionsByTest] = useState<Record<number, Question[]>>({});
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  const reload = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      setTests(await listTests());
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Failed to load tests.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    reload();
  }, [reload]);

  const toggleExpand = async (testId: number) => {
    if (expandedId === testId) {
      setExpandedId(null);
      return;
    }
    setExpandedId(testId);
    if (questionsByTest[testId] !== undefined) {
      return;
    }
    try {
      const questions = await listQuestions(testId);
      setQuestionsByTest((prev) => ({ ...prev, [testId]: questions }));
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Failed to load questions.');
    }
  };

  return (
    <div className="bp-kt-page">
      <header className="bp-kt-page__header">
        <h1>All tests</h1>
        {window.bpKnowledgeTestsAdmin?.adminUrls?.addTest ? (
          <a className="button button-primary" href={window.bpKnowledgeTestsAdmin.adminUrls.addTest}>
            Add test
          </a>
        ) : (
          <button type="button" className="button button-primary" onClick={onAddTest}>
            Add test
          </button>
        )}
      </header>

      <AdminSubnav active="tests" onNavigateSettings={onOpenSettings} />

      {loading && <p>Loading…</p>}
      {error !== null && <p className="bp-kt-error">{error}</p>}

      {!loading && tests.length === 0 && <p>No tests yet. Click Add test to create one.</p>}

      <table className="wp-list-table widefat fixed striped bp-kt-table" hidden={tests.length === 0}>
        <thead>
          <tr>
            <th scope="col" className="bp-kt-col-expand" />
            <th scope="col">Title</th>
            <th scope="col">Version</th>
            <th scope="col">Achievement for</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          {tests.map((test) => {
            const expanded = expandedId === test.id;
            const questions = questionsByTest[test.id] ?? [];
            return (
              <Fragment key={test.id}>
                <tr>
                  <td>
                    <button
                      type="button"
                      className="button button-small"
                      aria-expanded={expanded}
                      onClick={() => toggleExpand(test.id)}
                    >
                      {expanded ? '−' : '+'}
                    </button>
                  </td>
                  <td>{test.title}</td>
                  <td>{test.version}</td>
                  <td>{test.achievementArea ?? '—'}</td>
                  <td>
                    <button type="button" className="button" onClick={() => onEditTest(test.id)}>
                      Edit
                    </button>
                  </td>
                </tr>
                {expanded && (
                  <tr key={`${test.id}-questions`} className="bp-kt-table__nested">
                    <td colSpan={5}>
                      {questions.length === 0 && <p className="bp-kt-muted">No questions yet.</p>}
                      {questions.length > 0 && (
                        <ol className="bp-kt-question-preview">
                          {questions.map((q, index) => (
                            <li key={q.id}>
                              <span className="bp-kt-muted">Q{index + 1}.</span> {q.question}
                            </li>
                          ))}
                        </ol>
                      )}
                    </td>
                  </tr>
                )}
              </Fragment>
            );
          })}
        </tbody>
      </table>
    </div>
  );
}
