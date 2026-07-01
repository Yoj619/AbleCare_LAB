import React from 'react';
import { View, Text, ScrollView, StyleSheet, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const VITALS: { icon: keyof typeof Ionicons.glyphMap; label: string; value: string; unit: string; status: string; statusColor: string }[] = [
  { icon: 'heart-outline', label: 'Blood Pressure', value: '128/82', unit: 'mmHg', status: 'Normal', statusColor: Colors.success },
  { icon: 'pulse-outline', label: 'Heart Rate', value: '74', unit: 'bpm', status: 'Normal', statusColor: Colors.success },
  { icon: 'thermometer-outline', label: 'Temperature', value: '36.7', unit: '°C', status: 'Normal', statusColor: Colors.success },
  { icon: 'cloud-outline', label: 'Oxygen Saturation', value: '98', unit: '%', status: 'Normal', statusColor: Colors.success },
  { icon: 'scale-outline', label: 'Weight', value: '69', unit: 'kg', status: 'Stable', statusColor: Colors.primary },
];

export default function VitalSignsScreen() {
  const navigation = useNavigation<Nav>();

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>Vital Signs</Text>
          <Text style={styles.bannerSub}>Juan dela Cruz • Last recorded: Apr 20, 2026</Text>
        </Card>
        {VITALS.map(v => (
          <Card key={v.label} style={styles.vitalCard}>
            <View style={styles.vitalRow}>
              <Ionicons name={v.icon} size={28} color={Colors.primary} />
              <View style={styles.vitalInfo}>
                <Text style={styles.vitalLabel}>{v.label}</Text>
                <Text style={styles.vitalValue}>{v.value} <Text style={styles.vitalUnit}>{v.unit}</Text></Text>
              </View>
              <View style={[styles.statusBadge, { backgroundColor: v.statusColor + '20' }]}>
                <Text style={[styles.statusText, { color: v.statusColor }]}>{v.status}</Text>
              </View>
            </View>
          </Card>
        ))}
        <Card>
          <Text style={styles.noteTitle}>Last Checkup Notes</Text>
          <Text style={styles.noteText}>Patient is alert and responsive. Heart and lung sounds are normal. No signs of acute illness observed. Mild joint stiffness noted due to age. Overall condition is satisfactory.</Text>
        </Card>
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
  vitalCard: {},
  vitalRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.md },
  vitalIcon: { fontSize: 28 },
  vitalInfo: { flex: 1 },
  vitalLabel: { fontSize: Typography.size.xs, color: Colors.textSecondary, marginBottom: 2 },
  vitalValue: { fontSize: Typography.size.lg, fontWeight: Typography.weight.bold, color: Colors.dark },
  vitalUnit: { fontSize: Typography.size.sm, fontWeight: Typography.weight.regular, color: Colors.textMuted },
  statusBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: Radius.full },
  statusText: { fontSize: Typography.size.xs, fontWeight: Typography.weight.semiBold },
  noteTitle: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark, marginBottom: Spacing.sm },
  noteText: { fontSize: Typography.size.sm, color: Colors.textSecondary, lineHeight: 20 },
});
