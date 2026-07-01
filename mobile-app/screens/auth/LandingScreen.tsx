import React from 'react';
import {
  View,
  Text,
  ScrollView,
  StyleSheet,
  StatusBar,
  ImageBackground,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppButton from '../../components/AppButton';
import Logo from '../../components/Logo';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'Landing'>;

const BG = require('../../assets/bg.png') as number;

const FEATURES: { icon: keyof typeof Ionicons.glyphMap; label: string }[] = [
  { icon: 'heart-outline',   label: 'AI First Aid Guidance' },
  { icon: 'medkit-outline',  label: 'Clinic Recommendation' },
  { icon: 'warning-outline', label: 'Emergency Alert System' },
  { icon: 'person-outline',  label: 'Caregiver Support Platform' },
];

const STATS = [
  { value: '24/7',  label: 'AI Support' },
  { value: '500+',  label: 'Providers' },
  { value: '<2min', label: 'Response' },
];

export default function LandingScreen({ navigation }: Props) {
  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor="transparent" translucent />
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>

        {/* ── Hero with background image ── */}
        <ImageBackground source={BG} style={styles.heroBg} resizeMode="cover">
          {/* Dark teal overlay for readability */}
          <View style={styles.heroOverlay}>
            {/* Logo badge — white-bg logo reads cleanly on the dark overlay */}
            <View style={styles.logoBadge}>
              <Logo size="lg" />
            </View>

            <Text style={styles.appName}>AbleCare</Text>
            <Text style={styles.tagline}>Smart care support for elderly and PWD</Text>
            <Text style={styles.description}>
              AI-powered first aid guidance, personalized clinic recommendations,
              and rapid emergency assistance for caregivers.
            </Text>
          </View>
        </ImageBackground>

        {/* ── Content below the hero ── */}
        <View style={styles.body}>

          {/* Features */}
          <View style={styles.features}>
            {FEATURES.map(f => (
              <View key={f.label} style={styles.featureRow}>
                <View style={styles.featureIconWrap}>
                  <Ionicons name={f.icon} size={18} color={Colors.primary} />
                </View>
                <Text style={styles.featureLabel}>{f.label}</Text>
                <Ionicons name="chevron-forward" size={18} color={Colors.textMuted} />
              </View>
            ))}
          </View>

          {/* Actions */}
          <View style={styles.actions}>
            <AppButton label="Get Started" onPress={() => navigation.navigate('Register')} />
            <View style={styles.gap} />
            <AppButton label="Login" variant="outline" onPress={() => navigation.navigate('Login')} />
          </View>

          {/* Stats */}
          <View style={styles.statsRow}>
            {STATS.map((s, i) => (
              <React.Fragment key={s.label}>
                {i > 0 && <View style={styles.statDivider} />}
                <View style={styles.stat}>
                  <Text style={styles.statValue}>{s.value}</Text>
                  <Text style={styles.statLabel}>{s.label}</Text>
                </View>
              </React.Fragment>
            ))}
          </View>

          <Text style={styles.footer}>© 2025 AbleCare. All rights reserved.</Text>
        </View>

      </ScrollView>
    </SafeAreaView>
  );
}

const OVERLAY = 'rgba(20, 42, 42, 0.72)';

const styles = StyleSheet.create({
  safe:  { flex: 1, backgroundColor: Colors.white },
  scroll: { flexGrow: 1 },

  // ── Hero ──────────────────────────────────────────────────────
  heroBg: {
    width: '100%',
    minHeight: 340,
  },
  heroOverlay: {
    backgroundColor: OVERLAY,
    alignItems: 'center',
    paddingTop: 56,          // clears status bar + a bit more
    paddingBottom: Spacing.xl + Spacing.md,
    paddingHorizontal: Spacing.lg,
  },
  logoBadge: {
    width: 108,
    height: 108,
    borderRadius: 54,
    backgroundColor: Colors.white,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: Spacing.md,
    // Subtle glow so it pops off the dark overlay
    shadowColor: Colors.primary,
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.6,
    shadowRadius: 16,
    elevation: 8,
  },
  appName: {
    fontSize: Typography.size.xxl,
    fontWeight: Typography.weight.bold,
    color: Colors.white,
    marginTop: Spacing.xs,
  },
  tagline: {
    fontSize: Typography.size.lg,
    fontWeight: Typography.weight.semiBold,
    color: '#A8E6E3',
    marginTop: Spacing.xs,
    textAlign: 'center',
  },
  description: {
    fontSize: Typography.size.sm,
    color: 'rgba(255,255,255,0.80)',
    textAlign: 'center',
    marginTop: Spacing.sm,
    lineHeight: 21,
  },

  // ── Body ──────────────────────────────────────────────────────
  body: {
    backgroundColor: Colors.white,
    padding: Spacing.lg,
    paddingBottom: Spacing.xl,
  },
  features: { marginBottom: Spacing.xl },
  featureRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.white,
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: Radius.md,
    padding: Spacing.md,
    marginBottom: Spacing.sm,
    gap: Spacing.md,
  },
  featureIconWrap: {
    width: 36,
    height: 36,
    borderRadius: 18,
    backgroundColor: Colors.primaryLight,
    alignItems: 'center',
    justifyContent: 'center',
  },
  featureLabel: {
    flex: 1,
    fontSize: Typography.size.md,
    color: Colors.textPrimary,
    fontWeight: Typography.weight.medium,
  },
  actions: { marginBottom: Spacing.xl },
  gap:     { height: Spacing.sm },
  statsRow: {
    flexDirection: 'row',
    backgroundColor: Colors.primaryLight,
    borderRadius: Radius.lg,
    paddingVertical: Spacing.md,
    marginBottom: Spacing.lg,
  },
  stat: { flex: 1, alignItems: 'center' },
  statValue: {
    fontSize: Typography.size.lg,
    fontWeight: Typography.weight.bold,
    color: Colors.primary,
  },
  statLabel: {
    fontSize: Typography.size.xs,
    color: Colors.textSecondary,
    marginTop: 2,
  },
  statDivider: { width: 1, backgroundColor: Colors.border },
  footer: {
    textAlign: 'center',
    fontSize: Typography.size.xs,
    color: Colors.textMuted,
  },
});
