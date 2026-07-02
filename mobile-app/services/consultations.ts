import { ENDPOINTS } from '../config/api';
import { apiRequest, type ApiResult } from './apiClient';

// ─── Types ────────────────────────────────────────────────────────────────────

export interface ConsultationStatus {
  id: number;
  status: 'pending' | 'accepted' | 'completed' | 'declined';
  notes: string | null;
  providerName: string;
  clinicName: string | null;
  createdAt: string;
  updatedAt: string;
  alreadyExists?: boolean;
}

export type CreateConsultationResult = ApiResult<ConsultationStatus>;
export type GetConsultationStatusResult = ApiResult<ConsultationStatus | null>;

// ─── Service ──────────────────────────────────────────────────────────────────

export async function createConsultation(
  providerId: number,
  patientId: number,
): Promise<CreateConsultationResult> {
  return apiRequest(ENDPOINTS.createConsultation, {
    method: 'POST',
    body: { provider_id: providerId, patient_id: patientId },
  });
}

export async function getConsultationStatus(
  providerId?: number,
): Promise<GetConsultationStatusResult> {
  const url = providerId !== undefined
    ? `${ENDPOINTS.consultationStatus}?provider_id=${providerId}`
    : ENDPOINTS.consultationStatus;
  return apiRequest<ConsultationStatus | null>(url, { method: 'GET' });
}
