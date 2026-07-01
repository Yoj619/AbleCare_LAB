import React, { useEffect, useState, useCallback } from 'react';
import {
  View, Text, ScrollView, TouchableOpacity, StyleSheet, StatusBar,
  RefreshControl, ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import * as Location from 'expo-location';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import AppButton from '../../components/AppButton';
import { useActivePatient } from '../../hooks/useActivePatient';
import { getRecommendations, type ClinicRecommendation } from '../../services/clinics';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const NASUGBU_LAT = 14.0667;
const NASUGBU_LNG = 120.6333;

export default function RecommendedClinicsScreen() {
  const navigation = useNavigation<Nav>();
  const { patient, refresh: patientRefresh } = useActivePatient();
  const [clinics, setClinics] = useState<ClinicRecommendation[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [usingFallbackLocation, setUsingFallbackLocation] = useState(false);
  const [refreshKey, setRefreshKey] = useState(0);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    if (!patient) { setLoading(false); return; }
    let isMounted = true;
    setLoading(true);

    (async () => {
      // Re-fetch GPS on every load (no OS dialog — permission was asked at login)
      let lat = NASUGBU_LAT;
      let lng = NASUGBU_LNG;
      let usedFallback = false;

      try {
        const { status } = await Location.getForegroundPermissionsAsync();
        if (status === 'granted') {
          const position = await Location.getCurrentPositionAsync({});
          lat = position.coords.latitude;
          lng = position.coords.longitude;
        } else {
          usedFallback = true;
        }
      } catch {
        usedFallback = true;
      }

      if (!isMounted) return;
      setUsingFallbackLocation(usedFallback);

      const result = await getRecommendations(patient.id, lat, lng);
      if (!isMounted) return;

      if (result.ok) { setClinics(result.data); setError(null); }
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

  const patientCondition = patient?.specificCondition ?? patient?.disabilityCategory ?? null;

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => navigation.navigate('Notifications')} />
      <ScrollView
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />}
      >
        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>Smart Clinic Recommendation</Text>
          <Text style={styles.bannerSub}>Matched to your patient's disabilities & therapy training</Text>
          {usingFallbackLocation && !loading && (
            <View style={styles.locationNote}>
              <Ionicons name="location-outline" size={13} color={Colors.textMuted} />
              <Text style={styles.locationNoteText}>Showing results near Nasugbu, Batangas</Text>
            </View>
          )}
        </Card>

        {loading ? (
          <View style={styles.centered}>
            <ActivityIndicator size="large" color={Colors.primary} />
            <Text style={styles.loadingText}>Finding clinics near you...</Text>
          </View>
        ) : error ? (
          <View style={styles.centered}>
            <Text style={styles.emptyText}>{error}</Text>
            <AppButton label="Try Again" variant="outline" onPress={handleRetry} style={styles.retryBtn} />
          </View>
        ) : clinics.length === 0 ? (
          <View style={styles.centered}>
            {patientCondition ? (
              <>
                <Ionicons name="search-outline" size={40} color={Colors.textMuted} />
                <Text style={styles.emptyText}>
                  {'No clinics found matching\n'}
                  <Text style={styles.conditionHighlight}>{patientCondition}</Text>.
                </Text>
                <Text style={styles.emptyHint}>
                  No approved healthcare providers are available yet, or none match this patient's condition.
                  Check back after providers have been verified by the admin.
                </Text>
              </>
            ) : (
              <>
                <Ionicons name="medical-outline" size={40} color={Colors.textMuted} />
                <Text style={styles.emptyText}>No approved healthcare providers found.</Text>
                <Text style={styles.emptyHint}>
                  Check back after providers have been verified by the admin.
                </Text>
              </>
            )}
          </View>
        ) : (
          clinics.map((c, index) => {
            const isBestMatch = index === 0;
            const addressLine = c.address ?? (c.barangay ? `Brgy. ${c.barangay}` : null);
            return (
              <TouchableOpacity
                key={c.providerId}
                onPress={() => c.clinicId !== null && navigation.navigate('ClinicDetail', {
                  clinicId: String(c.clinicId),
                  clinicName: c.clinicName ?? c.providerName,
                })}
                activeOpacity={0.8}
                disabled={c.clinicId === null}
              >
                <Card style={[styles.clinicCard, isBestMatch ? styles.clinicCardBest : undefined]}>
                  <View style={styles.clinicHeader}>
                    <View style={styles.clinicTitleRow}>
                      <Text style={styles.clinicName}>{c.clinicName ?? c.providerName}</Text>
                      {isBestMatch && (
                        <View style={styles.bestBadge}>
                          <Text style={styles.bestText}>Best Match</Text>
                        </View>
                      )}
                    </View>
                    <View style={styles.scoreRow}>
                      <Ionicons name="star" size={13} color={Colors.orange} />
                      <Text style={styles.scoreText}>{Math.round(c.score)}%</Text>
                    </View>
                  </View>

                  <Text style={styles.providerName}>{c.providerName}</Text>

                  {c.distanceKm !== null && (
                    <View style={styles.infoRow}>
                      <Ionicons name="navigate-outline" size={13} color={Colors.textSecondary} style={styles.infoIcon} />
                      <Text style={styles.infoText}>{c.distanceKm.toFixed(1)} km away</Text>
                    </View>
                  )}
                  {addressLine != null && (
                    <View style={styles.infoRow}>
                      <Ionicons name="location-outline" size={13} color={Colors.textSecondary} style={styles.infoIcon} />
                      <Text style={styles.infoText}>{addressLine}</Text>
                    </View>
                  )}
                  {c.operatingHours && (
                    <View style={styles.infoRow}>
                      <Ionicons name="time-outline" size={13} color={Colors.textSecondary} style={styles.infoIcon} />
                      <Text style={styles.infoText}>{c.operatingHours}</Text>
                    </View>
                  )}

                  <View style={styles.badgeRow}>
                    {c.acceptsWalkIns && (
                      <View style={styles.featureBadge}>
                        <Text style={styles.featureBadgeText}>✅ Walk-ins</Text>
                      </View>
                    )}
                    {c.wheelchairAccessible && (
                      <View style={styles.featureBadge}>
                        <Text style={styles.featureBadgeText}>♿ Wheelchair</Text>
                      </View>
                    )}
                    {c.groundFloorAccess && (
                      <View style={styles.featureBadge}>
                        <Text style={styles.featureBadgeText}>🏠 Ground Floor</Text>
                      </View>
                    )}
                  </View>

                  {isBestMatch && (
                    <View style={styles.matchNoteRow}>
                      <Ionicons name="checkmark-circle" size={13} color={Colors.primary} />
                      <Text style={styles.matchNote}>Best match for your patient's condition</Text>
                    </View>
                  )}
                </Card>
              </TouchableOpacity>
            );
          })
        )}

        <AppButton label="Request Appointment" onPress={() => {}} />
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  content: { padding: Spacing.md, gap: Spacing.md, paddingBottom: Spacing.xl },
  banner: {},
  bannerTitle: { fontSize: Typography.size.lg, fontWeight: Typography.weight.bold, color: Colors.dark },
  bannerSub: { fontSize: Typography.size.sm, color: Colors.textSecondary, marginTop: 2 },
  locationNote: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: Spacing.sm },
  locationNoteText: { fontSize: Typography.size.xs, color: Colors.textMuted, fontStyle: 'italic' },
  centered: { alignItems: 'center', paddingVertical: Spacing.xl, gap: Spacing.md },
  loadingText: { fontSize: Typography.size.sm, color: Colors.textSecondary, marginTop: Spacing.sm },
  emptyText: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark, textAlign: 'center', lineHeight: 22 },
  conditionHighlight: { color: Colors.primary },
  emptyHint: { fontSize: Typography.size.xs, color: Colors.textMuted, textAlign: 'center', lineHeight: 18, maxWidth: 280 },
  retryBtn: { marginTop: 0 },
  clinicCard: { borderWidth: 1, borderColor: Colors.border },
  clinicCardBest: { borderColor: Colors.primary, borderWidth: 1.5 },
  clinicHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 4 },
  clinicTitleRow: { flex: 1, flexDirection: 'row', flexWrap: 'wrap', alignItems: 'center', gap: Spacing.xs },
  clinicName: { fontSize: Typography.size.md, fontWeight: Typography.weight.bold, color: Colors.dark },
  bestBadge: { backgroundColor: Colors.primary, paddingHorizontal: 8, paddingVertical: 2, borderRadius: Radius.full },
  bestText: { color: Colors.white, fontSize: Typography.size.xs, fontWeight: Typography.weight.semiBold },
  scoreRow: { flexDirection: 'row', alignItems: 'center', gap: 2 },
  scoreText: { fontSize: Typography.size.sm, fontWeight: Typography.weight.semiBold, color: Colors.dark },
  providerName: { fontSize: Typography.size.xs, color: Colors.primary, fontWeight: Typography.weight.medium, marginBottom: Spacing.sm },
  infoRow: { flexDirection: 'row', alignItems: 'flex-start', gap: 4, marginBottom: 3 },
  infoIcon: { width: 16, marginTop: 1 },
  infoText: { flex: 1, fontSize: Typography.size.xs, color: Colors.textSecondary },
  badgeRow: { flexDirection: 'row', flexWrap: 'wrap', gap: Spacing.xs, marginTop: Spacing.sm },
  featureBadge: { backgroundColor: Colors.primaryLight, borderRadius: Radius.full, paddingHorizontal: 10, paddingVertical: 3 },
  featureBadgeText: { fontSize: Typography.size.xs, color: Colors.primary },
  matchNoteRow: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: Spacing.sm },
  matchNote: { fontSize: Typography.size.xs, color: Colors.primary, fontWeight: Typography.weight.medium },
});
