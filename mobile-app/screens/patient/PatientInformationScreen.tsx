import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  StyleSheet,
  StatusBar,
  Modal,
  FlatList,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import Logo from '../../components/Logo';
import AppButton from '../../components/AppButton';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'PatientInformation'>;

// ─── Dropdown options ──────────────────────────────────────────────────────────
const GENDER_OPTIONS = ['Male', 'Female', 'Other'];
const BLOOD_OPTIONS = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

// ─── Medical conditions list ──────────────────────────────────────────────────
const CONDITIONS: { section: string; items: string[] }[] = [
  {
    section: 'PHYSICAL DISABILITIES',
    items: [
      'Mobility Impairment',
      'Partial Paralysis',
      'Full Paralysis',
      'Cerebral Palsy',
      'Muscular Dystrophy',
      'Amputation',
      'Spinal Cord Injury',
      'Post-stroke Recovery',
      "Parkinson's Disease",
      'Paraplegia',
      'Hemiplegia',
      'Quadriplegia',
      'Seizures / Epilepsy',
    ],
  },
  {
    section: 'NEUROLOGICAL / COGNITIVE',
    items: [
      'Dementia',
      'Alzheimer\'s Disease',
      'Autism Spectrum Disorder',
      'Down Syndrome',
      'Intellectual Disability',
      'Memory / Cognitive Challenges',
      'Mental Health Issues',
    ],
  },
  {
    section: 'SENSORY DISABILITIES',
    items: [
      'Visual Impairment / Blindness',
      'Hearing Impairment / Deafness',
      'Speech / Communication Disorder',
    ],
  },
  {
    section: 'CHRONIC CONDITIONS',
    items: [
      'Diabetes',
      'Hypertension',
      'Heart Disease',
      'Chronic Obstructive Pulmonary Disease (COPD)',
      'Kidney Disease',
      'Arthritis',
    ],
  },
];

// ─── Simple Select component ──────────────────────────────────────────────────
function SimpleSelect({
  label,
  value,
  options,
  placeholder,
  onSelect,
}: {
  label: string;
  value: string;
  options: string[];
  placeholder: string;
  onSelect: (v: string) => void;
}) {
  const [open, setOpen] = useState(false);

  return (
    <View style={selectStyles.wrapper}>
      {label ? <Text style={selectStyles.label}>{label}</Text> : null}
      <TouchableOpacity
        style={selectStyles.trigger}
        onPress={() => setOpen(true)}
        activeOpacity={0.8}
      >
        <Text style={[selectStyles.triggerText, !value && selectStyles.placeholder]}>
          {value || placeholder}
        </Text>
        <Text style={selectStyles.arrow}>▼</Text>
      </TouchableOpacity>

      <Modal visible={open} transparent animationType="fade" onRequestClose={() => setOpen(false)}>
        <TouchableOpacity
          style={selectStyles.overlay}
          activeOpacity={1}
          onPress={() => setOpen(false)}
        >
          <View style={selectStyles.sheet}>
            <Text style={selectStyles.sheetTitle}>{label || placeholder}</Text>
            {options.map(opt => (
              <TouchableOpacity
                key={opt}
                style={[selectStyles.option, value === opt && selectStyles.optionActive]}
                onPress={() => { onSelect(opt); setOpen(false); }}
                activeOpacity={0.7}
              >
                <Text style={[selectStyles.optionText, value === opt && selectStyles.optionTextActive]}>
                  {opt}
                </Text>
                {value === opt && <Ionicons name="checkmark" size={16} color={Colors.primary} />}
              </TouchableOpacity>
            ))}
          </View>
        </TouchableOpacity>
      </Modal>
    </View>
  );
}

const selectStyles = StyleSheet.create({
  wrapper: { marginBottom: Spacing.md },
  label: {
    fontSize: Typography.size.sm,
    fontWeight: Typography.weight.medium,
    color: Colors.textPrimary,
    marginBottom: Spacing.xs,
  },
  trigger: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: Colors.inputBg,
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: Radius.md,
    paddingHorizontal: Spacing.md,
    paddingVertical: 13,
  },
  triggerText: { flex: 1, fontSize: Typography.size.md, color: Colors.textPrimary },
  placeholder: { color: Colors.textMuted },
  arrow: { fontSize: 12, color: Colors.textMuted },
  overlay: {
    flex: 1,
    backgroundColor: Colors.overlayBg,
    justifyContent: 'center',
    padding: Spacing.xl,
  },
  sheet: {
    backgroundColor: Colors.white,
    borderRadius: Radius.xl,
    overflow: 'hidden',
    ...Shadows.header,
  },
  sheetTitle: {
    fontSize: Typography.size.md,
    fontWeight: Typography.weight.bold,
    color: Colors.dark,
    padding: Spacing.md,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  option: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: Spacing.md,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  optionActive: { backgroundColor: Colors.primaryLight },
  optionText: { flex: 1, fontSize: Typography.size.md, color: Colors.textPrimary },
  optionTextActive: { color: Colors.primary, fontWeight: Typography.weight.semiBold },
});

// ─── Main screen ──────────────────────────────────────────────────────────────
export default function PatientInformationScreen({ navigation }: Props) {
  const [name, setName] = useState('');
  const [age, setAge] = useState('');
  const [gender, setGender] = useState('');
  const [bloodType, setBloodType] = useState('');
  const [selected, setSelected] = useState<Set<string>>(new Set());

  function toggleCondition(item: string) {
    setSelected(prev => {
      const next = new Set(prev);
      next.has(item) ? next.delete(item) : next.add(item);
      return next;
    });
  }

  function handleContinue() {
    navigation.navigate('ClinicRecommendationResult', {
      conditions: Array.from(selected),
    });
  }

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        {/* Back */}
        <TouchableOpacity style={styles.back} onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={14} color={Colors.textSecondary} />
          <Text style={styles.backTxt}>Back</Text>
        </TouchableOpacity>

        {/* Logo */}
        <View style={styles.logoWrap}>
          <Logo size="md" />
        </View>

        {/* Heading */}
        <Text style={styles.title}>Patient Information</Text>
        <Text style={styles.subtitle}>Register your patient (PWD or Elderly)</Text>

        {/* Form card */}
        <View style={styles.formCard}>
          {/* Patient Full Name */}
          <View style={styles.fieldWrap}>
            <Text style={styles.fieldLabel}>Patient Full Name</Text>
            <TextInput
              style={styles.input}
              placeholder="Enter patient name"
              placeholderTextColor={Colors.textMuted}
              value={name}
              onChangeText={setName}
            />
          </View>

          {/* Age + Gender row */}
          <View style={styles.row}>
            <View style={[styles.fieldWrap, styles.rowHalf]}>
              <Text style={styles.fieldLabel}>Age</Text>
              <TextInput
                style={styles.input}
                placeholder="Age"
                placeholderTextColor={Colors.textMuted}
                keyboardType="numeric"
                value={age}
                onChangeText={setAge}
              />
            </View>
            <View style={[styles.fieldWrap, styles.rowHalf]}>
              <SimpleSelect
                label="Gender"
                value={gender}
                options={GENDER_OPTIONS}
                placeholder="Select Gender"
                onSelect={setGender}
              />
            </View>
          </View>

          {/* Blood Type */}
          <SimpleSelect
            label="Blood Type"
            value={bloodType}
            options={BLOOD_OPTIONS}
            placeholder="Select Blood Type"
            onSelect={setBloodType}
          />

          {/* Medical Conditions */}
          <Text style={styles.conditionHeading}>
            Medical Conditions & Disabilities{'\n'}
            <Text style={styles.conditionSub}>(Select all that apply)</Text>
          </Text>

          {CONDITIONS.map(group => (
            <View key={group.section} style={styles.conditionGroup}>
              <Text style={styles.sectionLabel}>{group.section}</Text>
              {group.items.map(item => {
                const checked = selected.has(item);
                return (
                  <TouchableOpacity
                    key={item}
                    style={styles.checkRow}
                    onPress={() => toggleCondition(item)}
                    activeOpacity={0.7}
                  >
                    <View style={[styles.checkbox, checked && styles.checkboxActive]}>
                      {checked && <Ionicons name="checkmark" size={12} color={Colors.white} />}
                    </View>
                    <Text style={[styles.checkLabel, checked && styles.checkLabelActive]}>
                      {item}
                    </Text>
                  </TouchableOpacity>
                );
              })}
            </View>
          ))}
        </View>

        {/* Continue button */}
        <AppButton
          label="Continue to Clinic Recommendation"
          onPress={handleContinue}
          style={styles.continueBtn}
        />
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  scroll: { padding: Spacing.lg, paddingBottom: Spacing.xl },
  back: { flexDirection: 'row', alignItems: 'center', gap: 4, marginBottom: Spacing.md },
  backTxt: { fontSize: Typography.size.sm, color: Colors.textSecondary, fontWeight: Typography.weight.medium },
  logoWrap: { alignItems: 'center', marginBottom: Spacing.md },
  title: {
    fontSize: Typography.size.xl,
    fontWeight: Typography.weight.bold,
    color: Colors.dark,
    textAlign: 'center',
    marginBottom: Spacing.xs,
  },
  subtitle: {
    fontSize: Typography.size.sm,
    color: Colors.primary,
    textAlign: 'center',
    marginBottom: Spacing.lg,
  },
  formCard: {
    backgroundColor: Colors.white,
    borderRadius: Radius.lg,
    padding: Spacing.md,
    marginBottom: Spacing.lg,
    ...Shadows.card,
  },
  fieldWrap: { marginBottom: Spacing.md },
  fieldLabel: {
    fontSize: Typography.size.sm,
    fontWeight: Typography.weight.medium,
    color: Colors.textPrimary,
    marginBottom: Spacing.xs,
  },
  input: {
    backgroundColor: Colors.inputBg,
    borderWidth: 1,
    borderColor: Colors.border,
    borderRadius: Radius.md,
    paddingHorizontal: Spacing.md,
    paddingVertical: 13,
    fontSize: Typography.size.md,
    color: Colors.textPrimary,
  },
  row: { flexDirection: 'row', gap: Spacing.sm },
  rowHalf: { flex: 1 },
  conditionHeading: {
    fontSize: Typography.size.md,
    fontWeight: Typography.weight.bold,
    color: Colors.dark,
    marginTop: Spacing.sm,
    marginBottom: Spacing.md,
    lineHeight: 22,
  },
  conditionSub: {
    fontWeight: Typography.weight.regular,
    color: Colors.textSecondary,
    fontSize: Typography.size.sm,
  },
  conditionGroup: { marginBottom: Spacing.md },
  sectionLabel: {
    fontSize: Typography.size.xs,
    fontWeight: Typography.weight.bold,
    color: Colors.primary,
    letterSpacing: 0.8,
    marginBottom: Spacing.sm,
  },
  checkRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.sm,
    paddingVertical: 8,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
  },
  checkbox: {
    width: 20,
    height: 20,
    borderRadius: 4,
    borderWidth: 1.5,
    borderColor: Colors.border,
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0,
  },
  checkboxActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  checkLabel: { flex: 1, fontSize: Typography.size.sm, color: Colors.textPrimary },
  checkLabelActive: { color: Colors.primary, fontWeight: Typography.weight.medium },
  continueBtn: {},
});
