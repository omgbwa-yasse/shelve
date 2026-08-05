/**
 * Feature Mails — configs CRUD + routes spéciales.
 */
import type { ResourceConfig } from '@/lib/crud/types';
import type { SpecialRoute } from '@/lib/crud/types';
import { col, textField, textareaField, dateField, badge } from '@/lib/crud/helpers';
import * as api from './services/mail.service';
import { ParapheurActions } from './components/parapheur-actions';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);

export const resources: ResourceConfig[] = [
  {
    path: '/mails', label: 'Courrier', plural: 'Courriers', description: 'Gestion du courrier (interne et externe)',
    api: api.mailsApi, titleKey: 'subject', codeKey: 'code', creatable: true, exportable: true,
    columns: [col('code', 'Code', { render: badge }), col('subject', 'Objet'), col('direction', 'Sens'), col('received_date', 'Reçu le', { render: DATETIME })],
    fields: [
      textField('code', 'Code'),
      textField('subject', 'Objet', { required: true }),
      textareaField('description', 'Description'),
      textField('sender', 'Expéditeur'),
      textField('recipient', 'Destinataire'),
      dateField('received_date', 'Date de réception'),
      dateField('sent_date', "Date d'envoi"),
    ],
    filters: [{ name: 'direction', label: 'Sens', type: 'select', options: [{ value: 'incoming', label: 'Entrant' }, { value: 'outgoing', label: 'Sortant' }] }],
  },
  {
    path: '/mails/batches', label: 'Parapheur', plural: 'Parapheurs',
    api: api.batchesApi, titleKey: 'name', codeKey: 'code', creatable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('status', 'Statut')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true }), textField('status', 'Statut')],
  },
  {
    path: '/mails/typologies', label: 'Typologie de courrier', plural: 'Typologies de courrier',
    api: api.mailTypologiesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/mail-typologies'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('description', 'Description')],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true }), textareaField('description', 'Description')],
  },
  {
    path: '/mails/archived', label: 'Courrier archivé', plural: 'Courrier archivé',
    api: api.mailArchivesApi, titleKey: 'name', codeKey: 'code',
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('archived_at', 'Archivé le', { render: DATETIME })],
    fields: [textField('code', 'Code'), textField('name', 'Nom')],
  },
  {
    path: '/mails/containers', label: 'Boîte de courrier', plural: 'Boîtes de courrier',
    api: api.mailContainersApi, titleKey: 'code', codeKey: 'code', creatable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom')],
  },
  {
    path: '/mails/attachments', label: 'Pièce jointe', plural: 'Pièces jointes',
    api: api.mailArchivesApi, titleKey: 'filename', creatable: false, editable: false, deletable: true,
    columns: [col('filename', 'Fichier'), col('size', 'Taille')],
    fields: [textField('filename', 'Fichier')],
  },
  {
    path: '/settings/batch-transactions', label: 'Transaction de lot', plural: 'Transactions de lot',
    api: api.batchTransactionsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('status', 'Statut')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/settings/mail-actions', label: 'Action de courrier', plural: 'Actions de courrier',
    api: api.mailActionsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/settings/mail-priorities', label: 'Priorité de courrier', plural: 'Priorités de courrier',
    api: api.mailPrioritiesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
];

export const specialRoutes: SpecialRoute[] = [
  { path: '/mails/batches/sign', exact: true, component: () => <ParapheurActions action="sign" /> },
  { path: '/mails/batches/send', exact: true, component: () => <ParapheurActions action="send" /> },
  { path: '/mails/batches/receive', exact: true, component: () => <ParapheurActions action="receive" /> },
];
