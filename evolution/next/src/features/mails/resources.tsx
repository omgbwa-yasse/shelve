/**
 * Feature Mails — configs CRUD + routes spéciales.
 *
 * Écrans de liste distincts partageant la ressource `mails` via des
 * `presetFilters` (convention API `filter[champ]=valeur`), conformément aux
 * écrans Blade `mails/{received,send,archived,…}`.
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, textareaField, dateField, selectField, refField, badge } from '@/lib/crud/helpers';
import * as api from './services/mail.service';
import { ParapheurActions } from './components/parapheur-actions';
import { MailAdvancedSearch, MailDateSelect } from './components/mail-search';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);
const STATUS_OPTIONS = [
  { value: 'draft', label: 'Brouillon' },
  { value: 'pending_review', label: 'En attente de révision' },
  { value: 'in_progress', label: 'En cours de traitement' },
  { value: 'pending_approval', label: "En attente d'approbation" },
  { value: 'approved', label: 'Approuvé' },
  { value: 'transmitted', label: 'Transmis' },
  { value: 'completed', label: 'Terminé' },
  { value: 'rejected', label: 'Rejeté' },
  { value: 'cancelled', label: 'Annulé' },
  { value: 'overdue', label: 'En retard' },
];
const MAIL_TYPE_OPTIONS = [
  { value: 'internal', label: 'Interne' },
  { value: 'incoming', label: 'Entrant' },
  { value: 'outgoing', label: 'Sortant' },
];

/** Base commune aux écrans de liste de courriers. */
function mailListConfig(path: string, label: string, plural: string, presetFilters: Record<string, string>, description?: string): ResourceConfig {
  return {
    path,
    label,
    plural,
    description,
    api: api.mailsApi,
    titleKey: 'name',
    codeKey: 'code',
    creatable: true,
    editable: true,
    deletable: true,
    exportable: true,
    searchField: 'name',
    presetFilters,
    columns: [
      col('code', 'Code', { render: badge, sortable: true }),
      col('name', 'Nom', { sortable: true }),
      col('date', 'Date', { render: DATETIME, sortable: true }),
      col('mail_type', 'Type', { render: (v) => MAIL_TYPE_OPTIONS.find((o) => o.value === v)?.label ?? String(v ?? '') }),
      col('status', 'Statut', { render: (v) => <StatusBadge value={String(v ?? '')} /> }),
    ],
    fields: [
      textField('code', 'Code', { required: true, help: 'Code séquentiel {année}/{typologie}/{numéro} ou libre.' }),
      textField('name', 'Nom', { required: true }),
      textareaField('description', 'Description'),
      dateField('date', 'Date'),
      dateField('deadline', 'Échéance'),
      selectField('mail_type', 'Type de courrier', MAIL_TYPE_OPTIONS),
      selectField('status', 'Statut', STATUS_OPTIONS),
      refField('priority_id', 'Priorité', api.mailPrioritiesApi),
      refField('typology_id', 'Typologie', api.mailTypologiesApi),
      refField('action_id', 'Action', api.mailActionsApi),
    ],
    filters: [
      { name: 'status', label: 'Statut', type: 'select', options: STATUS_OPTIONS },
      { name: 'mail_type', label: 'Type', type: 'select', options: MAIL_TYPE_OPTIONS },
      { name: 'is_archived', label: 'Archivé', type: 'boolean' },
    ],
    tabs: [
      {
        key: 'attachments',
        label: 'Pièces jointes',
        queryBy: (id) => `/api/v1/mail-attachments?filter[mail_id]=${id}`,
        deletable: true,
        columns: [col('name', 'Nom'), col('mime_type', 'Type'), col('size', 'Taille')],
      },
    ],
  };
}

export const resources: ResourceConfig[] = [
  mailListConfig('/mails', 'Courrier', 'Courriers', {}, 'Tous les courriers (internes, entrants, sortants).'),
  mailListConfig('/mails/received', 'Courrier reçu', 'Courriers reçus', { mail_type: 'incoming' }, 'Courriers reçus (entrants).'),
  mailListConfig('/mails/sent', 'Courrier envoyé', 'Courriers envoyés', { mail_type: 'outgoing' }, 'Courriers envoyés (sortants).'),
  mailListConfig('/mails/returned', 'Courrier retourné', 'Courriers retournés', { status: 'completed' }, 'Courriers traités / retournés.'),
  mailListConfig('/mails/to-return', 'Courrier à retourner', 'Courriers à retourner', { status: 'transmitted' }, 'Courriers transmis, à retourner.'),
  mailListConfig('/mails/external/send', 'Courrier sortant externe', 'Courriers sortants externes', { mail_type: 'outgoing' }, 'Courrier externe — envois.'),
  mailListConfig('/mails/external/receive', 'Courrier entrant externe', 'Courriers entrants externes', { mail_type: 'incoming' }, 'Courrier externe — réceptions.'),
  mailListConfig('/mails/archived', 'Courrier archivé', 'Courriers archivés', { is_archived: '1' }, 'Courriers archivés.'),

  {
    path: '/mails/batches', label: 'Parapheur', plural: 'Parapheurs',
    api: api.batchesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    searchField: 'name',
    columns: [col('code', 'Code', { render: badge, sortable: true }), col('name', 'Nom', { sortable: true })],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true })],
    tabs: [
      {
        key: 'transactions',
        label: 'Transactions',
        queryBy: (id) => `/api/v1/batch-transactions?filter[batch_id]=${id}`,
        deletable: true,
        columns: [col('batch_id', 'Parapheur'), col('organisation_send_id', 'Émetteur'), col('organisation_received_id', 'Destinataire'), col('created_at', 'Créée le', { render: DATETIME })],
      },
    ],
  },

  {
    path: '/mails/containers', label: 'Boîte de courrier', plural: 'Boîtes de courrier',
    api: api.mailContainersApi, titleKey: 'code', codeKey: 'code', creatable: true, editable: true, deletable: true,
    searchField: 'name',
    columns: [col('code', 'Code', { render: badge, sortable: true }), col('name', 'Nom', { sortable: true })],
    fields: [
      textField('code', 'Code', { required: true }),
      textField('name', 'Nom', { required: true }),
      refField('property_id', 'Propriété', api.containerPropertiesApi),
    ],
    tabs: [
      {
        key: 'mails',
        label: 'Courriers archivés',
        queryBy: (id) => `/api/v1/mail-archives?filter[container_id]=${id}`,
        deletable: true,
        columns: [col('mail_id', 'Courrier'), col('document_type', 'Type de document'), col('created_at', 'Archivé le', { render: DATETIME })],
      },
    ],
  },

  {
    path: '/mails/typologies', label: 'Typologie de courrier', plural: 'Typologies de courrier',
    api: api.mailTypologiesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/mail-typologies'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('description', 'Description')],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true }), textareaField('description', 'Description')],
  },
  {
    path: '/settings/mail-actions', label: 'Action de courrier', plural: 'Actions de courrier',
    api: api.mailActionsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/settings/mail-priorities', label: 'Priorité de courrier', plural: 'Priorités de courrier',
    api: api.mailPrioritiesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/mails/attachments', label: 'Pièce jointe', plural: 'Pièces jointes',
    api: api.mailAttachmentsApi, titleKey: 'name', creatable: false, editable: false, deletable: true,
    searchField: 'name',
    columns: [col('name', 'Nom'), col('mime_type', 'Type'), col('size', 'Taille')],
    fields: [textField('name', 'Nom')],
  },
  {
    path: '/settings/batch-transactions', label: 'Transaction de lot', plural: 'Transactions de lot',
    api: api.batchTransactionsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('batch_id', 'Parapheur'), col('organisation_send_id', 'Émetteur'), col('organisation_received_id', 'Destinataire'), col('created_at', 'Créée le', { render: DATETIME })],
    fields: [textField('name', 'Nom', { required: true })],
  },
];

export const specialRoutes: SpecialRoute[] = [
  { path: '/mails/batches/sign', exact: true, component: () => <ParapheurActions action="sign" /> },
  { path: '/mails/batches/send', exact: true, component: () => <ParapheurActions action="send" /> },
  { path: '/mails/batches/receive', exact: true, component: () => <ParapheurActions action="receive" /> },
  { path: '/mails/advanced', exact: true, component: MailAdvancedSearch },
  { path: '/mails/select/date', exact: true, component: MailDateSelect },
];

/** Badge de statut coloré selon la valeur (miroir de MailStatusEnum::color). */
function StatusBadge({ value }: { value: string }) {
  const colors: Record<string, string> = {
    draft: 'bg-muted text-muted-foreground',
    in_progress: 'bg-blue-100 text-blue-700',
    pending_approval: 'bg-orange-100 text-orange-700',
    approved: 'bg-green-100 text-green-700',
    transmitted: 'bg-purple-100 text-purple-700',
    completed: 'bg-emerald-100 text-emerald-700',
    rejected: 'bg-red-100 text-red-700',
    overdue: 'bg-red-100 text-red-700',
  };
  const label = STATUS_OPTIONS.find((o) => o.value === value)?.label ?? value;
  return <span className={`rounded px-1.5 py-0.5 text-xs ${colors[value] ?? 'bg-muted text-muted-foreground'}`}>{label}</span>;
}
