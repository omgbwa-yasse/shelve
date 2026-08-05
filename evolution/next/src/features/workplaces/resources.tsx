/**
 * Feature Workplaces — configs CRUD (espaces de travail + contenu partagé).
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, textareaField, boolField, badge, yesNo } from '@/lib/crud/helpers';
import * as api from './services/workplace.service';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);

export const resources: ResourceConfig[] = [
  {
    path: '/workplaces', label: 'Workplace', plural: 'Espaces de travail',
    api: api.workplacesApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('status', 'Statut'), col('is_public', 'Public', { render: yesNo }), col('created_at', 'Créé le', { render: DATETIME })],
    fields: [textField('name', 'Nom', { required: true }), textareaField('description', 'Description'), boolField('is_public', 'Public')],
    filters: [{ name: 'status', label: 'Statut', type: 'select', options: [{ value: 'active', label: 'Actif' }, { value: 'archived', label: 'Archivé' }] }],
    rowActions: [{ label: 'Archiver', verb: 'archive', method: 'action', confirm: 'Archiver ce workplace ?' }],
    tabs: [
      {
        key: 'members', label: 'Membres', parentApi: api.workplacesApi, apiPath: 'members', deletable: true,
        addVerb: 'members',
        fields: [textField('user_id', 'Utilisateur'), textField('role', 'Rôle')],
        columns: [col('user_id', 'Utilisateur'), col('role', 'Rôle')],
      },
      {
        key: 'documents', label: 'Documents partagés', parentApi: api.workplacesApi, apiPath: 'content/documents', deletable: true,
        columns: [col('name', 'Nom'), col('shared', 'Partagé', { render: yesNo })],
      },
      {
        key: 'folders', label: 'Dossiers partagés', parentApi: api.workplacesApi, apiPath: 'content/folders', deletable: true,
        columns: [col('name', 'Nom')],
      },
      {
        key: 'bookmarks', label: 'Favoris', parentApi: api.workplacesApi, apiPath: 'bookmarks', deletable: true,
        addVerb: 'bookmarks',
        fields: [textField('bookmarkable_type', 'Type'), textField('bookmarkable_id', 'Identifiant')],
        columns: [col('bookmarkable_type', 'Type'), col('bookmarkable_id', 'Identifiant')],
      },
    ],
  },
];

export const specialRoutes: SpecialRoute[] = [];
