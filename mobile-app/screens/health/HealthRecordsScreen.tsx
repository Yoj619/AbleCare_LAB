import React, { useEffect, useState, useCallback } from 'react';
import { View, Text, TouchableOpacity, ScrollView, StyleSheet, StatusBar, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import AppButton from '../../components/AppButton';
import { useActivePatient } from '../../hooks/useActivePatient';
import { listRecords, type HealthRecord, type HealthRecordType } from '../../services/healthRecords';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

type RecordIcon =
  | { family: 'ionicons'; name: keyof typeof Ionicons.glyphMap }
  | { family: 'mci'; name: keyof typeof MaterialCommunityIcons.glyphMap };

const RECORD_TYPE_DISPLAY: Record<HealthRecordType, { title: string; icon: RecordIcon; iconBg: string }> = {
  vitals:      { title: 'Vital Signs',     icon: { family: 'ionicons', name: 'flash-outline' },    iconBg: '#E8F5E9' },
  symptom_log: { title: 'Symptom Log',     icon: { family: 'mci', name: 'needle' },                 iconBg: '#E3F2FD' },
  medication:  { title: 'Medication',      icon: { family: 'mci', name: 'pill' },                   iconBg: '#FFF3E0' },
  general:     { title: 'General Record',  icon: { family: 'ionicons', name: 'clipboard-outline' }, iconBg: '#FFEBEE' },
};

function formatRecordDate(recordedAt: string): string {
  const date = new Date(recordedAt.replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return recordedAt;
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

export default function HealthRecordsScreen() {
  const navigation = useNavigation<Nav>();
  const { patient, refresh: patientRefresh } = useActivePatient();
  const [records, setRecords] = useState<HealthRecord[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [refreshKey, setRefreshKey] = useState(0);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    if (!patient) { setLoading(false); return; }
    let isMounted = true;
    setLoading(true);
    (async () => {
      const result = await listRecords(patient.id);
      if (!isMounted) return;
      if (result.ok) { setRecords(result.data); setError(null); }
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

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader
        onHamburgerPress={() => {}}
        onBellPress={() => navigation.navigate('Notifications')}
        showBack
        onBackPress={() => navigation.goBack()}
      />
      <ScrollView
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />}
      >
        {/* Banner */}
        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>Health Records</Text>
          <Text style={styles.bannerSub}>
            {patient ? `${patient.firstName} ${patient.lastName}` : 'No patient'} • Medical Document Library
          </Text>
        </Card>

        {/* Records */}
        {loading ? (
          <Text style={styles.bannerSub}>Loading records...</Text>
        ) : error ? (
          <>
            <Text style={styles.bannerSub}>{error}</Text>
            <AppButton label="Retry" variant="outline" onPress={handleRetry} />
          </>
        ) : records.length === 0 ? (
          <Text style={styles.bannerSub}>No health records yet.</Text>
        ) : (
          records.map(r => {
            const display = RECORD_TYPE_DISPLAY[r.recordType];
            return (
              <Card key={r.id} style={styles.recordCard}>
                <View style={styles.recordHeader}>
                  <View style={[styles.iconWrap, { backgroundColor: display.iconBg }]}>
                    {display.icon.family === 'ionicons' ? (
                      <Ionicons name={display.icon.name} size={18} color={Colors.dark} />
                    ) : (
                      <MaterialCommunityIcons name={display.icon.name} size={18} color={Colors.dark} />
                    )}
                  </View>
                  <View style={styles.recordMeta}>
                    <Text style={styles.recordDate}>{formatRecordDate(r.recordedAt)}</Text>
                  </View>
                </View>
                <Text style={styles.recordTitle}>{display.title}</Text>
                <Text style={styles.recordDesc}>{r.notes ?? 'No additional notes.'}</Text>
                <TouchableOpacity
                  style={styles.viewLinkRow}
                  onPress={() => navigation.navigate('HealthRecordDetail', { recordId: String(r.id), title: display.title })}
                >
                  <Text style={styles.viewLink}>View Document</Text>
                  <Ionicons name="arrow-forward" size={14} color={Colors.primary} />
                </TouchableOpacity>
              </Card>
            );
          })
        )}

        <View style={styles.actions}>
          <AppButton label="+ Add Record" onPress={() => navigation.navigate('AddHealthRecord')} />
          <View style={{ height: Spacing.sm }} />
          <AppButton label="View History" variant="outline" onPress={() => navigation.navigate('HealthRecordHistory')} />
        </View>
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
  recordCard: {},
  recordHeader: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', marginBottom: Spacing.sm },
  iconWrap: { width: 40, height: 40, borderRadius: 20, alignItems: 'center', justifyContent: 'center' },
  recordIcon: { fontSize: 18 },
  recordMeta: { flexDirection: 'row', alignItems: 'center', gap: Spacing.sm },
  recordDate: { fontSize: Typography.size.xs, color: Colors.textMuted },
  badge: { paddingHorizontal: 8, paddingVertical: 2, borderRadius: Radius.full },
  badgeText: { fontSize: Typography.size.xs, fontWeight: Typography.weight.semiBold },
  recordTitle: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark, marginBottom: 4 },
  recordDesc: { fontSize: Typography.size.sm, color: Colors.textSecondary, lineHeight: 20, marginBottom: Spacing.sm },
  viewLinkRow: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  viewLink: { fontSize: Typography.size.sm, color: Colors.primary, fontWeight: Typography.weight.medium },
  actions: { marginTop: Spacing.sm },
});
