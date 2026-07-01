import React from 'react';
import { View, Text, ScrollView, StyleSheet, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const HISTORY = [
  { id: 'h1', month: 'April 2026', records: ['General Health Checkup – Apr 20', 'Blood Test Results – Apr 15'] },
  { id: 'h2', month: 'March 2026', records: ['Medication Prescription – Mar 15', 'Physical Therapy Progress Report – Mar 5'] },
  { id: 'h3', month: 'January 2025', records: ['Medical Diagnosis Report – Jan 10'] },
];

export default function HealthRecordHistoryScreen() {
  const navigation = useNavigation<Nav>();

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>Record History</Text>
          <Text style={styles.bannerSub}>Juan dela Cruz • All health records by date</Text>
        </Card>
        {HISTORY.map(group => (
          <View key={group.id}>
            <Text style={styles.monthLabel}>{group.month}</Text>
            <Card>
              {group.records.map((r, i) => (
                <View key={i} style={[styles.histRow, i > 0 && styles.divider]}>
                  <Text style={styles.histDot}>•</Text>
                  <Text style={styles.histText}>{r}</Text>
                </View>
              ))}
            </Card>
          </View>
        ))}
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
  monthLabel: { fontSize: Typography.size.sm, fontWeight: Typography.weight.semiBold, color: Colors.textSecondary, marginBottom: Spacing.xs },
  histRow: { flexDirection: 'row', alignItems: 'center', paddingVertical: Spacing.sm, gap: Spacing.sm },
  divider: { borderTopWidth: 1, borderTopColor: Colors.border },
  histDot: { color: Colors.primary, fontSize: 18 },
  histText: { fontSize: Typography.size.sm, color: Colors.textPrimary },
});
