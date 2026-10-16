export type NotificationMode = 'block' | 'bottom';

export type SettingsForm = {
  bronzeThreshold: string;
  silverThreshold: string;
  goldThreshold: string;
  lockThreshold: string;
  retakeDelayDays: string;
  newTestNotificationDismissLimit: string;
  failedTestNotificationDismissLimit: string;
  newTestNotificationMode: NotificationMode;
  failedTestNotificationMode: NotificationMode;
  rankNameGold: string;
  rankNameSilver: string;
  rankNameBronze: string;
  rankNameFailed: string;
};

export const SETTINGS_KEY_MAP: Record<keyof SettingsForm, string> = {
  bronzeThreshold: 'bronze_threshold',
  silverThreshold: 'silver_threshold',
  goldThreshold: 'gold_threshold',
  lockThreshold: 'lock_threshold',
  retakeDelayDays: 'retake_delay_days',
  newTestNotificationDismissLimit: 'new_test_notification_dismiss_limit',
  failedTestNotificationDismissLimit: 'failed_test_notification_dismiss_limit',
  newTestNotificationMode: 'new_test_notification_mode',
  failedTestNotificationMode: 'failed_test_notification_mode',
  rankNameGold: 'rank_name_gold',
  rankNameSilver: 'rank_name_silver',
  rankNameBronze: 'rank_name_bronze',
  rankNameFailed: 'rank_name_failed',
};

export const DEFAULT_SETTINGS: SettingsForm = {
  bronzeThreshold: '70',
  silverThreshold: '80',
  goldThreshold: '90',
  lockThreshold: '95',
  retakeDelayDays: '1',
  newTestNotificationDismissLimit: '10',
  failedTestNotificationDismissLimit: '10',
  newTestNotificationMode: 'bottom',
  failedTestNotificationMode: 'bottom',
  rankNameGold: 'Guru',
  rankNameSilver: 'Expert',
  rankNameBronze: 'Specialist',
  rankNameFailed: 'Failed',
};

export function settingsFromApi(map: Record<string, string>): SettingsForm {
  const read = (apiKey: string, fallback: string): string => map[apiKey] ?? fallback;

  const mode = (apiKey: string, fallback: NotificationMode): NotificationMode => {
    const raw = map[apiKey];
    return raw === 'block' || raw === 'bottom' ? raw : fallback;
  };

  return {
    bronzeThreshold: read('bronze_threshold', DEFAULT_SETTINGS.bronzeThreshold),
    silverThreshold: read('silver_threshold', DEFAULT_SETTINGS.silverThreshold),
    goldThreshold: read('gold_threshold', DEFAULT_SETTINGS.goldThreshold),
    lockThreshold: read('lock_threshold', DEFAULT_SETTINGS.lockThreshold),
    retakeDelayDays: read('retake_delay_days', DEFAULT_SETTINGS.retakeDelayDays),
    newTestNotificationDismissLimit: read(
      'new_test_notification_dismiss_limit',
      DEFAULT_SETTINGS.newTestNotificationDismissLimit,
    ),
    failedTestNotificationDismissLimit: read(
      'failed_test_notification_dismiss_limit',
      DEFAULT_SETTINGS.failedTestNotificationDismissLimit,
    ),
    newTestNotificationMode: mode('new_test_notification_mode', DEFAULT_SETTINGS.newTestNotificationMode),
    failedTestNotificationMode: mode(
      'failed_test_notification_mode',
      DEFAULT_SETTINGS.failedTestNotificationMode,
    ),
    rankNameGold: read('rank_name_gold', DEFAULT_SETTINGS.rankNameGold),
    rankNameSilver: read('rank_name_silver', DEFAULT_SETTINGS.rankNameSilver),
    rankNameBronze: read('rank_name_bronze', DEFAULT_SETTINGS.rankNameBronze),
    rankNameFailed: read('rank_name_failed', DEFAULT_SETTINGS.rankNameFailed),
  };
}

export function settingsToApi(form: SettingsForm): Record<string, string> {
  const out: Record<string, string> = {};
  for (const [field, apiKey] of Object.entries(SETTINGS_KEY_MAP) as [keyof SettingsForm, string][]) {
    out[apiKey] = form[field];
  }
  return out;
}
