import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import Logo from '../../components/Logo';
import AppButton from '../../components/AppButton';
import { useAuth } from '../../context/AuthContext';
import { Colors, Radius, Spacing, Typography } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'Logout'>;

export default function LogoutScreen({ navigation }: Props) {
  const { logout } = useAuth();

  async function handleLogout() {
    await logout();
    navigation.reset({ index: 0, routes: [{ name: 'Landing' }] });
  }

  return (
    <View style={styles.overlay}>
      <View style={styles.card}>
        <Logo size="sm" />
        <Text style={styles.title}>Confirm Logout</Text>
        <Text style={styles.subtitle}>Are you sure you want to logout?</Text>
        <AppButton
          icon="checkmark" label="Yes"
          onPress={handleLogout}
          style={styles.yesBtn}
        />
        <View style={styles.gap} />
        <AppButton
          icon="close" label="No"
          variant="dangerOutline"
          onPress={() => navigation.goBack()}
        />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  overlay: {
    flex: 1,
    backgroundColor: Colors.overlayBg,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Spacing.xl,
  },
  card: {
    backgroundColor: Colors.white,
    borderRadius: Radius.xl,
    borderWidth: 1.5,
    borderColor: Colors.primary,
    padding: Spacing.xl,
    width: '100%',
    alignItems: 'center',
  },
  title: {
    fontSize: Typography.size.xl,
    fontWeight: Typography.weight.bold,
    color: Colors.dark,
    marginTop: Spacing.md,
    marginBottom: Spacing.xs,
  },
  subtitle: {
    fontSize: Typography.size.sm,
    color: Colors.primary,
    marginBottom: Spacing.lg,
    textAlign: 'center',
  },
  yesBtn: { width: '100%' },
  gap: { height: Spacing.sm },
});
