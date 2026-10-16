import { useCallback, useEffect, useState } from 'react';
import { AdminSubnav } from '../components/AdminSubnav';
import {
  fetchAnalyticsSummaries,
  fetchTestAnalytics,
  type TestAnalyticsDetail,
  type TestAnalyticsSummary,
} from '../api/reporting';

type Props = {
  onNavigateTests: () => void;
  onNavigateSettings: () => void;
  onNavigateResults: () => void;
};

function DistributionList({ distribution }: { distribution: TestAnalyticsSummary['scoreDistribution'] }) {
  const items = [
    ['Under 70%', distribution.under70],
    ['70–79%', distribution.from70To79],
    ['80–89%', distribution.from80To89],
    ['90%+', distribution.from90Plus],
  ];

  return (
    <ul className="bp-kt-stats-list">
      {items.map(([label, count]) => (
        <li key={label}>{label}: <strong>{count}</strong></li>
      ))}
    </ul>
  );
}

export function AnalyticsPage({ onNavigateTests, onNavigateSettings, onNavigateResults }: Props) {
  const [summaries, setSummaries] = useState<TestAnalyticsSummary[]>([]);
  const [selectedId, setSelectedId] = useState<number | null>(null);
  const [detail, setDetail] = useState<TestAnalyticsDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [detailLoading, setDetailLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const loadSummaries = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const list = await fetchAnalyticsSummaries();
      setSummaries(list);
      setSelectedId((current) => current ?? (list[0]?.testId ?? null));
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Failed to load analytics.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadSummaries();
  }, [loadSummaries]);

  useEffect(() => {
    if (selectedId === null) {
      setDetail(null);
      return;
    }
    let cancelled = false;
    setDetailLoading(true);
    fetchTestAnalytics(selectedId)
      .then((data) => {
        if (!cancelled) {
          setDetail(data);
        }
      })
      .catch((err: unknown) => {
        if (!cancelled) {
          setError(err instanceof Error ? err.message : 'Failed to load test analytics.');
        }
      })
      .finally(() => {
        if (!cancelled) {
          setDetailLoading(false);
        }
      });

    return () => {
      cancelled = true;
    };
  }, [selectedId]);

  return (
    <div className="bp-kt-page">
      <AdminSubnav
        active="analytics"
        onNavigateTests={onNavigateTests}
        onNavigateSettings={onNavigateSettings}
        onNavigateResults={onNavigateResults}
        onNavigateAnalytics={() => undefined}
      />
      <header className="bp-kt-page__header">
        <h1>Analytics</h1>
      </header>
      {error && <p className="bp-kt-error">{error}</p>}
      {loading ? (
        <p className="bp-kt-muted">Loading analytics…</p>
      ) : (
        <>
          <div className="bp-kt-card">
            <label>
              Test
              <select
                value={selectedId ?? ''}
                onChange={(e) => setSelectedId(Number.parseInt(e.target.value, 10))}
              >
                {summaries.map((s) => (
                  <option key={s.testId} value={s.testId}>{s.title}</option>
                ))}
              </select>
            </label>
          </div>
          {detailLoading || !detail ? (
            <p className="bp-kt-muted">Loading test detail…</p>
          ) : (
            <>
              <div className="bp-kt-card bp-kt-analytics-summary">
                <h2>{detail.title}</h2>
                <p className="bp-kt-muted">
                  Version {detail.currentVersion} · {detail.questionCount} questions
                </p>
                <div className="bp-kt-analytics-grid">
                  <div>
                    <h3>Attempts</h3>
                    <ul className="bp-kt-stats-list">
                      <li>Completed: <strong>{detail.attempts.completed}</strong></li>
                      <li>Passed: <strong>{detail.attempts.passed}</strong></li>
                      <li>Failed: <strong>{detail.attempts.failed}</strong></li>
                      <li>Outdated: <strong>{detail.attempts.outdated}</strong></li>
                      <li>Average score: <strong>{detail.attempts.averageScore.toFixed(2)}%</strong></li>
                    </ul>
                  </div>
                  <div>
                    <h3>Score distribution</h3>
                    <DistributionList distribution={detail.scoreDistribution} />
                  </div>
                </div>
              </div>
              <h2>Question level</h2>
              <table className="widefat striped bp-kt-table">
                <thead>
                  <tr>
                    <th>Question</th>
                    <th>Presented</th>
                    <th>Right sel.</th>
                    <th>Wrong sel.</th>
                    <th>Missed correct</th>
                    <th>Avg. question %</th>
                  </tr>
                </thead>
                <tbody>
                  {detail.questions.length === 0 ? (
                    <tr>
                      <td colSpan={6}>No questions in this test.</td>
                    </tr>
                  ) : (
                    detail.questions.map((q) => (
                      <tr key={q.questionId}>
                        <td>{q.question}</td>
                        <td>{q.timesPresented}</td>
                        <td>{q.totalRightSelections}</td>
                        <td>{q.totalFailedSelections}</td>
                        <td>{q.missedCorrectSelections}</td>
                        <td>
                          {q.averageQuestionScorePercent !== null
                            ? `${q.averageQuestionScorePercent.toFixed(2)}%`
                            : '—'}
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </>
          )}
        </>
      )}
    </div>
  );
}
