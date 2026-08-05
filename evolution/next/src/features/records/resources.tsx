/**
 * Feature Records (Notices) — configs CRUD + routes spéciales.
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, textareaField, dateField, selectField, refField, boolField, emailField, badge, yesNo } from '@/lib/crud/helpers';
import * as api from './services/record.service';
import { RecordsTree, DragDrop } from './components/records-views';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);

export const resources: ResourceConfig[] = [
  {
    path: '/records', label: 'Notice', plural: 'Notices', description: 'Répertoire — notices d’archives',
    api: api.recordsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true, exportable: true,
    columns: [
      col('code', 'Code', { render: badge }),
      col('name', 'Nom'),
      col('type_id', 'Typologie'),
      col('created_at', 'Créée le', { render: DATETIME }),
    ],
    fields: [
      textField('code', 'Code', { required: true, help: 'Code unique de la notice' }),
      textField('name', 'Nom', { required: true }),
      textareaField('description', 'Description'),
      refField('type_id', 'Typologie', api.recordTypesApi),
      selectField('level_id', 'Niveau', [{ value: '1', label: 'Fonds' }, { value: '2', label: 'Série' }, { value: '3', label: 'Dossier' }, { value: '4', label: 'Pièce' }]),
      dateField('start_date', 'Date début'),
      dateField('end_date', 'Date fin'),
      dateField('date_exact', 'Date exacte'),
      selectField('access_level', "Niveau d'accès", [{ value: 'internal', label: 'Interne' }, { value: 'public', label: 'Public' }, { value: 'confidential', label: 'Confidentiel' }, { value: 'secret', label: 'Secret' }]),
      boolField('is_essential', 'Document essentiel'),
      boolField('unavailable', 'Indisponible'),
    ],
    filters: [{ name: 'type_id', label: 'Typologie', type: 'select', options: [] }],
    tabs: [
      { key: 'children', label: 'Sous-notices', parentApi: api.recordsApi, apiPath: 'children', deletable: true, columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('type_id', 'Typologie')] },
      { key: 'containers', label: 'Contenants', parentApi: api.recordsApi, apiPath: 'containers', deletable: true, columns: [col('code', 'Code', { render: badge })] },
      { key: 'attachments', label: 'Pièces jointes', parentApi: api.recordsApi, apiPath: 'attachments', deletable: true, columns: [col('name', 'Nom'), col('mime_type', 'Type'), col('file_size', 'Taille')] },
      { key: 'authors', label: 'Auteurs', parentApi: api.recordsApi, apiPath: 'authors', deletable: true, columns: [col('name', 'Nom'), col('role', 'Fonction')] },
    ],
  },
  {
    path: '/records/trash', label: 'Notice supprimée', plural: 'Corbeille',
    api: api.recordsApi, titleKey: 'name', codeKey: 'code', creatable: false, editable: false, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('deleted_at', 'Supprimée le', { render: DATETIME })],
    fields: [textField('name', 'Nom')],
  },
  {
    path: '/records/authors', label: 'Auteur', plural: 'Auteurs',
    api: api.authorsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('role', 'Fonction')],
    fields: [textField('name', 'Nom', { required: true }), textField('role', 'Fonction')],
  },
  {
    path: '/records/author-contacts', label: 'Contact d’auteur', plural: 'Contacts d’auteurs',
    api: api.authorContactsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('email', 'Email')],
    fields: [textField('name', 'Nom', { required: true }), emailField('email', 'Email')],
  },
  {
    path: '/records/digital-folders', label: 'Dossier numérique', plural: 'Dossiers numériques',
    api: api.digitalFoldersApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/records/digital-documents', label: 'Document numérique', plural: 'Documents numériques',
    api: api.digitalDocumentsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/tools/record-types', label: 'Typologie de notice', plural: 'Typologies de notices',
    api: api.recordTypesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/record-types'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('is_container', 'Conteneur', { render: yesNo }), col('is_active', 'Actif', { render: yesNo })],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true }), textareaField('description', 'Description'), boolField('is_container', 'Conteneur (dossier)'), boolField('is_active', 'Actif')],
  },
  {
    path: '/tools/record-statuses', label: 'Statut de notice', plural: 'Statuts de notices',
    api: api.recordStatusesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/record-statuses'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/tools/record-supports', label: 'Support', plural: 'Supports de notices',
    api: api.recordSupportsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/record-supports'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/tools/metadata-definitions', label: 'Définition de métadonnée', plural: 'Définitions de métadonnées',
    api: api.metadataDefinitionsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/metadata-definitions'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('data_type', 'Type'), col('searchable', 'Recherchable', { render: yesNo })],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true }), selectField('data_type', 'Type', [{ value: 'text', label: 'Texte' }, { value: 'textarea', label: 'Zone de texte' }, { value: 'number', label: 'Nombre' }, { value: 'date', label: 'Date' }, { value: 'boolean', label: 'Booléen' }, { value: 'select', label: 'Liste' }, { value: 'reference_list', label: 'Référence' }]), boolField('searchable', 'Recherchable')],
  },
  {
    path: '/tools/folder-types', label: 'Type de dossier numérique', plural: 'Types de dossiers numériques',
    api: api.recordTypesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true,
    aliases: ['/settings/folder-types'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/tools/document-types', label: 'Type de document numérique', plural: 'Types de documents numériques',
    api: api.recordTypesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true,
    aliases: ['/settings/document-types'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
];

export const specialRoutes: SpecialRoute[] = [
  { path: '/records/tree', exact: true, component: RecordsTree },
  { path: '/records/drag-drop', exact: true, component: DragDrop },
];
