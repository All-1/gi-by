function adminConfig() {
  const config = window.bpKnowledgeTestsAdmin;
  if (!config?.restUrl || !config.restNonce) {
    throw new Error('Admin REST config is missing.');
  }
  return config;
}

export async function apiRequest<T>(
  path: string,
  options: RequestInit = {},
): Promise<T> {
  const { restUrl, restNonce } = adminConfig();
  const headers = new Headers(options.headers);
  headers.set('X-WP-Nonce', restNonce);
  if (options.body !== undefined && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }

  const response = await fetch(`${restUrl}${path}`, {
    ...options,
    credentials: 'same-origin',
    headers,
  });

  if (!response.ok) {
    let detail = `REST error ${response.status}`;
    try {
      const errBody = (await response.json()) as { message?: string };
      if (errBody.message) {
        detail = errBody.message;
      }
    } catch {
      // ignore parse errors
    }
    throw new Error(detail);
  }

  if (response.status === 204) {
    return undefined as T;
  }

  return (await response.json()) as T;
}
