/**
 * Feature Transferts — configs CRUD + routes spéciales.
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, textareaField, badge } from '@/lib/crud/helpers';
import * as api from './services/transferring.service';
import { SlipsImport, SlipsExport } from './components/slips-import-export';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);

export const resources: ResourceConfig[] = [
  {
    path: '/transferrings', label: 'Bordereau', plural: 'Bordereaux de versement',
    api: api.slipsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('status', 'Statut'), col('created_at', 'Créé le', { render: DATETIME })],
    fields: [textField('code', 'Code', { required: true }), textField('name', 'Nom', { required: true }), textareaField('description', 'Description'), textField('status', 'Statut')],
    rowActions: [
      { label: 'Recevoir', verb: 'receive', method: 'action' },
      { label: 'Approuver', verb: 'approve', method: 'action' },
    ],
    tabs: [
      {
        key: 'records', label: 'Notices du bordereau', parentApi: api.slipsApi, apiPath: 'records', deletable: true,
        addVerb: 'records',
        fields: [textField('record_id', 'Notice')],
        columns: [col('record_id', 'Notice'), col('status', 'Statut')],
      },
      {
        key: 'containers', label: 'Contenants', parentApi: api.slipsApi, apiPath: 'records', deletable: true,
        columns: [col('container_id', 'Contenant')],
      },
    ],
  },
  {
    path: '/transferrings/declassement-lists', label: 'Liste de déclassement', plural: 'Listes de déclassement',
    api: api.declassementListsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('status', 'Statut')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true }), textField('status', 'Statut')],
    rowActions: [
      { label: 'Approuver', verb: 'approve', method: 'action' },
      { label: 'Valider', verb: 'validate', method: 'action' },
      { label: 'Rejeter', verb: 'reject', method: 'action', variant: 'danger' },
    ],
  },
  {
    path: '/transferrings/reactivations', label: 'Réactivation', plural: 'Réactivations',
    api: api.declassementListsApi, titleKey: 'record_id', creatable: false, editable: false, deletable: true,
    columns: [col('record_id', 'Notice'), col('status', 'Statut'), col('created_at', 'Créée le', { render: DATETIME })],
    fields: [textField('record_id', 'Notice')],
  },
  {
    path: '/tools/retentions', label: 'Règle de conservation', plural: 'Durées de conservation',
    api: api.retentionsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('duration', 'Durée')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true }), textField('duration', 'Durée')],
  },
  {
    path: '/settings/transferring-status', label: 'Statut de transfert', plural: 'Statuts de transfert',
    api: api.slipStatusesApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true })],
  },
];

export const specialRoutes: SpecialRoute[] = [
  { path: '/transferrings/import', component: SlipsImport },
  { path: '/transferrings/export', component: SlipsExport },
];
