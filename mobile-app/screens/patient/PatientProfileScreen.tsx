import React, { useState, useEffect, useCallback } from 'react';
import { View, Text, ScrollView, TouchableOpacity, StyleSheet, StatusBar, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import DrawerMenu from '../../components/DrawerMenu';
import Card from '../../components/Card';
import AppButton from '../../components/AppButton';
import { useActivePatient } from '../../hooks/useActivePatient';
import { getPatient, type Patient } from '../../services/patients';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

function calculateAge(dateOfBirth: string | null): string {
  if (!dateOfBirth) return '—';
  const dob = new Date(dateOfBirth);
  if (Number.isNaN(dob.getTime())) return '—';
  const ageMs = Date.now() - dob.getTime();
  return String(Math.floor(ageMs / (1000 * 60 * 60 * 24 * 365.25)));
}

function formatGender(gender: Patient['gender']): string {
  if (!gender) return '—';
  return gender.charAt(0).toUpperCase() + gender.slice(1);
}

export default function PatientProfileScreen() {
  const navigation = useNavigation<Nav>();
  const [drawerOpen, setDrawerOpen] = useState(false);

  const { patient: activePatient, loading: activePatientLoading, refresh: patientRefresh } = useActivePatient();
  const [patient, setPatient] = useState<Patient | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [refreshKey, setRefreshKey] = useState(0);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    if (!activePatient) { setLoading(false); return; }
    let isMounted = true;
    setLoading(true);
    (async () => {
      const result = await getPatient(activePatient.id);
      if (!isMounted) return;
      if (result.ok) { setPatient(result.data); setError(null); }
      else { setError(result.error); }
      setLoading(false);
    })();
    return () => { isMounted = false; };
  }, [activePatient, refreshKey]);

  useEffect(() => {
    if (!loading) setRefreshing(false);
  }, [loading]);

  const handleRefresh = useCallback((): void => {
    setRefreshing(true);
    patientRefresh();
    setRefreshKey(k => k + 1);
  }, [patientRefresh]);

  function handleRetry(): void {
    setRefreshKey(k => k + 1);
  }

  function handleDrawerNav(key: string) {
    switch (key) {
      case 'Dashboard': navigation.navigate('Main', { screen: 'Home' }); break;
      case 'HealthRecords': navigation.navigate('HealthRecords'); break;
      case 'AIHelp': navigation.navigate('Main', { screen: 'AIHelp' }); break;
      case 'TherapySchedule': navigation.navigate('TherapySchedule'); break;
      case 'Messages': navigation.navigate('Messages'); break;
      case 'RecommendedClinics': navigation.navigate('RecommendedClinics'); break;
      case 'EmergencyAlert': navigation.navigate('Main', { screen: 'Emergency' }); break;
    }
  }

  const isLoading = activePatientLoading || loading;

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader
        onHamburgerPress={() => setDrawerOpen(true)}
        onBellPress={() => navigation.navigate('Notifications')}
      />
      <ScrollView
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />}
      >
        {/* Profile Header Card */}
        <LinearGradient
          colors={[Colors.primary, Colors.primaryDark]}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={styles.profileCard}
        >
          {isLoading ? (
            <Text style={styles.patientId}>Loading patient...</Text>
          ) : error || !patient ? (
            <>
              <Text style={styles.patientId}>{error ?? 'No patient on file yet.'}</Text>
              {error ? (
                <TouchableOpacity onPress={handleRetry} style={styles.retryBtn}>
                  <Text style={styles.retryTxt}>Tap to retry</Text>
                </TouchableOpacity>
              ) : null}
            </>
          ) : (
            <>
              <View style={styles.patientAvatar}>
                <Text style={styles.patientAvatarText}>
                  {patient.firstName.charAt(0)}{patient.lastName.charAt(0)}
                </Text>
              </View>
              <Text style={styles.patientName}>{patient.firstName} {patient.lastName}</Text>
              <Text style={styles.patientId}>Patient ID: #{patient.id}</Text>
              <View style={styles.statsBar}>
                <View style={styles.statItem}>
                  <Text style={styles.statValue}>{calculateAge(patient.dateOfBirth)} yrs</Text>
                  <Text style={styles.statLabel}>Age</Text>
                </View>
                <View style={styles.statDivider} />
                <View style={styles.statItem}>
                  <Text style={styles.statValue}>{formatGender(patient.gender)}</Text>
                  <Text style={styles.statLabel}>Gender</Text>
                </View>
              </View>
            </>
          )}
        </LinearGradient>

        {/* Contact Info */}
        <Card style={styles.sectionCard}>
          <View style={styles.sectionHeader}>
            <Ionicons name="call-outline" size={18} color={Colors.dark} />
            <Text style={styles.sectionTitle}>Contact Information</Text>
          </View>
          <InfoRow icon="call-outline" label="Phone" value="—" />
          <InfoRow icon="location-outline" label="Address" value="—" />
        </Card>

        {/* Medical Condition */}
        <Card style={styles.sectionCard}>
          <View style={styles.sectionHeader}>
            <Ionicons name="heart-outline" size={18} color={Colors.dark} />
            <Text style={styles.sectionTitle}>Medical Condition</Text>
          </View>
          {patient?.specificCondition || patient?.disabilityCategory ? (
            <View style={styles.conditionRow}>
              <Text style={styles.conditionName}>{patient.specificCondition ?? patient.disabilityCategory}</Text>
            </View>
          ) : (
            <Text style={styles.conditionNote}>No condition on file yet.</Text>
          )}
          {patient?.medicalHistory ? (
            <View style={[styles.conditionRow, { marginTop: Spacing.sm }]}>
              <Text style={styles.conditionNote}>{patient.medicalHistory}</Text>
            </View>
          ) : null}
        </Card>

        {/* Medications — no medications service exists yet */}
        <Card style={styles.sectionCard}>
          <View style={styles.sectionHeader}>
            <MaterialCommunityIcons name="pill" size={18} color={Colors.dark} />
            <Text style={styles.sectionTitle}>Current Medications</Text>
          </View>
          <Text style={styles.conditionNote}>No medications on file.</Text>
        </Card>

        <AppButton label="Edit Patient Profile" onPress={() => navigation.navigate('EditPatientProfile')} />
        <View style={{ height: Spacing.sm }} />
        <AppButton
          label="Register New Patient"
          variant="outline"
          onPress={() => navigation.navigate('PatientInformation')}
        />
      </ScrollView>

      <DrawerMenu
        visible={drawerOpen}
        activeKey="Patient"
        onClose={() => setDrawerOpen(false)}
        onNavigate={handleDrawerNav}
        onLogout={() => { setDrawerOpen(false); navigation.navigate('Logout'); }}
      />
    </SafeAreaView>
  );
}

function InfoRow({ icon, label, value }: { icon: keyof typeof Ionicons.glyphMap; label: string; value: string }) {
  return (
    <View style={infoStyles.row}>
      <Ionicons name={icon} size={16} color={Colors.primary} style={infoStyles.icon} />
      <Text style={infoStyles.label}>{label}</Text>
      <Text style={infoStyles.value}>{value}</Text>
    </View>
  );
}

const infoStyles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center', paddingVertical: Spacing.sm, gap: Spacing.sm },
  icon: { fontSize: 16, width: 20, color: Colors.primary },
  label: { width: 72, fontSize: Typography.size.sm, color: Colors.textSecondary },
  value: { flex: 1, fontSize: Typography.size.sm, color: Colors.textPrimary, fontWeight: Typography.weight.medium },
});

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  content: { padding: Spacing.md, gap: Spacing.md, paddingBottom: Spacing.xl },
  profileCard: { borderRadius: Radius.lg, padding: Spacing.lg, alignItems: 'center', ...Shadows.card },
  patientAvatar: { width: 80, height: 80, borderRadius: 40, backgroundColor: 'rgba(255,255,255,0.3)', alignItems: 'center', justifyContent: 'center', marginBottom: Spacing.sm },
  patientAvatarText: { color: Colors.white, fontSize: Typography.size.xl, fontWeight: Typography.weight.bold },
  patientName: { color: Colors.white, fontSize: Typography.size.lg, fontWeight: Typography.weight.bold },
  patientId: { color: Colors.white, fontSize: Typography.size.xs, opacity: 0.85, marginBottom: Spacing.md },
  retryBtn: { marginTop: Spacing.xs, paddingHorizontal: Spacing.md, paddingVertical: 6, backgroundColor: 'rgba(255,255,255,0.2)', borderRadius: Radius.md },
  retryTxt: { color: Colors.white, fontSize: Typography.size.xs, fontWeight: Typography.weight.medium },
  statsBar: { flexDirection: 'row', backgroundColor: 'rgba(255,255,255,0.2)', borderRadius: Radius.md, paddingVertical: Spacing.sm, paddingHorizontal: Spacing.lg, gap: Spacing.lg },
  statItem: { alignItems: 'center' },
  statValue: { color: Colors.white, fontSize: Typography.size.md, fontWeight: Typography.weight.bold },
  statLabel: { color: Colors.white, fontSize: Typography.size.xs, opacity: 0.8 },
  statDivider: { width: 1, backgroundColor: 'rgba(255,255,255,0.3)' },
  sectionCard: {},
  sectionHeader: { flexDirection: 'row', alignItems: 'center', gap: Spacing.sm, marginBottom: Spacing.sm },
  sectionTitle: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark },
  conditionRow: { paddingVertical: 4 },
  conditionName: { fontSize: Typography.size.sm, fontWeight: Typography.weight.medium, color: Colors.textPrimary },
  conditionNote: { fontSize: Typography.size.xs, color: Colors.textMuted },
});
