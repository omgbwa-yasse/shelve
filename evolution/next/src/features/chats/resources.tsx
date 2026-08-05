/**
 * Feature Chat — conversations (D12).
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField } from '@/lib/crud/helpers';
import * as api from './services/chat.service';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);

export const resources: ResourceConfig[] = [
  {
    path: '/chats', label: 'Conversation', plural: 'Conversations',
    api: api.chatConversationsApi, titleKey: 'name', creatable: true, editable: true,
    columns: [col('name', 'Nom'), col('created_at', 'Créée le', { render: DATETIME })],
    fields: [textField('name', 'Nom', { required: true })],
  },
];

export const specialRoutes: SpecialRoute[] = [];
