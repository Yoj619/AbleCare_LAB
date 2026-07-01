import React, { useState, useEffect } from 'react';
import {
  View, Text, ScrollView, StyleSheet, StatusBar, ActivityIndicator,
  TouchableOpacity, TextInput,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import AppInput from '../../components/AppInput';
import AppButton from '../../components/AppButton';
import { useActivePatient } from '../../hooks/useActivePatient';
import { getPatient, updatePatient, type Patient, type PatientGender, type DisabilityCategory } from '../../services/patients';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

// ── Chip selector ────────────────────────────────────────────────────────────
interface ChipOption<T extends string> {
  value: T;
  label: string;
}

function ChipSelect<T extends string>({
  label, options, value, onChange,
}: {
  label: string;
  options: ChipOption<T>[];
  value: T | '';
  onChange: (v: T) => void;
}) {
  return (
    <View style={chipStyles.wrapper}>
      <Text style={chipStyles.label}>{label}</Text>
      <View style={chipStyles.row}>
        {options.map(opt => {
          const selected = opt.value === value;
          return (
            <TouchableOpacity
              key={opt.value}
              style={[chipStyles.chip, selected && chipStyles.chipSelected]}
              onPress={() => onChange(opt.value)}
              activeOpacity={0.7}
            >
              <Text style={[chipStyles.chipText, selected && chipStyles.chipTextSelected]}>
                {opt.label}
              </Text>
            </TouchableOpacity>
          );
        })}
      </View>
    </View>
  );
}

const chipStyles = StyleSheet.create({
  wrapper: { marginBottom: Spacing.md },
  label: {
    fontSize: Typography.size.sm,
    fontWeight: Typography.weight.medium,
    color: Colors.textPrimary,
    marginBottom: Spacing.xs,
  },
  row: { flexDirection: 'row', flexWrap: 'wrap', gap: Spacing.xs },
  chip: {
    paddingHorizontal: Spacing.md,
    paddingVertical: 10,
    borderRadius: Radius.md,
    borderWidth: 1,
    borderColor: Colors.border,
    backgroundColor: Colors.inputBg,
  },
  chipSelected: {
    backgroundColor: Colors.primary,
    borderColor: Colors.primary,
  },
  chipText: {
    fontSize: Typography.size.sm,
    color: Colors.textSecondary,
    fontWeight: Typography.weight.medium,
  },
  chipTextSelected: {
    color: Colors.white,
  },
});

// ── Date input ───────────────────────────────────────────────────────────────
function DateInput({
  label, value, onChange,
}: { label: string; value: string; onChange: (v: string) => void }) {
  function handleChange(raw: string) {
    // Auto-insert dashes: YYYY-MM-DD
    const digits = raw.replace(/\D/g, '').slice(0, 8);
    let formatted = digits;
    if (digits.length > 4) formatted = digits.slice(0, 4) + '-' + digits.slice(4);
    if (digits.length > 6) formatted = digits.slice(0, 4) + '-' + digits.slice(4, 6) + '-' + digits.slice(6);
    onChange(formatted);
  }

  return (
    <View style={dateStyles.wrapper}>
      <Text style={dateStyles.label}>{label}</Text>
      <View style={dateStyles.row}>
        <Ionicons name="calendar-outline" size={16} color={Colors.textMuted} style={dateStyles.icon} />
        <TextInput
          style={dateStyles.input}
          value={value}
          onChangeText={handleChange}
          placeholder="YYYY-MM-DD"
          placeholderTextColor={Colors.textMuted}
          keyboardType="numeric"
          maxLength={10}
        />
      </View>
    </View>
  );
}

const dateStyles = StyleSheet.create({
  wrapper: { marginBottom: Spacing.md },
  label: {
    fontSize: Typography.size.sm,
    fontWeight: Typography.weight.medium,
    color: Colors.textPrimary,
    marginBottom: Spacing.xs,
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.inputBg,
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: Radius.md,
    paddingHorizontal: Spacing.md,
  },
  icon: { marginRight: Spacing.sm },
  input: {
    flex: 1,
    paddingVertical: 13,
    fontSize: Typography.size.md,
    color: Colors.textPrimary,
  },
});

// ── Constants ────────────────────────────────────────────────────────────────
const GENDER_OPTIONS: ChipOption<PatientGender>[] = [
  { value: 'male',   label: 'Male' },
  { value: 'female', label: 'Female' },
  { value: 'other',  label: 'Other' },
];

const DISABILITY_OPTIONS: ChipOption<DisabilityCategory>[] = [
  { value: 'physical',         label: 'Physical' },
  { value: 'sensory_visual',   label: 'Visual' },
  { value: 'sensory_hearing',  label: 'Hearing' },
  { value: 'cognitive',        label: 'Cognitive' },
];

// ── Screen ───────────────────────────────────────────────────────────────────
export default function EditPatientProfileScreen() {
  const navigation = useNavigation<Nav>();
  const { patient: activePatient, loading: listLoading, refresh: patientRefresh } = useActivePatient();

  const [patient, setPatient] = useState<Patient | null>(null);
  const [fetchLoading, setFetchLoading] = useState(true);
  const [fetchError, setFetchError] = useState<string | null>(null);
  const [retryKey, setRetryKey] = useState(0);

  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [gender, setGender] = useState<PatientGender | ''>('');
  const [dateOfBirth, setDateOfBirth] = useState('');
  const [disabilityCategory, setDisabilityCategory] = useState<DisabilityCategory | ''>('');
  const [specificCondition, setSpecificCondition] = useState('');

  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState<string | null>(null);
  const [saveSuccess, setSaveSuccess] = useState(false);

  useEffect(() => {
    if (!activePatient) { setFetchLoading(false); return; }
    let isMounted = true;
    setFetchLoading(true);
    (async () => {
      const result = await getPatient(activePatient.id);
      if (!isMounted) return;
      if (result.ok) {
        const p = result.data;
        setPatient(p);
        setFirstName(p.firstName);
        setLastName(p.lastName);
        setGender(p.gender ?? '');
        setDateOfBirth(p.dateOfBirth ?? '');
        setDisabilityCategory(p.disabilityCategory ?? '');
        setSpecificCondition(p.specificCondition ?? '');
        setFetchError(null);
      } else {
        setFetchError(result.error);
      }
      setFetchLoading(false);
    })();
    return () => { isMounted = false; };
  }, [activePatient, retryKey]);

  async function handleSave(): Promise<void> {
    if (!patient || saving) return;
    if (!firstName.trim() || !lastName.trim()) {
      setSaveError('First and last name are required.');
      return;
    }
    setSaving(true);
    setSaveError(null);
    setSaveSuccess(false);

    const result = await updatePatient({
      id: patient.id,
      firstName: firstName.trim(),
      lastName: lastName.trim(),
      gender: gender !== '' ? gender : undefined,
      dateOfBirth: dateOfBirth.trim() || undefined,
      disabilityCategory: disabilityCategory !== '' ? disabilityCategory : undefined,
      specificCondition: specificCondition.trim() || undefined,
    });

    setSaving(false);
    if (result.ok) {
      setSaveSuccess(true);
      patientRefresh();
      setTimeout(() => navigation.goBack(), 1500);
    } else {
      setSaveError(result.error);
    }
  }

  const isLoading = listLoading || fetchLoading;
  const initials = patient
    ? `${patient.firstName.charAt(0)}${patient.lastName.charAt(0)}`.toUpperCase()
    : '—';

  if (isLoading) {
    return (
      <SafeAreaView style={styles.safe} edges={['bottom']}>
        <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
        <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
        <View style={styles.centered}>
          <ActivityIndicator size="large" color={Colors.primary} />
        </View>
      </SafeAreaView>
    );
  }

  if (fetchError || !patient) {
    return (
      <SafeAreaView style={styles.safe} edges={['bottom']}>
        <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
        <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
        <View style={styles.centered}>
          <Text style={styles.errorText}>{fetchError ?? 'No patient on file yet.'}</Text>
          {fetchError ? (
            <TouchableOpacity
              style={styles.retryBtn}
              onPress={() => { setFetchError(null); setRetryKey(k => k + 1); }}
            >
              <Text style={styles.retryTxt}>Retry</Text>
            </TouchableOpacity>
          ) : null}
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>

        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>Edit Patient Profile</Text>
          <Text style={styles.bannerSub}>Update {patient.firstName} {patient.lastName}'s information</Text>
        </Card>

        <Card style={styles.photoCard}>
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>{initials}</Text>
          </View>
        </Card>

        <Card>
          <Text style={styles.sectionLabel}>Personal Information</Text>

          {saveSuccess && (
            <View style={styles.successBanner}>
              <Ionicons name="checkmark-circle" size={18} color={Colors.success} />
              <Text style={styles.successText}>Patient profile updated successfully.</Text>
            </View>
          )}
          {saveError ? <Text style={styles.saveError}>{saveError}</Text> : null}

          <AppInput
            label="First Name"
            leftIcon="person-outline"
            value={firstName}
            onChangeText={setFirstName}
            placeholder="First name"
          />
          <AppInput
            label="Last Name"
            leftIcon="person-outline"
            value={lastName}
            onChangeText={setLastName}
            placeholder="Last name"
          />

          <ChipSelect
            label="Gender"
            options={GENDER_OPTIONS}
            value={gender}
            onChange={setGender}
          />

          <DateInput
            label="Date of Birth"
            value={dateOfBirth}
            onChange={setDateOfBirth}
          />

          <ChipSelect
            label="Disability Category"
            options={DISABILITY_OPTIONS}
            value={disabilityCategory}
            onChange={setDisabilityCategory}
          />

          <AppInput
            label="Specific Condition"
            leftIcon="heart-outline"
            value={specificCondition}
            onChangeText={setSpecificCondition}
            placeholder="e.g., Mobility impairment, Low vision"
          />

          <AppButton label={saving ? 'Saving…' : 'Save Changes'} onPress={() => void handleSave()} />
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
  centered: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: Spacing.xl },
  errorText: { fontSize: Typography.size.sm, color: Colors.textSecondary, textAlign: 'center', marginBottom: Spacing.md },
  retryBtn: { backgroundColor: Colors.primary, borderRadius: Radius.md, paddingHorizontal: Spacing.lg, paddingVertical: Spacing.sm },
  retryTxt: { color: Colors.white, fontSize: Typography.size.sm, fontWeight: Typography.weight.medium },
  banner: {},
  bannerTitle: { fontSize: Typography.size.lg, fontWeight: Typography.weight.bold, color: Colors.dark },
  bannerSub: { fontSize: Typography.size.sm, color: Colors.textSecondary, marginTop: 2 },
  photoCard: { alignItems: 'center' },
  avatar: { width: 80, height: 80, borderRadius: 40, backgroundColor: Colors.primaryLight, alignItems: 'center', justifyContent: 'center' },
  avatarText: { color: Colors.primary, fontSize: Typography.size.xl, fontWeight: Typography.weight.bold },
  sectionLabel: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark, marginBottom: Spacing.md },
  successBanner: {
    flexDirection: 'row', alignItems: 'center', gap: Spacing.sm,
    backgroundColor: '#E8F5E9', borderRadius: Radius.md,
    padding: Spacing.md, marginBottom: Spacing.md,
  },
  successText: { flex: 1, fontSize: Typography.size.sm, color: Colors.success, fontWeight: Typography.weight.medium },
  saveError: { fontSize: Typography.size.sm, color: Colors.danger, marginBottom: Spacing.md },
});
