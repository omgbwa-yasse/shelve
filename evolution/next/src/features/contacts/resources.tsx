/**
 * Feature Contacts — configs CRUD (D01 sous-lot contacts).
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, emailField, textareaField, refField } from '@/lib/crud/helpers';
import * as api from './services/contact.service';

export const resources: ResourceConfig[] = [
  {
    path: '/contacts', label: 'Contact externe', plural: 'Contacts externes',
    api: api.externalContactsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('email', 'Email'), col('phone', 'Téléphone')],
    fields: [textField('name', 'Nom', { required: true }), emailField('email', 'Email'), textField('phone', 'Téléphone'), refField('organisation_id', 'Organisation', api.externalOrganizationsApi)],
  },
  {
    path: '/contacts/organisations', label: 'Organisation externe', plural: 'Organisations externes',
    api: api.externalOrganizationsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('email', 'Email'), col('phone', 'Téléphone')],
    fields: [textField('name', 'Nom', { required: true }), emailField('email', 'Email'), textField('phone', 'Téléphone'), textareaField('address', 'Adresse')],
  },
];

export const specialRoutes: SpecialRoute[] = [];
