/**
 * Feature Dépôts — configs CRUD (localisation physique D03).
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, numberField, selectField, refField, yesNo, badge } from '@/lib/crud/helpers';
import * as api from './services/deposit.service';

export const resources: ResourceConfig[] = [
  {
    path: '/deposits/buildings', label: 'Bâtiment', plural: 'Bâtiments',
    api: api.buildingsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('visibility', 'Visibilité')],
    fields: [textField('name', 'Nom', { required: true }), selectField('visibility', 'Visibilité', [{ value: 'public', label: 'Public' }, { value: 'private', label: 'Privé' }, { value: 'inherit', label: 'Hérité' }])],
  },
  {
    path: '/deposits/floors', label: 'Étage', plural: 'Étages',
    api: api.floorsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('building_id', 'Bâtiment')],
    fields: [textField('name', 'Nom', { required: true }), refField('building_id', 'Bâtiment', api.buildingsApi)],
  },
  {
    path: '/deposits/rooms', label: 'Salle', plural: 'Salles',
    api: api.roomsApi, titleKey: 'name', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('name', 'Nom'), col('type', 'Type')],
    fields: [textField('code', 'Code'), textField('name', 'Nom', { required: true }), refField('floor_id', 'Étage', api.floorsApi)],
  },
  {
    path: '/deposits/shelves', label: 'Étagère', plural: 'Étagères',
    api: api.shelvesApi, titleKey: 'code', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('room_id', 'Salle')],
    fields: [textField('code', 'Code', { required: true }), refField('room_id', 'Salle', api.roomsApi), numberField('face', 'Face'), numberField('ear', 'Oreille'), numberField('shelf', 'Tablette'), numberField('shelf_length', 'Longueur (cm)')],
  },
  {
    path: '/deposits/containers', label: 'Contenant', plural: 'Contenants',
    api: api.containersApi, titleKey: 'code', codeKey: 'code', creatable: true, editable: true, deletable: true,
    columns: [col('code', 'Code', { render: badge }), col('status_id', 'Statut'), col('capacity_cm', 'Capacité (cm)'), col('is_archived', 'Archivé', { render: yesNo })],
    fields: [textField('code', 'Code', { required: true }), refField('shelve_id', 'Étagère', api.shelvesApi), refField('status_id', 'Statut', api.containerStatusesApi), refField('property_id', 'Propriété', api.containerPropertiesApi), numberField('capacity_cm', 'Capacité (cm)')],
  },
  {
    path: '/tools/container-status', label: 'Statut de contenant', plural: 'Statuts de contenants',
    api: api.containerStatusesApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/container-status'],
    columns: [col('name', 'Nom')],
    fields: [textField('name', 'Nom', { required: true })],
  },
  {
    path: '/tools/container-property', label: 'Propriété de contenant', plural: 'Propriétés de contenants',
    api: api.containerPropertiesApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    aliases: ['/settings/container-property'],
    columns: [col('name', 'Nom'), col('width', 'Largeur'), col('length', 'Longueur'), col('depth', 'Profondeur')],
    fields: [textField('name', 'Nom', { required: true }), numberField('width', 'Largeur'), numberField('length', 'Longueur'), numberField('depth', 'Profondeur')],
  },
];

export const specialRoutes: SpecialRoute[] = [];
