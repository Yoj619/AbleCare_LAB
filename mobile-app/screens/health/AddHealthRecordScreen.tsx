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

export default function AddHealthRecordScreen() {
  const navigation = useNavigation<Nav>();
  const [form, setForm] = useState({ title: '', date: '', description: '', type: '' });
  const update = (k: keyof typeof form) => (v: string) => setForm(f => ({ ...f, [k]: v }));

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>Add Health Record</Text>
          <Text style={styles.bannerSub}>Juan dela Cruz • Medical Document Library</Text>
        </Card>
        <Card>
          <AppInput label="Record Title" leftIcon="clipboard-outline" placeholder="e.g., Blood Test Results" value={form.title} onChangeText={update('title')} />
          <AppInput label="Date" leftIcon="calendar-outline" placeholder="e.g., Apr 20, 2026" value={form.date} onChangeText={update('date')} />
          <AppInput label="Record Type" leftIcon="pricetag-outline" placeholder="e.g., Lab Result, Prescription" value={form.type} onChangeText={update('type')} />
          <AppInput label="Description" leftIcon="create-outline" placeholder="Brief description of this record" value={form.description} onChangeText={update('description')} multiline numberOfLines={3} />
          <AppButton label="Save Record" onPress={() => navigation.goBack()} />
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
