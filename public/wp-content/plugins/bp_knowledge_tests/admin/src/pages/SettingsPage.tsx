import { type FormEvent, useCallback, useEffect, useState } from 'react';
import { fetchSettings, saveSettings } from '../api/config';
import { AdminSubnav } from '../components/AdminSubnav';
import {
  type NotificationMode,
  type SettingsForm,
  DEFAULT_SETTINGS,
  settingsFromApi,
  settingsToApi,
} from '../config/settingsSchema';

type Props = {
  onNavigateTests: () => void;
};

export function SettingsPage({ onNavigateTests }: Props) {
  const [form, setForm] = useState<SettingsForm>(DEFAULT_SETTINGS);
  const [error, setError] = useState<string | null>(null);
  const [notice, setNotice] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      setForm(settingsFromApi(await fetchSettings()));
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Failed to load settings.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    load();
  }, [load]);

  const updateField = <K extends keyof SettingsForm>(key: K, value: SettingsForm[K]) => {
    setForm((prev) => ({ ...prev, [key]: value }));
  };

  const onSubmit = async (event: FormEvent) => {
    event.preventDefault();
    setBusy(true);
    setError(null);
    setNotice(null);
    try {
      const result = await saveSettings(settingsToApi(form));
      setForm(settingsFromApi(result.settings));
      if (result.achievementsRecalculated) {
        setNotice('Settings saved. User medals were recalculated for the new thresholds.');
      } else {
        setNotice('Settings saved.');
      }
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Save failed.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="bp-kt-page">
      <header className="bp-kt-page__header">
        <h1>Settings</h1>
      </header>

      <AdminSubnav active="settings" onNavigateTests={onNavigateTests} />

      {loading && <p>Loading…</p>}
      {error !== null && <p className="bp-kt-error">{error}</p>}
      {notice !== null && <p className="bp-kt-notice">{notice}</p>}

      {!loading && (
        <form className="bp-kt-settings" onSubmit={onSubmit}>
          <section className="bp-kt-card">
            <h2>Achievement thresholds (%)</h2>
            <p className="bp-kt-muted">Passed = at least Bronze. Lock disables retakes at this score or higher.</p>
            <label>
              Bronze
              <input
                className="regular-text"
                type="number"
                min={0}
                max={100}
                step="0.01"
                required
                value={form.bronzeThreshold}
                onChange={(e) => updateField('bronzeThreshold', e.target.value)}
              />
            </label>
            <label>
              Silver
              <input
                className="regular-text"
                type="number"
                min={0}
                max={100}
                step="0.01"
                required
                value={form.silverThreshold}
                onChange={(e) => updateField('silverThreshold', e.target.value)}
              />
            </label>
            <label>
              Gold
              <input
                className="regular-text"
                type="number"
                min={0}
                max={100}
                step="0.01"
                required
                value={form.goldThreshold}
                onChange={(e) => updateField('goldThreshold', e.target.value)}
              />
            </label>
            <label>
              Lock
              <input
                className="regular-text"
                type="number"
                min={0}
                max={100}
                step="0.01"
                required
                value={form.lockThreshold}
                onChange={(e) => updateField('lockThreshold', e.target.value)}
              />
            </label>
          </section>

          <section className="bp-kt-card">
            <h2>Retake</h2>
            <label>
              Delay (days)
              <input
                className="regular-text"
                type="number"
                min={0}
                step={1}
                required
                value={form.retakeDelayDays}
                onChange={(e) => updateField('retakeDelayDays', e.target.value)}
              />
            </label>
            <p className="bp-kt-muted">1 means the user can retake on the following calendar day.</p>
          </section>

          <section className="bp-kt-card">
            <h2>Notifications</h2>
            <label>
              New test — max dismissals
              <input
                className="regular-text"
                type="number"
                min={0}
                step={1}
                required
                value={form.newTestNotificationDismissLimit}
                onChange={(e) => updateField('newTestNotificationDismissLimit', e.target.value)}
              />
            </label>
            <label>
              New test — after limit
              <select
                value={form.newTestNotificationMode}
                onChange={(e) =>
                  updateField('newTestNotificationMode', e.target.value as NotificationMode)
                }
              >
                <option value="bottom">Bottom bar</option>
                <option value="block">Block account</option>
              </select>
            </label>
            <label>
              Failed test — max dismissals
              <input
                className="regular-text"
                type="number"
                min={0}
                step={1}
                required
                value={form.failedTestNotificationDismissLimit}
                onChange={(e) => updateField('failedTestNotificationDismissLimit', e.target.value)}
              />
            </label>
            <label>
              Failed test — after limit
              <select
                value={form.failedTestNotificationMode}
                onChange={(e) =>
                  updateField('failedTestNotificationMode', e.target.value as NotificationMode)
                }
              >
                <option value="bottom">Bottom bar</option>
                <option value="block">Block account</option>
              </select>
            </label>
          </section>

          <section className="bp-kt-card">
            <h2>Rank display names</h2>
            <p className="bp-kt-muted">Shown in hover text as Rank — Area (area is set per test).</p>
            <label>
              Gold rank
              <input
                className="regular-text"
                type="text"
                required
                value={form.rankNameGold}
                onChange={(e) => updateField('rankNameGold', e.target.value)}
              />
            </label>
            <label>
              Silver rank
              <input
                className="regular-text"
                type="text"
                required
                value={form.rankNameSilver}
                onChange={(e) => updateField('rankNameSilver', e.target.value)}
              />
            </label>
            <label>
              Bronze rank
              <input
                className="regular-text"
                type="text"
                required
                value={form.rankNameBronze}
                onChange={(e) => updateField('rankNameBronze', e.target.value)}
              />
            </label>
            <label>
              Failed rank
              <input
                className="regular-text"
                type="text"
                required
                value={form.rankNameFailed}
                onChange={(e) => updateField('rankNameFailed', e.target.value)}
              />
            </label>
          </section>

          <div className="bp-kt-actions">
            <button type="submit" className="button button-primary" disabled={busy}>
              {busy ? 'Saving…' : 'Save settings'}
            </button>
          </div>
        </form>
      )}
    </div>
  );
}
