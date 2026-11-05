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
  onNavigateResults: () => void;
  onNavigateAnalytics: () => void;
};

export function SettingsPage({ onNavigateTests, onNavigateResults, onNavigateAnalytics }: Props) {
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
      setError(err instanceof Error ? err.message : 'Не удалось загрузить настройки.');
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
        setNotice('Настройки сохранены. Медали пользователей пересчитаны по новым порогам.');
      } else {
        setNotice('Настройки сохранены.');
      }
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : 'Не удалось сохранить.');
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="bp-kt-page">
      <header className="bp-kt-page__header">
        <h1>Настройки</h1>
      </header>

      <AdminSubnav
        active="settings"
        onNavigateTests={onNavigateTests}
        onNavigateResults={onNavigateResults}
        onNavigateAnalytics={onNavigateAnalytics}
      />

      {loading && <p>Загрузка…</p>}
      {error !== null && <p className="bp-kt-error">{error}</p>}
      {notice !== null && <p className="bp-kt-notice">{notice}</p>}

      {!loading && (
        <form className="bp-kt-settings" onSubmit={onSubmit}>
          <section className="bp-kt-card">
            <h2>Пороги достижений (%)</h2>
            <p className="bp-kt-muted">Сдан — не ниже бронзы. Блокировка запрещает пересдачу при этом результате и выше.</p>
            <label>
              Бронза
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
              Серебро
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
              Золото
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
              Блокировка
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
            <h2>Пересдача</h2>
            <label>
              Задержка (дней)
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
            <p className="bp-kt-muted">1 — пересдача доступна на следующий календарный день.</p>
          </section>

          <section className="bp-kt-card">
            <h2>Уведомления</h2>
            <label>
              Новый тест — максимум закрытий
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
            <p className="bp-kt-muted">
              «Блокировка аккаунта» — полноэкранное окно с кнопкой «Понятно». Новый тест всё равно
              один раз открывается как всплывающее окно. «Нижняя панель» остаётся полосой, которую можно
              закрыть. Выбор применяется при следующем входе в аккаунт после сохранения.
            </p>
            <label>
              Новый тест — после лимита
              <select
                value={form.newTestNotificationMode}
                onChange={(e) =>
                  updateField('newTestNotificationMode', e.target.value as NotificationMode)
                }
              >
                <option value="bottom">Нижняя панель</option>
                <option value="block">Блокировка аккаунта</option>
              </select>
            </label>
            <label>
              Несданный тест — максимум закрытий
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
              Несданный тест — после лимита
              <select
                value={form.failedTestNotificationMode}
                onChange={(e) =>
                  updateField('failedTestNotificationMode', e.target.value as NotificationMode)
                }
              >
                <option value="bottom">Нижняя панель</option>
                <option value="block">Блокировка аккаунта</option>
              </select>
            </label>
          </section>

          <section className="bp-kt-card">
            <h2>Названия званий</h2>
            <p className="bp-kt-muted">Показываются во всплывающей подсказке как «Звание — область» (область задаётся у теста).</p>
            <label>
              Звание за золото
              <input
                className="regular-text"
                type="text"
                required
                value={form.rankNameGold}
                onChange={(e) => updateField('rankNameGold', e.target.value)}
              />
            </label>
            <label>
              Звание за серебро
              <input
                className="regular-text"
                type="text"
                required
                value={form.rankNameSilver}
                onChange={(e) => updateField('rankNameSilver', e.target.value)}
              />
            </label>
            <label>
              Звание за бронзу
              <input
                className="regular-text"
                type="text"
                required
                value={form.rankNameBronze}
                onChange={(e) => updateField('rankNameBronze', e.target.value)}
              />
            </label>
            <label>
              Звание при несданном тесте
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
              {busy ? 'Сохранение…' : 'Сохранить настройки'}
            </button>
          </div>
        </form>
      )}
    </div>
  );
}
