'use client';

import { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import type { ResourceApi } from '@/lib/api/resources';
import type { Entity } from '@/lib/api/types';
import type { TableColumn } from '@/components/ui/table';
import { useResourceList, useDestroy, useCreate } from '@/lib/api/hooks';
import { DataTable, Pagination } from '@/components/ui/table';
import * as api from './services/setting.service';
import { Account, RolePermissions, SystemUpdates, Ldap } from './components/setting-views';
import type { FeatureRoute } from '@/lib/routing';

function makeList(r: ResourceApi, key: string, columns: TableColumn<Entity>[], o: { title: string; create?: string }) {
  return function List() {
    const [page, setPage] = useState(1);
    const { data, isLoading, isError } = useResourceList(r, key, { page, 'page.size': 20 } as never);
    const destroy = useDestroy(r, key);
    const rows = (data?.data ?? []) as Entity[];
    return (
      <div className="flex h-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{o.title}</h1>
          {o.create && <Link href={o.create} className="rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">+ Nouveau</Link>}
        </header>
        <DataTable columns={columns} rows={rows} loading={isLoading} error={isError}
          actions={(row) => <button type="button" onClick={() => { if (window.confirm('Supprimer ?')) destroy.mutate(row.id); }} className="rounded border border-border px-2 py-1 text-xs text-danger">Supprimer</button>} />
        <Pagination page={page} totalPages={data?.meta?.last_page ?? 1} total={data?.meta?.total} onChange={setPage} />
      </div>
    );
  };
}

function makeForm(r: ResourceApi, key: string, o: { title: string; back: string; fields: { name: string; label: string; required?: boolean }[] }) {
  return function Form() {
    const router = useRouter();
    const create = useCreate(r, key);
    const [v, setV] = useState<Record<string, string>>({});
    async function submit(e: React.FormEvent) {
      e.preventDefault();
      await create.mutateAsync(v);
      router.push(o.back);
    }
    return (
      <form onSubmit={submit} className="flex w-full flex-col gap-4">
        <header className="flex items-center justify-between">
          <h1 className="text-xl font-semibold">{o.title}</h1>
          <button type="button" onClick={() => router.push(o.back)} className="rounded border border-border px-3 py-1.5 text-sm">Annuler</button>
        </header>
        <div className="grid w-full grid-cols-1 gap-4 rounded border border-border bg-surface p-4 md:grid-cols-2 xl:grid-cols-3">
          {o.fields.map((f) => (
            <label key={f.name} className="flex flex-col gap-1 text-sm">
              <span>{f.label} {f.required && <span className="text-danger">*</span>}</span>
              <input value={v[f.name] ?? ''} onChange={(e) => setV((p) => ({ ...p, [f.name]: e.target.value }))} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
            </label>
          ))}
        </div>
        <footer className="flex justify-end"><button type="submit" className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground">Enregistrer</button></footer>
      </form>
    );
  };
}

const NAME_COLS: TableColumn<Entity>[] = [{ key: 'name', label: 'Nom' }, { key: 'email', label: 'Email' }];

export const routes: FeatureRoute[] = [
  { path: '/settings/definitions', List: makeList(api.settingsApi, 'settings', [{ key: 'key', label: 'Clé', render: (r) => <span className="font-mono text-xs">{String(r.key ?? '')}</span> }, { key: 'name', label: 'Nom' }, { key: 'value', label: 'Valeur' }], { title: 'Paramètres', create: '/settings/definitions/create' }), Form: makeForm(api.settingsApi, 'settings', { title: 'Paramètre', back: '/settings/definitions', fields: [{ name: 'key', label: 'Clé', required: true }, { name: 'name', label: 'Nom', required: true }, { name: 'value', label: 'Valeur' }] }) },
  { path: '/settings/categories', List: makeList(api.settingCategoriesApi, 'setting-categories', [{ key: 'name', label: 'Nom' }], { title: 'Catégories de paramètres', create: '/settings/categories/create' }), Form: makeForm(api.settingCategoriesApi, 'setting-categories', { title: 'Catégorie', back: '/settings/categories', fields: [{ name: 'name', label: 'Nom', required: true }] }) },
  { path: '/settings/users', List: makeList(api.usersApi, 'users', NAME_COLS, { title: 'Utilisateurs', create: '/settings/users/create' }), Form: makeForm(api.usersApi, 'users', { title: 'Utilisateur', back: '/settings/users', fields: [{ name: 'name', label: 'Nom', required: true }, { name: 'email', label: 'Email', required: true }] }) },
  { path: '/settings/roles', List: makeList(api.rolesApi, 'roles', [{ key: 'name', label: 'Nom' }, { key: 'description', label: 'Description' }], { title: 'Rôles', create: '/settings/roles/create' }), Form: makeForm(api.rolesApi, 'roles', { title: 'Rôle', back: '/settings/roles', fields: [{ name: 'name', label: 'Nom', required: true }, { name: 'description', label: 'Description' }] }) },
  { path: '/settings/user-roles', List: makeList(api.userRolesApi, 'user-roles', [{ key: 'user_id', label: 'Utilisateur' }, { key: 'role_id', label: 'Rôle' }], { title: 'Rôles utilisateurs', create: '/settings/user-roles/create' }), Form: makeForm(api.userRolesApi, 'user-roles', { title: 'Rôle utilisateur', back: '/settings/user-roles', fields: [{ name: 'user_id', label: 'Utilisateur' }, { name: 'role_id', label: 'Rôle' }] }) },
  { path: '/settings/user-organisation-role', List: makeList(api.userOrganisationRolesApi, 'user-organisation-roles', [{ key: 'user_id', label: 'Utilisateur' }, { key: 'organisation_id', label: 'Organisation' }, { key: 'role_id', label: 'Rôle' }], { title: 'Postes assignés', create: '/settings/user-organisation-role/create' }), Form: makeForm(api.userOrganisationRolesApi, 'user-organisation-roles', { title: 'Poste assigné', back: '/settings/user-organisation-role', fields: [{ name: 'user_id', label: 'Utilisateur' }, { name: 'organisation_id', label: 'Organisation' }, { name: 'role_id', label: 'Rôle' }] }) },
  { path: '/settings/backups', List: makeList(api.backupsApi, 'backups', [{ key: 'name', label: 'Nom' }, { key: 'created_at', label: 'Créée le', render: (r) => (r.created_at ? new Date(String(r.created_at)).toLocaleDateString('fr-FR') : '—') }], { title: 'Sauvegardes', create: '/settings/backups/create' }), Form: makeForm(api.backupsApi, 'backups', { title: 'Sauvegarde', back: '/settings/backups', fields: [{ name: 'name', label: 'Nom', required: true }] }) },
  { path: '/settings/backup-files', List: makeList(api.backupFilesApi, 'backup-files', [{ key: 'filename', label: 'Fichier' }, { key: 'size', label: 'Taille' }], { title: 'Fichiers de sauvegarde' }) },
  { path: '/settings/backup-plannings', List: makeList(api.backupPlanningsApi, 'backup-plannings', [{ key: 'name', label: 'Nom' }, { key: 'frequency', label: 'Fréquence' }], { title: 'Planifications de sauvegarde', create: '/settings/backup-plannings/create' }), Form: makeForm(api.backupPlanningsApi, 'backup-plannings', { title: 'Planification', back: '/settings/backup-plannings', fields: [{ name: 'name', label: 'Nom', required: true }, { name: 'frequency', label: 'Fréquence' }] }) },

  { path: '/settings/account', List: Account },
  { path: '/settings/role-permissions', List: RolePermissions },
  { path: '/settings/system-updates', List: SystemUpdates },
  { path: '/settings/ldap', List: Ldap },
];
