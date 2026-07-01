import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  ScrollView,
  StyleSheet,
  StatusBar,
  KeyboardAvoidingView,
  Platform,
  ActivityIndicator,
  Animated,
} from 'react-native';
import { Audio } from 'expo-av';
import { SafeAreaView } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import {
  requestAIGuidance,
  SEVERITY_CONFIG,
  type AIGuidanceResponse,
} from '../../services/aiGuidance';
import {
  requestMicPermission,
  startRecording,
  stopRecording,
  transcribeAudio,
  type StartRecordingResult,
} from '../../services/voiceRecording';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Nav   = NativeStackNavigationProp<RootStackParamList>;
type Route = RouteProp<RootStackParamList, 'AIGuidanceResult'>;

// ─── Message types ────────────────────────────────────────────────────────────

type UserMessage = {
  id: string;
  from: 'user';
  text: string;
  time: string;
};

type AIMessage = {
  id: string;
  from: 'ai';
  text: string;
  time: string;
  severity?: AIGuidanceResponse['severity'];
  disclaimer?: string;
};

type LoadingMessage = {
  id: string;
  from: 'loading';
};

type Message = UserMessage | AIMessage | LoadingMessage;

// ─── Helpers ──────────────────────────────────────────────────────────────────

function now(): string {
  return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

// Patient context passed along with every query (demo values from PatientProfile)
const PATIENT_CONTEXT = {
  disabilityType: 'Post-stroke Recovery',
  medicalHistory: 'Hypertension, managed with Aspirin 100mg and Losartan 50mg',
};

// ─── Sub-components ───────────────────────────────────────────────────────────

function SeverityBadge({ severity }: { severity: AIGuidanceResponse['severity'] }) {
  const cfg = SEVERITY_CONFIG[severity];
  return (
    <View style={[badgeStyles.badge, { backgroundColor: cfg.bgColor }]}>
      <Ionicons name={cfg.icon} size={14} color={cfg.color} />
      <Text style={[badgeStyles.text, { color: cfg.color }]}>{cfg.label}</Text>
    </View>
  );
}

const badgeStyles = StyleSheet.create({
  badge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    alignSelf: 'flex-start',
    borderRadius: Radius.full,
    paddingHorizontal: 10,
    paddingVertical: 4,
    marginBottom: Spacing.xs,
  },
  text: { fontSize: Typography.size.xs, fontWeight: Typography.weight.bold },
});

function DisclaimerRow({ text }: { text: string }) {
  return (
    <View style={disclaimerStyles.row}>
      <Text style={disclaimerStyles.icon}>ⓘ</Text>
      <Text style={disclaimerStyles.text}>{text}</Text>
    </View>
  );
}

const disclaimerStyles = StyleSheet.create({
  row: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 4,
    marginTop: Spacing.xs,
    paddingTop: Spacing.xs,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
  },
  icon: { fontSize: Typography.size.xs, color: Colors.textMuted, marginTop: 1 },
  text: { flex: 1, fontSize: Typography.size.xs, color: Colors.textMuted, lineHeight: 16 },
});

// ─── Main screen ──────────────────────────────────────────────────────────────

export default function AIGuidanceResultScreen() {
  const navigation = useNavigation<Nav>();
  const route      = useRoute<Route>();
  const initialQ   = route.params?.initialQuestion ?? '';

  const scrollRef = useRef<ScrollView>(null);

  const [messages, setMessages] = useState<Message[]>([
    {
      id: '0',
      from: 'ai',
      text: 'Hello! I\'m AbleCare\'s AI Health Assistant. Describe your patient\'s symptoms and I\'ll provide preliminary care guidance.',
      time: now(),
    },
  ]);
  const [input,       setInput]       = useState(initialQ);
  const [loading,     setLoading]     = useState(false);
  const [recording,   setRecording]   = useState<Audio.Recording | null>(null);
  const [transcribing, setTranscribing] = useState(false);

  // Pulsing animation for the recording indicator
  const pulseAnim = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    if (recording) {
      Animated.loop(
        Animated.sequence([
          Animated.timing(pulseAnim, { toValue: 1.35, duration: 600, useNativeDriver: true }),
          Animated.timing(pulseAnim, { toValue: 1,    duration: 600, useNativeDriver: true }),
        ]),
      ).start();
    } else {
      pulseAnim.stopAnimation();
      pulseAnim.setValue(1);
    }
  }, [recording, pulseAnim]);

  async function handleVoicePress() {
    if (transcribing || loading) return;

    if (recording) {
      // ── Stop recording ──────────────────────────────────────────────────────
      const current = recording;
      setRecording(null);
      setTranscribing(true);

      const audio = await stopRecording(current);
      if (!audio) {
        setTranscribing(false);
        return;
      }

      const result = await transcribeAudio(audio.uri, audio.mimeType);
      setTranscribing(false);

      if (result.ok) {
        // Auto-send the transcribed question
        handleSend(result.transcript);
      } else {
        setMessages(prev => [
          ...prev,
          {
            id: String(Date.now()),
            from: 'ai' as const,
            text: `Voice error: ${result.error}`,
            time: now(),
          },
        ]);
      }
    } else {
      // ── Start recording ─────────────────────────────────────────────────────
      const granted = await requestMicPermission();
      if (!granted) {
        setMessages(prev => [
          ...prev,
          {
            id: String(Date.now()),
            from: 'ai' as const,
            text: 'Microphone permission is required for voice input. Please enable it in your device settings.',
            time: now(),
          },
        ]);
        return;
      }
      const result: StartRecordingResult = await startRecording();
      if (!result.ok) {
        setMessages(prev => [
          ...prev,
          {
            id: String(Date.now()),
            from: 'ai' as const,
            text: `Could not start recording: ${result.error}`,
            time: now(),
          },
        ]);
        return;
      }
      setRecording(result.recording);
    }
  }

  // Auto-send the initial question that came from the quick-question shortcut
  useEffect(() => {
    if (initialQ.trim()) {
      handleSend(initialQ);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  function scrollToBottom() {
    setTimeout(() => scrollRef.current?.scrollToEnd({ animated: true }), 100);
  }

  async function handleSend(text?: string) {
    const msgText = (text ?? input).trim();
    if (!msgText || loading) return;

    setInput('');
    setLoading(true);

    const userMsg: UserMessage = {
      id: String(Date.now()),
      from: 'user',
      text: msgText,
      time: now(),
    };

    const loadingMsg: LoadingMessage = {
      id: 'loading',
      from: 'loading',
    };

    setMessages(prev => [...prev, userMsg, loadingMsg]);
    scrollToBottom();

    // ── Call the real API ────────────────────────────────────────────────────
    const result = await requestAIGuidance({
      symptoms: msgText,
      ...PATIENT_CONTEXT,
    });

    setMessages(prev => {
      const withoutLoader = prev.filter(m => m.from !== 'loading');

      const aiMsg: AIMessage = result.ok
        ? {
            id: String(Date.now() + 1),
            from: 'ai',
            text: result.data.guidance,
            time: now(),
            severity: result.data.severity,
            disclaimer: result.data.disclaimer,
          }
        : {
            id: String(Date.now() + 1),
            from: 'ai',
            text: result.error,
            time: now(),
          };

      return [...withoutLoader, aiMsg];
    });

    setLoading(false);
    scrollToBottom();
  }

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader
        onHamburgerPress={() => {}}
        onBellPress={() => navigation.navigate('Notifications')}
      />

      <KeyboardAvoidingView
        style={styles.flex}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={0}
      >
        {/* AI banner */}
        <LinearGradient
          colors={[Colors.primary, Colors.primaryDark]}
          start={{ x: 0, y: 0 }}
          end={{ x: 1, y: 1 }}
          style={styles.banner}
        >
          <Ionicons name="sparkles" size={28} color={Colors.white} />
          <View style={styles.bannerText}>
            <Text style={styles.bannerTitle}>AI Health Guidance</Text>
            <Text style={styles.bannerSub}>Powered by Google Gemini · AbleCare AI</Text>
          </View>
        </LinearGradient>

        {/* Patient context pill */}
        <View style={styles.contextPill}>
          <Ionicons name="person-outline" size={12} color={Colors.textSecondary} />
          <Text style={styles.contextText}>
            Patient: Juan dela Cruz · Post-stroke Recovery · Hypertension
          </Text>
        </View>

        {/* Chat messages */}
        <ScrollView
          ref={scrollRef}
          contentContainerStyle={styles.chat}
          showsVerticalScrollIndicator={false}
          onContentSizeChange={scrollToBottom}
        >
          {messages.map(m => {
            if (m.from === 'loading') {
              return (
                <View key="loading" style={styles.aiBlock}>
                  <View style={[styles.aiBubble, styles.loadingBubble]}>
                    <ActivityIndicator size="small" color={Colors.primary} />
                    <Text style={styles.loadingText}>AbleCare AI is thinking…</Text>
                  </View>
                </View>
              );
            }

            if (m.from === 'user') {
              return (
                <View key={m.id} style={styles.userBlock}>
                  <View style={styles.userBubble}>
                    <Text style={styles.userTxt}>{m.text}</Text>
                  </View>
                  <Text style={styles.timeStamp}>{m.time}</Text>
                </View>
              );
            }

            // AI message
            const aiMsg = m as AIMessage;
            return (
              <View key={aiMsg.id} style={styles.aiBlock}>
                <View style={styles.aiBubble}>
                  {aiMsg.severity && <SeverityBadge severity={aiMsg.severity} />}
                  <Text style={styles.aiTxt}>{aiMsg.text}</Text>
                  {aiMsg.disclaimer && <DisclaimerRow text={aiMsg.disclaimer} />}
                </View>
                <Text style={styles.timeStamp}>{aiMsg.time}</Text>
              </View>
            );
          })}
        </ScrollView>

        {/* Recording banner */}
        {(recording || transcribing) && (
          <View style={styles.recordingBanner}>
            {transcribing ? (
              <>
                <ActivityIndicator size="small" color={Colors.primary} />
                <Text style={styles.recordingText}>Transcribing…</Text>
              </>
            ) : (
              <>
                <Animated.View style={[styles.recordingDot, { transform: [{ scale: pulseAnim }] }]} />
                <Text style={styles.recordingText}>Recording… tap mic to stop</Text>
              </>
            )}
          </View>
        )}

        {/* Input row */}
        <View style={styles.inputRow}>
          {/* Mic button */}
          <TouchableOpacity
            style={[
              styles.micBtn,
              recording   && styles.micBtnActive,
              transcribing && styles.micBtnTranscribing,
            ]}
            onPress={handleVoicePress}
            activeOpacity={0.8}
            disabled={loading || transcribing}
          >
            {transcribing
              ? <ActivityIndicator size="small" color={Colors.white} />
              : <Ionicons name={recording ? 'stop' : 'mic'} size={18} color={Colors.white} />
            }
          </TouchableOpacity>

          <TextInput
            style={styles.input}
            placeholder="Describe symptoms or ask a question…"
            placeholderTextColor={Colors.textMuted}
            value={input}
            onChangeText={setInput}
            onSubmitEditing={() => handleSend()}
            returnKeyType="send"
            editable={!loading && !recording && !transcribing}
            multiline
          />
          <TouchableOpacity
            style={[styles.sendBtn, (loading || !!recording || transcribing) && styles.sendBtnDisabled]}
            onPress={() => handleSend()}
            activeOpacity={0.8}
            disabled={loading || !!recording || transcribing}
          >
            {loading
              ? <ActivityIndicator size="small" color={Colors.white} />
              : <Ionicons name="send" size={16} color={Colors.white} />
            }
          </TouchableOpacity>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  flex: { flex: 1 },

  banner: {
    marginHorizontal: Spacing.md,
    marginTop: Spacing.sm,
    borderRadius: Radius.lg,
    padding: Spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.md,
  },
  bannerIcon: { fontSize: 28 },
  bannerText: { flex: 1 },
  bannerTitle: { color: Colors.white, fontSize: Typography.size.md, fontWeight: Typography.weight.bold },
  bannerSub: { color: Colors.white, fontSize: Typography.size.xs, opacity: 0.85, marginTop: 2 },

  contextPill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    marginHorizontal: Spacing.md,
    marginTop: Spacing.sm,
    backgroundColor: Colors.primaryLight,
    borderRadius: Radius.full,
    paddingHorizontal: Spacing.md,
    paddingVertical: 6,
  },
  contextText: {
    fontSize: Typography.size.xs,
    color: Colors.primary,
    fontWeight: Typography.weight.medium,
  },

  chat: {
    flexGrow: 1,
    padding: Spacing.md,
    gap: Spacing.md,
    paddingBottom: Spacing.lg,
  },

  userBlock: { alignItems: 'flex-end', gap: 4 },
  aiBlock:   { alignItems: 'flex-start', gap: 4 },

  userBubble: {
    backgroundColor: Colors.primary,
    borderRadius: Radius.lg,
    borderBottomRightRadius: 4,
    padding: Spacing.md,
    maxWidth: '82%',
  },
  userTxt: { color: Colors.white, fontSize: Typography.size.sm, lineHeight: 20 },

  aiBubble: {
    backgroundColor: Colors.white,
    borderRadius: Radius.lg,
    borderBottomLeftRadius: 4,
    padding: Spacing.md,
    maxWidth: '88%',
    ...Shadows.card,
  },
  aiTxt: { color: Colors.textPrimary, fontSize: Typography.size.sm, lineHeight: 20 },

  loadingBubble: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.sm,
    paddingVertical: Spacing.sm,
  },
  loadingText: { fontSize: Typography.size.sm, color: Colors.textMuted },

  timeStamp: { fontSize: Typography.size.xs, color: Colors.textMuted },

  inputRow: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    padding: Spacing.md,
    gap: Spacing.sm,
    borderTopWidth: 1,
    borderTopColor: Colors.border,
    backgroundColor: Colors.white,
  },
  input: {
    flex: 1,
    backgroundColor: Colors.inputBg,
    borderRadius: Radius.lg,
    paddingHorizontal: Spacing.md,
    paddingVertical: 10,
    fontSize: Typography.size.sm,
    color: Colors.textPrimary,
    maxHeight: 100,
  },
  sendBtn: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: Colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  sendBtnDisabled: { backgroundColor: Colors.textMuted },
  sendTxt: { color: Colors.white, fontSize: Typography.size.md, fontWeight: Typography.weight.bold },

  micBtn: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: Colors.inputBg,
    borderWidth: 1,
    borderColor: Colors.border,
    alignItems: 'center',
    justifyContent: 'center',
  },
  micBtnActive:       { backgroundColor: '#FF4444', borderColor: '#FF4444' },
  micBtnTranscribing: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  micIcon: { fontSize: 18 },

  recordingBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.sm,
    paddingHorizontal: Spacing.md,
    paddingVertical: 8,
    backgroundColor: '#FFF5F5',
    borderTopWidth: 1,
    borderTopColor: '#FFD0D0',
  },
  recordingDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#FF4444',
  },
  recordingText: {
    fontSize: Typography.size.xs,
    color: '#CC0000',
    fontWeight: Typography.weight.medium,
  },
});
