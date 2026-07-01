import { ENDPOINTS } from '../config/api';
import { apiRequest, withQuery, type ApiResult } from './apiClient';

// ─── Types ────────────────────────────────────────────────────────────────────

export interface ChatMessage {
  id: number;
  senderId: number;
  receiverId: number;
  messageText: string;
  isRead: boolean;
  sentAt: string;
}

export interface InboxConversation {
  userId: number;
  name: string;
  role: 'caregiver' | 'healthcare_provider' | 'admin';
  lastMessage: string;
  lastMessageAt: string;
  fromMe: boolean;
  unreadCount: number;
}

export type SendMessageResult     = ApiResult<{ id: number }>;
export type GetConversationResult = ApiResult<ChatMessage[]>;
export type GetInboxResult        = ApiResult<InboxConversation[]>;

// ─── Service ──────────────────────────────────────────────────────────────────

export async function sendMessage(receiverId: number, messageText: string): Promise<SendMessageResult> {
  if (!messageText.trim()) {
    return { ok: false, error: 'Message cannot be empty.' };
  }

  return apiRequest(ENDPOINTS.sendMessage, {
    method: 'POST',
    body: { receiver_id: receiverId, message_text: messageText },
  });
}

export async function getConversation(withUserId: number): Promise<GetConversationResult> {
  return apiRequest(withQuery(ENDPOINTS.getConversation, { with: withUserId }));
}

export async function getInbox(): Promise<GetInboxResult> {
  return apiRequest(ENDPOINTS.getInbox);
}
