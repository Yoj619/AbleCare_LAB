import { ENDPOINTS } from '../config/api';
import { apiRequest, withQuery, type ApiResult } from './apiClient';

// ─── Types ────────────────────────────────────────────────────────────────────

export type TherapySessionStatus = 'scheduled' | 'completed' | 'missed' | 'cancelled';

export interface TherapySession {
  id: number;
  healthProviderId: number;
  providerName: string;
  sessionDate: string;
  sessionTime: string;
  status: TherapySessionStatus;
  notes: string | null;
}

export interface ScheduleSessionRequest {
  patientId: number;
  healthcareProviderId: number;
  sessionDate: string;
  sessionTime: string;
  notes?: string;
}

export interface UpdateSessionRequest {
  id: number;
  status: TherapySessionStatus;
  notes?: string;
}

export type ScheduleSessionResult = ApiResult<{ id: number; status: TherapySessionStatus }>;
export type ListSessionsResult    = ApiResult<TherapySession[]>;
export type UpdateSessionResult   = ApiResult<{ message: string }>;

// ─── Service ──────────────────────────────────────────────────────────────────

export async function scheduleSession(params: ScheduleSessionRequest): Promise<ScheduleSessionResult> {
  return apiRequest(ENDPOINTS.createTherapySession, {
    method: 'POST',
    body: {
      patient_id: params.patientId,
      healthcare_provider_id: params.healthcareProviderId,
      session_date: params.sessionDate,
      session_time: params.sessionTime,
      notes: params.notes,
    },
  });
}

export async function listSessions(patientId: number): Promise<ListSessionsResult> {
  return apiRequest(withQuery(ENDPOINTS.listTherapySessions, { patient_id: patientId }));
}

export async function updateSession(params: UpdateSessionRequest): Promise<UpdateSessionResult> {
  return apiRequest(ENDPOINTS.updateTherapySession, {
    method: 'POST',
    body: { id: params.id, status: params.status, notes: params.notes },
  });
}
