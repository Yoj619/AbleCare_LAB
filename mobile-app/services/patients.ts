import { ENDPOINTS } from '../config/api';
import { apiRequest, withQuery, type ApiResult } from './apiClient';

// ─── Types ────────────────────────────────────────────────────────────────────

export type DisabilityCategory = 'physical' | 'sensory_visual' | 'sensory_hearing' | 'cognitive';
export type PatientGender = 'male' | 'female' | 'other';

export interface Patient {
  id: number;
  firstName: string;
  lastName: string;
  dateOfBirth: string | null;
  gender: PatientGender | null;
  disabilityCategory: DisabilityCategory | null;
  specificCondition: string | null;
  medicalHistory: string | null;
  createdAt: string;
}

export interface CreatePatientRequest {
  firstName: string;
  lastName: string;
  dateOfBirth?: string;
  gender?: PatientGender;
  disabilityCategory?: DisabilityCategory;
  specificCondition?: string;
  medicalHistory?: string;
}

export interface UpdatePatientRequest extends Partial<CreatePatientRequest> {
  id: number;
}

export type CreatePatientResult = ApiResult<{ id: number }>;
export type ListPatientsResult  = ApiResult<Patient[]>;
export type GetPatientResult    = ApiResult<Patient>;
export type UpdatePatientResult = ApiResult<{ message: string }>;
export type DeletePatientResult = ApiResult<{ message: string }>;

// ─── Service ──────────────────────────────────────────────────────────────────

export async function createPatient(params: CreatePatientRequest): Promise<CreatePatientResult> {
  if (!params.firstName.trim() || !params.lastName.trim()) {
    return { ok: false, error: 'First and last name are required.' };
  }

  return apiRequest(ENDPOINTS.createPatient, {
    method: 'POST',
    body: {
      first_name: params.firstName,
      last_name: params.lastName,
      date_of_birth: params.dateOfBirth,
      gender: params.gender,
      disability_category: params.disabilityCategory,
      specific_condition: params.specificCondition,
      medical_history: params.medicalHistory,
    },
  });
}

export async function listPatients(): Promise<ListPatientsResult> {
  return apiRequest(ENDPOINTS.listPatients);
}

export async function getPatient(id: number): Promise<GetPatientResult> {
  return apiRequest(withQuery(ENDPOINTS.getPatient, { id }));
}

export async function updatePatient(params: UpdatePatientRequest): Promise<UpdatePatientResult> {
  return apiRequest(ENDPOINTS.updatePatient, {
    method: 'POST',
    body: {
      id: params.id,
      first_name: params.firstName,
      last_name: params.lastName,
      date_of_birth: params.dateOfBirth,
      gender: params.gender,
      disability_category: params.disabilityCategory,
      specific_condition: params.specificCondition,
      medical_history: params.medicalHistory,
    },
  });
}

export async function deletePatient(id: number): Promise<DeletePatientResult> {
  return apiRequest(ENDPOINTS.deletePatient, {
    method: 'POST',
    body: { id },
  });
}
