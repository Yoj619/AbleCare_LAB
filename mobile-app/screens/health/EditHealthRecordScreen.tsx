import React, { useState } from 'react';
import { View, Text, ScrollView, StyleSheet, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import AppInput from '../../components/AppInput';
import AppButton from '../../components/AppButton';
import { Colors, Spacing, Typography } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'EditHealthRecord'>;

export default function EditHealthRecordScreen({ navigation, route }: Props) {
  const [form, setForm] = useState({ title: 'General Health Checkup', date: 'Apr 20, 2026', description: 'Regular checkup with complete vital signs monitoring.', type: 'Checkup' });
  const update = (k: keyof typeof form) => (v: string) => setForm(f => ({ ...f, [k]: v }));

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>Edit Health Record</Text>
          <Text style={styles.bannerSub}>Juan dela Cruz • Medical Document Library</Text>
        </Card>
        <Card>
          <AppInput label="Record Title" leftIcon="clipboard-outline" value={form.title} onChangeText={update('title')} />
          <AppInput label="Date" leftIcon="calendar-outline" value={form.date} onChangeText={update('date')} />
          <AppInput label="Record Type" leftIcon="pricetag-outline" value={form.type} onChangeText={update('type')} />
          <AppInput label="Description" leftIcon="create-outline" value={form.description} onChangeText={update('description')} multiline numberOfLines={3} />
          <AppButton label="Save Changes" onPress={() => navigation.goBack()} />
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
