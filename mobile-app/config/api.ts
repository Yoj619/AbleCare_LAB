/**
 * API base URL for the AbleCare PHP backend.
 *
 * Development options:
 *  - Android Emulator  → http://10.0.2.2/AbleCare/backend/api
 *  - iOS Simulator     → http://localhost/AbleCare/backend/api
 *  - Physical device   → replace with your computer's local IP, e.g.
 *                        http://192.168.1.5/AbleCare/backend/api
 *
 * For Expo Go on a physical device, use your machine's LAN IP so the
 * device can reach XAMPP over the same Wi-Fi network.
 */
export const API_BASE_URL = 'http://192.168.50.76/AbleCare/backend/api';

export const ENDPOINTS = {
  aiGuidance:  `${API_BASE_URL}/ai-guidance/get-guidance.php`,
  transcribe:  `${API_BASE_URL}/ai-guidance/transcribe.php`,

  // Auth
  login:             `${API_BASE_URL}/auth/login.php`,
  registerCaregiver: `${API_BASE_URL}/auth/register-caregiver.php`,
  logout:            `${API_BASE_URL}/auth/logout.php`,

  // Patients
  createPatient: `${API_BASE_URL}/patients/create.php`,
  listPatients:  `${API_BASE_URL}/patients/list.php`,
  getPatient:    `${API_BASE_URL}/patients/get.php`,
  updatePatient: `${API_BASE_URL}/patients/update.php`,
  deletePatient: `${API_BASE_URL}/patients/delete.php`,

  // Health Records
  createHealthRecord: `${API_BASE_URL}/health-records/create.php`,
  listHealthRecords:  `${API_BASE_URL}/health-records/list.php`,
  getHealthRecord:    `${API_BASE_URL}/health-records/get.php`,

  // Emergency
  triggerEmergencyAlert: `${API_BASE_URL}/emergency/trigger.php`,
  resolveEmergencyAlert: `${API_BASE_URL}/emergency/resolve.php`,
  listEmergencyAlerts:   `${API_BASE_URL}/emergency/list.php`,

  // Therapy
  createTherapySession: `${API_BASE_URL}/therapy/create.php`,
  listTherapySessions:  `${API_BASE_URL}/therapy/list.php`,
  updateTherapySession: `${API_BASE_URL}/therapy/update.php`,

  // Clinics
  recommendClinics: `${API_BASE_URL}/clinics/recommend.php`,

  // Messages
  sendMessage:      `${API_BASE_URL}/messages/send.php`,
  getConversation:  `${API_BASE_URL}/messages/list.php`,
  getInbox:         `${API_BASE_URL}/messages/inbox.php`,

  // Notifications
  listNotifications:    `${API_BASE_URL}/notifications/list.php`,
  markNotificationsRead: `${API_BASE_URL}/notifications/mark-read.php`,
} as const;
