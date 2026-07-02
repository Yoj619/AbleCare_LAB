import { ENDPOINTS } from '../config/api';
import { apiRequest, type ApiResult } from './apiClient';
import { setToken, clearToken } from './authToken';

// ─── Types ────────────────────────────────────────────────────────────────────

export type UserRole = 'caregiver' | 'healthcare_provider' | 'admin';

export interface AuthUser {
  id: number;
  role: UserRole;
  firstName: string;
  lastName: string;
  email: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  token: string;
  user: AuthUser;
}

export interface RegisterCaregiverRequest {
  name: string;
  email: string;
  password: string;
  phone: string;
  address?: string;
  barangay?: string;
}

export interface RegisterCaregiverResponse {
  token: string;
  user: AuthUser;
}

export interface LogoutResponse {
  message: string;
}

export type LoginResult            = ApiResult<LoginResponse>;
export type RegisterCaregiverResult = ApiResult<RegisterCaregiverResponse>;
export type LogoutResult           = ApiResult<LogoutResponse>;

// ─── Service ──────────────────────────────────────────────────────────────────

export async function login(params: LoginRequest): Promise<LoginResult> {
  const email    = params.email.trim();
  const password = params.password;

  if (!email || !password) {
    return { ok: false, error: 'Please enter your email and password.' };
  }

  const result = await apiRequest<LoginResponse>(ENDPOINTS.login, {
    method: 'POST',
    body: { email, password },
    auth: false,
  });

  if (result.ok) {
    await setToken(result.data.token);
  }

  return result;
}

export async function registerCaregiver(params: RegisterCaregiverRequest): Promise<RegisterCaregiverResult> {
  const { name, email, password, phone, address = '', barangay = '' } = params;

  if (!name.trim() || !email.trim() || !phone.trim()) {
    return { ok: false, error: 'Please fill in all required fields.' };
  }
  if (password.length < 8) {
    return { ok: false, error: 'Password must be at least 8 characters.' };
  }

  const result = await apiRequest<RegisterCaregiverResponse>(ENDPOINTS.registerCaregiver, {
    method: 'POST',
    body: { name, email, password, phone, address, barangay },
    auth: false,
  });

  if (result.ok) {
    await setToken(result.data.token);
  }

  return result;
}

export async function logout(): Promise<LogoutResult> {
  const result = await apiRequest<LogoutResponse>(ENDPOINTS.logout, { method: 'POST' });

  // Clear the local session regardless of whether the server call succeeded —
  // the user's intent is to log out even if the network request fails.
  await clearToken();

  return result;
}
