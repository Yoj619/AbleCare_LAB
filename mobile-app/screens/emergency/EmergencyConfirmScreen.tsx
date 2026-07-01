import React, { useState } from 'react';
import { View, Text, StyleSheet, Linking } from 'react-native';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import Logo from '../../components/Logo';
import AppButton from '../../components/AppButton';
import { Colors, Radius, Spacing, Typography } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'EmergencyConfirm'>;

const EMERGENCY_NUMBER = '+639972610109';

// This screen is preserved for deep-link compatibility but the primary emergency
// flow now runs entirely within EmergencyAlertScreen.
export default function EmergencyConfirmScreen({ navigation }: Props) {
  const [sentAt, setSentAt] = useState<string | null>(null);

  function handleSendAlert(): void {
    const now = new Date();
    setSentAt(
      now.toLocaleTimeString([], {
        hour:   '2-digit',
        minute: '2-digit',
        second: '2-digit',
      })
    );
    void Linking.openURL(`tel:${EMERGENCY_NUMBER}`);
  }

  // ── Confirmation state ────────────────────────────────────────────────────
  if (sentAt !== null) {
    return (
      <View style={styles.overlay}>
        <View style={styles.card}>
          <Logo size="sm" />
          <Text style={styles.title}>Emergency Alert Sent</Text>
          <Text style={styles.subtitle}>Help is on the way.</Text>
          <Text style={styles.timeText}>Triggered at {sentAt}</Text>
          <AppButton
            icon="call"
            label="Call MMDRMO"
            variant="danger"
            onPress={() => void Linking.openURL(`tel:${EMERGENCY_NUMBER}`)}
            style={styles.primaryBtn}
          />
          <View style={styles.gap} />
          <AppButton
            icon="arrow-back"
            label="Back to Home"
            variant="dangerOutline"
            onPress={() => navigation.reset({ index: 0, routes: [{ name: 'Main' }] })}
          />
        </View>
      </View>
    );
  }

  // ── Confirm prompt ────────────────────────────────────────────────────────
  return (
    <View style={styles.overlay}>
      <View style={styles.card}>
        <Logo size="sm" />
        <Text style={styles.title}>Emergency Call?</Text>
        <Text style={styles.subtitle}>Emergency responders will be notified immediately.</Text>
        <AppButton
          icon="checkmark"
          label="Yes, Call MMDRMO"
          variant="danger"
          onPress={handleSendAlert}
          style={styles.primaryBtn}
        />
        <View style={styles.gap} />
        <AppButton
          icon="close"
          label="Cancel"
          variant="dangerOutline"
          onPress={() => navigation.goBack()}
        />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: Colors.overlayBg,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.xl,
  },
  card: {
    backgroundColor: Colors.white,
    borderRadius: Radius.xl,
    borderWidth: 1.5,
    borderColor: Colors.danger,
    padding: Spacing.xl,
    width: '100%',
    alignItems: 'center',
  },
  title: {
    fontSize: Typography.size.xl,
    fontWeight: Typography.weight.bold,
    color: Colors.dark,
    marginTop: Spacing.md,
    marginBottom: Spacing.xs,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: Typography.size.sm,
    color: Colors.textSecondary,
    marginBottom: Spacing.lg,
    textAlign: 'center',
  },
  timeText: {
    fontSize: Typography.size.sm,
    color: Colors.danger,
    fontWeight: Typography.weight.semiBold,
    marginBottom: Spacing.lg,
    textAlign: 'center',
  },
  primaryBtn: { width: '100%' },
  gap: { height: Spacing.sm },
});
