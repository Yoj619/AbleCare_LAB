import { ENDPOINTS } from '../config/api';
import { apiRequest, type ApiResult } from './apiClient';

// ─── Types ────────────────────────────────────────────────────────────────────

export type NotificationType = 'emergency' | 'consultation' | 'therapy' | 'message' | 'system';

export interface AppNotification {
  id: number;
  title: string;
  message: string;
  type: NotificationType;
  isRead: boolean;
  createdAt: string;
}

export type GetNotificationsResult = ApiResult<AppNotification[]>;
export type MarkAsReadResult       = ApiResult<{ message: string }>;

// ─── Service ──────────────────────────────────────────────────────────────────

export async function getNotifications(): Promise<GetNotificationsResult> {
  return apiRequest(ENDPOINTS.listNotifications);
}

export async function markAsRead(idOrIds: number | number[]): Promise<MarkAsReadResult> {
  const body = Array.isArray(idOrIds) ? { ids: idOrIds } : { id: idOrIds };
  return apiRequest(ENDPOINTS.markNotificationsRead, { method: 'POST', body });
}
