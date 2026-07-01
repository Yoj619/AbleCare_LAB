import { getAuthHeaders } from './authToken';

// ─── Shared request/response plumbing for every AbleCare service ───────────────
// Mirrors the error-handling pattern in aiGuidance.ts, but factored out since
// every backend/api/** endpoint now shares the same { data, error } envelope.

export type ApiResult<T> = { ok: true; data: T } | { ok: false; error: string };

interface ApiEnvelope<T> {
  data: T | null;
  error: string | null;
}

interface ApiRequestOptions {
  method?: 'GET' | 'POST';
  body?: unknown;
  /** Attach the stored bearer token. Defaults to true — only auth/login and
   *  auth/register-caregiver should pass false. */
  auth?: boolean;
}

export async function apiRequest<T>(
  url: string,
  options: ApiRequestOptions = {},
): Promise<ApiResult<T>> {
  const { method = 'GET', body, auth = true } = options;

  try {
    const headers: Record<string, string> = { 'Content-Type': 'application/json' };
    if (auth) {
      Object.assign(headers, await getAuthHeaders());
    }

    const response = await fetch(url, {
      method,
      headers,
      body: body !== undefined ? JSON.stringify(body) : undefined,
    });

    const json = (await response.json()) as ApiEnvelope<T>;

    if (!response.ok || json.error) {
      return {
        ok: false,
        error: typeof json.error === 'string'
          ? json.error
          : 'An unexpected error occurred. Please try again.',
      };
    }

    if (json.data === null || json.data === undefined) {
      return { ok: false, error: 'Received an empty response from the server.' };
    }

    return { ok: true, data: json.data };
  } catch (err) {
    // Network error, timeout, JSON parse failure, etc.
    const message =
      err instanceof TypeError && err.message.includes('Network request failed')
        ? 'Could not reach the server. Check that XAMPP is running and your device is on the same network.'
        : 'Something went wrong. Please try again.';

    return { ok: false, error: message };
  }
}

/** Appends query parameters to a base URL, skipping undefined/null values. */
export function withQuery(baseUrl: string, params: Record<string, string | number | undefined | null>): string {
  const query = Object.entries(params)
    .filter(([, v]) => v !== undefined && v !== null)
    .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(String(v))}`)
    .join('&');
  return query ? `${baseUrl}?${query}` : baseUrl;
}
