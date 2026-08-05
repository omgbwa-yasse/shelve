/**
 * Feature Communications — configs CRUD (communications + réservations).
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, badge } from '@/lib/crud/helpers';
import * as api from './services/communication.service';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);

export const resources: ResourceConfig[] = [
  {
    path: '/communications', label: 'Communication', plural: 'Communications',
    api: api.communicationsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('status', 'Statut'), col('created_at', 'Créée le', { render: DATETIME })],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true }), textField('status', 'Statut')],
    rowActions: [
      { label: 'Valider', verb: 'validate', method: 'action' },
      { label: 'Rejeter', verb: 'reject', method: 'action', variant: 'danger' },
      { label: 'Transmettre', verb: 'transmit', method: 'action' },
    ],
    tabs: [
      {
        key: 'records', label: 'Notices communiquées', parentApi: api.communicationsApi, apiPath: 'records', deletable: true,
        addVerb: 'records',
        fields: [textField('record_id', 'Notice')],
        columns: [col('record_id', 'Notice'), col('status', 'Statut')],
      },
    ],
  },
  {
    path: '/communications/reservations', label: 'Réservation', plural: 'Réservations',
    api: api.reservationsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('status', 'Statut')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true }), textField('status', 'Statut')],
    rowActions: [{ label: 'Marquer retourné', verb: 'mark-returned', method: 'action' }],
    tabs: [
      {
        key: 'records', label: 'Notices réservées', parentApi: api.reservationsApi, apiPath: 'records', deletable: true,
        addVerb: 'records',
        fields: [textField('record_id', 'Notice')],
        columns: [col('record_id', 'Notice'), col('status', 'Statut')],
      },
    ],
  },
];

export const specialRoutes: SpecialRoute[] = [];
