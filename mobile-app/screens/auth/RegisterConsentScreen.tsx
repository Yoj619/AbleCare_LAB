import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  ScrollView,
  StyleSheet,
  StatusBar,
  ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppButton from '../../components/AppButton';
import Card from '../../components/Card';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';
import { registerCaregiver } from '../../services/auth';
import { useAuth } from '../../context/AuthContext';

type Props = NativeStackScreenProps<RootStackParamList, 'RegisterConsent'>;

const CONSENT_TEXT = `By registering and using the AbleCare system, you agree to provide necessary personal and health-related information for the purpose of therapy support, monitoring, and emergency response.

The information you provide will only be used for academic and system functionality purposes. Your data will be handled with care and will not be shared with unauthorized individuals.

You understand that the system provides general guidance only and does not replace professional medical advice. By proceeding, you confirm that you voluntarily give your consent to use the system.`;

const NDA_TEXT = `By using the AbleCare system, you agree to keep all sensitive information, including patient data and system details, confidential.

You agree not to share, copy, or distribute any patient information or system-derived information obtained from the system without proper authorization.

The system collects and stores user information only for system functionality and improvement. All reasonable security measures are taken to protect user data.

Any misuse of data or breach of this agreement may result in account restriction or removal.`;

export default function RegisterConsentScreen({ navigation, route }: Props) {
  const { fullName, email, phone, address, barangay, password } = route.params;
  const { restoreUser } = useAuth();

  const [consentChecked, setConsentChecked] = useState(false);
  const [ndaChecked, setNdaChecked] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  async function handleCreateAccount() {
    setError('');
    setLoading(true);

    const result = await registerCaregiver({
      name: fullName,
      email,
      phone,
      address,
      barangay,
      password,
    });

    setLoading(false);

    if (!result.ok) {
      setError(result.error);
      return;
    }

    // Token is stored by registerCaregiver(); also hydrate the auth context so
    // the drawer shows the correct user name from the first screen after registration.
    await restoreUser(result.data.user);
    navigation.navigate('PatientInformation');
  }

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
      <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
        <TouchableOpacity style={styles.back} onPress={() => navigation.goBack()}>
          <Ionicons name="arrow-back" size={14} color={Colors.textSecondary} />
          <Text style={styles.backTxt}>Back</Text>
        </TouchableOpacity>

        <Text style={styles.title}>Create Account</Text>
        <Text style={styles.subtitle}>Join AbleCare as a Caregiver</Text>

        {error !== '' && (
          <View style={styles.errorBox}>
            <Ionicons name="alert-circle-outline" size={16} color={Colors.danger} />
            <Text style={styles.errorTxt}>{error}</Text>
          </View>
        )}

        <Card style={styles.card}>
          <Text style={styles.sectionTitle}>CONSENT FORM</Text>
          <Text style={styles.bodyText}>{CONSENT_TEXT}</Text>

          <View style={styles.divider} />

          <Text style={styles.sectionTitle}>NDA / PRIVACY AGREEMENT</Text>
          <Text style={styles.bodyText}>{NDA_TEXT}</Text>

          <View style={styles.divider} />

          <TouchableOpacity style={styles.checkRow} onPress={() => setConsentChecked(v => !v)} activeOpacity={0.7}>
            <View style={[styles.checkbox, consentChecked && styles.checkboxActive]}>
              {consentChecked && <Ionicons name="checkmark" size={12} color={Colors.white} />}
            </View>
            <Text style={styles.checkLabel}>
              I agree to the Consent Form and understand that my data will be used for academic purposes only.
            </Text>
          </TouchableOpacity>

          <TouchableOpacity style={styles.checkRow} onPress={() => setNdaChecked(v => !v)} activeOpacity={0.7}>
            <View style={[styles.checkbox, ndaChecked && styles.checkboxActive]}>
              {ndaChecked && <Ionicons name="checkmark" size={12} color={Colors.white} />}
            </View>
            <Text style={styles.checkLabel}>
              I agree to keep all information confidential and follow the Privacy and Non-Disclosure Agreement.
            </Text>
          </TouchableOpacity>
        </Card>

        {loading ? (
          <View style={styles.loadingWrap}>
            <ActivityIndicator size="large" color={Colors.primary} />
            <Text style={styles.loadingTxt}>Creating your account…</Text>
          </View>
        ) : (
          <AppButton
            label="Create Account"
            onPress={() => void handleCreateAccount()}
            disabled={!consentChecked || !ndaChecked}
            style={styles.createBtn}
          />
        )}

        <View style={styles.loginRow}>
          <Text style={styles.loginTxt}>Already have an account? </Text>
          <TouchableOpacity onPress={() => navigation.navigate('Login')}>
            <Text style={styles.loginLink}>Login</Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  scroll: { padding: Spacing.lg, paddingBottom: Spacing.xl },
  back: { flexDirection: 'row', alignItems: 'center', gap: 4, marginBottom: Spacing.md },
  backTxt: { fontSize: Typography.size.sm, color: Colors.textSecondary, fontWeight: Typography.weight.medium },
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
  errorBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.xs,
    backgroundColor: '#FEE2E2',
    borderRadius: 8,
    padding: Spacing.sm,
    marginBottom: Spacing.md,
  },
  errorTxt: { flex: 1, fontSize: Typography.size.sm, color: Colors.danger },
  card: { marginBottom: Spacing.lg },
  sectionTitle: {
    fontSize: Typography.size.sm,
    fontWeight: Typography.weight.bold,
    color: Colors.dark,
    marginBottom: Spacing.sm,
    letterSpacing: 0.5,
  },
  bodyText: {
    fontSize: Typography.size.xs,
    color: Colors.textSecondary,
    lineHeight: 18,
    marginBottom: Spacing.sm,
  },
  divider: { height: 1, backgroundColor: Colors.border, marginVertical: Spacing.md },
  checkRow: { flexDirection: 'row', gap: Spacing.sm, marginTop: Spacing.sm },
  checkbox: {
    width: 20,
    height: 20,
    borderRadius: 4,
    borderWidth: 1.5,
    borderColor: Colors.border,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 1,
    flexShrink: 0,
  },
  checkboxActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  checkLabel: { flex: 1, fontSize: Typography.size.xs, color: Colors.textSecondary, lineHeight: 18 },
  loadingWrap: { alignItems: 'center', gap: Spacing.sm, marginBottom: Spacing.md },
  loadingTxt: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  createBtn: { marginBottom: Spacing.md },
  loginRow: { flexDirection: 'row', justifyContent: 'center' },
  loginTxt: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  loginLink: { fontSize: Typography.size.sm, color: Colors.primary, fontWeight: Typography.weight.bold },

});
