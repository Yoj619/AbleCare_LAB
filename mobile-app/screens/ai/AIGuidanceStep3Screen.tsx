import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, ScrollView, StyleSheet, StatusBar, KeyboardAvoidingView, Platform } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const MESSAGES = [
  { id: '1', from: 'user', text: 'When to take medications?', time: '2:15 PM' },
  {
    id: '2', from: 'ai', time: '2:20 PM',
    text: 'When to Take Medications\n\n• Take medications exactly as prescribed by your doctor\n• Follow the recommended time and dosage daily\n• Some medicines are taken after meals, while others may be before meals\n• Try to take medications at the same time each day\n• Do not skip doses or stop taking medicine without medical advice\n• Use reminders or pill organizers if needed to avoid missed doses',
  },
];

export default function AIGuidanceStep3Screen() {
  const navigation = useNavigation<Nav>();
  const [input, setInput] = useState('');

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader onHamburgerPress={() => {}} onBellPress={() => navigation.navigate('Notifications')} />
      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <LinearGradient colors={[Colors.primary, Colors.primaryDark]} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }} style={styles.banner}>
          <Ionicons name="sparkles" size={28} color={Colors.white} />
          <View>
            <Text style={styles.bannerTitle}>AI Health Guidance</Text>
            <Text style={styles.bannerSub}>Powered by AbleCare AI</Text>
          </View>
        </LinearGradient>
        <ScrollView contentContainerStyle={styles.chat} showsVerticalScrollIndicator={false}>
          {MESSAGES.map(m => (
            <View key={m.id} style={m.from === 'user' ? styles.userBlock : styles.aiBlock}>
              {m.from === 'user' && <View style={styles.userBubble}><Text style={styles.userTxt}>{m.text}</Text></View>}
              <Text style={styles.time}>{m.time}</Text>
              {m.from === 'ai' && <View style={styles.aiBubble}><Text style={styles.aiTxt}>{m.text}</Text></View>}
            </View>
          ))}
        </ScrollView>
        <View style={styles.inputRow}>
          <TextInput style={styles.input} placeholder="Type your message..." placeholderTextColor={Colors.textMuted} value={input} onChangeText={setInput} />
          <TouchableOpacity style={styles.sendBtn} activeOpacity={0.8}><Ionicons name="send" size={16} color={Colors.white} /></TouchableOpacity>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  flex: { flex: 1 },
  banner: { marginHorizontal: Spacing.md, marginTop: Spacing.md, borderRadius: Radius.lg, padding: Spacing.md, flexDirection: 'row', alignItems: 'center', gap: Spacing.md },
  bannerIcon: { fontSize: 28 },
  bannerTitle: { color: Colors.white, fontSize: Typography.size.md, fontWeight: Typography.weight.bold },
  bannerSub: { color: Colors.white, fontSize: Typography.size.xs, opacity: 0.85 },
  chat: { padding: Spacing.md, gap: Spacing.sm, paddingBottom: Spacing.lg },
  userBlock: { alignItems: 'flex-end', gap: 4 },
  aiBlock: { alignItems: 'flex-start', gap: 4 },
  userBubble: { backgroundColor: Colors.primary, borderRadius: Radius.lg, borderBottomRightRadius: 4, padding: Spacing.md, maxWidth: '80%' },
  userTxt: { color: Colors.white, fontSize: Typography.size.sm },
  aiBubble: { backgroundColor: Colors.white, borderRadius: Radius.lg, borderBottomLeftRadius: 4, padding: Spacing.md, maxWidth: '85%', ...Shadows.card },
  aiTxt: { color: Colors.textPrimary, fontSize: Typography.size.sm, lineHeight: 20 },
  time: { fontSize: Typography.size.xs, color: Colors.textMuted },
  inputRow: { flexDirection: 'row', padding: Spacing.md, gap: Spacing.sm, borderTopWidth: 1, borderTopColor: Colors.border, backgroundColor: Colors.white },
  input: { flex: 1, backgroundColor: Colors.inputBg, borderRadius: Radius.full, paddingHorizontal: Spacing.md, paddingVertical: 10, fontSize: Typography.size.sm, color: Colors.textPrimary },
  sendBtn: { backgroundColor: Colors.primary, borderRadius: Radius.full, paddingHorizontal: Spacing.md, paddingVertical: 10, justifyContent: 'center' },
  sendTxt: { color: Colors.white, fontSize: Typography.size.sm, fontWeight: Typography.weight.semiBold },
});
