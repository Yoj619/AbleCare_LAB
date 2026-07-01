import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Colors, Spacing, Typography } from '../constants/theme';

interface AppHeaderProps {
  title?: string;
  subtitle?: string;
  onHamburgerPress?: () => void;
  onBellPress?: () => void;
  showBack?: boolean;
  onBackPress?: () => void;
}

export default function AppHeader({
  title = 'AbleCare',
  subtitle = 'Caregiver Portal',
  onHamburgerPress,
  onBellPress,
  showBack = false,
  onBackPress,
}: AppHeaderProps) {
  const insets = useSafeAreaInsets();

  return (
    <LinearGradient
      colors={[Colors.primary, Colors.primaryDark]}
      start={{ x: 0, y: 0 }}
      end={{ x: 1, y: 0 }}
      style={[styles.header, { paddingTop: insets.top + Spacing.sm }]}
    >
      <View style={styles.left}>
        {showBack ? (
          <TouchableOpacity onPress={onBackPress} style={styles.iconBtn}>
            <Ionicons name="arrow-back" size={20} color={Colors.white} />
          </TouchableOpacity>
        ) : (
          <TouchableOpacity onPress={onHamburgerPress} style={styles.iconBtn}>
            <Ionicons name="menu" size={20} color={Colors.white} />
          </TouchableOpacity>
        )}
        <View style={styles.titleBlock}>
          <Text style={styles.title}>{title}</Text>
          <Text style={styles.subtitle}>{subtitle}</Text>
        </View>
      </View>
      <View style={styles.right}>
        <Ionicons name="pulse-outline" size={16} color={Colors.white} style={styles.signal} />
        <TouchableOpacity onPress={onBellPress} style={styles.iconBtn}>
          <Ionicons name="notifications" size={20} color={Colors.white} />
        </TouchableOpacity>
      </View>
    </LinearGradient>
  );
}

const styles = StyleSheet.create({
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: Spacing.md,
    paddingBottom: Spacing.md,
  },
  left: { flexDirection: 'row', alignItems: 'center', gap: Spacing.sm },
  right: { flexDirection: 'row', alignItems: 'center', gap: Spacing.xs },
  iconBtn: { padding: 6 },
  signal: { opacity: 0.8 },
  titleBlock: { marginLeft: 4 },
  title: {
    color: Colors.white,
    fontSize: Typography.size.md,
    fontWeight: Typography.weight.bold,
  },
  subtitle: {
    color: Colors.white,
    fontSize: Typography.size.xs,
    opacity: 0.85,
  },
});
