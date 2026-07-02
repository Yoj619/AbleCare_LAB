import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StatusBar,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import Logo from '../../components/Logo';
import AppInput from '../../components/AppInput';
import AppButton from '../../components/AppButton';
import Card from '../../components/Card';
import { Colors, Spacing, Typography } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'Register'>;

export default function RegisterScreen({ navigation }: Props) {
  const [form, setForm] = useState({
    fullName: '',
    email: '',
    phone: '',
    address: '',
    barangay: '',
    password: '',
    confirm: '',
  });
  const [error, setError] = useState('');

  const update = (key: keyof typeof form) => (val: string) =>
    setForm(f => ({ ...f, [key]: val }));

  function handleNext() {
    const { fullName, email, phone, address, barangay, password, confirm } = form;

    if (!fullName.trim() || !email.trim() || !phone.trim() || !address.trim() || !barangay.trim() || !password || !confirm) {
      setError('Please fill in all fields.');
      return;
    }
    if (!/\S+@\S+\.\S+/.test(email.trim())) {
      setError('Please enter a valid email address.');
      return;
    }
    if (password.length < 8) {
      setError('Password must be at least 8 characters.');
      return;
    }
    if (password !== confirm) {
      setError('Passwords do not match.');
      return;
    }

    setError('');
    navigation.navigate('RegisterConsent', {
      fullName: fullName.trim(),
      email: email.trim(),
      phone: phone.trim(),
      address: address.trim(),
      barangay: barangay.trim(),
      password,
    });
  }

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <ScrollView contentContainerStyle={styles.scroll} showsVerticalScrollIndicator={false}>
          <TouchableOpacity style={styles.back} onPress={() => navigation.goBack()}>
            <Ionicons name="arrow-back" size={14} color={Colors.textSecondary} />
            <Text style={styles.backTxt}>Back</Text>
          </TouchableOpacity>

          <View style={styles.logoWrap}>
            <Logo size="md" />
          </View>

          <Text style={styles.title}>Create Account</Text>
          <Text style={styles.subtitle}>Join AbleCare as a Caregiver</Text>

          {error !== '' && (
            <View style={styles.errorBox}>
              <Ionicons name="alert-circle-outline" size={16} color={Colors.danger} />
              <Text style={styles.errorTxt}>{error}</Text>
            </View>
          )}

          <Card style={styles.card}>
            <AppInput
              label="Full Name"
              leftIcon="person-outline"
              placeholder="Enter your full name"
              value={form.fullName}
              onChangeText={update('fullName')}
            />
            <AppInput
              label="Email Address"
              leftIcon="mail-outline"
              placeholder="Enter your email"
              keyboardType="email-address"
              autoCapitalize="none"
              value={form.email}
              onChangeText={update('email')}
            />
            <AppInput
              label="Phone Number"
              leftIcon="call-outline"
              placeholder="Enter your phone number"
              keyboardType="phone-pad"
              value={form.phone}
              onChangeText={update('phone')}
            />
            <AppInput
              label="Home Address"
              leftIcon="home-outline"
              placeholder="Street, house no., city"
              value={form.address}
              onChangeText={update('address')}
            />
            <AppInput
              label="Barangay"
              leftIcon="location-outline"
              placeholder="Enter your barangay"
              value={form.barangay}
              onChangeText={update('barangay')}
            />
            <AppInput
              label="Password"
              leftIcon="lock-closed-outline"
              placeholder="At least 8 characters"
              secure
              value={form.password}
              onChangeText={update('password')}
            />
            <AppInput
              label="Confirm Password"
              leftIcon="lock-closed-outline"
              placeholder="Re-enter your password"
              secure
              value={form.confirm}
              onChangeText={update('confirm')}
            />
            <AppButton label="Next" onPress={handleNext} />
          </Card>

          <View style={styles.loginRow}>
            <Text style={styles.loginTxt}>Already have an account? </Text>
            <TouchableOpacity onPress={() => navigation.navigate('Login')}>
              <Text style={styles.loginLink}>Login</Text>
            </TouchableOpacity>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  flex: { flex: 1 },
  scroll: { padding: Spacing.lg, paddingBottom: Spacing.xl },
  back: { flexDirection: 'row', alignItems: 'center', gap: 4, marginBottom: Spacing.md },
  backTxt: { fontSize: Typography.size.sm, color: Colors.textSecondary, fontWeight: Typography.weight.medium },
  logoWrap: { alignItems: 'center', marginBottom: Spacing.lg },
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
  loginRow: { flexDirection: 'row', justifyContent: 'center' },
  loginTxt: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  loginLink: { fontSize: Typography.size.sm, color: Colors.primary, fontWeight: Typography.weight.bold },
});
