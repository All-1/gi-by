import { type FormEvent, useCallback, useEffect, useState } from 'react';
import { AdminSubnav } from '../components/AdminSubnav';
import { fetchResults, listTests, type AttemptResultRow, type Test } from '../api/reporting';

type Props = {
  onNavigateTests: () => void;
  onNavigateSettings: () => void;
  onNavigateAnalytics: () => void;
};

const STATUS_OPTIONS = [
  { value: '', label: 'Все статусы' },
  { value: 'passed', label: 'Сдан' },
  { value: 'failed', label: 'Не сдан' },
  { value: 'outdated', label: 'Устарел' },
];

function formatUser(row: AttemptResultRow): string {
  if (row.userDisplayName) {
    return `${row.userDisplayName} (#${row.userId})`;
  }
  return `#${row.userId}`;
}

function validityLabel(validity: AttemptResultRow['validity']): string {
  if (validity === 'valid') {
    return 'Действителен';
  }
  if (validity === 'expired') {
    return 'Истёк';
  }
  return '—';
}

function statusLabel(status: string): string {
  if (status === 'passed') {
    return 'Сдан';
  }
  if (status === 'failed') {
    return 'Не сдан';
  }
  if (status === 'outdated') {
    return 'Устарел';
  }
  return status;
}

function attemptCountLabel(total: number): string {
  const mod10 = total % 10;
  const mod100 = total % 100;
  if (mod10 === 1 && mod100 !== 11) {
    return `${total} завершённая попытка`;
  }
  if (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14)) {
    return `${total} завершённые попытки`;
  }
  return `${total} завершённых попыток`;
}

export function ResultsPage({ onNavigateTests, onNavigateSettings, onNavigateAnalytics }: Props) {
  const [tests, setTests] = useState<Test[]>([]);
  const [rows, setRows] = useState<AttemptResultRow[]>([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [testId, setTestId] = useState('');
  const [userId, setUserId] = useState('');
  const [status, setStatus] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const [testList, results] = await Promise.all([
        listTests(),
        fetchResults({
          testId: testId ? Number.parseInt(testId, 10) : undefined,
          userId: userId ? Number.parseInt(userId, 10) : undefined,
          status: status || undefined,
          page,
          perPage: 50,
        }),
      ]);
      setTests(testList);
      setRows(results.items);
      setTotal(results.total);
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Не удалось загрузить результаты.');
    } finally {
      setLoading(false);
    }
  }, [page, status, testId, userId]);

  useEffect(() => {
    load();
  }, [load]);

  const onFilter = (event: FormEvent) => {
    event.preventDefault();
    setPage(1);
  };

  const pageCount = Math.max(1, Math.ceil(total / 50));

  return (
    <div className="bp-kt-page">
      <AdminSubnav
        active="results"
        onNavigateTests={onNavigateTests}
        onNavigateSettings={onNavigateSettings}
        onNavigateResults={() => undefined}
        onNavigateAnalytics={onNavigateAnalytics}
      />
      <header className="bp-kt-page__header">
        <h1>Результаты</h1>
      </header>
      <form className="bp-kt-card bp-kt-filters" onSubmit={onFilter}>
        <label>
          Тест
          <select value={testId} onChange={(e) => setTestId(e.target.value)}>
            <option value="">Все тесты</option>
            {tests.map((t) => (
              <option key={t.id} value={String(t.id)}>{t.title}</option>
            ))}
          </select>
        </label>
        <label>
          ID пользователя
          <input
            type="number"
            min={1}
            className="regular-text"
            value={userId}
            onChange={(e) => setUserId(e.target.value)}
            placeholder="ID пользователя"
          />
        </label>
        <label>
          Статус
          <select value={status} onChange={(e) => setStatus(e.target.value)}>
            {STATUS_OPTIONS.map((opt) => (
              <option key={opt.value || 'all'} value={opt.value}>{opt.label}</option>
            ))}
          </select>
        </label>
        <div className="bp-kt-actions">
          <button type="submit" className="button button-primary">Применить фильтры</button>
        </div>
      </form>
      {error && <p className="bp-kt-error">{error}</p>}
      {loading ? (
        <p className="bp-kt-muted">Загрузка результатов…</p>
      ) : (
        <>
          <p className="bp-kt-muted">{attemptCountLabel(total)}</p>
          <table className="widefat striped bp-kt-table">
            <thead>
              <tr>
                <th>Пользователь</th>
                <th>Тест</th>
                <th>Версия</th>
                <th>Балл</th>
                <th>Статус</th>
                <th>Срок</th>
                <th>Завершён</th>
              </tr>
            </thead>
            <tbody>
              {rows.length === 0 ? (
                <tr>
                  <td colSpan={7}>Нет попыток по этим фильтрам.</td>
                </tr>
              ) : (
                rows.map((row) => (
                  <tr key={row.id}>
                    <td>{formatUser(row)}</td>
                    <td>{row.testTitle}</td>
                    <td>{row.testVersion}</td>
                    <td>{row.score.toFixed(2)}%</td>
                    <td>{statusLabel(row.status)}</td>
                    <td>{validityLabel(row.validity)}</td>
                    <td>{row.dateFinished}</td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
          {pageCount > 1 && (
            <div className="bp-kt-actions">
              <button
                type="button"
                className="button"
                disabled={page <= 1}
                onClick={() => setPage((p) => Math.max(1, p - 1))}
              >
                Назад
              </button>
              <span className="bp-kt-muted">Страница {page} из {pageCount}</span>
              <button
                type="button"
                className="button"
                disabled={page >= pageCount}
                onClick={() => setPage((p) => p + 1)}
              >
                Далее
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
}
