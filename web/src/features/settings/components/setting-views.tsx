'use client';

import { useEffect, useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { PageHeader, InfoPanel, InfoScreen } from '@/components/ui/page';
import { rolesApi, usersApi } from '../services/setting.service';
import * as installApi from '@/features/install/services/install.service';

/** Mon compte — profil de l'agent connecté (session `auth/me`). */
export function Account() {
  return (
    <InfoScreen
      title="Mon compte"
      description="Profil de l'agent connecté."
      sections={[
        ['Profil', 'Nom, email, organisation courante — issus de la session (GET /api/v1/auth/me).'],
        ['Mot de passe', 'Changement de mot de passe à connecter à un formulaire dédié.'],
      ]}
    />
  );
}

/** Matrice rôles / utilisateurs (permissions fines côté Laravel). */
export function RolePermissions() {
  const { data: roles } = useQuery({ queryKey: ['roles'], queryFn: async () => (await rolesApi.list({ per_page: 100 })) as { data: any[] } });
  const { data: users } = useQuery({ queryKey: ['users'], queryFn: async () => (await usersApi.list({ per_page: 100 })) as { data: any[] } });

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Rôles et permissions" description="Matrice rôles / utilisateurs (les permissions fines sont gérées côté Laravel)." />
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
        <InfoPanel title="Rôles" items={(roles?.data ?? []).map((r) => [r.name, r.description ?? '—'] as [string, string])} />
        <InfoPanel title="Utilisateurs" items={(users?.data ?? []).map((u) => [u.name, u.email ?? '—'] as [string, string])} />
      </div>
    </div>
  );
}

/** Mises à jour système — version courante. */
export function SystemUpdates() {
  return (
    <InfoScreen
      title="Mises à jour système"
      description="Gestion des versions de l'application."
      sections={[['Version', "Version courante exposée par Laravel (version.json) — à brancher sur un endpoint dédié."]]}
    />
  );
}

/** Configuration LDAP. */
export function Ldap() {
  return (
    <InfoScreen
      title="Connexion LDAP"
      description="Configuration de l'annuaire LDAP."
      sections={[['Non porté', 'La configuration LDAP est gérée côté Laravel (paramètres applicatifs).']]}
    />
  );
}

/** Activation / désactivation des modules — réservé au superadmin. */
export function Modules() {
  const queryClient = useQueryClient();
  const { data, isLoading } = useQuery({
    queryKey: ['enabled-modules'],
    queryFn: installApi.getEnabledModules,
  });
  const [selected, setSelected] = useState<Set<string>>(new Set());
  const [saving, setSaving] = useState(false);
  const [saved, setSaved] = useState(false);

  const modules = data?.data ?? [];

  // Synchronise la sélection avec les données chargées.
  useEffect(() => {
    setSelected(new Set(modules.filter((m) => m.enabled).map((m) => m.key)));
  }, [data]);

  async function save() {
    setSaving(true);
    setSaved(false);
    try {
      await installApi.setEnabledModules([...selected]);
      setSaved(true);
      queryClient.invalidateQueries({ queryKey: ['enabled-modules'] });
    } finally {
      setSaving(false);
    }
  }

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader
        title="Modules"
        description="Activez ou désactivez les modules de l'application. Les modules obligatoires (Notices, Transferts, Outils, Paramètres) restent toujours actifs."
      />
      {isLoading ? (
        <p className="text-sm text-muted-foreground">Chargement…</p>
      ) : (
        <div className="grid grid-cols-1 gap-2 md:grid-cols-2">
          {modules.map((m) => {
            const checked = selected.has(m.key);
            const locked = m.required;
            return (
              <label
                key={m.key}
                className={`flex cursor-pointer items-start gap-3 rounded-lg border p-3 ${checked ? 'border-primary bg-primary/5' : 'border-border'}`}
              >
                <input
                  type="checkbox"
                  checked={checked}
                  disabled={locked}
                  onChange={() => {
                    setSelected((prev) => {
                      const next = new Set(prev);
                      if (next.has(m.key)) next.delete(m.key);
                      else next.add(m.key);
                      return next;
                    });
                    setSaved(false);
                  }}
                  className="mt-0.5 h-4 w-4"
                />
                <span>
                  <span className="block text-sm font-medium">
                    {m.label}
                    {m.required && (
                      <span className="ml-1 rounded bg-primary/10 px-1.5 py-0.5 text-[10px] font-semibold text-primary">obligatoire</span>
                    )}
                  </span>
                  <span className="block text-xs text-muted-foreground">{m.description}</span>
                </span>
              </label>
            );
          })}
        </div>
      )}
      <div className="flex items-center gap-3">
        <button
          type="button"
          disabled={saving}
          onClick={() => void save()}
          className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:opacity-60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
          {saving ? 'Enregistrement…' : 'Enregistrer'}
        </button>
        {saved && <span className="text-sm text-muted-foreground">Modules enregistrés.</span>}
      </div>
    </div>
  );
}
