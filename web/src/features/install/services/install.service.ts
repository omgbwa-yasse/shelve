/**
 * Installation + gestion des modules — endpoints consommés par la page `/install`
 * (sans auth) et par l'écran paramètres « Modules » (superadmin, auth).
 */
import { apiFetch } from '@/lib/api/client';

export type ModuleOption = {
  key: string;
  label: string;
  description: string;
  required: boolean;
};

export type InstallationResult = {
  executed: Record<string, string>;
  organisation_name: string;
  modules: string[];
  seeders: string[];
};

export type InstallationOptions = {
  options: ModuleOption[];
  required_modules: string[];
  installed: boolean;
};

/** GET /api/v1/installation — liste des modules cochables + état installé. */
export function getInstallationOptions() {
  return apiFetch<InstallationOptions>('/api/v1/installation');
}

/** POST /api/v1/installation — exécute l'installation. */
export function runInstallation(organisationName: string, modules: string[], force = false) {
  return apiFetch<{ data: InstallationResult }>('/api/v1/installation', {
    method: 'POST',
    body: { organisation_name: organisationName, modules, force },
  });
}

export type EnabledModule = ModuleOption & { enabled: boolean };

/** GET /api/v1/settings/modules — modules avec état (superadmin). */
export function getEnabledModules() {
  return apiFetch<{ data: EnabledModule[] }>('/api/v1/settings/modules');
}

/** PUT /api/v1/settings/modules — active/désactive les modules. */
export function setEnabledModules(modules: string[]) {
  return apiFetch<{ data: string[] }>('/api/v1/settings/modules', {
    method: 'PUT',
    body: { modules },
  });
}
