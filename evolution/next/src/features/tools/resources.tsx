/**
 * Feature Outils — configs CRUD + routes spéciales (D01 + D08).
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, textareaField, boolField, refField, yesNo, badge } from '@/lib/crud/helpers';
import * as api from './services/tool.service';
import { ThesaurusViews, Barcode } from './components/tool-views';

export const resources: ResourceConfig[] = [
  {
    path: '/tools/activities', label: 'Classe', plural: 'Plan de classement',
    api: api.activitiesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('parent_id', 'Classe parente')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true }), refField('parent_id', 'Classe parente', api.activitiesApi)],
  },
  {
    path: '/tools/communicabilities', label: 'Classe de communicabilité', plural: 'Communicabilité',
    api: api.communicabilitiesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/tools/organisations', label: 'Unité', plural: 'Organigramme',
    api: api.organisationsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('parent_id', 'Unité parente')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true }), refField('parent_id', 'Unité parente', api.organisationsApi)],
  },
  {
    path: '/tools/reference-lists', label: 'Domaine de valeurs', plural: 'Domaines de valeurs',
    api: api.referenceListsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/reference-lists'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('active', 'Actif', { render: yesNo })],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true }), textareaField('description', 'Description'), boolField('active', 'Actif')],
  },
  {
    path: '/tools/thesaurus', label: 'Schéma de thésaurus', plural: 'Schémas de thésaurus',
    api: api.thesaurusSchemesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/tools/thesaurus/concepts', label: 'Terme du thésaurus', plural: 'Termes du thésaurus',
    api: api.thesaurusConceptsApi, titleKey: 'preferred_label', creatable: true, editable: true, deletable: true,
    columns: [col('preferred_label', 'Libellé'), col('language', 'Langue')],
    fields: [textField('preferred_label', 'Libellé préféré', { required: true }), textField('language', 'Langue')],
  },
  {
    path: '/tools/languages', label: 'Langue', plural: 'Langues',
    api: api.languagesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/languages'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/tools/sorts', label: 'Sort final', plural: 'Sorts finaux',
    api: api.sortsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/sorts'],
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/tools/laws', label: 'Loi', plural: 'Lois',
    api: api.lawsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
  {
    path: '/tools/law-articles', label: 'Article de loi', plural: 'Articles de loi',
    api: api.lawArticlesApi, titleKey: 'title', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('title', 'Intitulé')],
    fields: [textField('code', 'Code'), textField('title', 'Intitulé', { required: true })],
  },
  {
    path: '/tools/keywords', label: 'Mot-clé', plural: 'Mots-clés',
    api: api.keywordsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom')],
    fields: [textField('name', 'Nom', { required: true })],
  },
];

export const specialRoutes: SpecialRoute[] = [
  { path: '/tools/thesaurus/hierarchy', component: () => <ThesaurusViews view="hierarchy" /> },
  { path: '/tools/thesaurus/search', component: () => <ThesaurusViews view="search" /> },
  { path: '/tools/thesaurus/export-import', component: () => <ThesaurusViews view="export-import" /> },
  { path: '/tools/barcode', component: Barcode },
];
