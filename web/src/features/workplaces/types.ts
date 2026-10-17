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
  workplace?: { id: number; code?: string; name: string } | null;
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
  attachment_name?: string | null;
  attachment_mime?: string | null;
  attachment_size?: number | null;
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
  visibility?: 'public' | 'moderated' | 'private';
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

/** Demande d'adhésion à un workplace (mode moderated). */
export type WorkplaceMembershipRequest = {
  id: number;
  workplace_id: number;
  user_id: number;
  message?: string | null;
  status: 'pending' | 'approved' | 'rejected';
  reviewed_by?: number | null;
  reviewed_at?: string | null;
  created_at?: string | null;
  user?: { id: number; name: string; email?: string } | null;
};

/** Sujet de discussion d'un workplace. */
export type WorkplaceTopic = {
  id: number;
  workplace_id: number;
  user_id: number;
  title: string;
  body: string;
  is_pinned: boolean;
  is_locked: boolean;
  posts_count?: number | null;
  created_by?: number | null;
  updated_by?: number | null;
  created_at?: string | null;
  updated_at?: string | null;
  user?: { id: number; name: string } | null;
};

/** Réponse dans une discussion de workplace. */
export type WorkplaceTopicPost = {
  id: number;
  workplace_id: number;
  topic_id: number;
  user_id: number;
  body: string;
  created_at?: string | null;
  updated_at?: string | null;
  user?: { id: number; name: string } | null;
};

/** Billet de blog d'un workplace. */
export type WorkplaceBlogPost = {
  id: number;
  workplace_id: number;
  user_id: number;
  title: string;
  body: string;
  status: 'draft' | 'published';
  published_at?: string | null;
  created_by?: number | null;
  updated_by?: number | null;
  created_at?: string | null;
  updated_at?: string | null;
  user?: { id: number; name: string } | null;
};

/** Permission par nœud (ACL dossier/document) dans un workplace. */
export type WorkplaceNodePermission = {
  id: number;
  workplace_id: number;
  record_id: number;
  grantee_type: 'user' | 'member_role';
  grantee_id?: number | null;
  can_view: boolean;
  can_edit: boolean;
  can_delete: boolean;
  can_share: boolean;
  granted_by?: number | null;
  created_at?: string | null;
  updated_at?: string | null;
  record?: { id: number; name: string; is_folder: boolean } | null;
};

/** Tâche rattachée à un workplace (site task). */
export type WorkplaceTask = {
  id: number;
  title: string;
  description?: string | null;
  status: string;
  priority: string;
  assigned_to?: number | null;
  due_date?: string | null;
  created_at?: string | null;
  assignee?: { id: number; name: string } | null;
};

/** Règle de dossier (déclencheur) rattachée à un workplace. */
export type WorkplaceTrigger = {
  id: number;
  name: string;
  description?: string | null;
  event_type: string;
  condition_mode: string;
  action_type: string;
  is_enabled: boolean;
  run_count: number;
  last_run_at?: string | null;
  sort_order: number;
};
