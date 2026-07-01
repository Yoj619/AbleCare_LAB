import React, { useState } from 'react';
import {
  View,
  TextInput,
  Text,
  TouchableOpacity,
  StyleSheet,
  TextInputProps,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { Colors, Radius, Spacing, Typography } from '../constants/theme';

interface AppInputProps extends TextInputProps {
  label?: string;
  leftIcon?: keyof typeof Ionicons.glyphMap;
  secure?: boolean;
  error?: string;
}

export default function AppInput({ label, leftIcon, secure, error, style, ...props }: AppInputProps) {
  const [visible, setVisible] = useState(false);

  return (
    <View style={styles.wrapper}>
      {label && <Text style={styles.label}>{label}</Text>}
      <View style={[styles.row, error ? styles.rowError : null]}>
        {leftIcon && <Ionicons name={leftIcon} size={16} color={Colors.textMuted} style={styles.icon} />}
        <TextInput
          style={[styles.input, style]}
          secureTextEntry={secure && !visible}
          placeholderTextColor={Colors.textMuted}
          {...props}
        />
        {secure && (
          <TouchableOpacity onPress={() => setVisible(v => !v)} style={styles.eye}>
            <Ionicons name={visible ? 'eye-off' : 'eye'} size={18} color={Colors.textMuted} />
          </TouchableOpacity>
        )}
      </View>
      {error && <Text style={styles.error}>{error}</Text>}
    </View>
  );
}

const styles = StyleSheet.create({
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
  rowError: { borderColor: Colors.danger },
  icon: { marginRight: Spacing.sm },
  input: {
    flex: 1,
    paddingVertical: 13,
    fontSize: Typography.size.md,
    color: Colors.textPrimary,
  },
  eye: { padding: Spacing.xs },
  error: {
    marginTop: 4,
    fontSize: Typography.size.xs,
    color: Colors.danger,
  },
});
