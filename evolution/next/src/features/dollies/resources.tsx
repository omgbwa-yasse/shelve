/**
 * Feature Chariots — configs CRUD (D11).
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, selectField } from '@/lib/crud/helpers';
import * as api from './services/dolly.service';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);

export const resources: ResourceConfig[] = [
  {
    path: '/dollies', label: 'Chariot', plural: 'Chariots',
    api: api.dolliesApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('category', 'Catégorie'), col('created_at', 'Créé le', { render: DATETIME })],
    fields: [
      textField('name', 'Nom', { required: true }),
      selectField('category', 'Catégorie', [
        { value: 'mail', label: 'Courrier' }, { value: 'record', label: 'Archives' }, { value: 'communication', label: 'Communication' },
        { value: 'room', label: 'Salle' }, { value: 'shelf', label: 'Étagère' }, { value: 'container', label: 'Boîtes d’archives' },
        { value: 'slip_record', label: 'Transfert d’archives' }, { value: 'slip', label: 'Transfert' },
        { value: 'digital_folder', label: 'Dossiers numériques' }, { value: 'digital_document', label: 'Documents numériques' },
      ]),
    ],
    rowActions: [{ label: 'Vider', verb: 'clear', method: 'action', confirm: 'Vider ce chariot ?' }],
  },
];

export const specialRoutes: SpecialRoute[] = [];
