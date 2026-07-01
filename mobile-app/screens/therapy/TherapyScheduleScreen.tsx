import React, { useState, useEffect, useCallback } from 'react';
import { View, Text, ScrollView, StyleSheet, StatusBar, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import DrawerMenu from '../../components/DrawerMenu';
import Card from '../../components/Card';
import AppButton from '../../components/AppButton';
import { useActivePatient } from '../../hooks/useActivePatient';
import { listSessions, type TherapySession, type TherapySessionStatus } from '../../services/therapy';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const STATUS_DISPLAY: Record<TherapySessionStatus, { label: string; color: string }> = {
  scheduled: { label: 'Scheduled', color: Colors.primary },
  completed: { label: 'Completed', color: Colors.success },
  missed:    { label: 'Missed',    color: Colors.danger },
  cancelled: { label: 'Cancelled', color: Colors.textMuted },
};

function formatSessionDate(sessionDate: string): string {
  const date = new Date(`${sessionDate}T00:00:00`);
  if (Number.isNaN(date.getTime())) return sessionDate;
  return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
}

function formatSessionTime(sessionTime: string): string {
  const [hours, minutes] = sessionTime.split(':').map(Number);
  if (Number.isNaN(hours) || Number.isNaN(minutes)) return sessionTime;
  const date = new Date();
  date.setHours(hours, minutes);
  return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

export default function TherapyScheduleScreen() {
  const navigation = useNavigation<Nav>();
  const [drawerOpen, setDrawerOpen] = useState(false);
  const { patient, refresh: patientRefresh } = useActivePatient();
  const [sessions, setSessions] = useState<TherapySession[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [refreshKey, setRefreshKey] = useState(0);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    if (!patient) { setLoading(false); return; }
    let isMounted = true;
    setLoading(true);
    (async () => {
      const result = await listSessions(patient.id);
      if (!isMounted) return;
      if (result.ok) { setSessions(result.data); setError(null); }
      else { setError(result.error); }
      setLoading(false);
    })();
    return () => { isMounted = false; };
  }, [patient, refreshKey]);

  useEffect(() => {
    if (!loading) setRefreshing(false);
  }, [loading]);

  const handleRefresh = useCallback((): void => {
    setRefreshing(true);
    patientRefresh();
    setRefreshKey(k => k + 1);
  }, [patientRefresh]);

  function handleRetry(): void { patientRefresh(); setRefreshKey(k => k + 1); }

  function handleDrawerNav(key: string) {
    switch (key) {
      case 'Dashboard': navigation.navigate('Main', { screen: 'Home' }); break;
      case 'Patient': navigation.navigate('Main', { screen: 'Patient' }); break;
      case 'HealthRecords': navigation.navigate('HealthRecords'); break;
      case 'AIHelp': navigation.navigate('Main', { screen: 'AIHelp' }); break;
      case 'Messages': navigation.navigate('Messages'); break;
      case 'RecommendedClinics': navigation.navigate('RecommendedClinics'); break;
      case 'EmergencyAlert': navigation.navigate('Main', { screen: 'Emergency' }); break;
    }
  }

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
        {/* Banner */}
        <Card style={styles.banner}>
          <View style={styles.bannerRow}>
            <Ionicons name="calendar-outline" size={20} color={Colors.primary} />
            <Text style={styles.bannerTitle}>Therapy Schedule</Text>
          </View>
        </Card>

        {/* Sessions */}
        {loading ? (
          <Text style={styles.dateLabel}>Loading sessions...</Text>
        ) : error ? (
          <>
            <Text style={styles.dateLabel}>{error}</Text>
            <AppButton label="Retry" variant="outline" onPress={handleRetry} />
          </>
        ) : sessions.length === 0 ? (
          <Text style={styles.dateLabel}>No therapy sessions scheduled yet.</Text>
        ) : (
          sessions.map(s => {
            const status = STATUS_DISPLAY[s.status];
            return (
              <View key={s.id}>
                <Text style={styles.dateLabel}>{formatSessionDate(s.sessionDate)}</Text>
                <Card style={styles.sessionCard}>
                  <View style={styles.sessionBadges}>
                    <View style={styles.timeBadge}>
                      <Text style={styles.timeBadgeText}>{formatSessionTime(s.sessionTime)}</Text>
                    </View>
                    <View style={[styles.statusBadge, { backgroundColor: status.color + '20' }]}>
                      <Text style={[styles.statusText, { color: status.color }]}>{status.label}</Text>
                    </View>
                  </View>
                  <Text style={styles.sessionProvider}>{s.providerName}</Text>
                  {s.notes ? <Text style={styles.sessionProvider}>{s.notes}</Text> : null}
                </Card>
              </View>
            );
          })
        )}

        <AppButton label="+ Add Session" onPress={() => navigation.navigate('AddTherapySession')} />
      </ScrollView>

      <DrawerMenu
        visible={drawerOpen}
        activeKey="TherapySchedule"
        onClose={() => setDrawerOpen(false)}
        onNavigate={handleDrawerNav}
        onLogout={() => { setDrawerOpen(false); navigation.navigate('Logout'); }}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  content: { padding: Spacing.md, gap: Spacing.md, paddingBottom: Spacing.xl },
  banner: {},
  bannerRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.md },
  bannerIcon: { fontSize: 24 },
  bannerTitle: { fontSize: Typography.size.lg, fontWeight: Typography.weight.bold, color: Colors.dark },
  dateLabel: { fontSize: Typography.size.sm, fontWeight: Typography.weight.semiBold, color: Colors.textSecondary, marginBottom: Spacing.xs },
  sessionCard: {},
  sessionBadges: { flexDirection: 'row', gap: Spacing.sm, marginBottom: Spacing.sm },
  timeBadge: { backgroundColor: Colors.primary, paddingHorizontal: 12, paddingVertical: 4, borderRadius: Radius.full },
  timeBadgeText: { color: Colors.white, fontSize: Typography.size.sm, fontWeight: Typography.weight.semiBold },
  statusBadge: { paddingHorizontal: 12, paddingVertical: 4, borderRadius: Radius.full },
  statusText: { fontSize: Typography.size.sm, fontWeight: Typography.weight.medium },
  sessionTitle: { fontSize: Typography.size.md, fontWeight: Typography.weight.bold, color: Colors.dark },
  sessionProvider: { fontSize: Typography.size.sm, color: Colors.primary, marginTop: 2 },
});
