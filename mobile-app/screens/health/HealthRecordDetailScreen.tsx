import React from 'react';
import { View, Text, ScrollView, TouchableOpacity, StyleSheet, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import { Colors, Spacing, Typography } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'HealthRecordDetail'>;

const RECORD_CONTENTS: Record<string, string> = {
  r1: `General Health Checkup\nApril 20, 2026 • 8:30 AM\n\nPatient Information\nName: Juan dela Cruz\nAge/Sex: 72 / Male\nDate of Checkup: April 20, 2026\nCheckup ID: HC-2026-001\n\nVital Signs\n• Blood Pressure: 128/82 mmHg\n• Heart Rate: 74 bpm\n• Respiratory Rate: 18 breaths/min\n• Temperature: 36.7°C\n• Oxygen Saturation: 98%\n• Weight: 69 kg\n• Height: 168 cm\n\nGeneral Findings\n• Patient is alert and responsive\n• Heart and lung sounds are normal\n• No signs of acute illness observed\n• Mild joint stiffness noted due to age\n• Overall condition is satisfactory\n\nRecommendations\n• Maintain balanced and nutritious diet\n• Continue light daily exercise or walking\n• Stay hydrated and get adequate rest\n• Return for routine annual checkup\n\nPhysician Authorization\nAttending Physician:\nDr. Maria Santos (Demo Only)\nLicense No.: 123456 (Sample)\nSignature: _________________\nDate: May 9, 2026`,
  r2: `Blood Test Results\nApril 15, 2026\n\nComplete blood panel and cholesterol screening results.\n\nName: Juan dela Cruz\nAge/Sex: 72 / Male\nDate of Test: April 15, 2026\nLaboratory ID: LAB-2026-014\n\nComplete Blood Count (CBC)\nHemoglobin: 13.8 g/dL\nReference Range: 13.0 – 17.0 g/dL\nHematocrit: 41%\nReference Range: 40 – 50%\nPlatelets: 198,000 x10³/μL\nReference Range: 150 – 450 x10³/μL\n\nCholesterol Screening\nTotal Cholesterol: 185 mg/dL\nReference Range: Below 200 mg/dL\nTriglycerides: 140 mg/dL\nReference Range: Below 150 mg/dL\n\nInterpretation\nBlood count and cholesterol screening results are within normal limits. No significant abnormalities were detected during laboratory testing.\n\nLaboratory Authorization\nMedical Technologist:\nMaria Santos, RMT (Demo Only)\nPathologist:\nDr. Roberto Cruz (Sample Only)\nDate Released: May 9, 2026`,
  r3: `Medication Prescription\nMarch 15, 2026\n\nPrescription for daily medications – Aspirin and Losartan.\n\nPatient Information\nName: Juan dela Cruz\nAge/Sex: 72 / Male\nDate Issued: March 10, 2026\nPrescription ID: RX-2026-001\n\nMedications\n1. Aspirin 80 mg (tablet)\n  • Dosage: 1 Tablet\n  • Frequency: Once daily\n  • Route: Oral\n  • Timing: After meals (Morning preferred)\n  • Duration: Continuous use as advised\n  • Purpose: Reduces risk of heart attack and stroke\n\n2. Losartan 50 mg (tablet)\n  • Dosage: 1 Tablet\n  • Frequency: Once daily\n  • Route: Oral\n  • Timing: Same time each day\n  • Duration: Continuous use as prescribed\n  • Purpose: Controls and maintains normal blood pressure\n\nInstructions & Advice\n• Take medications exactly as prescribed by your doctor\n• Do not skip doses or discontinue without medical advice\n• Monitor blood pressure daily and keep a record\n• Maintain a low-sodium, heart-healthy diet\n• Engage in regular physical activity if able\n• Avoid smoking and limit alcohol consumption\n• Schedule follow-up consultation after 1–4 weeks\n\nPhysician Authorization\nDr. Maria Santos (Demo Only)\nLicense No.: 123456 (Sample)\nPrescribing Physician\nDate: March 15, 2026`,
};

export default function HealthRecordDetailScreen({ navigation, route }: Props) {
  const { recordId, title } = route.params;
  const content = RECORD_CONTENTS[recordId] ?? `${title}\n\nDocument content not available.`;

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader
        onHamburgerPress={() => {}}
        onBellPress={() => navigation.navigate('Notifications')}
        showBack
        onBackPress={() => navigation.goBack()}
      />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>Health Records</Text>
          <Text style={styles.bannerSub}>Juan dela Cruz • Medical Document Library</Text>
        </Card>

        <Card style={styles.docCard}>
          <Text style={styles.docText}>{content}</Text>
          <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
            <Text style={styles.backLink}>Back</Text>
            <Ionicons name="arrow-forward" size={14} color={Colors.primary} />
          </TouchableOpacity>
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
  docCard: {},
  docText: { fontSize: Typography.size.sm, color: Colors.textPrimary, lineHeight: 22 },
  backBtn: { flexDirection: 'row', alignItems: 'center', gap: 4, marginTop: Spacing.lg, alignSelf: 'flex-end' },
  backLink: { fontSize: Typography.size.sm, color: Colors.primary, fontWeight: Typography.weight.medium },
});
