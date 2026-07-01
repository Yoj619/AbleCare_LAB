import React, { useState } from 'react';
import { View, Text, ScrollView, StyleSheet, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import AppInput from '../../components/AppInput';
import AppButton from '../../components/AppButton';
import { Colors, Spacing, Typography } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

export default function AddTherapySessionScreen() {
  const navigation = useNavigation<Nav>();
  const [form, setForm] = useState({ type: '', provider: '', date: '', time: '', notes: '' });
  const update = (k: keyof typeof form) => (v: string) => setForm(f => ({ ...f, [k]: v }));

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>Add Therapy Session</Text>
          <Text style={styles.bannerSub}>Schedule a new therapy appointment</Text>
        </Card>
        <Card>
          <AppInput label="Session Type" leftIcon="walk-outline" placeholder="e.g., Physical Therapy" value={form.type} onChangeText={update('type')} />
          <AppInput label="Provider / Therapist" leftIcon="person-outline" placeholder="e.g., Dr. Jose Reyes" value={form.provider} onChangeText={update('provider')} />
          <AppInput label="Date" leftIcon="calendar-outline" placeholder="e.g., April 10, 2026" value={form.date} onChangeText={update('date')} />
          <AppInput label="Time" leftIcon="time-outline" placeholder="e.g., 2:00 PM" value={form.time} onChangeText={update('time')} />
          <AppInput label="Notes" leftIcon="create-outline" placeholder="Any special notes or instructions..." value={form.notes} onChangeText={update('notes')} multiline numberOfLines={3} />
          <AppButton label="Save Session" onPress={() => navigation.goBack()} />
          <View style={{ height: Spacing.sm }} />
          <AppButton label="Cancel" variant="outline" onPress={() => navigation.goBack()} />
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
});
