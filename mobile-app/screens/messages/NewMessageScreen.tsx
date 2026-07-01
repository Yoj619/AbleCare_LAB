import React, { useState } from 'react';
import { View, Text, ScrollView, StyleSheet, StatusBar } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import type { RootStackParamList } from '../../navigation/types';
import AppHeader from '../../components/AppHeader';
import Card from '../../components/Card';
import AppInput from '../../components/AppInput';
import AppButton from '../../components/AppButton';
import { Colors, Spacing, Typography } from '../../constants/theme';

type Nav = NativeStackNavigationProp<RootStackParamList>;

export default function NewMessageScreen() {
  const navigation = useNavigation<Nav>();
  const [to, setTo] = useState('');
  const [message, setMessage] = useState('');

  return (
    <SafeAreaView style={styles.safe} edges={['bottom']}>
      <StatusBar barStyle="light-content" backgroundColor={Colors.primary} />
      <AppHeader showBack onBackPress={() => navigation.goBack()} onHamburgerPress={() => {}} onBellPress={() => {}} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <Card style={styles.banner}>
          <Text style={styles.bannerTitle}>New Message</Text>
          <Text style={styles.bannerSub}>Send a message to your care team</Text>
        </Card>
        <Card>
          <AppInput label="To" leftIcon="person-outline" placeholder="Search contact name..." value={to} onChangeText={setTo} />
          <AppInput label="Message" leftIcon="chatbubble-outline" placeholder="Type your message here..." value={message} onChangeText={setMessage} multiline numberOfLines={5} />
          <AppButton
            label="Send Message"
            onPress={() => navigation.navigate('Conversation', { contactName: to || 'Unknown', contactRole: 'Healthcare Provider', userId: 0 })}
          />
          <View style={{ height: Spacing.sm }} />
          <AppButton label="Cancel" variant="outline" onPress={() => navigation.goBack()} />
        </Card>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: Colors.background },
  content: { padding: Spacing.md, gap: Spacing.md, paddingBottom: Spacing.xl },
  banner: {},
  bannerTitle: { fontSize: Typography.size.lg, fontWeight: Typography.weight.bold, color: Colors.dark },
  bannerSub: { fontSize: Typography.size.sm, color: Colors.textSecondary, marginTop: 2 },
});
