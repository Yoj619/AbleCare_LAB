import React, { useEffect, useState, useCallback } from 'react';
import { View, Text, TouchableOpacity, ScrollView, StyleSheet, StatusBar, RefreshControl } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import AppButton from '../../components/AppButton';
import { getInbox, type InboxConversation } from '../../services/messages';
import { Colors, Spacing, Typography, Radius } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

function getInitials(name: string): string {
  const parts = name.trim().split(/\s+/);
  return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase();
}

function formatRole(role: InboxConversation['role']): string {
  if (role === 'healthcare_provider') return 'Healthcare Provider';
  if (role === 'admin') return 'LGU Admin';
  return 'Caregiver';
}

function formatRelativeTime(isoLike: string): string {
  const date = new Date(isoLike.replace(' ', 'T'));
  if (Number.isNaN(date.getTime())) return isoLike;
  const minutes = Math.floor((Date.now() - date.getTime()) / 60000);
  if (minutes < 1) return 'Just now';
  if (minutes < 60) return `${minutes} min ago`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours} hr ago`;
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

export default function MessagesScreen() {
  const navigation = useNavigation<Nav>();
  const [conversations, setConversations] = useState<InboxConversation[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [refreshKey, setRefreshKey] = useState(0);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    let isMounted = true;
    setLoading(true);
    (async () => {
      const result = await getInbox();
      if (!isMounted) return;
      if (result.ok) { setConversations(result.data); setError(null); }
      else { setError(result.error); }
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

  const totalUnread = conversations.reduce((sum, c) => sum + c.unreadCount, 0);

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader
        onHamburgerPress={() => {}}
        onBellPress={() => navigation.navigate('Notifications')}
        showBack
        onBackPress={() => navigation.goBack()}
      />
      <ScrollView
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={handleRefresh} />}
      >
        {/* Banner */}
        <Card style={styles.banner}>
          <View style={styles.bannerRow}>
            <Ionicons name="chatbubble-outline" size={28} color={Colors.primary} />
            <View style={styles.bannerText}>
              <Text style={styles.bannerTitle}>Messages</Text>
              <Text style={styles.bannerSub}>{totalUnread} unread message{totalUnread === 1 ? '' : 's'}</Text>
            </View>
            {totalUnread > 0 && (
              <View style={styles.unreadBadge}>
                <Text style={styles.unreadText}>{totalUnread}</Text>
              </View>
            )}
          </View>
        </Card>

        {/* Conversations */}
        {loading ? (
          <Text style={styles.bannerSub}>Loading conversations...</Text>
        ) : error ? (
          <Text style={styles.bannerSub}>{error}</Text>
        ) : conversations.length === 0 ? (
          <Text style={styles.bannerSub}>No conversations yet.</Text>
        ) : (
          conversations.map(c => (
            <TouchableOpacity
              key={c.userId}
              onPress={() => navigation.navigate('Conversation', { contactName: c.name, contactRole: formatRole(c.role), userId: c.userId })}
              activeOpacity={0.7}
            >
              <Card style={styles.convCard}>
                <View style={styles.convRow}>
                  <View style={styles.avatarWrap}>
                    <View style={styles.avatar}>
                      <Text style={styles.avatarText}>{getInitials(c.name)}</Text>
                    </View>
                    {c.unreadCount > 0 && <View style={styles.onlineDot} />}
                  </View>
                  <View style={styles.convInfo}>
                    <Text style={styles.convName}>{c.name}</Text>
                    <Text style={styles.convRole}>{formatRole(c.role)}</Text>
                    <Text style={styles.convMsg} numberOfLines={2}>{c.lastMessage}</Text>
                  </View>
                  <View style={styles.convMeta}>
                    <Text style={styles.convTime}>{formatRelativeTime(c.lastMessageAt)}</Text>
                    {c.unreadCount > 0 && <View style={styles.unreadDot} />}
                  </View>
                </View>
              </Card>
            </TouchableOpacity>
          ))
        )}

        <AppButton label="+ New Message" onPress={() => navigation.navigate('NewMessage')} variant="outline" />
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  content: { padding: Spacing.md, gap: Spacing.md, paddingBottom: Spacing.xl },
  banner: {},
  bannerRow: { flexDirection: 'row', alignItems: 'center', gap: Spacing.md },
  bannerIcon: { fontSize: 28 },
  bannerText: { flex: 1 },
  bannerTitle: { fontSize: Typography.size.lg, fontWeight: Typography.weight.bold, color: Colors.dark },
  bannerSub: { fontSize: Typography.size.sm, color: Colors.textSecondary },
  unreadBadge: { backgroundColor: Colors.badgeRed, borderRadius: Radius.full, minWidth: 26, height: 26, alignItems: 'center', justifyContent: 'center', paddingHorizontal: 6 },
  unreadText: { color: Colors.white, fontSize: Typography.size.xs, fontWeight: Typography.weight.bold },
  convCard: {},
  convRow: { flexDirection: 'row', gap: Spacing.md },
  avatarWrap: { position: 'relative' },
  avatar: { width: 52, height: 52, borderRadius: 26, backgroundColor: Colors.primaryLight, alignItems: 'center', justifyContent: 'center' },
  avatarText: { color: Colors.primary, fontWeight: Typography.weight.bold, fontSize: Typography.size.md },
  onlineDot: { position: 'absolute', bottom: 2, right: 2, width: 12, height: 12, borderRadius: 6, backgroundColor: Colors.success, borderWidth: 2, borderColor: Colors.white },
  convInfo: { flex: 1 },
  convName: { fontSize: Typography.size.md, fontWeight: Typography.weight.semiBold, color: Colors.dark },
  convRole: { fontSize: Typography.size.xs, color: Colors.textSecondary, marginBottom: 4 },
  convMsg: { fontSize: Typography.size.sm, color: Colors.textMuted, lineHeight: 18 },
  convMeta: { alignItems: 'flex-end', gap: Spacing.xs },
  convTime: { fontSize: Typography.size.xs, color: Colors.textMuted },
  unreadDot: { width: 10, height: 10, borderRadius: 5, backgroundColor: Colors.primary },
});
