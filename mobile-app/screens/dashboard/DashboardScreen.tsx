import React, { useState, useEffect, useCallback } from 'react';
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  StyleSheet,
  StatusBar,
  Linking,
  RefreshControl,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import DrawerMenu from '../../components/DrawerMenu';
import Card from '../../components/Card';
import { useActivePatient } from '../../hooks/useActivePatient';
import { useAuth } from '../../context/AuthContext';
import { listSessions, type TherapySession } from '../../services/therapy';
import { getInbox } from '../../services/messages';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

function calculateAge(dateOfBirth: string | null): string {
  if (!dateOfBirth) return '—';
  const dob = new Date(dateOfBirth);
  if (Number.isNaN(dob.getTime())) return '—';
  const ageMs = Date.now() - dob.getTime();
  const age = Math.floor(ageMs / (1000 * 60 * 60 * 24 * 365.25));
  return `${age} years`;
}

function getGreeting(): string {
  const h = new Date().getHours();
  if (h < 12) return 'Good Morning!';
  if (h < 18) return 'Good Afternoon!';
  return 'Good Evening!';
}

function formatCurrentDate(): string {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long', month: 'long', day: 'numeric', year: 'numeric',
  });
}

interface NextAppointment { time: string; sub: string }

function getNextAppointment(sessions: TherapySession[]): NextAppointment | null {
  const today = new Date().toISOString().slice(0, 10);
  const upcoming = sessions
    .filter(s => s.status === 'scheduled' && s.sessionDate >= today)
    .sort((a, b) => {
      const d = a.sessionDate.localeCompare(b.sessionDate);
      return d !== 0 ? d : a.sessionTime.localeCompare(b.sessionTime);
    });
  const next = upcoming[0];
  if (!next) return null;
  const [h, m] = next.sessionTime.split(':').map(Number);
  const d = new Date();
  if (!Number.isNaN(h) && !Number.isNaN(m)) d.setHours(h, m);
  const time = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
  const sub = next.sessionDate === today
    ? 'Today'
    : new Date(`${next.sessionDate}T00:00:00`).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  return { time, sub };
}

export default function DashboardScreen() {
  const navigation = useNavigation<Nav>();
  const [drawerOpen, setDrawerOpen] = useState(false);
  const { patient, loading: patientLoading, error: patientError, refresh: patientRefresh } = useActivePatient();
  const { locationPermission, user } = useAuth();

  const [sessions, setSessions] = useState<TherapySession[]>([]);
  const [sessionsLoading, setSessionsLoading] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    if (!patient) { setSessionsLoading(false); return; }
    let isMounted = true;
    setSessionsLoading(true);
    (async () => {
      const result = await listSessions(patient.id);
      if (!isMounted) return;
      if (result.ok) setSessions(result.data);
      setSessionsLoading(false);
    })();
    return () => { isMounted = false; };
  }, [patient]);

  useEffect(() => {
    let isMounted = true;
    (async () => {
      const result = await getInbox();
      if (!isMounted) return;
      if (result.ok) setUnreadCount(result.data.reduce((s, c) => s + c.unreadCount, 0));
    })();
    return () => { isMounted = false; };
  }, []);

  const handleRefresh = useCallback(async (): Promise<void> => {
    setRefreshing(true);
    patientRefresh();
    const result = await getInbox();
    if (result.ok) setUnreadCount(result.data.reduce((s, c) => s + c.unreadCount, 0));
    setRefreshing(false);
  }, [patientRefresh]);

  function handleDrawerNav(key: string) {
    switch (key) {
      case 'Dashboard': break;
      case 'Patient': navigation.navigate('Main', { screen: 'Patient' }); break;
      case 'HealthRecords': navigation.navigate('HealthRecords'); break;
      case 'AIHelp': navigation.navigate('Main', { screen: 'AIHelp' }); break;
      case 'TherapySchedule': navigation.navigate('TherapySchedule'); break;
      case 'Messages': navigation.navigate('Messages'); break;
      case 'RecommendedClinics': navigation.navigate('RecommendedClinics'); break;
      case 'EmergencyAlert': navigation.navigate('Main', { screen: 'Emergency' }); break;
    }
  }

  const displaySessionsLoading = patientLoading || sessionsLoading;
  const nextAppt = displaySessionsLoading ? null : getNextAppointment(sessions);
  const activeSessions = displaySessionsLoading
    ? '—'
    : String(sessions.filter(s => s.status === 'scheduled').length);
  const userInitials = user
    ? `${user.firstName.charAt(0)}${user.lastName.charAt(0)}`
    : '—';
  const userName = user ? `${user.firstName} ${user.lastName}` : '—';
  const msgSubtitle = unreadCount > 0
    ? `${unreadCount} unread message${unreadCount === 1 ? '' : 's'}`
    : 'No new messages';

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader
        onHamburgerPress={() => setDrawerOpen(true)}
        onBellPress={() => navigation.navigate('Notifications')}
      />

      {/* Location permission warning — shown whenever location access is denied.
          Disappears automatically when the caregiver grants permission and returns
          from device settings (AppState listener in AuthContext handles the recheck). */}
      {locationPermission === 'denied' && (
        <View style={styles.locBanner}>
          <Ionicons name="warning" size={18} color={Colors.orange} style={styles.locBannerIcon} />
          <View style={styles.locBannerText}>
            <Text style={styles.locBannerTitle}>Location access is disabled.</Text>
            <Text style={styles.locBannerSub}>
              Emergency alerts will not work until you enable location in your phone settings.
            </Text>
          </View>
          <TouchableOpacity
            style={styles.locBannerBtn}
            onPress={() => void Linking.openSettings()}
          >
            <Text style={styles.locBannerBtnTxt}>Enable</Text>
          </TouchableOpacity>
        </View>
      )}

      <ScrollView
        style={styles.scroll}
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => void handleRefresh()} />}
      >
        {/* Greeting Card */}
        <Card style={styles.greetCard}>
          <View style={styles.greetRow}>
            <View style={styles.avatar}>
              <Text style={styles.avatarText}>{userInitials}</Text>
            </View>
            <View>
              <Text style={styles.greetTitle}>{getGreeting()}</Text>
              <Text style={styles.greetName}>{userName}</Text>
              <Text style={styles.greetDate}>{formatCurrentDate()}</Text>
            </View>
          </View>
        </Card>

        {/* Stat Cards */}
        <View style={styles.statsRow}>
          <Card style={styles.statCard}>
            <Ionicons name="time-outline" size={20} color={Colors.primary} style={styles.statIcon} />
            <Text style={styles.statLabel}>Next Appointment</Text>
            <Text style={styles.statValue}>{displaySessionsLoading ? '—' : (nextAppt?.time ?? 'None')}</Text>
            <Text style={styles.statSub}>{displaySessionsLoading ? '—' : (nextAppt?.sub ?? 'No upcoming')}</Text>
          </Card>
          <Card style={styles.statCard}>
            <Ionicons name="flash-outline" size={20} color={Colors.primary} style={styles.statIcon} />
            <Text style={styles.statLabel}>Active Sessions</Text>
            <Text style={styles.statValue}>{activeSessions}</Text>
            <Text style={styles.statSub}>Ongoing</Text>
          </Card>
        </View>

        {/* Patient Overview */}
        <Card style={styles.sectionCard}>
          <View style={styles.sectionHeader}>
            <Ionicons name="person-outline" size={18} color={Colors.dark} />
            <Text style={styles.sectionTitle}>Patient Overview</Text>
          </View>
          {patientLoading ? (
            <Text style={styles.overviewLabel}>Loading patient...</Text>
          ) : patientError || !patient ? (
            <Text style={styles.overviewLabel}>{patientError ?? 'No patient on file yet.'}</Text>
          ) : (
            <>
              <View style={styles.overviewRow}>
                <Text style={styles.overviewLabel}>Name</Text>
                <Text style={styles.overviewValue}>{patient.firstName} {patient.lastName}</Text>
              </View>
              <View style={styles.overviewRow}>
                <Text style={styles.overviewLabel}>Age</Text>
                <Text style={styles.overviewValue}>{calculateAge(patient.dateOfBirth)}</Text>
              </View>
              <View style={styles.overviewRow}>
                <Text style={styles.overviewLabel}>Condition</Text>
                <View style={styles.badge}>
                  <Text style={styles.badgeText}>{patient.specificCondition ?? patient.disabilityCategory ?? 'Not specified'}</Text>
                </View>
              </View>
            </>
          )}
        </Card>

        {/* Quick Actions */}
        <Text style={styles.quickTitle}>Quick Actions</Text>
        <Card style={styles.sectionCard} padding={0}>
          <QuickAction
            icon="sparkles-outline"
            title="AI Health Guidance"
            subtitle="Get instant health advice"
            onPress={() => navigation.navigate('Main', { screen: 'AIHelp' })}
          />
          <View style={styles.divider} />
          <QuickAction
            icon="calendar-outline"
            title="Therapy Schedule"
            subtitle="View upcoming appointments"
            onPress={() => navigation.navigate('TherapySchedule')}
          />
          <View style={styles.divider} />
          <QuickAction
            icon="chatbubble-outline"
            title="Messages"
            subtitle={msgSubtitle}
            badge={unreadCount > 0 ? unreadCount : undefined}
            onPress={() => navigation.navigate('Messages')}
          />
        </Card>
      </ScrollView>

      <DrawerMenu
        visible={drawerOpen}
        activeKey="Dashboard"
        onClose={() => setDrawerOpen(false)}
        onNavigate={handleDrawerNav}
        onLogout={() => { setDrawerOpen(false); navigation.navigate('Logout'); }}
      />
    </SafeAreaView>
  );
}

function QuickAction({
  icon, title, subtitle, badge, onPress,
}: { icon: keyof typeof Ionicons.glyphMap; title: string; subtitle: string; badge?: number; onPress: () => void }) {
  return (
    <TouchableOpacity style={styles.qaRow} onPress={onPress} activeOpacity={0.7}>
      <View style={styles.qaIconWrap}>
        <Ionicons name={icon} size={18} color={Colors.primary} />
      </View>
      <View style={styles.qaText}>
        <Text style={styles.qaTitle}>{title}</Text>
        <Text style={styles.qaSubtitle}>{subtitle}</Text>
      </View>
      {badge !== undefined ? (
        <View style={styles.qaBadge}>
          <Text style={styles.qaBadgeText}>{badge}</Text>
        </View>
      ) : (
        <Ionicons name="chevron-forward" size={20} color={Colors.textMuted} />
      )}
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  scroll: { flex: 1 },
  content: { padding: Spacing.md, gap: Spacing.md, paddingBottom: Spacing.xl },

  // ── Location warning banner ──────────────────────────────────────────────
  locBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.orangeLight,
    borderBottomWidth: 1,
    borderBottomColor: Colors.orange,
    paddingHorizontal: Spacing.md,
    paddingVertical: Spacing.sm,
    gap: Spacing.sm,
  },
  locBannerIcon: { flexShrink: 0 },
  locBannerText: { flex: 1 },
  locBannerTitle: {
    fontSize: Typography.size.xs,
    fontWeight: Typography.weight.bold,
    color: '#92400E',
  },
  locBannerSub: {
    fontSize: Typography.size.xs,
    color: '#92400E',
    marginTop: 1,
    lineHeight: 16,
  },
  locBannerBtn: {
    backgroundColor: Colors.orange,
    borderRadius: Radius.sm,
    paddingHorizontal: Spacing.sm,
    paddingVertical: 4,
    flexShrink: 0,
  },
  locBannerBtnTxt: {
    fontSize: Typography.size.xs,
    fontWeight: Typography.weight.bold,
    color: Colors.white,
  },

  // ── Rest ─────────────────────────────────────────────────────────────────
  greetCard: {},
  greetRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.md },
  avatar: {
    width: 52, height: 52, borderRadius: 26,
    backgroundColor: Colors.primaryLight,
    alignItems: 'center', justifyContent: 'center',
  },
  avatarText: { color: Colors.primary, fontWeight: Typography.weight.bold, fontSize: Typography.size.md },
  greetTitle: { fontSize: Typography.size.lg, fontWeight: Typography.weight.bold, color: Colors.dark },
  greetName: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  greetDate: { fontSize: Typography.size.xs, color: Colors.textMuted, marginTop: 2 },
  statsRow: { flexDirection: 'row', gap: Spacing.md },
  statCard: { flex: 1 },
  statIcon: { fontSize: 20, marginBottom: Spacing.xs },
  statLabel: { fontSize: Typography.size.xs, color: Colors.textSecondary, marginBottom: 4 },
  statValue: { fontSize: Typography.size.xl, fontWeight: Typography.weight.bold, color: Colors.dark },
  statSub: { fontSize: Typography.size.xs, color: Colors.textMuted },
  sectionCard: {},
  sectionHeader: { flexDirection: 'row', alignItems: 'center', gap: Spacing.sm, marginBottom: Spacing.sm },
  sectionTitle: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark },
  overviewRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 6 },
  overviewLabel: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  overviewValue: { fontSize: Typography.size.sm, fontWeight: Typography.weight.medium, color: Colors.textPrimary },
  badge: { backgroundColor: Colors.primaryLight, paddingHorizontal: 10, paddingVertical: 3, borderRadius: Radius.full },
  badgeText: { color: Colors.primary, fontSize: Typography.size.xs, fontWeight: Typography.weight.semiBold },
  quickTitle: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark },
  divider: { height: 1, backgroundColor: Colors.border },
  qaRow: { flexDirection: 'row', alignItems: 'center', padding: Spacing.md, gap: Spacing.md },
  qaIconWrap: {
    width: 40, height: 40, borderRadius: 20,
    backgroundColor: Colors.primaryLight,
    alignItems: 'center', justifyContent: 'center',
  },
  qaText: { flex: 1 },
  qaTitle: { fontSize: Typography.size.md, fontWeight: Typography.weight.medium, color: Colors.textPrimary },
  qaSubtitle: { fontSize: Typography.size.xs, color: Colors.textMuted, marginTop: 2 },
  qaBadge: {
    backgroundColor: Colors.badgeRed, borderRadius: Radius.full,
    minWidth: 22, height: 22, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 6,
  },
  qaBadgeText: { color: Colors.white, fontSize: Typography.size.xs, fontWeight: Typography.weight.bold },
});
