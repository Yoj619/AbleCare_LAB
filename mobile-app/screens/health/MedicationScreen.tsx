import React from 'react';
import { View, Text, ScrollView, StyleSheet, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { MaterialCommunityIcons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const MEDS: { id: string; icon: keyof typeof MaterialCommunityIcons.glyphMap; name: string; freq: string; timing: string; timingColor: string }[] = [
  { id: 'm1', icon: 'pill', name: 'Aspirin 100mg', freq: 'Once daily', timing: 'Morning', timingColor: Colors.orange },
  { id: 'm2', icon: 'pill', name: 'Losartan 50mg', freq: 'Once daily', timing: 'Evening', timingColor: Colors.primary },
];

export default function MedicationScreen() {
  const navigation = useNavigation<Nav>();

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>Current Medications</Text>
          <Text style={styles.bannerSub}>Juan dela Cruz • Active prescriptions</Text>
        </Card>
        {MEDS.map(m => (
          <Card key={m.id} style={styles.medCard}>
            <View style={styles.medRow}>
              <View style={styles.medIconWrap}>
                <MaterialCommunityIcons name={m.icon} size={22} color={Colors.orange} />
              </View>
              <View style={styles.medInfo}>
                <Text style={styles.medName}>{m.name}</Text>
                <Text style={styles.medFreq}>{m.freq}</Text>
              </View>
              <View style={[styles.timingBadge, { backgroundColor: m.timingColor + '25' }]}>
                <Text style={[styles.timingText, { color: m.timingColor }]}>{m.timing}</Text>
              </View>
            </View>
          </Card>
        ))}
        <Card>
          <Text style={styles.noteTitle}>Prescription Notes</Text>
          <Text style={styles.noteText}>
            Take medications exactly as prescribed by your doctor. Do not skip doses or discontinue without medical advice. Monitor blood pressure daily and keep a record. Maintain a low-sodium, heart-healthy diet.
          </Text>
          <Text style={styles.prescriber}>{'\n'}Prescribing Physician{'\n'}Dr. Maria Santos (Demo Only){'\n'}License No.: 123456 (Sample){'\n'}Date: March 15, 2026</Text>
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
  medCard: {},
  medRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.md },
  medIconWrap: { width: 44, height: 44, borderRadius: 22, backgroundColor: Colors.orangeLight, alignItems: 'center', justifyContent: 'center' },
  medIcon: { fontSize: 22 },
  medInfo: { flex: 1 },
  medName: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark },
  medFreq: { fontSize: Typography.size.xs, color: Colors.textSecondary, marginTop: 2 },
  timingBadge: { paddingHorizontal: 12, paddingVertical: 4, borderRadius: Radius.full },
  timingText: { fontSize: Typography.size.xs, fontWeight: Typography.weight.semiBold },
  noteTitle: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark, marginBottom: Spacing.sm },
  noteText: { fontSize: Typography.size.sm, color: Colors.textSecondary, lineHeight: 20 },
  prescriber: { fontSize: Typography.size.sm, color: Colors.textMuted, lineHeight: 20 },
});
