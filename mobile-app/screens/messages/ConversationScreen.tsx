import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View, Text, TextInput, TouchableOpacity, ScrollView, StyleSheet, StatusBar,
  KeyboardAvoidingView, Platform, ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { Ionicons } from '@expo/vector-icons';
import type { NativeStackScreenProps } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import { LinearGradient } from 'expo-linear-gradient';
import { getConversation, sendMessage, type ChatMessage } from '../../services/messages';
import { useAuth } from '../../context/AuthContext';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Props = NativeStackScreenProps<RootStackParamList, 'Conversation'>;

function getInitials(name: string): string {
  const parts = name.trim().split(/\s+/);
  return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase();
}

function formatMessageTime(sentAt: string): string {
  const d = new Date(sentAt.replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return sentAt;
  return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

export default function ConversationScreen({ navigation, route }: Props) {
  const { contactName, contactRole, userId } = route.params;
  const { user } = useAuth();
  const scrollRef = useRef<ScrollView>(null);

  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [input, setInput] = useState('');
  const [sending, setSending] = useState(false);

  const loadMessages = useCallback(async (): Promise<void> => {
    setLoading(true);
    const result = await getConversation(userId);
    if (result.ok) { setMessages(result.data); setError(null); }
    else { setError(result.error); }
    setLoading(false);
  }, [userId]);

  useEffect(() => {
    void loadMessages();
  }, [loadMessages]);

  function scrollToEnd() {
    setTimeout(() => scrollRef.current?.scrollToEnd({ animated: true }), 80);
  }

  async function handleSend(): Promise<void> {
    const text = input.trim();
    if (!text || sending) return;
    setInput('');
    setSending(true);
    const result = await sendMessage(userId, text);
    if (result.ok) { await loadMessages(); scrollToEnd(); }
    setSending(false);
  }

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />

      {/* Header */}
      <LinearGradient colors={[Colors.primary, Colors.primaryDark]} start={{ x: 0, y: 0 }} end={{ x: 1, y: 0 }} style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
          <Ionicons name="arrow-back" size={22} color={Colors.white} />
        </TouchableOpacity>
        <View style={styles.contactAvatar}>
          <Text style={styles.contactInitials}>{getInitials(contactName)}</Text>
        </View>
        <View style={styles.contactInfo}>
          <Text style={styles.contactName}>{contactName}</Text>
          <Text style={styles.contactStatus}>{contactRole}</Text>
        </View>
        <TouchableOpacity style={styles.phoneBtn}>
          <Ionicons name="call" size={20} color={Colors.white} />
        </TouchableOpacity>
      </LinearGradient>

      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        {loading ? (
          <View style={styles.centered}>
            <ActivityIndicator size="large" color={Colors.primary} />
          </View>
        ) : error ? (
          <View style={styles.centered}>
            <Text style={styles.errorText}>{error}</Text>
            <TouchableOpacity style={styles.retryBtn} onPress={() => void loadMessages()}>
              <Text style={styles.retryTxt}>Retry</Text>
            </TouchableOpacity>
          </View>
        ) : (
          <ScrollView
            ref={scrollRef}
            contentContainerStyle={styles.chat}
            showsVerticalScrollIndicator={false}
            onContentSizeChange={scrollToEnd}
          >
            {messages.length === 0 ? (
              <Text style={styles.emptyText}>No messages yet. Say hello!</Text>
            ) : (
              messages.map(m => {
                const isMe = m.senderId === user?.id;
                return (
                  <View key={m.id} style={isMe ? styles.meBlock : styles.themBlock}>
                    {isMe ? (
                      <View style={styles.meBubble}><Text style={styles.meTxt}>{m.messageText}</Text></View>
                    ) : (
                      <View style={styles.themBubble}><Text style={styles.themTxt}>{m.messageText}</Text></View>
                    )}
                    <Text style={styles.msgTime}>{formatMessageTime(m.sentAt)}</Text>
                  </View>
                );
              })
            )}
          </ScrollView>
        )}

        <View style={styles.inputRow}>
          <TextInput
            style={styles.input}
            placeholder="Type your message..."
            placeholderTextColor={Colors.textMuted}
            value={input}
            onChangeText={setInput}
            onSubmitEditing={() => void handleSend()}
            returnKeyType="send"
          />
          <TouchableOpacity
            style={[styles.sendBtn, sending && styles.sendBtnDisabled]}
            onPress={() => void handleSend()}
            activeOpacity={0.8}
            disabled={sending}
          >
            {sending
              ? <ActivityIndicator size="small" color={Colors.white} />
              : <Ionicons name="send" size={18} color={Colors.white} />
            }
          </TouchableOpacity>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: '#F5F5F5' },
  flex: { flex: 1 },
  header: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: Spacing.md, paddingVertical: Spacing.md, gap: Spacing.sm },
  backBtn: { padding: 4 },
  contactAvatar: { width: 40, height: 40, borderRadius: 20, backgroundColor: 'rgba(255,255,255,0.3)', alignItems: 'center', justifyContent: 'center' },
  contactInitials: { color: Colors.white, fontWeight: Typography.weight.bold },
  contactInfo: { flex: 1 },
  contactName: { color: Colors.white, fontWeight: Typography.weight.bold, fontSize: Typography.size.md },
  contactStatus: { color: Colors.white, fontSize: Typography.size.xs, opacity: 0.9 },
  phoneBtn: { padding: 4 },
  centered: { flex: 1, alignItems: 'center', justifyContent: 'center', padding: Spacing.xl },
  errorText: { fontSize: Typography.size.sm, color: Colors.textSecondary, textAlign: 'center', marginBottom: Spacing.md },
  retryBtn: { backgroundColor: Colors.primary, borderRadius: Radius.md, paddingHorizontal: Spacing.lg, paddingVertical: Spacing.sm },
  retryTxt: { color: Colors.white, fontSize: Typography.size.sm, fontWeight: Typography.weight.medium },
  chat: { padding: Spacing.md, gap: Spacing.sm, paddingBottom: Spacing.lg },
  emptyText: { textAlign: 'center', color: Colors.textMuted, fontSize: Typography.size.sm, marginTop: Spacing.xl },
  meBlock: { alignItems: 'flex-end', gap: 2 },
  themBlock: { alignItems: 'flex-start', gap: 2 },
  meBubble: { backgroundColor: Colors.primary, borderRadius: Radius.lg, borderBottomRightRadius: 4, padding: Spacing.md, maxWidth: '80%' },
  meTxt: { color: Colors.white, fontSize: Typography.size.sm },
  themBubble: { backgroundColor: Colors.white, borderRadius: Radius.lg, borderBottomLeftRadius: 4, padding: Spacing.md, maxWidth: '80%', ...Shadows.card },
  themTxt: { color: Colors.textPrimary, fontSize: Typography.size.sm },
  msgTime: { fontSize: Typography.size.xs, color: Colors.textMuted },
  inputRow: { flexDirection: 'row', padding: Spacing.md, gap: Spacing.sm, borderTopWidth: 1, borderTopColor: Colors.border, backgroundColor: Colors.white },
  input: { flex: 1, backgroundColor: Colors.inputBg, borderRadius: Radius.full, paddingHorizontal: Spacing.md, paddingVertical: 10, fontSize: Typography.size.sm, color: Colors.textPrimary },
  sendBtn: { backgroundColor: Colors.primary, borderRadius: Radius.full, paddingHorizontal: Spacing.md, paddingVertical: 10, justifyContent: 'center' },
  sendBtnDisabled: { backgroundColor: Colors.textMuted },
});
