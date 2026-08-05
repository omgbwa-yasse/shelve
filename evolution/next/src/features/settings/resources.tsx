/**
 * Feature Paramètres — configs CRUD + routes spéciales (D01/D09/D16).
 */
import type { ResourceConfig, SpecialRoute } from '@/lib/crud/types';
import { col, textField, textareaField, boolField, refField, emailField, yesNo, badge } from '@/lib/crud/helpers';
import * as api from './services/setting.service';
import { Account, RolePermissions, SystemUpdates, Ldap } from './components/setting-views';

const DATETIME = (v: unknown) => (v ? new Date(String(v)).toLocaleDateString('fr-FR') : <span className="text-muted-foreground/60">—</span>);

export const resources: ResourceConfig[] = [
  {
    path: '/settings/definitions', label: 'Paramètre', plural: 'Paramètres',
    api: api.settingsApi, titleKey: 'name', codeKey: 'key', creatable: true, editable: true, deletable: true,
    columns: [col('key', 'Clé', { render: badge }), col('name', 'Nom'), col('value', 'Valeur')],
    fields: [textField('key', 'Clé', { required: true }), textField('name', 'Nom', { required: true }), textField('value', 'Valeur')],
  },
  {
    path: '/settings/categories', label: 'Catégorie de paramètres', plural: 'Catégories de paramètres',
    api: api.settingCategoriesApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('parent_id', 'Catégorie parente')],
    fields: [textField('name', 'Nom', { required: true }), refField('parent_id', 'Catégorie parente', api.settingCategoriesApi)],
  },
  {
    path: '/settings/users', label: 'Utilisateur', plural: 'Utilisateurs',
    api: api.usersApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('email', 'Email'), col('surname', 'Prénom')],
    fields: [textField('name', 'Nom', { required: true }), emailField('email', 'Email', { required: true }), textField('surname', 'Prénom'), textField('password', 'Mot de passe', { type: 'password' })],
  },
  {
    path: '/settings/roles', label: 'Rôle', plural: 'Rôles',
    api: api.rolesApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('description', 'Description')],
    fields: [textField('name', 'Nom', { required: true }), textareaField('description', 'Description')],
  },
  {
    path: '/settings/user-roles', label: 'Rôle utilisateur', plural: 'Rôles utilisateurs',
    api: api.userRolesApi, titleKey: 'user_id', creatable: true, editable: true, deletable: true,
    columns: [col('user_id', 'Utilisateur'), col('role_id', 'Rôle')],
    fields: [refField('user_id', 'Utilisateur', api.usersApi), refField('role_id', 'Rôle', api.rolesApi)],
  },
  {
    path: '/settings/user-organisation-role', label: 'Poste assigné', plural: 'Postes assignés',
    api: api.userOrganisationRolesApi, titleKey: 'user_id', creatable: true, editable: true, deletable: true,
    columns: [col('user_id', 'Utilisateur'), col('organisation_id', 'Organisation'), col('role_id', 'Rôle')],
    fields: [refField('user_id', 'Utilisateur', api.usersApi), refField('organisation_id', 'Organisation', api.organisationsApi), refField('role_id', 'Rôle', api.rolesApi)],
  },
  {
    path: '/settings/backups', label: 'Sauvegarde', plural: 'Sauvegardes',
    api: api.backupsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('created_at', 'Créée le', { render: DATETIME })],
    fields: [textField('name', 'Nom', { required: true })],
  },
  {
    path: '/settings/backup-files', label: 'Fichier de sauvegarde', plural: 'Fichiers de sauvegarde',
    api: api.backupFilesApi, titleKey: 'filename', creatable: false, editable: false, deletable: true,
    columns: [col('filename', 'Fichier'), col('size', 'Taille')],
    fields: [textField('filename', 'Fichier')],
  },
  {
    path: '/settings/backup-plannings', label: 'Planification de sauvegarde', plural: 'Planifications de sauvegarde',
    api: api.backupPlanningsApi, titleKey: 'name', creatable: true, editable: true, deletable: true,
    columns: [col('name', 'Nom'), col('frequency', 'Fréquence'), col('enabled', 'Activée', { render: yesNo })],
    fields: [textField('name', 'Nom', { required: true }), textField('frequency', 'Fréquence'), boolField('enabled', 'Activée')],
  },
];

export const specialRoutes: SpecialRoute[] = [
  { path: '/settings/account', exact: true, component: Account },
  { path: '/settings/role-permissions', component: RolePermissions },
  { path: '/settings/system-updates', exact: true, component: SystemUpdates },
  { path: '/settings/ldap', exact: true, component: Ldap },
];
