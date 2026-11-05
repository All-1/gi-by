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
    ['Ниже 70%', distribution.under70],
    ['70–79%', distribution.from70To79],
    ['80–89%', distribution.from80To89],
    ['90% и выше', distribution.from90Plus],
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
      setError(err instanceof Error ? err.message : 'Не удалось загрузить аналитику.');
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
          setError(err instanceof Error ? err.message : 'Не удалось загрузить аналитику теста.');
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
        <h1>Аналитика</h1>
      </header>
      {error && <p className="bp-kt-error">{error}</p>}
      {loading ? (
        <p className="bp-kt-muted">Загрузка аналитики…</p>
      ) : (
        <>
          <div className="bp-kt-card">
            <label>
              Тест
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
            <p className="bp-kt-muted">Загрузка данных теста…</p>
          ) : (
            <>
              <div className="bp-kt-card bp-kt-analytics-summary">
                <h2>{detail.title}</h2>
                <p className="bp-kt-muted">
                  Версия {detail.currentVersion} · вопросов: {detail.questionCount}
                </p>
                <div className="bp-kt-analytics-grid">
                  <div>
                    <h3>Попытки</h3>
                    <ul className="bp-kt-stats-list">
                      <li>Завершено: <strong>{detail.attempts.completed}</strong></li>
                      <li>Сдано: <strong>{detail.attempts.passed}</strong></li>
                      <li>Не сдано: <strong>{detail.attempts.failed}</strong></li>
                      <li>Устарело: <strong>{detail.attempts.outdated}</strong></li>
                      <li>Средний балл: <strong>{detail.attempts.averageScore.toFixed(2)}%</strong></li>
                    </ul>
                  </div>
                  <div>
                    <h3>Распределение баллов</h3>
                    <DistributionList distribution={detail.scoreDistribution} />
                  </div>
                </div>
              </div>
              <h2>По вопросам</h2>
              <table className="widefat striped bp-kt-table">
                <thead>
                  <tr>
                    <th>Вопрос</th>
                    <th>Показан</th>
                    <th>Верных</th>
                    <th>Неверных</th>
                    <th>Пропущено верных</th>
                    <th>Средний % вопроса</th>
                  </tr>
                </thead>
                <tbody>
                  {detail.questions.length === 0 ? (
                    <tr>
                      <td colSpan={6}>В этом тесте нет вопросов.</td>
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
