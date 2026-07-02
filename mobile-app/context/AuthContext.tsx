import React, {
  createContext,
  useContext,
  useEffect,
  useState,
  useCallback,
  type ReactNode,
} from 'react';
import { AppState, type AppStateStatus } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as Location from 'expo-location';
import {
  login as loginRequest,
  logout as logoutRequest,
  type AuthUser,
  type LoginRequest,
} from '../services/auth';
import { loadToken } from '../services/authToken';

// ─── Types ────────────────────────────────────────────────────────────────────

const USER_STORAGE_KEY = 'ablecare_user';

export type LocationPermission = 'granted' | 'denied' | 'undetermined';
export type LoginOutcome = { ok: true } | { ok: false; error: string };

interface AuthContextValue {
  user: AuthUser | null;
  isAuthenticated: boolean;
  /** True until the persisted session has been restored on app launch. */
  isLoading: boolean;
  login: (params: LoginRequest) => Promise<LoginOutcome>;
  logout: () => Promise<void>;
  /** Hydrates the user into context from an already-stored token (e.g. after registration). */
  restoreUser: (user: AuthUser) => Promise<void>;
  /** Current foreground location permission status. */
  locationPermission: LocationPermission;
  /** Silently request foreground location permission (called once after login). */
  requestLocationPermission: () => Promise<void>;
  /** Re-read OS permission status without prompting (called via AppState change). */
  recheckLocationPermission: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

// ─── Helper ───────────────────────────────────────────────────────────────────

function toPermission(status: string): LocationPermission {
  if (status === 'granted') return 'granted';
  if (status === 'denied')  return 'denied';
  return 'undetermined';
}

// ─── Provider ─────────────────────────────────────────────────────────────────

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [locationPermission, setLocationPermission] = useState<LocationPermission>('undetermined');

  // Session restoration + initial permission check on app launch
  useEffect(() => {
    let isMounted = true;

    (async () => {
      const token = await loadToken();
      const cachedUserRaw = await AsyncStorage.getItem(USER_STORAGE_KEY);

      if (token && cachedUserRaw) {
        try {
          const cachedUser = JSON.parse(cachedUserRaw) as AuthUser;
          if (isMounted) setUser(cachedUser);

          // Sync permission state on session restore without prompting the user
          const { status } = await Location.getForegroundPermissionsAsync();
          if (isMounted) setLocationPermission(toPermission(status));
        } catch {
          await AsyncStorage.removeItem(USER_STORAGE_KEY);
        }
      }

      if (isMounted) setIsLoading(false);
    })();

    return () => { isMounted = false; };
  }, []);

  // Re-read permission whenever the app comes back to foreground.
  // Handles the case where the user toggled location in device settings.
  const recheckLocationPermission = useCallback(async (): Promise<void> => {
    const { status } = await Location.getForegroundPermissionsAsync();
    setLocationPermission(toPermission(status));
  }, []);

  useEffect(() => {
    const subscription = AppState.addEventListener('change', (nextState: AppStateStatus) => {
      if (nextState === 'active') {
        void recheckLocationPermission();
      }
    });
    return () => subscription.remove();
  }, [recheckLocationPermission]);

  // Shows the OS permission dialog once, immediately after a successful login
  const requestLocationPermission = useCallback(async (): Promise<void> => {
    const { status } = await Location.requestForegroundPermissionsAsync();
    setLocationPermission(toPermission(status));
  }, []);

  const restoreUser = useCallback(async (authUser: AuthUser): Promise<void> => {
    setUser(authUser);
    await AsyncStorage.setItem(USER_STORAGE_KEY, JSON.stringify(authUser));
  }, []);

  const login = useCallback(async (params: LoginRequest): Promise<LoginOutcome> => {
    const result = await loginRequest(params);
    if (!result.ok) {
      return { ok: false, error: result.error };
    }

    setUser(result.data.user);
    await AsyncStorage.setItem(USER_STORAGE_KEY, JSON.stringify(result.data.user));
    return { ok: true };
  }, []);

  const logout = useCallback(async () => {
    await logoutRequest();
    await AsyncStorage.removeItem(USER_STORAGE_KEY);
    setUser(null);
    setLocationPermission('undetermined');
  }, []);

  const value: AuthContextValue = {
    user,
    isAuthenticated: user !== null,
    isLoading,
    login,
    logout,
    restoreUser,
    locationPermission,
    requestLocationPermission,
    recheckLocationPermission,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

// ─── Hook ─────────────────────────────────────────────────────────────────────

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth() must be used within an <AuthProvider>.');
  }
  return ctx;
}
