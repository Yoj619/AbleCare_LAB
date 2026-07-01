import { ENDPOINTS } from '../config/api';
import { apiRequest, type ApiResult } from './apiClient';

// ─── Types ────────────────────────────────────────────────────────────────────

export interface TriggerAlertRequest {
  patientId: number;
  latitude:  number;
  longitude: number;
}

export interface TriggerAlertResponse {
  id:          number;
  status:      'active';
  triggeredAt: string;  // ISO 8601 timestamp from the server
}

export type TriggerAlertResult = ApiResult<TriggerAlertResponse>;
export type ResolveAlertResult = ApiResult<{ message: string }>;

// ─── Service ──────────────────────────────────────────────────────────────────

export async function triggerAlert(params: TriggerAlertRequest): Promise<TriggerAlertResult> {
  return apiRequest(ENDPOINTS.triggerEmergencyAlert, {
    method: 'POST',
    body: {
      patient_id:   params.patientId,
      latitude:     params.latitude,
      longitude:    params.longitude,
      triggered_at: new Date().toISOString(),
    },
  });
}

export async function resolveAlert(id: number): Promise<ResolveAlertResult> {
  return apiRequest(ENDPOINTS.resolveEmergencyAlert, {
    method: 'POST',
    body: { id },
  });
}
