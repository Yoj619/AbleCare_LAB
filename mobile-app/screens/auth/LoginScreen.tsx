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
import { useAuth } from '../../context/AuthContext';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'Login'>;

export default function LoginScreen({ navigation }: Props) {
  const { login, requestLocationPermission } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  async function handleLogin() {
    setError('');
    setSubmitting(true);
    const result = await login({ email, password });

    if (!result.ok) {
      setSubmitting(false);
      setError(result.error);
      return;
    }

    // Ask for location permission while the login button is still in its loading
    // state — the OS dialog appears here with no custom UI from our side.
    // Navigation to Main happens regardless of what the caregiver selects.
    await requestLocationPermission();

    setSubmitting(false);
    navigation.reset({ index: 0, routes: [{ name: 'Main' }] });
  }

  return (
    <SafeAreaView style={styles.safe} edges={['top', 'bottom']}>
      <StatusBar barStyle="dark-content" backgroundColor={Colors.background} />
      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
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
          <Text style={styles.title}>Welcome Back</Text>
          <Text style={styles.subtitle}>Login to continue using AbleCare</Text>

          {/* Form Card */}
          <Card style={styles.card}>
            {!!error && <Text style={styles.errorTxt}>{error}</Text>}
            <AppInput
              label="Email Address"
              leftIcon="mail-outline"
              placeholder="Enter your email"
              keyboardType="email-address"
              autoCapitalize="none"
              value={email}
              onChangeText={setEmail}
            />
            <AppInput
              label="Password"
              leftIcon="lock-closed-outline"
              placeholder="Enter your password"
              secure
              value={password}
              onChangeText={setPassword}
            />
            <TouchableOpacity
              style={styles.forgotWrap}
              onPress={() => navigation.navigate('ForgotPassword')}
            >
              <Text style={styles.forgotTxt}>Forgot password?</Text>
            </TouchableOpacity>
            <AppButton
              label={submitting ? 'Logging in...' : 'Login'}
              onPress={handleLogin}
              disabled={submitting}
            />
          </Card>

          {/* Register link */}
          <View style={styles.registerRow}>
            <Text style={styles.registerTxt}>Don't have an account? </Text>
            <TouchableOpacity onPress={() => navigation.navigate('Register')}>
              <Text style={styles.registerLink}>Register</Text>
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
    marginBottom: Spacing.xl,
  },
  card: { marginBottom: Spacing.lg },
  errorTxt: { fontSize: Typography.size.sm, color: Colors.danger, marginBottom: Spacing.md, textAlign: 'center' },
  forgotWrap: { alignSelf: 'flex-end', marginBottom: Spacing.lg, marginTop: -Spacing.sm },
  forgotTxt: { fontSize: Typography.size.sm, color: Colors.primary, fontWeight: Typography.weight.medium },
  registerRow: { flexDirection: 'row', justifyContent: 'center', marginTop: Spacing.md },
  registerTxt: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  registerLink: { fontSize: Typography.size.sm, color: Colors.primary, fontWeight: Typography.weight.bold },
});
