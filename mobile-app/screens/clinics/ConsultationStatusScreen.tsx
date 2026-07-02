import React, { useState, useCallback } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  ScrollView,
  StyleSheet,
  StatusBar,
  ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useFocusEffect } from '@react-navigation/native';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import Logo from '../../components/Logo';
import AppButton from '../../components/AppButton';
import { getConsultationStatus, type ConsultationStatus } from '../../services/consultations';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'ConsultationStatus'>;

type StatusConfig = {
  icon: keyof typeof Ionicons.glyphMap;
  iconColor: string;
  bgColor: string;
  label: string;
  message: string;
};

const STATUS_CONFIG: Record<string, StatusConfig> = {
  pending: {
    icon: 'time-outline',
    iconColor: '#F59E0B',
    bgColor: '#FFFBEB',
    label: 'Pending',
    message:
      'Your request has been submitted and is waiting for the healthcare provider to review it. You will see this status update once they respond.',
  },
  accepted: {
    icon: 'checkmark-circle-outline',
    iconColor: Colors.primary,
    bgColor: '#F0FDF4',
    label: 'Approved',
    message:
      'Great news! The healthcare provider has approved your consultation request. You may now contact the clinic to schedule a visit.',
  },
  completed: {
    icon: 'ribbon-outline',
    iconColor: Colors.primary,
    bgColor: '#F0FDF4',
    label: 'Completed',
    message: 'This consultation has been completed.',
  },
  declined: {
    icon: 'close-circle-outline',
    iconColor: Colors.danger,
    bgColor: '#FEF2F2',
    label: 'Declined',
    message:
      'The healthcare provider has declined your request. Please return to the clinic list to select another provider.',
  },
};

function formatDate(iso: string): string {
  try {
    return new Date(iso).toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
    });
  } catch {
    return iso;
  }
}

export default function ConsultationStatusScreen({ navigation, route }: Props) {
  const { providerId, providerName, clinicName, patientId } = route.params;

  const [consultation, setConsultation] = useState<ConsultationStatus | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchStatus = useCallback(async () => {
    setLoading(true);
    setError(null);
    const result = await getConsultationStatus(providerId);
    setLoading(false);
    if (result.ok) {
      setConsultation(result.data);
    } else {
      setError(result.error);
    }
  }, [providerId]);

  // Re-fetch every time this screen comes into focus (handles back-navigation)
  useFocusEffect(
    useCallback(() => {
      void fetchStatus();
    }, [fetchStatus]),
  );

  const status = consultation?.status ?? 'pending';
  const cfg = STATUS_CONFIG[status] ?? STATUS_CONFIG.pending;

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>

        {/* Back */}
        <TouchableOpacity style={styles.back} onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={14} color={Colors.textSecondary} />
          <Text style={styles.backTxt}>Back to Clinics</Text>
        </TouchableOpacity>

        <View style={styles.logoWrap}>
          <Logo size="sm" />
        </View>

        <Text style={styles.title}>Consultation Request</Text>
        <Text style={styles.subtitle}>
          {clinicName ?? providerName}
        </Text>

        {loading ? (
          <View style={styles.centered}>
            <ActivityIndicator size="large" color={Colors.primary} />
            <Text style={styles.loadingTxt}>Checking status…</Text>
          </View>
        ) : error ? (
          <View style={styles.centered}>
            <Ionicons name="alert-circle-outline" size={40} color={Colors.danger} />
            <Text style={styles.errorTxt}>{error}</Text>
            <AppButton
              label="Try Again"
              variant="outline"
              onPress={() => void fetchStatus()}
              style={styles.retryBtn}
            />
          </View>
        ) : (
          <>
            {/* Status card */}
            <View style={[styles.statusCard, { backgroundColor: cfg.bgColor }]}>
              <View style={styles.statusIconWrap}>
                <Ionicons name={cfg.icon} size={44} color={cfg.iconColor} />
              </View>
              <View style={[styles.statusBadge, { borderColor: cfg.iconColor }]}>
                <Text style={[styles.statusBadgeText, { color: cfg.iconColor }]}>
                  {cfg.label}
                </Text>
              </View>
              <Text style={styles.statusMessage}>{cfg.message}</Text>

              {/* Provider notes on decline */}
              {status === 'declined' && consultation?.notes ? (
                <View style={styles.notesBox}>
                  <Text style={styles.notesLabel}>Provider's note:</Text>
                  <Text style={styles.notesText}>{consultation.notes}</Text>
                </View>
              ) : null}
            </View>

            {/* Info rows */}
            <View style={styles.infoCard}>
              <InfoRow
                icon="person-outline"
                label="Healthcare Provider"
                value={consultation?.providerName ?? providerName}
              />
              {(consultation?.clinicName ?? clinicName) ? (
                <InfoRow
                  icon="business-outline"
                  label="Clinic"
                  value={(consultation?.clinicName ?? clinicName) as string}
                />
              ) : null}
              {consultation ? (
                <>
                  <InfoRow
                    icon="calendar-outline"
                    label="Requested"
                    value={formatDate(consultation.createdAt)}
                  />
                  {consultation.updatedAt !== consultation.createdAt && (
                    <InfoRow
                      icon="refresh-outline"
                      label="Last Updated"
                      value={formatDate(consultation.updatedAt)}
                    />
                  )}
                </>
              ) : null}
            </View>
          </>
        )}

        {/* Actions */}
        <View style={styles.actions}>
          <AppButton
            label="Refresh Status"
            variant="outline"
            onPress={() => void fetchStatus()}
            style={styles.refreshBtn}
          />
          <AppButton
            label="Return to Home"
            onPress={() => navigation.reset({ index: 0, routes: [{ name: 'Main' }] })}
            style={styles.homeBtn}
          />
          {status === 'declined' && (
            <AppButton
              label="View Other Clinics"
              variant="outline"
              onPress={() => navigation.goBack()}
              style={styles.retryBtn}
            />
          )}
        </View>

      </ScrollView>
    </SafeAreaView>
  );
}

function InfoRow({
  icon,
  label,
  value,
}: {
  icon: keyof typeof Ionicons.glyphMap;
  label: string;
  value: string;
}) {
  return (
    <View style={styles.infoRow}>
      <Ionicons name={icon} size={15} color={Colors.textSecondary} style={styles.infoIcon} />
      <View style={styles.infoText}>
        <Text style={styles.infoLabel}>{label}</Text>
        <Text style={styles.infoValue}>{value}</Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  safe:    { flex: 1, backgroundColor: Colors.background },
  scroll:  { padding: Spacing.lg, paddingBottom: Spacing.xl },

  back:    { flexDirection: 'row', alignItems: 'center', gap: 4, marginBottom: Spacing.sm },
  backTxt: { fontSize: Typography.size.sm, color: Colors.textSecondary, fontWeight: Typography.weight.medium },

  logoWrap: { alignItems: 'center', marginBottom: Spacing.sm },

  title: {
    fontSize: Typography.size.xl,
    fontWeight: Typography.weight.bold,
    color: Colors.dark,
    textAlign: 'center',
    marginBottom: Spacing.xs,
  },
  subtitle: {
    fontSize: Typography.size.sm,
    color: Colors.primary,
    textAlign: 'center',
    fontWeight: Typography.weight.medium,
    marginBottom: Spacing.lg,
  },

  centered:   { alignItems: 'center', paddingVertical: Spacing.xl, gap: Spacing.sm },
  loadingTxt: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  errorTxt:   { fontSize: Typography.size.sm, color: Colors.danger, textAlign: 'center' },

  statusCard: {
    borderRadius: Radius.lg,
    padding: Spacing.lg,
    alignItems: 'center',
    marginBottom: Spacing.md,
    ...Shadows.card,
  },
  statusIconWrap: { marginBottom: Spacing.sm },
  statusBadge: {
    borderWidth: 1.5,
    borderRadius: 20,
    paddingHorizontal: Spacing.md,
    paddingVertical: 4,
    marginBottom: Spacing.sm,
  },
  statusBadgeText: { fontSize: Typography.size.sm, fontWeight: Typography.weight.bold },
  statusMessage: {
    fontSize: Typography.size.sm,
    color: Colors.textSecondary,
    textAlign: 'center',
    lineHeight: 20,
  },

  notesBox: {
    marginTop: Spacing.md,
    backgroundColor: '#FEE2E2',
    borderRadius: Radius.md,
    padding: Spacing.sm,
    width: '100%',
  },
  notesLabel: { fontSize: Typography.size.xs, fontWeight: Typography.weight.bold, color: Colors.danger, marginBottom: 2 },
  notesText:  { fontSize: Typography.size.sm, color: Colors.danger },

  infoCard: {
    backgroundColor: Colors.white,
    borderRadius: Radius.lg,
    padding: Spacing.md,
    marginBottom: Spacing.md,
    gap: Spacing.sm,
    ...Shadows.card,
  },
  infoRow:  { flexDirection: 'row', alignItems: 'flex-start', gap: Spacing.sm },
  infoIcon: { marginTop: 2 },
  infoText: { flex: 1 },
  infoLabel: {
    fontSize: Typography.size.xs,
    color: Colors.textMuted,
    fontWeight: Typography.weight.medium,
    textTransform: 'uppercase',
    letterSpacing: 0.4,
    marginBottom: 1,
  },
  infoValue: { fontSize: Typography.size.sm, color: Colors.textPrimary, fontWeight: Typography.weight.medium },

  actions:    { gap: Spacing.sm },
  refreshBtn: { },
  homeBtn:    { },
  retryBtn:   { },
});
