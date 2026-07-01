import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { View, StyleSheet, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Colors } from '../constants/theme';
import { useAuth } from '../context/AuthContext';
import type { RootStackParamList, MainTabParamList } from './types';

// Auth
import LandingScreen from '../screens/auth/LandingScreen';
import LoginScreen from '../screens/auth/LoginScreen';
import RegisterScreen from '../screens/auth/RegisterScreen';
import RegisterConsentScreen from '../screens/auth/RegisterConsentScreen';
import ForgotPasswordScreen from '../screens/auth/ForgotPasswordScreen';
import LogoutScreen from '../screens/auth/LogoutScreen';

// Dashboard
import DashboardScreen from '../screens/dashboard/DashboardScreen';
import NotificationsScreen from '../screens/dashboard/NotificationsScreen';

// AI Guidance
import AIGuidanceScreen from '../screens/ai/AIGuidanceScreen';
import AIGuidanceStep1Screen from '../screens/ai/AIGuidanceStep1Screen';
import AIGuidanceStep2Screen from '../screens/ai/AIGuidanceStep2Screen';
import AIGuidanceStep3Screen from '../screens/ai/AIGuidanceStep3Screen';
import AIGuidanceStep4Screen from '../screens/ai/AIGuidanceStep4Screen';
import AIGuidanceResultScreen from '../screens/ai/AIGuidanceResultScreen';

// Emergency
import EmergencyAlertScreen from '../screens/emergency/EmergencyAlertScreen';
import EmergencyConfirmScreen from '../screens/emergency/EmergencyConfirmScreen';

// Health Records
import HealthRecordsScreen from '../screens/health/HealthRecordsScreen';
import HealthRecordDetailScreen from '../screens/health/HealthRecordDetailScreen';
import AddHealthRecordScreen from '../screens/health/AddHealthRecordScreen';
import EditHealthRecordScreen from '../screens/health/EditHealthRecordScreen';
import HealthRecordHistoryScreen from '../screens/health/HealthRecordHistoryScreen';
import VitalSignsScreen from '../screens/health/VitalSignsScreen';
import MedicationScreen from '../screens/health/MedicationScreen';

// Messages
import MessagesScreen from '../screens/messages/MessagesScreen';
import ConversationScreen from '../screens/messages/ConversationScreen';
import NewMessageScreen from '../screens/messages/NewMessageScreen';

// Patient
import PatientProfileScreen from '../screens/patient/PatientProfileScreen';
import EditPatientProfileScreen from '../screens/patient/EditPatientProfileScreen';
import PatientInformationScreen from '../screens/patient/PatientInformationScreen';

// Clinics
import RecommendedClinicsScreen from '../screens/clinics/RecommendedClinicsScreen';
import ClinicDetailScreen from '../screens/clinics/ClinicDetailScreen';
import ClinicRecommendationResultScreen from '../screens/clinics/ClinicRecommendationResultScreen';

// Therapy
import TherapyScheduleScreen from '../screens/therapy/TherapyScheduleScreen';
import AddTherapySessionScreen from '../screens/therapy/AddTherapySessionScreen';

const Stack = createNativeStackNavigator<RootStackParamList>();
const Tab = createBottomTabNavigator<MainTabParamList>();

function TabIcon({ name, focused }: { name: string; focused: boolean }) {
  const icons: Record<string, keyof typeof Ionicons.glyphMap> = {
    Home: 'home-outline',
    Patient: 'person-outline',
    AIHelp: 'sparkles-outline',
    Emergency: 'warning-outline',
  };
  return (
    <Ionicons name={icons[name]} size={20} color={focused ? Colors.primary : Colors.textMuted} />
  );
}

function MainTabs() {
  return (
    <Tab.Navigator
      screenOptions={({ route }) => ({
        headerShown: false,
        tabBarIcon: ({ focused }) => <TabIcon name={route.name} focused={focused} />,
        tabBarActiveTintColor: Colors.primary,
        tabBarInactiveTintColor: Colors.textMuted,
        tabBarStyle: styles.tabBar,
        tabBarLabelStyle: styles.tabLabel,
      })}
    >
      <Tab.Screen name="Home" component={DashboardScreen} options={{ title: 'Home' }} />
      <Tab.Screen name="Patient" component={PatientProfileScreen} options={{ title: 'Patient' }} />
      <Tab.Screen name="AIHelp" component={AIGuidanceScreen} options={{ title: 'AI Help' }} />
      <Tab.Screen
        name="Emergency"
        component={EmergencyAlertScreen}
        options={{
          title: 'Emergency',
          tabBarActiveTintColor: Colors.danger,
          tabBarIcon: ({ focused }) => (
            <Ionicons name="warning-outline" size={20} color={focused ? Colors.danger : Colors.textMuted} />
          ),
        }}
      />
    </Tab.Navigator>
  );
}

export default function RootNavigator() {
  const { isAuthenticated, isLoading } = useAuth();

  if (isLoading) {
    return (
      <View style={styles.loadingScreen}>
        <ActivityIndicator size="large" color={Colors.primary} />
      </View>
    );
  }

  return (
    <Stack.Navigator
      initialRouteName={isAuthenticated ? 'Main' : 'Landing'}
      screenOptions={{ headerShown: false, animation: 'slide_from_right' }}
    >
      {/* Auth */}
      <Stack.Screen name="Landing" component={LandingScreen} />
      <Stack.Screen name="Login" component={LoginScreen} />
      <Stack.Screen name="Register" component={RegisterScreen} />
      <Stack.Screen name="RegisterConsent" component={RegisterConsentScreen} />
      <Stack.Screen name="ForgotPassword" component={ForgotPasswordScreen} />
      {/* Main */}
      <Stack.Screen name="Main" component={MainTabs} />
      {/* Logout modal */}
      <Stack.Screen name="Logout" component={LogoutScreen} options={{ presentation: 'transparentModal', animation: 'fade' }} />
      {/* Notifications */}
      <Stack.Screen name="Notifications" component={NotificationsScreen} />
      {/* AI Guidance */}
      <Stack.Screen name="AIGuidanceStep1" component={AIGuidanceStep1Screen} />
      <Stack.Screen name="AIGuidanceStep2" component={AIGuidanceStep2Screen} />
      <Stack.Screen name="AIGuidanceStep3" component={AIGuidanceStep3Screen} />
      <Stack.Screen name="AIGuidanceStep4" component={AIGuidanceStep4Screen} />
      <Stack.Screen name="AIGuidanceResult" component={AIGuidanceResultScreen} />
      {/* Emergency */}
      <Stack.Screen name="EmergencyConfirm" component={EmergencyConfirmScreen} options={{ presentation: 'transparentModal', animation: 'fade' }} />
      {/* Health Records */}
      <Stack.Screen name="HealthRecords" component={HealthRecordsScreen} />
      <Stack.Screen name="HealthRecordDetail" component={HealthRecordDetailScreen} />
      <Stack.Screen name="AddHealthRecord" component={AddHealthRecordScreen} />
      <Stack.Screen name="EditHealthRecord" component={EditHealthRecordScreen} />
      <Stack.Screen name="HealthRecordHistory" component={HealthRecordHistoryScreen} />
      <Stack.Screen name="VitalSigns" component={VitalSignsScreen} />
      <Stack.Screen name="Medication" component={MedicationScreen} />
      {/* Messages */}
      <Stack.Screen name="Messages" component={MessagesScreen} />
      <Stack.Screen name="Conversation" component={ConversationScreen} />
      <Stack.Screen name="NewMessage" component={NewMessageScreen} />
      {/* Patient */}
      <Stack.Screen name="EditPatientProfile" component={EditPatientProfileScreen} />
      <Stack.Screen name="PatientInformation" component={PatientInformationScreen} />
      {/* Clinics */}
      <Stack.Screen name="RecommendedClinics" component={RecommendedClinicsScreen} />
      <Stack.Screen name="ClinicDetail" component={ClinicDetailScreen} />
      <Stack.Screen name="ClinicRecommendationResult" component={ClinicRecommendationResultScreen} />
      {/* Therapy */}
      <Stack.Screen name="TherapySchedule" component={TherapyScheduleScreen} />
      <Stack.Screen name="AddTherapySession" component={AddTherapySessionScreen} />
    </Stack.Navigator>
  );
}

const styles = StyleSheet.create({
  loadingScreen: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: Colors.background,
  },
  tabBar: {
    borderTopColor: Colors.border,
    borderTopWidth: 1,
    height: 64,
    paddingBottom: 8,
    paddingTop: 6,
  },
  tabLabel: {
    fontSize: 11,
    fontWeight: '500',
  },
});
