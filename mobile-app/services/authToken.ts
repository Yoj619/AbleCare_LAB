import AsyncStorage from '@react-native-async-storage/async-storage';

// ─── Shared bearer-token store ──────────────────────────────────────────────────
// Used by every service in services/ to attach Authorization headers, and by
// AuthContext to restore the session on app launch. Kept outside React so
// plain service functions don't need to depend on a context provider.

const TOKEN_STORAGE_KEY = 'ablecare_api_token';

let cachedToken: string | null = null;

export async function setToken(token: string): Promise<void> {
  cachedToken = token;
  await AsyncStorage.setItem(TOKEN_STORAGE_KEY, token);
}

export async function clearToken(): Promise<void> {
  cachedToken = null;
  await AsyncStorage.removeItem(TOKEN_STORAGE_KEY);
}

/** Loads the persisted token into memory. Call once on app launch. */
export async function loadToken(): Promise<string | null> {
  if (cachedToken !== null) return cachedToken;
  cachedToken = await AsyncStorage.getItem(TOKEN_STORAGE_KEY);
  return cachedToken;
}

/** Synchronous read of the in-memory token (null until loadToken() has run). */
export function getCachedToken(): string | null {
  return cachedToken;
}

export async function getAuthHeaders(): Promise<Record<string, string>> {
  const token = cachedToken ?? (await loadToken());
  return token ? { Authorization: `Bearer ${token}` } : {};
}
