import React from 'react';
import {
  TouchableOpacity,
  Text,
  StyleSheet,
  ActivityIndicator,
  ViewStyle,
  TextStyle,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Colors, Radius, Spacing, Typography } from '../constants/theme';

type Variant = 'primary' | 'outline' | 'danger' | 'dangerOutline' | 'ghost';
type BtnSize = 'sm' | 'md' | 'lg';

interface AppButtonProps {
  label: string;
  onPress: () => void;
  variant?: Variant;
  size?: BtnSize;
  loading?: boolean;
  disabled?: boolean;
  style?: ViewStyle;
  textStyle?: TextStyle;
  icon?: keyof typeof Ionicons.glyphMap;
}

const ICON_TEXT_COLOR: Record<Variant, string> = {
  primary: Colors.white,
  outline: Colors.primary,
  danger: Colors.white,
  dangerOutline: Colors.danger,
  ghost: Colors.primary,
};

export default function AppButton({
  label,
  onPress,
  variant = 'primary',
  size = 'lg',
  loading = false,
  disabled = false,
  style,
  textStyle,
  icon,
}: AppButtonProps) {
  return (
    <TouchableOpacity
      onPress={onPress}
      disabled={disabled || loading}
      style={[styles.base, styles[variant], styles[`size_${size}`], disabled && styles.disabled, style]}
      activeOpacity={0.78}
    >
      {loading ? (
        <ActivityIndicator color={variant === 'primary' || variant === 'danger' ? Colors.white : Colors.primary} />
      ) : (
        <>
          {icon && <Ionicons name={icon} size={18} color={ICON_TEXT_COLOR[variant]} style={styles.icon} />}
          <Text style={[styles.text, styles[`text_${variant}`], styles[`textSize_${size}`], textStyle]}>
            {label}
          </Text>
        </>
      )}
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  base: {
    borderRadius: Radius.full,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
  },
  icon: { marginRight: Spacing.xs },
  // Variants
  primary: { backgroundColor: Colors.primary },
  outline: { backgroundColor: 'transparent', borderWidth: 1.5, borderColor: Colors.primary },
  danger: { backgroundColor: Colors.danger },
  dangerOutline: { backgroundColor: 'transparent', borderWidth: 1.5, borderColor: Colors.danger },
  ghost: { backgroundColor: 'transparent' },
  disabled: { opacity: 0.5 },
  // Sizes
  size_sm: { paddingVertical: 8, paddingHorizontal: 20 },
  size_md: { paddingVertical: 12, paddingHorizontal: 24 },
  size_lg: { paddingVertical: 15, paddingHorizontal: 32 },
  // Text
  text: { fontWeight: Typography.weight.semiBold },
  text_primary: { color: Colors.white },
  text_outline: { color: Colors.primary },
  text_danger: { color: Colors.white },
  text_dangerOutline: { color: Colors.danger },
  text_ghost: { color: Colors.primary },
  // Text sizes
  textSize_sm: { fontSize: Typography.size.sm },
  textSize_md: { fontSize: Typography.size.md },
  textSize_lg: { fontSize: Typography.size.md },
});
