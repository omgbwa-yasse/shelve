'use client';

import { useQuery } from '@tanstack/react-query';
import { PageHeader, InfoPanel, InfoScreen } from '@/components/ui/page';
import { rolesApi, usersApi } from '../services/setting.service';

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
