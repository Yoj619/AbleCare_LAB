import React, { useEffect, useRef, useState } from 'react';
import {
  Modal,
  View,
  Text,
  TouchableOpacity,
  ScrollView,
  StyleSheet,
  Dimensions,
  Animated,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { Colors, Radius, Spacing, Typography } from '../constants/theme';
import { useAuth } from '../context/AuthContext';

const DRAWER_WIDTH = Dimensions.get('window').width * 0.78;

interface DrawerItem {
  label: string;
  icon: keyof typeof Ionicons.glyphMap;
  key: string;
}

const MENU_ITEMS: DrawerItem[] = [
  { label: 'Dashboard', icon: 'home-outline', key: 'Dashboard' },
  { label: 'Patient Profile', icon: 'person-outline', key: 'Patient' },
  { label: 'Health Records', icon: 'clipboard-outline', key: 'HealthRecords' },
  { label: 'AI Guidance', icon: 'sparkles-outline', key: 'AIHelp' },
  { label: 'Therapy Schedule', icon: 'calendar-outline', key: 'TherapySchedule' },
  { label: 'Messages', icon: 'chatbubble-outline', key: 'Messages' },
  { label: 'Clinic Recommendation', icon: 'medkit-outline', key: 'RecommendedClinics' },
  { label: 'Emergency Alert', icon: 'warning-outline', key: 'EmergencyAlert' },
];

interface DrawerMenuProps {
  visible: boolean;
  activeKey?: string;
  onClose: () => void;
  onNavigate: (key: string) => void;
  onLogout: () => void;
}

export default function DrawerMenu({ visible, activeKey, onClose, onNavigate, onLogout }: DrawerMenuProps) {
  const insets = useSafeAreaInsets();
  const { user } = useAuth();

  const fullName = user ? `${user.firstName} ${user.lastName}`.trim() : 'Caregiver';
  const initials = user
    ? `${user.firstName.charAt(0)}${user.lastName.charAt(0)}`.toUpperCase()
    : '?';
  const [modalVisible, setModalVisible] = useState(visible);
  const drawerOpacity = useRef(new Animated.Value(0)).current;
  const backdropOpacity = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    if (visible) {
      setModalVisible(true);
      Animated.parallel([
        Animated.timing(drawerOpacity, { toValue: 1, duration: 200, useNativeDriver: true }),
        Animated.timing(backdropOpacity, { toValue: 1, duration: 200, useNativeDriver: true }),
      ]).start();
    } else {
      Animated.parallel([
        Animated.timing(drawerOpacity, { toValue: 0, duration: 150, useNativeDriver: true }),
        Animated.timing(backdropOpacity, { toValue: 0, duration: 150, useNativeDriver: true }),
      ]).start(() => setModalVisible(false));
    }
  }, [visible, drawerOpacity, backdropOpacity]);

  return (
    <Modal visible={modalVisible} transparent animationType="none" onRequestClose={onClose}>
      <View style={styles.overlay}>
        <Animated.View style={[styles.drawer, { paddingBottom: insets.bottom + Spacing.md, opacity: drawerOpacity }]}>
          {/* Header */}
          <LinearGradient
            colors={[Colors.primary, Colors.primaryDark]}
            start={{ x: 0, y: 0 }}
            end={{ x: 1, y: 1 }}
            style={[styles.drawerHeader, { paddingTop: insets.top + Spacing.md }]}
          >
            <View style={styles.avatar}>
              <Text style={styles.avatarText}>{initials}</Text>
            </View>
            <View style={styles.userInfo}>
              <Text style={styles.userName}>{fullName}</Text>
              <Text style={styles.userRole}>Caregiver</Text>
            </View>
            <TouchableOpacity onPress={onClose} style={styles.closeBtn}>
              <Ionicons name="close" size={18} color={Colors.white} />
            </TouchableOpacity>
          </LinearGradient>

          {/* Menu items */}
          <ScrollView style={styles.menuList} showsVerticalScrollIndicator={false}>
            {MENU_ITEMS.map(item => {
              const isActive = item.key === activeKey;
              return (
                <TouchableOpacity
                  key={item.key}
                  style={[styles.menuItem, isActive && styles.menuItemActive]}
                  onPress={() => { onNavigate(item.key); onClose(); }}
                  activeOpacity={0.7}
                >
                  <Ionicons
                    name={item.icon}
                    size={18}
                    color={isActive ? Colors.primary : Colors.textSecondary}
                    style={styles.menuIcon}
                  />
                  <Text style={[styles.menuLabel, isActive && styles.menuLabelActive]}>
                    {item.label}
                  </Text>
                  <Ionicons name="chevron-forward" size={18} color={Colors.textMuted} />
                </TouchableOpacity>
              );
            })}
          </ScrollView>

          {/* Logout */}
          <TouchableOpacity style={styles.logoutRow} onPress={onLogout} activeOpacity={0.7}>
            <Ionicons name="log-out-outline" size={18} color={Colors.danger} style={styles.logoutIcon} />
            <Text style={styles.logoutLabel}>Logout</Text>
            <Ionicons name="chevron-forward" size={18} color={Colors.textMuted} />
          </TouchableOpacity>
        </Animated.View>
        <TouchableOpacity style={styles.backdropTouchable} onPress={onClose} activeOpacity={1}>
          <Animated.View style={[styles.backdrop, { opacity: backdropOpacity }]} />
        </TouchableOpacity>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: { flex: 1, flexDirection: 'row' },
  backdropTouchable: { flex: 1 },
  backdrop: { flex: 1, backgroundColor: Colors.overlayBg },
  drawer: {
    width: DRAWER_WIDTH,
    backgroundColor: Colors.drawerBg,
  },
  drawerHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: Spacing.md,
    paddingBottom: Spacing.lg,
    gap: Spacing.sm,
  },
  avatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: 'rgba(255,255,255,0.3)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: { color: Colors.white, fontWeight: Typography.weight.bold, fontSize: Typography.size.md },
  userInfo: { flex: 1 },
  userName: { color: Colors.white, fontWeight: Typography.weight.bold, fontSize: Typography.size.md },
  userRole: { color: Colors.white, fontSize: Typography.size.xs, opacity: 0.85 },
  closeBtn: { padding: Spacing.xs },
  menuList: { flex: 1 },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: Spacing.md,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: Colors.border,
    gap: Spacing.sm,
  },
  menuItemActive: { backgroundColor: Colors.primaryLight },
  menuIcon: { width: 24 },
  menuLabel: { flex: 1, fontSize: Typography.size.md, color: Colors.textPrimary, fontWeight: Typography.weight.medium },
  menuLabelActive: { color: Colors.primary, fontWeight: Typography.weight.semiBold },
  logoutRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: Spacing.md,
    paddingVertical: 16,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    gap: Spacing.sm,
  },
  logoutIcon: { width: 24 },
  logoutLabel: { flex: 1, fontSize: Typography.size.md, color: Colors.danger, fontWeight: Typography.weight.medium },
});
