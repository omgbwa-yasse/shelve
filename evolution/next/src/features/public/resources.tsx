/**
 * Feature Public — configs CRUD + routes spéciales (D15).
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, textareaField, dateField, badge } from '@/lib/crud/helpers';
import * as api from './services/public.service';
import { PublicDashboard, PublicInfo } from './components/public-views';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);

export const resources: ResourceConfig[] = [
  {
    path: '/public/news', label: 'Actualité', plural: 'Actualités',
    api: api.publicNewsApi, titleKey: 'title', creatable: true, editable: true, deletable: true,
    columns: [col('title', 'Titre'), col('published_at', 'Publiée le', { render: DATETIME })],
    fields: [textField('title', 'Titre', { required: true }), textareaField('content', 'Contenu'), dateField('published_at', 'Date de publication')],
  },
  {
    path: '/public/events', label: 'Événement', plural: 'Événements',
    api: api.publicEventsApi, titleKey: 'title', creatable: true, editable: true, deletable: true,
    columns: [col('title', 'Titre'), col('start_date', 'Début', { render: DATETIME }), col('end_date', 'Fin', { render: DATETIME })],
    fields: [textField('title', 'Titre', { required: true }), textareaField('description', 'Description'), dateField('start_date', 'Début'), dateField('end_date', 'Fin')],
  },
  {
    path: '/public/pages', label: 'Page publique', plural: 'Pages publiques',
    api: api.publicPagesApi, titleKey: 'title', creatable: true, editable: true, deletable: true,
    columns: [col('title', 'Titre'), col('slug', 'Slug')],
    fields: [textField('title', 'Titre', { required: true }), textField('slug', 'Slug'), textareaField('content', 'Contenu')],
  },
  {
    path: '/public/templates', label: 'Template', plural: 'Templates publics',
    api: api.publicTemplatesApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('type', 'Type')],
    fields: [textField('name', 'Nom', { required: true }), textField('type', 'Type')],
  },
  {
    path: '/public/users', label: 'Utilisateur public', plural: 'Utilisateurs publics',
    api: api.publicUsersApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('email', 'Email')],
    fields: [textField('name', 'Nom', { required: true }), textField('email', 'Email')],
  },
  {
    path: '/public/records', label: 'Notice publique', plural: 'Notices publiques',
    api: api.publicRecordsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/public/feedback', label: 'Retour', plural: 'Retours',
    api: api.publicFeedbacksApi, titleKey: 'subject', creatable: false, editable: false, deletable: true,
    columns: [col('subject', 'Sujet'), col('created_at', 'Reçu le', { render: DATETIME })],
    fields: [textField('subject', 'Sujet')],
  },
  {
    path: '/public/search-logs', label: 'Journal de recherche', plural: 'Journaux de recherche',
    api: api.publicSearchLogsApi, titleKey: 'query', creatable: false, editable: false, deletable: true,
    columns: [col('query', 'Requête'), col('created_at', 'Date', { render: DATETIME })],
    fields: [textField('query', 'Requête')],
  },
];

export const specialRoutes: SpecialRoute[] = [
  { path: '/public/dashboard', exact: true, component: PublicDashboard },
  { path: '/public/statistics', exact: true, component: PublicDashboard },
  { path: '/public/configurations', exact: true, component: () => <PublicInfo title="Configuration OPAC" description="Paramètres généraux du portail public." note="Configurations OPAC gérées côté Laravel." /> },
  { path: '/public/opac-templates', exact: true, component: () => <PublicInfo title="Templates OPAC" description="Modèles de rendu du portail public." note="Preview/duplication côté Laravel (R05 : jamais de rendu brut côté API)." /> },
  { path: '/public/document-requests', exact: true, component: () => <PublicInfo title="Demandes de documents" description="Demandes des usagers publics." note="Seul POST store est exposé ; le CRUD d'administration reste côté Laravel." /> },
  { path: '/public/responses', exact: true, component: () => <PublicInfo title="Réponses" description="Réponses aux demandes publiques." note="Seul POST store est exposé." /> },
  { path: '/public/response-attachments', exact: true, component: () => <PublicInfo title="Pièces jointes de réponse" description="Pièces jointes associées aux réponses." note="Contrôleur API non routé." /> },
  { path: '/public/chats', exact: true, component: () => <PublicInfo title="Chats publics" description="Conversations des usagers." note="PublicChat*ApiController créés mais non routés." /> },
  { path: '/public/chat-participants', exact: true, component: () => <PublicInfo title="Participants aux chats" description="Participants des conversations publiques." note="Pas d'endpoint API." /> },
  { path: '/public/event-registrations', exact: true, component: () => <PublicInfo title="Inscriptions aux événements" description="Inscriptions des usagers aux événements." note="Auto-enregistrement exposé ; administration complète côté Laravel." /> },
];
