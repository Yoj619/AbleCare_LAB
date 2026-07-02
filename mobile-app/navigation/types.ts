import type { NavigatorScreenParams } from '@react-navigation/native';

export type MainTabParamList = {
  Home: undefined;
  Patient: undefined;
  AIHelp: undefined;
  Emergency: undefined;
};

export type RootStackParamList = {
  // Auth
  Landing: undefined;
  Login: undefined;
  Register: undefined;
  RegisterConsent: {
    fullName: string;
    email: string;
    phone: string;
    password: string;
    address: string;
    barangay: string;
  };
  ForgotPassword: undefined;
  // Main tabs
  Main: NavigatorScreenParams<MainTabParamList>;
  // Utility
  Logout: undefined;
  Notifications: undefined;
  // AI Guidance
  AIGuidanceStep1: undefined;
  AIGuidanceStep2: undefined;
  AIGuidanceStep3: undefined;
  AIGuidanceStep4: undefined;
  AIGuidanceResult: { initialQuestion?: string };
  // Emergency
  EmergencyConfirm: undefined;
  // Health Records
  HealthRecords: undefined;
  HealthRecordDetail: { recordId: string; title: string };
  AddHealthRecord: undefined;
  EditHealthRecord: { recordId: string };
  HealthRecordHistory: undefined;
  VitalSigns: undefined;
  Medication: undefined;
  // Messages
  Messages: undefined;
  Conversation: { contactName: string; contactRole: string; userId: number };
  NewMessage: undefined;
  // Patient
  EditPatientProfile: undefined;
  PatientInformation: undefined;
  // Clinics
  RecommendedClinics: undefined;
  ClinicDetail: { clinicId: string; clinicName: string };
  ClinicRecommendationResult: { conditions: string[] };
  ConsultationStatus: {
    providerId: number;
    providerName: string;
    clinicName: string | null;
    patientId?: number;
  };
  // Therapy
  TherapySchedule: undefined;
  AddTherapySession: undefined;
};
