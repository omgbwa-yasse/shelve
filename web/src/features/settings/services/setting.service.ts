/**
 * Feature Paramètres — services connectés à l'API (D01/D09/D16).
 */
import { createResourceApi } from '@/lib/api/resources';

export const settingsApi = createResourceApi('settings');
export const settingCategoriesApi = createResourceApi('setting-categories');
export const usersApi = createResourceApi('users');
export const rolesApi = createResourceApi('roles');
export const userRolesApi = createResourceApi('user-roles');
export const userOrganisationRolesApi = createResourceApi('user-organisation-roles');
export const organisationsApi = createResourceApi('organisations');
export const backupsApi = createResourceApi('backups');
export const backupFilesApi = createResourceApi('backup-files');
export const backupPlanningsApi = createResourceApi('backup-plannings');
export const logsApi = createResourceApi('logs');
