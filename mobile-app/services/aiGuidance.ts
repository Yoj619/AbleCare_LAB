import { ENDPOINTS } from '../config/api';

// ─── Types ────────────────────────────────────────────────────────────────────

export interface AIGuidanceRequest {
  symptoms: string;
  disabilityType?: string;
  medicalHistory?: string;
}

export interface AIGuidanceResponse {
  guidance: string;
  disclaimer: string;
  severity: 'low' | 'medium' | 'high';
}

export type AIGuidanceError = {
  message: string;
};

export type AIGuidanceResult =
  | { ok: true; data: AIGuidanceResponse }
  | { ok: false; error: string };

// ─── Service ──────────────────────────────────────────────────────────────────

/**
 * Sends symptoms + patient context to the AbleCare PHP backend,
 * which forwards the request to the Gemini API and returns structured guidance.
 */
export async function requestAIGuidance(
  params: AIGuidanceRequest,
): Promise<AIGuidanceResult> {
  const { symptoms, disabilityType = '', medicalHistory = '' } = params;

  if (!symptoms.trim()) {
    return { ok: false, error: 'Please describe the symptoms before asking for guidance.' };
  }

  try {
    const response = await fetch(ENDPOINTS.aiGuidance, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ symptoms, disabilityType, medicalHistory }),
    });

    const json = await response.json();

    // Backend returned a business-level error
    if (!response.ok || json.error) {
      return {
        ok: false,
        error: typeof json.error === 'string'
          ? json.error
          : 'An unexpected error occurred. Please try again.',
      };
    }

    // Validate shape of success response
    if (
      typeof json.guidance !== 'string' ||
      typeof json.disclaimer !== 'string' ||
      !['low', 'medium', 'high'].includes(json.severity)
    ) {
      return { ok: false, error: 'Received an unrecognised response from the AI service.' };
    }

    return {
      ok: true,
      data: {
        guidance: json.guidance,
        disclaimer: json.disclaimer,
        severity: json.severity as AIGuidanceResponse['severity'],
      },
    };
  } catch (err) {
    // Network error, timeout, JSON parse failure, etc.
    const message =
      err instanceof TypeError && err.message.includes('Network request failed')
        ? 'Could not reach the server. Check that XAMPP is running and your device is on the same network.'
        : 'Something went wrong. Please try again.';

    return { ok: false, error: message };
  }
}

// ─── Severity helpers ─────────────────────────────────────────────────────────

export const SEVERITY_CONFIG = {
  high: {
    label: 'High Priority',
    icon: 'alert-circle',
    color: '#D95F3B',
    bgColor: '#FDE8E2',
  },
  medium: {
    label: 'Medium Priority',
    icon: 'warning',
    color: '#F0A500',
    bgColor: '#FFF3D6',
  },
  low: {
    label: 'Low Priority',
    icon: 'checkmark-circle',
    color: '#4CAF50',
    bgColor: '#E8F5E9',
  },
} as const satisfies Record<
  AIGuidanceResponse['severity'],
  { label: string; icon: string; color: string; bgColor: string }
>;
