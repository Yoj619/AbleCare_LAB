import React from 'react';
import { View, Text, ScrollView, TouchableOpacity, StyleSheet, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import AppButton from '../../components/AppButton';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'ClinicDetail'>;

export default function ClinicDetailScreen({ navigation, route }: Props) {
  const { clinicName } = route.params;

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Card style={styles.headerCard}>
          <View style={styles.clinicHeader}>
            <Text style={styles.clinicName}>{clinicName}</Text>
            <View style={styles.bestBadge}><Text style={styles.bestText}>Best Match</Text></View>
          </View>
          <View style={styles.ratingRow}>
            <View style={styles.starRow}>
              {[0, 1, 2, 3, 4].map(i => (
                <Ionicons key={i} name="star" size={14} color={Colors.orange} />
              ))}
            </View>
            <Text style={styles.rating}>4.8</Text>
          </View>
          <View style={styles.typeBadge}><Text style={styles.typeText}>Private Clinic</Text></View>
        </Card>

        <Card>
          <Text style={styles.sectionTitle}>Contact & Location</Text>
          <InfoRow icon="location-outline" label="Address" value="Brgy. Poblacion 1, Nasugbu, Batangas" />
          <InfoRow icon="call-outline" label="Phone" value="+63 917 234 5678" />
          <InfoRow icon="time-outline" label="Hours" value="Mon–Sat, 8:00 AM – 5:00 PM" />
        </Card>

        <Card>
          <Text style={styles.sectionTitle}>Services & Features</Text>
          {['Automated Appointment Reminders', 'Caregiver-Provider Messaging', 'Therapy Monitoring', 'Digital Health Records', 'Emergency Alert Integration'].map(f => (
            <View key={f} style={styles.featureRow}>
              <Ionicons name="checkmark" size={16} color={Colors.success} style={styles.featureCheck} />
              <Text style={styles.featureText}>{f}</Text>
            </View>
          ))}
        </Card>

        <Card>
          <Text style={styles.sectionTitle}>Why This Clinic?</Text>
          <Text style={styles.bodyText}>
            St. Mary Medical Center specializes in post-stroke rehabilitation and elderly care. Their team has experience managing hypertension alongside stroke recovery, making them an ideal match for Juan dela Cruz's conditions.
          </Text>
        </Card>

        <AppButton label="Request Appointment" onPress={() => {}} />
      </ScrollView>
    </SafeAreaView>
  );
}

function InfoRow({ icon, label, value }: { icon: keyof typeof Ionicons.glyphMap; label: string; value: string }) {
  return (
    <View style={infoStyles.row}>
      <Ionicons name={icon} size={16} color={Colors.textSecondary} style={infoStyles.icon} />
      <Text style={infoStyles.label}>{label}</Text>
      <Text style={infoStyles.value}>{value}</Text>
    </View>
  );
}

const infoStyles = StyleSheet.create({
  row: { flexDirection: 'row', paddingVertical: Spacing.sm, gap: Spacing.sm },
  icon: { width: 20 },
  label: { width: 68, fontSize: Typography.size.sm, color: Colors.textSecondary },
  value: { flex: 1, fontSize: Typography.size.sm, color: Colors.textPrimary },
});

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  content: { padding: Spacing.md, gap: Spacing.md, paddingBottom: Spacing.xl },
  headerCard: {},
  clinicHeader: { flexDirection: 'row', alignItems: 'center', flexWrap: 'wrap', gap: Spacing.xs, marginBottom: Spacing.xs },
  clinicName: { fontSize: Typography.size.lg, fontWeight: Typography.weight.bold, color: Colors.dark, flex: 1 },
  bestBadge: { backgroundColor: Colors.primary, paddingHorizontal: 10, paddingVertical: 3, borderRadius: Radius.full },
  bestText: { color: Colors.white, fontSize: Typography.size.xs, fontWeight: Typography.weight.semiBold },
  ratingRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.xs, marginBottom: Spacing.xs },
  starRow: { flexDirection: 'row', gap: 2 },
  rating: { fontSize: Typography.size.md, fontWeight: Typography.weight.bold, color: Colors.dark },
  typeBadge: { alignSelf: 'flex-start', backgroundColor: Colors.primaryLight, paddingHorizontal: 10, paddingVertical: 3, borderRadius: Radius.full },
  typeText: { color: Colors.primary, fontSize: Typography.size.xs, fontWeight: Typography.weight.medium },
  sectionTitle: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark, marginBottom: Spacing.sm },
  featureRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.sm, paddingVertical: 4 },
  featureCheck: { color: Colors.success, fontSize: 16, width: 20 },
  featureText: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  bodyText: { fontSize: Typography.size.sm, color: Colors.textSecondary, lineHeight: 20 },
});
