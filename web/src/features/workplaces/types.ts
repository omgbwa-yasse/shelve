/**
 * Types du domaine WorkPlaces (D12) — alignés sur `WorkplaceResource`,
 * `WorkplaceMemberResource` et `WorkplaceActivityResource` de l'API Laravel.
 */

export type WorkplaceMember = {
  id: number;
  workplace_id: number;
  user_id: number;
  role: string;
  can_create_folders: boolean;
  can_create_documents: boolean;
  can_delete: boolean;
  can_share: boolean;
  can_invite: boolean;
  notify_on_new_content: boolean;
  notify_on_mentions: boolean;
  notify_on_updates: boolean;
  invited_by?: number | null;
  joined_at?: string | null;
  last_activity_at?: string | null;
  user?: { id: number; name: string; email: string } | null;
};

export type WorkplaceActivity = {
  id: number;
  workplace_id: number;
  user_id: number;
  activity_type: string;
  description?: string | null;
  metadata?: Record<string, unknown> | null;
  created_at?: string | null;
  user?: { id: number; name: string; email?: string } | null;
};

export type WorkplaceConversation = {
  id: number;
  workplace_id?: number | null;
  type: 'group' | 'channel' | 'private';
  name?: string | null;
  description?: string | null;
  created_by?: number | null;
  created_at?: string | null;
  updated_at?: string | null;
  participants?: WorkplaceConversationParticipant[];
  messages?: WorkplaceMessage[];
};

export type WorkplaceConversationParticipant = {
  id: number;
  conversation_id: number;
  user_id: number;
  role?: string;
  last_read_at?: string | null;
  user?: { id: number; name: string; email: string } | null;
};

export type WorkplaceMessage = {
  id: number;
  conversation_id: number;
  user_id: number;
  content: string;
  created_at?: string | null;
  updated_at?: string | null;
  user?: { id: number; name: string; email?: string } | null;
};

/** Dossier ou fichier de la bibliothèque Documents d'un workplace. */
export type WorkplaceDocument = {
  id: number;
  name: string;
  code?: string | null;
  workplace_id: number;
  parent_id?: number | null;
  is_folder: boolean;
  /** Visible du module Records (partagé hors du workplace). */
  is_shared: boolean;
  children_count: number;
  created_at?: string | null;
  creator?: { id: number; name: string } | null;
  attachment?: {
    id: number;
    name: string;
    size?: number | null;
    mime_type?: string | null;
  } | null;
};

export type Workplace = {
  id: number;
  code: string;
  name: string;
  description?: string | null;
  category_id?: number | null;
  icon?: string | null;
  color?: string | null;
  settings?: Record<string, unknown> | null;
  is_public: boolean;
  allow_external_sharing: boolean;
  max_members?: number | null;
  max_storage_mb?: number | null;
  members_count: number;
  storage_used_bytes?: number;
  storage_used_mb?: number;
  storage_percentage?: number;
  is_full?: boolean;
  status?: string;
  start_date?: string | null;
  end_date?: string | null;
  archived_at?: string | null;
  organisation_id?: number;
  owner_id?: number;
  created_by?: number;
  updated_by?: number;
  created_at?: string;
  updated_at?: string;
  deleted_at?: string | null;

  // Relations chargées via `?include=`.
  category?: { id: number; name: string } | null;
  owner?: { id: number; name: string; email?: string } | null;
  members?: WorkplaceMember[] | null;
  activities?: WorkplaceActivity[] | null;
};
