import { ENDPOINTS } from '../config/api';
import { apiRequest, type ApiResult } from './apiClient';

// ─── Types ────────────────────────────────────────────────────────────────────

export interface ClinicScoreBreakdown {
  specialization: number;
  distance: number;
  accessibility: number;
  availability: number;
}

export interface ClinicRecommendation {
  providerId: number;
  providerName: string;
  licenseNumber: string | null;
  clinicId: number | null;
  clinicName: string | null;
  address: string | null;
  barangay: string | null;
  operatingHours: string | null;
  acceptsWalkIns: boolean;
  wheelchairAccessible: boolean;
  groundFloorAccess: boolean;
  distanceKm: number | null;
  score: number;
  scoreBreakdown: ClinicScoreBreakdown;
}

export type GetRecommendationsResult = ApiResult<ClinicRecommendation[]>;

// ─── Service ──────────────────────────────────────────────────────────────────

export async function getRecommendations(
  patientId: number,
  latitude: number,
  longitude: number,
): Promise<GetRecommendationsResult> {
  return apiRequest(ENDPOINTS.recommendClinics, {
    method: 'POST',
    body: { patient_id: patientId, latitude, longitude },
  });
}
