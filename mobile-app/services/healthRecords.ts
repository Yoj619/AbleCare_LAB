import { ENDPOINTS } from '../config/api';
import { apiRequest, withQuery, type ApiResult } from './apiClient';

// ─── Types ────────────────────────────────────────────────────────────────────

export type HealthRecordType = 'vitals' | 'symptom_log' | 'medication' | 'general';

export interface HealthRecord {
  id: number;
  patientId?: number;
  recordType: HealthRecordType;
  notes: string | null;
  recordedBy: number;
  recordedAt: string;
}

export interface CreateRecordRequest {
  patientId: number;
  recordType: HealthRecordType;
  notes?: string;
}

export type CreateRecordResult = ApiResult<{ id: number }>;
export type ListRecordsResult  = ApiResult<HealthRecord[]>;
export type GetRecordResult    = ApiResult<HealthRecord>;

// ─── Service ──────────────────────────────────────────────────────────────────

export async function createRecord(params: CreateRecordRequest): Promise<CreateRecordResult> {
  return apiRequest(ENDPOINTS.createHealthRecord, {
    method: 'POST',
    body: {
      patient_id: params.patientId,
      record_type: params.recordType,
      notes: params.notes,
    },
  });
}

export async function listRecords(patientId: number): Promise<ListRecordsResult> {
  return apiRequest(withQuery(ENDPOINTS.listHealthRecords, { patient_id: patientId }));
}

export async function getRecord(id: number): Promise<GetRecordResult> {
  return apiRequest(withQuery(ENDPOINTS.getHealthRecord, { id }));
}
