/**
 * Feature IA — configs CRUD + routes spéciales (D14).
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, textareaField, boolField, yesNo } from '@/lib/crud/helpers';
import * as api from './services/ai.service';
import { AiResources, AiTest } from './components/ai-views';

export const resources: ResourceConfig[] = [
  {
    path: '/ai-search/resources', label: 'Skill IA', plural: 'Skills IA',
    api: api.aiSkillsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/prompts'],
    columns: [col('name', 'Nom'), col('description', 'Description'), col('active', 'Actif', { render: yesNo })],
    fields: [textField('name', 'Nom', { required: true }), textareaField('description', 'Description'), boolField('active', 'Actif')],
    rowActions: [{ label: 'Basculer', verb: 'toggle', method: 'action' }],
  },
  {
    path: '/ai-search/prompts', label: 'Prompt', plural: 'Prompts',
    api: api.promptsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('description', 'Description')],
    fields: [textField('name', 'Nom', { required: true }), textareaField('description', 'Description'), textareaField('content', 'Contenu')],
  },
];

export const specialRoutes: SpecialRoute[] = [
  { path: '/ai-search/resources', component: AiResources },
  { path: '/ai-search/test', exact: true, component: AiTest },
];
