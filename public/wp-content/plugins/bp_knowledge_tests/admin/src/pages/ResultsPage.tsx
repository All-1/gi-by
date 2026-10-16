import { type FormEvent, useCallback, useEffect, useState } from 'react';
import { AdminSubnav } from '../components/AdminSubnav';
import { fetchResults, listTests, type AttemptResultRow, type Test } from '../api/reporting';

type Props = {
  onNavigateTests: () => void;
  onNavigateSettings: () => void;
  onNavigateAnalytics: () => void;
};

const STATUS_OPTIONS = [
  { value: '', label: 'All statuses' },
  { value: 'passed', label: 'Passed' },
  { value: 'failed', label: 'Failed' },
  { value: 'outdated', label: 'Outdated' },
];

function formatUser(row: AttemptResultRow): string {
  if (row.userDisplayName) {
    return `${row.userDisplayName} (#${row.userId})`;
  }
  return `#${row.userId}`;
}

function validityLabel(validity: AttemptResultRow['validity']): string {
  if (validity === 'valid') {
    return 'Valid';
  }
  if (validity === 'expired') {
    return 'Expired';
  }
  return '—';
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
      setError(err instanceof Error ? err.message : 'Failed to load results.');
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
        <h1>Results</h1>
      </header>
      <form className="bp-kt-card bp-kt-filters" onSubmit={onFilter}>
        <label>
          Test
          <select value={testId} onChange={(e) => setTestId(e.target.value)}>
            <option value="">All tests</option>
            {tests.map((t) => (
              <option key={t.id} value={String(t.id)}>{t.title}</option>
            ))}
          </select>
        </label>
        <label>
          User ID
          <input
            type="number"
            min={1}
            className="regular-text"
            value={userId}
            onChange={(e) => setUserId(e.target.value)}
            placeholder="gi_new_users id"
          />
        </label>
        <label>
          Status
          <select value={status} onChange={(e) => setStatus(e.target.value)}>
            {STATUS_OPTIONS.map((opt) => (
              <option key={opt.value || 'all'} value={opt.value}>{opt.label}</option>
            ))}
          </select>
        </label>
        <div className="bp-kt-actions">
          <button type="submit" className="button button-primary">Apply filters</button>
        </div>
      </form>
      {error && <p className="bp-kt-error">{error}</p>}
      {loading ? (
        <p className="bp-kt-muted">Loading results…</p>
      ) : (
        <>
          <p className="bp-kt-muted">{total} completed attempt{total === 1 ? '' : 's'}</p>
          <table className="widefat striped bp-kt-table">
            <thead>
              <tr>
                <th>User</th>
                <th>Test</th>
                <th>Version</th>
                <th>Score</th>
                <th>Status</th>
                <th>Validity</th>
                <th>Finished</th>
              </tr>
            </thead>
            <tbody>
              {rows.length === 0 ? (
                <tr>
                  <td colSpan={7}>No attempts match the filters.</td>
                </tr>
              ) : (
                rows.map((row) => (
                  <tr key={row.id}>
                    <td>{formatUser(row)}</td>
                    <td>{row.testTitle}</td>
                    <td>{row.testVersion}</td>
                    <td>{row.score.toFixed(2)}%</td>
                    <td>{row.status}</td>
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
                Previous
              </button>
              <span className="bp-kt-muted">Page {page} of {pageCount}</span>
              <button
                type="button"
                className="button"
                disabled={page >= pageCount}
                onClick={() => setPage((p) => p + 1)}
              >
                Next
              </button>
            </div>
          )}
        </>
      )}
    </div>
  );
}
