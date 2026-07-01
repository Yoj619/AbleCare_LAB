import { Audio } from 'expo-av';
import { readAsStringAsync } from 'expo-file-system/legacy';
import { Platform } from 'react-native';
import { ENDPOINTS } from '../config/api';

// ─── Permission ───────────────────────────────────────────────────────────────

export async function requestMicPermission(): Promise<boolean> {
  const { granted } = await Audio.requestPermissionsAsync();
  return granted;
}

// ─── Recording lifecycle ──────────────────────────────────────────────────────

export type StartRecordingResult =
  | { ok: true; recording: Audio.Recording }
  | { ok: false; error: string };

export async function startRecording(): Promise<StartRecordingResult> {
  try {
    await Audio.setAudioModeAsync({
      allowsRecordingIOS: true,
      playsInSilentModeIOS: true,
    });

    const { recording } = await Audio.Recording.createAsync(
      Audio.RecordingOptionsPresets.HIGH_QUALITY,
    );

    return { ok: true, recording };
  } catch (err) {
    const message = err instanceof Error ? err.message : 'Could not start recording.';
    return { ok: false, error: message };
  }
}

export async function stopRecording(
  recording: Audio.Recording,
): Promise<{ uri: string; mimeType: string } | null> {
  try {
    await recording.stopAndUnloadAsync();
    await Audio.setAudioModeAsync({ allowsRecordingIOS: false });

    const uri = recording.getURI();
    if (!uri) return null;

    const mimeType = Platform.OS === 'web' ? 'audio/webm' : 'audio/m4a';
    return { uri, mimeType };
  } catch {
    return null;
  }
}

// ─── Transcription ────────────────────────────────────────────────────────────

export type TranscribeResult =
  | { ok: true; transcript: string }
  | { ok: false; error: string };

export async function transcribeAudio(
  uri: string,
  mimeType: string,
): Promise<TranscribeResult> {
  try {
    const base64 = await readAsStringAsync(uri, { encoding: 'base64' });

    const response = await fetch(ENDPOINTS.transcribe, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ audio: base64, mimeType }),
    });

    const json = await response.json();

    if (!response.ok || json.error) {
      return {
        ok: false,
        error: typeof json.error === 'string'
          ? json.error
          : 'Transcription failed. Please try again.',
      };
    }

    if (typeof json.transcript !== 'string' || json.transcript.trim() === '') {
      return { ok: false, error: 'Could not understand the audio. Please try again.' };
    }

    return { ok: true, transcript: json.transcript.trim() };
  } catch (err) {
    const isNetwork =
      err instanceof TypeError && err.message.includes('Network request failed');
    return {
      ok: false,
      error: isNetwork
        ? 'Could not reach the server. Check your network connection.'
        : 'Transcription error. Please try again.',
    };
  }
}
