import { apiRequest } from './client';

export type ConfigSaveResult = {
  settings: Record<string, string>;
  achievementsRecalculated?: boolean;
};

export async function fetchSettings(): Promise<Record<string, string>> {
  const body = await apiRequest<{ settings: Record<string, string> }>('/config');
  return body.settings ?? {};
}

export async function saveSettings(settings: Record<string, string>): Promise<ConfigSaveResult> {
  return apiRequest<ConfigSaveResult>('/config', {
    method: 'PUT',
    body: JSON.stringify({ settings }),
  });
}
