import React, { useState, useEffect, useCallback } from 'react';
import { View, Text, TouchableOpacity, ScrollView, StyleSheet, StatusBar, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import DrawerMenu from '../../components/DrawerMenu';
import Card from '../../components/Card';
import { getNotifications, markAsRead, type AppNotification, type NotificationType } from '../../services/notifications';
import { useAuth } from '../../context/AuthContext';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

function getGreeting(): string {
  const h = new Date().getHours();
  if (h < 12) return 'Good Morning!';
  if (h < 18) return 'Good Afternoon!';
  return 'Good Evening!';
}

function formatCurrentDate(): string {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long', month: 'long', day: 'numeric', year: 'numeric',
  });
}

type Nav = NativeStackNavigationProp<RootStackParamList>;

const TYPE_ICON: Record<NotificationType, keyof typeof Ionicons.glyphMap> = {
  emergency:    'warning-outline',
  consultation: 'medkit-outline',
  therapy:      'calendar-outline',
  message:      'chatbubble-outline',
  system:       'information-circle-outline',
};

function formatNotifDate(createdAt: string): string {
  const date = new Date(createdAt.replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return createdAt;
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

export default function NotificationsScreen() {
  const navigation = useNavigation<Nav>();
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [notifications, setNotifications] = useState<AppNotification[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [refreshKey, setRefreshKey] = useState(0);
  const [refreshing, setRefreshing] = useState(false);
  const { user } = useAuth();

  useEffect(() => {
    let isMounted = true;
    setLoading(true);
    (async () => {
      const result = await getNotifications();
      if (!isMounted) return;
      if (result.ok) {
        setNotifications(result.data);
        setError(null);
      } else {
        setError(result.error);
      }
      setLoading(false);
    })();
    return () => { isMounted = false; };
  }, [refreshKey]);

  useEffect(() => {
    if (!loading) setRefreshing(false);
  }, [loading]);

  const handleRefresh = useCallback((): void => {
    setRefreshing(true);
    setRefreshKey(k => k + 1);
  }, []);

  async function handlePress(notification: AppNotification) {
    if (!notification.isRead) {
      const result = await markAsRead(notification.id);
      if (result.ok) {
        setNotifications(prev => prev.map(n => n.id === notification.id ? { ...n, isRead: true } : n));
      }
    }

    if (notification.type === 'therapy') navigation.navigate('TherapySchedule');
    else if (notification.type === 'message') navigation.navigate('Messages');
    else if (notification.type === 'emergency') navigation.navigate('Main', { screen: 'Emergency' });
  }

  function handleDrawerNav(key: string) {
    switch (key) {
      case 'Dashboard': navigation.navigate('Main', { screen: 'Home' }); break;
      case 'Patient': navigation.navigate('Main', { screen: 'Patient' }); break;
      case 'HealthRecords': navigation.navigate('HealthRecords'); break;
      case 'AIHelp': navigation.navigate('Main', { screen: 'AIHelp' }); break;
      case 'TherapySchedule': navigation.navigate('TherapySchedule'); break;
      case 'Messages': navigation.navigate('Messages'); break;
      case 'RecommendedClinics': navigation.navigate('RecommendedClinics'); break;
      case 'EmergencyAlert': navigation.navigate('Main', { screen: 'Emergency' }); break;
    }
  }

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader
        onHamburgerPress={() => setDrawerOpen(true)}
        onBellPress={() => {}}
      />
      <ScrollView
        style={styles.scroll}
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />}
      >
        {/* Greeting */}
        <Card style={styles.greetCard}>
          <View style={styles.greetRow}>
            <View style={styles.avatar}>
              <Text style={styles.avatarText}>
                {user ? `${user.firstName.charAt(0)}${user.lastName.charAt(0)}` : '—'}
              </Text>
            </View>
            <View>
              <Text style={styles.greetTitle}>{getGreeting()}</Text>
              <Text style={styles.greetName}>{user ? `${user.firstName} ${user.lastName}` : '—'}</Text>
              <Text style={styles.greetDate}>{formatCurrentDate()}</Text>
            </View>
          </View>
        </Card>

        <Text style={styles.sectionTitle}>Notifications</Text>

        {loading ? (
          <Text style={styles.notifSub}>Loading notifications...</Text>
        ) : error ? (
          <Text style={styles.notifSub}>{error}</Text>
        ) : notifications.length === 0 ? (
          <Text style={styles.notifSub}>No notifications yet.</Text>
        ) : (
          <Card style={styles.notifCard} padding={0}>
            {notifications.map((n, i) => (
              <React.Fragment key={n.id}>
                {i > 0 && <View style={styles.divider} />}
                <TouchableOpacity
                  style={styles.notifRow}
                  onPress={() => handlePress(n)}
                  activeOpacity={0.7}
                >
                  <View style={styles.iconWrap}>
                    <Ionicons name={TYPE_ICON[n.type]} size={18} color={Colors.primary} />
                  </View>
                  <View style={styles.notifText}>
                    <Text style={styles.notifLabel}>{n.title}</Text>
                    <Text style={styles.notifSub}>{n.message}</Text>
                  </View>
                  <Text style={styles.notifSub}>{formatNotifDate(n.createdAt)}</Text>
                  {!n.isRead && <View style={styles.unreadDot} />}
                  <Ionicons name="chevron-forward" size={20} color={Colors.textMuted} />
                </TouchableOpacity>
              </React.Fragment>
            ))}
          </Card>
        )}
      </ScrollView>

      <DrawerMenu
        visible={drawerOpen}
        onClose={() => setDrawerOpen(false)}
        onNavigate={handleDrawerNav}
        onLogout={() => { setDrawerOpen(false); navigation.navigate('Logout'); }}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  scroll: { flex: 1 },
  content: { padding: Spacing.md, gap: Spacing.md, paddingBottom: Spacing.xl },
  greetCard: {},
  greetRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.md },
  avatar: {
    width: 52, height: 52, borderRadius: 26,
    backgroundColor: Colors.primaryLight,
    alignItems: 'center', justifyContent: 'center',
  },
  avatarText: { color: Colors.primary, fontWeight: Typography.weight.bold, fontSize: Typography.size.md },
  greetTitle: { fontSize: Typography.size.lg, fontWeight: Typography.weight.bold, color: Colors.dark },
  greetName: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  greetDate: { fontSize: Typography.size.xs, color: Colors.textMuted, marginTop: 2 },
  sectionTitle: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark },
  notifCard: {},
  notifRow: { flexDirection: 'row', alignItems: 'center', padding: Spacing.md, gap: Spacing.md },
  iconWrap: {
    width: 40, height: 40, borderRadius: 20,
    backgroundColor: Colors.primaryLight, alignItems: 'center', justifyContent: 'center',
  },
  notifIcon: { fontSize: 18 },
  notifText: { flex: 1 },
  notifLabel: { fontSize: Typography.size.md, fontWeight: Typography.weight.medium, color: Colors.textPrimary },
  notifSub: { fontSize: Typography.size.xs, color: Colors.textMuted, marginTop: 2 },
  badge: {
    backgroundColor: Colors.badgeRed, borderRadius: Radius.full,
    minWidth: 22, height: 22, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 6,
  },
  badgeText: { color: Colors.white, fontSize: Typography.size.xs, fontWeight: Typography.weight.bold },
  unreadDot: { width: 8, height: 8, borderRadius: 4, backgroundColor: Colors.primary },
  divider: { height: 1, backgroundColor: Colors.border },
});
