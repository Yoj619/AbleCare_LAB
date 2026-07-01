import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  StatusBar,
  Linking,
  ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import * as Location from 'expo-location';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import DrawerMenu from '../../components/DrawerMenu';
import AppButton from '../../components/AppButton';
import { useActivePatient } from '../../hooks/useActivePatient';
import { useAuth } from '../../context/AuthContext';
import { triggerAlert } from '../../services/emergency';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;
type Phase = 'idle' | 'locating' | 'sent' | 'denied' | 'error';

const EMERGENCY_NUMBER = '+639972610109';
const GPS_TIMEOUT_MS   = 10_000;

export default function EmergencyAlertScreen() {
  const navigation = useNavigation<Nav>();
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [phase, setPhase]           = useState<Phase>('idle');
  const [sentAt, setSentAt]         = useState('');
  const [errorMsg, setErrorMsg]     = useState('');

  const { patient }            = useActivePatient();
  const { locationPermission } = useAuth();

  function handleDrawerNav(key: string) {
    switch (key) {
      case 'Dashboard':          navigation.navigate('Main', { screen: 'Home' });    break;
      case 'Patient':            navigation.navigate('Main', { screen: 'Patient' }); break;
      case 'HealthRecords':      navigation.navigate('HealthRecords');               break;
      case 'AIHelp':             navigation.navigate('Main', { screen: 'AIHelp' }); break;
      case 'TherapySchedule':    navigation.navigate('TherapySchedule');             break;
      case 'Messages':           navigation.navigate('Messages');                    break;
      case 'RecommendedClinics': navigation.navigate('RecommendedClinics');          break;
    }
  }

  async function handleEmergencyPress(): Promise<void> {
    // Permission denied — show the dedicated screen rather than silently failing
    if (locationPermission !== 'granted') {
      setPhase('denied');
      return;
    }

    if (!patient) {
      setErrorMsg('No patient on file. Please add a patient before triggering an alert.');
      setPhase('error');
      return;
    }

    setPhase('locating');

    // Fetch live GPS with a hard 10-second timeout
    let loc: Location.LocationObject;
    try {
      loc = await Promise.race([
        Location.getCurrentPositionAsync({ accuracy: Location.Accuracy.High }),
        new Promise<never>((_, reject) =>
          setTimeout(() => reject(new Error('timeout')), GPS_TIMEOUT_MS)
        ),
      ]);
    } catch (e) {
      setErrorMsg(
        e instanceof Error && e.message === 'timeout'
          ? 'Unable to get your location. Check your GPS signal and try again.'
          : 'Failed to get your location. Please try again.'
      );
      setPhase('error');
      return;
    }

    // Send alert with live GPS coordinates
    const result = await triggerAlert({
      patientId: patient.id,
      latitude:  loc.coords.latitude,
      longitude: loc.coords.longitude,
    });

    if (!result.ok) {
      setErrorMsg(result.error ?? 'Failed to send emergency alert. Please try again.');
      setPhase('error');
      return;
    }

    setSentAt(
      new Date().toLocaleTimeString([], {
        hour:   '2-digit',
        minute: '2-digit',
        second: '2-digit',
      })
    );
    setPhase('sent');

    // Open emergency dialer immediately after confirmation
    void Linking.openURL(`tel:${EMERGENCY_NUMBER}`);
  }

  // ── Locating overlay ─────────────────────────────────────────────────────

  if (phase === 'locating') {
    return (
      <View style={styles.overlay}>
        <ActivityIndicator size="large" color={Colors.danger} style={styles.spinner} />
        <Text style={styles.overlayHeading}>Getting your location...</Text>
        <Text style={styles.overlaySub}>Please wait while we pinpoint your GPS position.</Text>
      </View>
    );
  }

  // ── Sent confirmation ────────────────────────────────────────────────────

  if (phase === 'sent') {
    return (
      <View style={styles.overlay}>
        <View style={styles.overlayCard}>
          <View style={[styles.stateIcon, styles.stateIconSent]}>
            <Ionicons name="checkmark" size={32} color={Colors.white} />
          </View>
          <Text style={styles.overlayHeading}>Emergency Alert Sent</Text>
          <Text style={styles.overlaySub}>Help is on the way.</Text>
          <Text style={styles.sentTime}>Triggered at {sentAt}</Text>
          <AppButton
            icon="call"
            label="Call MMDRMO"
            variant="danger"
            onPress={() => void Linking.openURL(`tel:${EMERGENCY_NUMBER}`)}
            style={styles.fullBtn}
          />
          <View style={styles.gap} />
          <AppButton
            icon="home-outline"
            label="Back to Home"
            variant="dangerOutline"
            onPress={() => navigation.reset({ index: 0, routes: [{ name: 'Main' }] })}
          />
        </View>
      </View>
    );
  }

  // ── Location denied ──────────────────────────────────────────────────────

  if (phase === 'denied') {
    return (
      <View style={styles.overlay}>
        <View style={styles.overlayCard}>
          <View style={[styles.stateIcon, styles.stateIconWarn]}>
            <Ionicons name="location-outline" size={28} color={Colors.white} />
          </View>
          <Text style={styles.overlayHeading}>Location Access Required</Text>
          <Text style={styles.overlaySub}>
            Please enable location access in settings to use emergency alerts.
          </Text>
          <AppButton
            icon="settings-outline"
            label="Open Settings"
            variant="danger"
            onPress={() => void Linking.openSettings()}
            style={styles.fullBtn}
          />
          <View style={styles.gap} />
          <AppButton
            icon="arrow-back"
            label="Go Back"
            variant="dangerOutline"
            onPress={() => setPhase('idle')}
          />
        </View>
      </View>
    );
  }

  // ── Error ────────────────────────────────────────────────────────────────

  if (phase === 'error') {
    return (
      <View style={styles.overlay}>
        <View style={styles.overlayCard}>
          <View style={[styles.stateIcon, styles.stateIconWarn]}>
            <Ionicons name="alert-circle-outline" size={28} color={Colors.white} />
          </View>
          <Text style={styles.overlayHeading}>Alert Not Sent</Text>
          <Text style={styles.overlaySub}>{errorMsg}</Text>
          <AppButton
            icon="refresh-outline"
            label="Try Again"
            variant="danger"
            onPress={() => void handleEmergencyPress()}
            style={styles.fullBtn}
          />
          <View style={styles.gap} />
          <AppButton
            icon="arrow-back"
            label="Go Back"
            variant="dangerOutline"
            onPress={() => setPhase('idle')}
          />
        </View>
      </View>
    );
  }

  // ── Idle ─────────────────────────────────────────────────────────────────

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader
        onHamburgerPress={() => setDrawerOpen(true)}
        onBellPress={() => navigation.navigate('Notifications')}
      />
      <View style={styles.content}>
        {/* Alert info banner */}
        <View style={styles.alertBanner}>
          <View style={styles.alertIconWrap}>
            <Ionicons name="warning" size={20} color={Colors.white} />
          </View>
          <View>
            <Text style={styles.alertBannerTitle}>Emergency Alert</Text>
            <Text style={styles.alertBannerSub}>Immediate assistance available.</Text>
          </View>
        </View>

        {/* Pre-warning when location is already known to be denied */}
        {locationPermission === 'denied' && (
          <View style={styles.locWarn}>
            <Ionicons name="location-outline" size={14} color={Colors.danger} />
            <Text style={styles.locWarnTxt}>
              Location disabled — enable it in settings before sending an alert.
            </Text>
          </View>
        )}

        {/* Big Emergency Button */}
        <TouchableOpacity
          style={styles.emergencyBtn}
          onPress={() => void handleEmergencyPress()}
          activeOpacity={0.85}
        >
          <View style={styles.emergencyCircle}>
            <Text style={styles.emergencyExclaim}>!</Text>
          </View>
          <Text style={styles.emergencyLabel}>EMERGENCY ALERT</Text>
        </TouchableOpacity>
      </View>

      <DrawerMenu
        visible={drawerOpen}
        activeKey="EmergencyAlert"
        onClose={() => setDrawerOpen(false)}
        onNavigate={handleDrawerNav}
        onLogout={() => { setDrawerOpen(false); navigation.navigate('Logout'); }}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe:    { flex: 1, backgroundColor: Colors.background },
  content: { flex: 1, padding: Spacing.md, gap: Spacing.xl },

  // ── Full-screen overlays ──────────────────────────────────────────────────
  overlay: {
    flex: 1,
    backgroundColor: Colors.overlayBg,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.xl,
  },
  spinner:        { marginBottom: Spacing.md },
  overlayCard: {
    backgroundColor: Colors.white,
    borderRadius: Radius.xl,
    borderWidth: 1.5,
    borderColor: Colors.danger,
    padding: Spacing.xl,
    width: '100%',
    alignItems: 'center',
  },
  stateIcon: {
    width: 64, height: 64, borderRadius: 32,
    alignItems: 'center', justifyContent: 'center',
    marginBottom: Spacing.md,
  },
  stateIconSent: { backgroundColor: Colors.success },
  stateIconWarn: { backgroundColor: Colors.danger },
  overlayHeading: {
    fontSize: Typography.size.xl,
    fontWeight: Typography.weight.bold,
    color: Colors.dark,
    textAlign: 'center',
    marginBottom: Spacing.xs,
  },
  overlaySub: {
    fontSize: Typography.size.sm,
    color: Colors.textSecondary,
    textAlign: 'center',
    marginBottom: Spacing.lg,
    lineHeight: 20,
  },
  sentTime: {
    fontSize: Typography.size.sm,
    fontWeight: Typography.weight.semiBold,
    color: Colors.danger,
    marginBottom: Spacing.lg,
    textAlign: 'center',
  },
  fullBtn: { width: '100%' },
  gap:     { height: Spacing.sm },

  // ── Idle state ────────────────────────────────────────────────────────────
  alertBanner: {
    backgroundColor: Colors.dangerLight,
    borderRadius: Radius.lg,
    padding: Spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.md,
  },
  alertIconWrap: {
    width: 40, height: 40, borderRadius: 20,
    backgroundColor: Colors.danger,
    alignItems: 'center', justifyContent: 'center',
  },
  alertBannerTitle: {
    fontSize: Typography.size.md,
    fontWeight: Typography.weight.bold,
    color: Colors.danger,
  },
  alertBannerSub: { fontSize: Typography.size.xs, color: Colors.danger, opacity: 0.8 },

  locWarn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: Colors.dangerLight,
    borderRadius: Radius.md,
    paddingHorizontal: Spacing.sm,
    paddingVertical: 6,
    marginTop: -Spacing.sm,
  },
  locWarnTxt: {
    flex: 1,
    fontSize: Typography.size.xs,
    color: Colors.danger,
    lineHeight: 16,
  },

  emergencyBtn: {
    backgroundColor: Colors.danger,
    borderRadius: Radius.lg,
    paddingVertical: Spacing.xxl,
    alignItems: 'center',
    justifyContent: 'center',
    gap: Spacing.md,
    ...Shadows.card,
  },
  emergencyCircle: {
    width: 72, height: 72, borderRadius: 36,
    borderWidth: 3, borderColor: Colors.white,
    alignItems: 'center', justifyContent: 'center',
  },
  emergencyExclaim: {
    color: Colors.white,
    fontSize: 40,
    fontWeight: Typography.weight.bold,
    lineHeight: 44,
  },
  emergencyLabel: {
    color: Colors.white,
    fontSize: Typography.size.lg,
    fontWeight: Typography.weight.bold,
    letterSpacing: 1,
  },
});
