import React, { useState, useEffect } from 'react';
import {
  View, Text, ScrollView, TouchableOpacity, StyleSheet, StatusBar, ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import * as Location from 'expo-location';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import Logo from '../../components/Logo';
import AppButton from '../../components/AppButton';
import { useActivePatient } from '../../hooks/useActivePatient';
import { getRecommendations, type ClinicRecommendation } from '../../services/clinics';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'ClinicRecommendationResult'>;

const NASUGBU_LAT = 14.0667;
const NASUGBU_LNG = 120.6333;

export default function ClinicRecommendationResultScreen({ navigation, route }: Props) {
  const { conditions } = route.params;
  const { patient } = useActivePatient();

  const [clinics, setClinics] = useState<ClinicRecommendation[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [retryKey, setRetryKey] = useState(0);

  useEffect(() => {
    if (!patient) { setLoading(false); return; }
    let isMounted = true;
    setLoading(true);

    (async () => {
      let lat = NASUGBU_LAT;
      let lng = NASUGBU_LNG;

      try {
        const { status } = await Location.getForegroundPermissionsAsync();
        if (status === 'granted') {
          const position = await Location.getCurrentPositionAsync({});
          lat = position.coords.latitude;
          lng = position.coords.longitude;
        }
      } catch {
        // use Nasugbu fallback silently
      }

      const result = await getRecommendations(patient.id, lat, lng);
      if (!isMounted) return;

      if (result.ok) { setClinics(result.data); setError(null); }
      else { setError(result.error); }
      setLoading(false);
    })();

    return () => { isMounted = false; };
  }, [patient, retryKey]);

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>

        {/* Back */}
        <TouchableOpacity style={styles.back} onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={14} color={Colors.textSecondary} />
          <Text style={styles.backTxt}>Back</Text>
        </TouchableOpacity>

        {/* Logo */}
        <View style={styles.logoWrap}>
          <Logo size="sm" />
        </View>

        {/* Heading */}
        <Text style={styles.title}>Smart Clinic Recommendation</Text>
        <Text style={styles.subtitle}>Matched to your patient's disabilities & conditions</Text>
        <Text style={styles.description}>
          All clinics offer fast emergency response, digital records & therapy tracking
        </Text>

        {/* Selected conditions summary */}
        {conditions.length > 0 && (
          <View style={styles.conditionSummary}>
            <Text style={styles.conditionSummaryLabel}>
              Based on {conditions.length} selected condition{conditions.length > 1 ? 's' : ''}:
            </Text>
            <View style={styles.conditionChips}>
              {conditions.slice(0, 3).map(c => (
                <View key={c} style={styles.conditionChip}>
                  <Text style={styles.conditionChipText}>{c}</Text>
                </View>
              ))}
              {conditions.length > 3 && (
                <View style={styles.conditionChip}>
                  <Text style={styles.conditionChipText}>+{conditions.length - 3} more</Text>
                </View>
              )}
            </View>
          </View>
        )}

        {/* States */}
        {loading ? (
          <View style={styles.centered}>
            <ActivityIndicator size="large" color={Colors.primary} />
            <Text style={styles.loadingText}>Finding clinics near you...</Text>
          </View>
        ) : error ? (
          <View style={styles.centered}>
            <Text style={styles.emptyText}>Unable to load recommendations. Try again.</Text>
            <AppButton
              label="Retry"
              variant="outline"
              onPress={() => { setError(null); setRetryKey(k => k + 1); }}
              style={styles.retryBtn}
            />
          </View>
        ) : clinics.length === 0 ? (
          <View style={styles.centered}>
            <Ionicons name="search-outline" size={40} color={Colors.textMuted} />
            <Text style={styles.emptyText}>No recommended clinics found for this patient's condition.</Text>
            <Text style={styles.emptyHint}>
              No approved healthcare providers are available yet, or none match this patient's condition.
              Check back after providers have been verified by the admin.
            </Text>
          </View>
        ) : (
          clinics.map((clinic, index) => {
            const isBestMatch = index === 0;
            const addressLine = clinic.address ?? (clinic.barangay ? `Brgy. ${clinic.barangay}` : null);
            return (
              <View
                key={clinic.providerId}
                style={[styles.clinicCard, isBestMatch && styles.clinicCardBest]}
              >
                {/* Name + Best Match badge */}
                <View style={styles.clinicHeader}>
                  <Text style={styles.clinicName}>{clinic.clinicName ?? clinic.providerName}</Text>
                  {isBestMatch && (
                    <View style={styles.bestBadge}>
                      <Text style={styles.bestText}>Best Match</Text>
                    </View>
                  )}
                </View>

                {/* Score */}
                <View style={styles.ratingRow}>
                  <Ionicons name="star" size={14} color={Colors.success} />
                  <Text style={styles.ratingValue}>Match Score: {Math.round(clinic.score)}%</Text>
                </View>

                {/* Provider name chip */}
                <View style={styles.typeChip}>
                  <Text style={styles.typeText}>{clinic.providerName}</Text>
                </View>

                {/* Distance */}
                {clinic.distanceKm !== null && (
                  <View style={styles.infoRow}>
                    <Ionicons name="navigate-outline" size={13} color={Colors.textSecondary} style={styles.infoIcon} />
                    <Text style={styles.infoText}>{clinic.distanceKm.toFixed(1)} km away</Text>
                  </View>
                )}

                {/* Address */}
                {addressLine != null && (
                  <View style={styles.infoRow}>
                    <Ionicons name="location-outline" size={13} color={Colors.textSecondary} style={styles.infoIcon} />
                    <Text style={styles.infoText}>{addressLine}</Text>
                  </View>
                )}

                {/* Operating hours */}
                {clinic.operatingHours && (
                  <View style={styles.infoRow}>
                    <Ionicons name="time-outline" size={13} color={Colors.textSecondary} style={styles.infoIcon} />
                    <Text style={styles.infoText}>{clinic.operatingHours}</Text>
                  </View>
                )}

                {/* Feature badges */}
                <View style={styles.featureChips}>
                  {clinic.acceptsWalkIns && (
                    <View style={styles.featureChip}>
                      <Text style={styles.featureChipText}>✅ Accepts Walk-ins</Text>
                    </View>
                  )}
                  {clinic.wheelchairAccessible && (
                    <View style={styles.featureChip}>
                      <Text style={styles.featureChipText}>♿ Wheelchair Accessible</Text>
                    </View>
                  )}
                  {clinic.groundFloorAccess && (
                    <View style={styles.featureChip}>
                      <Text style={styles.featureChipText}>🏠 Ground Floor Access</Text>
                    </View>
                  )}
                </View>

                {/* Specialization match */}
                {isBestMatch && (
                  <View style={styles.matchRow}>
                    <Ionicons name="checkmark" size={14} color={Colors.primary} />
                    <Text style={styles.matchNote}>Best match for your patient's condition</Text>
                  </View>
                )}
              </View>
            );
          })
        )}

        {/* Confirm button */}
        {!loading && !error && (
          <AppButton
            label="Confirm"
            onPress={() => navigation.reset({ index: 0, routes: [{ name: 'Main' }] })}
            style={styles.confirmBtn}
          />
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  scroll: { padding: Spacing.lg, paddingBottom: Spacing.xl },

  back: { flexDirection: 'row', alignItems: 'center', gap: 4, marginBottom: Spacing.sm },
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
    marginBottom: Spacing.xs,
  },
  description: {
    fontSize: Typography.size.xs,
    color: Colors.textSecondary,
    textAlign: 'center',
    marginBottom: Spacing.lg,
    lineHeight: 18,
  },

  conditionSummary: {
    backgroundColor: Colors.primaryLight,
    borderRadius: Radius.md,
    padding: Spacing.md,
    marginBottom: Spacing.md,
  },
  conditionSummaryLabel: {
    fontSize: Typography.size.xs,
    color: Colors.primary,
    fontWeight: Typography.weight.semiBold,
    marginBottom: Spacing.xs,
  },
  conditionChips: { flexDirection: 'row', flexWrap: 'wrap', gap: Spacing.xs },
  conditionChip: {
    backgroundColor: Colors.white,
    borderRadius: Radius.full,
    paddingHorizontal: 10,
    paddingVertical: 3,
    borderWidth: 1,
    borderColor: Colors.primary,
  },
  conditionChipText: { fontSize: Typography.size.xs, color: Colors.primary },

  centered: { alignItems: 'center', paddingVertical: Spacing.xl, gap: Spacing.md },
  loadingText: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  emptyText: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark, textAlign: 'center', lineHeight: 22 },
  emptyHint: { fontSize: Typography.size.xs, color: Colors.textMuted, textAlign: 'center', lineHeight: 18, maxWidth: 280 },
  retryBtn: { marginTop: 0 },

  clinicCard: {
    backgroundColor: Colors.white,
    borderRadius: Radius.lg,
    padding: Spacing.md,
    marginBottom: Spacing.md,
    borderWidth: 1,
    borderColor: Colors.border,
    ...Shadows.card,
  },
  clinicCardBest: {
    borderColor: Colors.primary,
    borderWidth: 1.5,
  },

  clinicHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    marginBottom: Spacing.xs,
    gap: Spacing.sm,
  },
  clinicName: {
    flex: 1,
    fontSize: Typography.size.md,
    fontWeight: Typography.weight.bold,
    color: Colors.dark,
  },
  bestBadge: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.full,
    paddingHorizontal: 10,
    paddingVertical: 3,
    flexShrink: 0,
  },
  bestText: { color: Colors.white, fontSize: Typography.size.xs, fontWeight: Typography.weight.semiBold },

  ratingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginBottom: Spacing.sm,
  },
  ratingValue: { fontSize: Typography.size.sm, fontWeight: Typography.weight.bold, color: Colors.dark },

  typeChip: {
    alignSelf: 'flex-start',
    backgroundColor: Colors.primaryLight,
    borderRadius: Radius.full,
    paddingHorizontal: 12,
    paddingVertical: 4,
    marginBottom: Spacing.sm,
  },
  typeText: { fontSize: Typography.size.xs, color: Colors.primary, fontWeight: Typography.weight.medium },

  infoRow: { flexDirection: 'row', alignItems: 'flex-start', gap: Spacing.xs, marginBottom: 4 },
  infoIcon: { width: 18, marginTop: 1 },
  infoText: { flex: 1, fontSize: Typography.size.xs, color: Colors.textSecondary },

  featureChips: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: Spacing.xs,
    marginTop: Spacing.sm,
  },
  featureChip: {
    backgroundColor: Colors.primaryLight,
    borderRadius: Radius.full,
    paddingHorizontal: 10,
    paddingVertical: 4,
  },
  featureChipText: { fontSize: Typography.size.xs, color: Colors.primary, fontWeight: Typography.weight.medium },

  matchRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: Spacing.xs,
    marginTop: Spacing.sm,
    paddingTop: Spacing.sm,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  matchNote: { flex: 1, fontSize: Typography.size.xs, color: Colors.primary, fontWeight: Typography.weight.medium },

  confirmBtn: { marginTop: Spacing.sm },
});
