/**
 * Feature Workflow — configs CRUD + routes spéciales.
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, textareaField, numberField, dateField, selectField, refField, badge } from '@/lib/crud/helpers';
import * as api from './services/workflow.service';
import { WorkflowDashboard } from './components/workflow-dashboard';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);

export const resources: ResourceConfig[] = [
  {
    path: '/workflow/definitions', label: 'Définition de workflow', plural: 'Définitions de workflow',
    api: api.workflowDefinitionsApi, titleKey: 'name', codeKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [
      col('name', 'Nom'),
      col('version', 'Version'),
      col('status', 'Statut', { render: (v) => <span className="rounded bg-muted px-1.5 py-0.5 text-xs">{String(v ?? '')}</span> }),
    ],
    fields: [
      textField('name', 'Nom', { required: true }),
      textareaField('description', 'Description'),
      textareaField('bpmn_xml', 'BPMN XML'),
      numberField('version', 'Version'),
      textField('status', 'Statut'),
    ],
    filters: [{ name: 'status', label: 'Statut', type: 'select', options: [{ value: 'active', label: 'Actif' }, { value: 'draft', label: 'Brouillon' }, { value: 'archived', label: 'Archivé' }] }],
  },
  {
    path: '/workflow/instances', label: 'Instance de workflow', plural: 'Instances de workflow',
    api: api.workflowInstancesApi, titleKey: 'name', creatable: true, editable: false,
    columns: [col('name', 'Nom'), col('status', 'Statut'), col('started_at', 'Démarré le', { render: DATETIME })],
    fields: [textField('name', 'Nom', { required: true }), refField('definition_id', 'Définition', api.workflowDefinitionsApi)],
    rowActions: [
      { label: 'Démarrer', verb: 'start', method: 'action' },
      { label: 'Pause', verb: 'pause', method: 'action' },
      { label: 'Reprendre', verb: 'resume', method: 'action' },
      { label: 'Annuler', verb: 'cancel', method: 'action', variant: 'danger' },
    ],
  },
  {
    path: '/workflow/tasks', label: 'Tâche', plural: 'Tâches',
    api: api.tasksApi, titleKey: 'title', creatable: true, editable: true, deletable: true,
    columns: [
      col('title', 'Titre'),
      col('status', 'Statut'),
      col('priority', 'Priorité'),
      col('assigned_to', 'Assigné à'),
      col('due_date', 'Échéance', { render: DATETIME }),
    ],
    fields: [
      textField('title', 'Titre', { required: true }),
      textareaField('description', 'Description'),
      selectField('status', 'Statut', [{ value: 'pending', label: 'En attente' }, { value: 'in_progress', label: 'En cours' }, { value: 'completed', label: 'Terminée' }, { value: 'cancelled', label: 'Annulée' }]),
      selectField('priority', 'Priorité', [{ value: 'low', label: 'Basse' }, { value: 'normal', label: 'Normale' }, { value: 'high', label: 'Haute' }, { value: 'urgent', label: 'Urgente' }]),
      dateField('due_date', 'Échéance'),
    ],
    filters: [{ name: 'status', label: 'Statut', type: 'select', options: [{ value: 'pending', label: 'En attente' }, { value: 'in_progress', label: 'En cours' }, { value: 'completed', label: 'Terminée' }] }],
  },
];

export const specialRoutes: SpecialRoute[] = [
  { path: '/workflow/dashboard', exact: true, component: WorkflowDashboard },
];
