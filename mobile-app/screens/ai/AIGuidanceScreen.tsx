import React, { useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  ScrollView,
  StyleSheet,
  StatusBar,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import DrawerMenu from '../../components/DrawerMenu';
import AppButton from '../../components/AppButton';
import { Colors, Spacing, Typography, Radius, Shadows } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

const QUICK_QUESTIONS = [
  { label: 'How to manage blood pressure?', question: 'Patient has high blood pressure and feeling dizzy. How should I manage this?' },
  { label: 'What foods are good for stroke recovery?', question: 'What foods should I give my patient who is recovering from a stroke?' },
  { label: 'When to take medications?', question: 'When is the right time to give medications to my elderly patient?' },
];

export default function AIGuidanceScreen() {
  const navigation = useNavigation<Nav>();
  const [drawerOpen, setDrawerOpen] = useState(false);

  function handleDrawerNav(key: string) {
    switch (key) {
      case 'Dashboard':        navigation.navigate('Main', { screen: 'Home' }); break;
      case 'Patient':          navigation.navigate('Main', { screen: 'Patient' }); break;
      case 'HealthRecords':    navigation.navigate('HealthRecords'); break;
      case 'TherapySchedule':  navigation.navigate('TherapySchedule'); break;
      case 'Messages':         navigation.navigate('Messages'); break;
      case 'RecommendedClinics': navigation.navigate('RecommendedClinics'); break;
      case 'EmergencyAlert':   navigation.navigate('Main', { screen: 'Emergency' }); break;
    }
  }

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader
        onHamburgerPress={() => setDrawerOpen(true)}
        onBellPress={() => navigation.navigate('Notifications')}
      />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>

        {/* Banner */}
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

        {/* Quick Questions */}
        <Text style={styles.sectionTitle}>Quick Questions</Text>
        {QUICK_QUESTIONS.map(q => (
          <TouchableOpacity
            key={q.label}
            style={styles.questionRow}
            onPress={() => navigation.navigate('AIGuidanceResult', { initialQuestion: q.question })}
            activeOpacity={0.7}
          >
            <Text style={styles.questionLabel}>{q.label}</Text>
            <Ionicons name="chevron-forward" size={20} color={Colors.primary} />
          </TouchableOpacity>
        ))}

        {/* Ask AI button */}
        <View style={styles.btnWrap}>
          <AppButton
            icon="sparkles" label="Ask AI Assistant"
            onPress={() => navigation.navigate('AIGuidanceResult', {})}
          />
        </View>

        {/* Info note */}
        <View style={styles.noteBox}>
          <Text style={styles.noteText}>
            ⓘ  AI guidance is preliminary and not a substitute for professional medical advice.
            Always consult a licensed healthcare provider for diagnosis and treatment.
          </Text>
        </View>
      </ScrollView>

      <DrawerMenu
        visible={drawerOpen}
        activeKey="AIHelp"
        onClose={() => setDrawerOpen(false)}
        onNavigate={handleDrawerNav}
        onLogout={() => { setDrawerOpen(false); navigation.navigate('Logout'); }}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  content: { padding: Spacing.md, gap: Spacing.md, paddingBottom: Spacing.xl },
  banner: {
    borderRadius: Radius.lg,
    padding: Spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
    gap: Spacing.md,
  },
  bannerIcon: { fontSize: 32 },
  bannerText: { flex: 1 },
  bannerTitle: { color: Colors.white, fontSize: Typography.size.md, fontWeight: Typography.weight.bold },
  bannerSub: { color: Colors.white, fontSize: Typography.size.xs, opacity: 0.85, marginTop: 2 },
  sectionTitle: {
    fontSize: Typography.size.md,
    fontWeight: Typography.weight.semiBold,
    color: Colors.dark,
  },
  questionRow: {
    backgroundColor: Colors.white,
    borderRadius: Radius.md,
    borderWidth: 1,
    borderColor: Colors.border,
    flexDirection: 'row',
    alignItems: 'center',
    padding: Spacing.md,
    ...Shadows.card,
  },
  questionLabel: { flex: 1, fontSize: Typography.size.sm, color: Colors.textPrimary },
  questionArrow: { color: Colors.primary, fontSize: 20, fontWeight: Typography.weight.bold },
  btnWrap: { marginTop: Spacing.xs },
  noteBox: {
    backgroundColor: Colors.primaryLight,
    borderRadius: Radius.md,
    padding: Spacing.md,
  },
  noteText: {
    fontSize: Typography.size.xs,
    color: Colors.textSecondary,
    lineHeight: 18,
  },
});
