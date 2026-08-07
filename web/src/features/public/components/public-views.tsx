'use client';

import { useQuery } from '@tanstack/react-query';
import { PageHeader, StatCard, InfoPanel, InfoScreen } from '@/components/ui/page';
import { publicNewsApi, publicEventsApi, publicUsersApi } from '../services/public.service';

/** Tableau de bord du portail public — compteurs sur données réelles. */
export function PublicDashboard() {
  const { data: news } = useQuery({ queryKey: ['pub-news'], queryFn: async () => (await publicNewsApi.list({ per_page: 5 })) as { data: any[] } });
  const { data: events } = useQuery({ queryKey: ['pub-events'], queryFn: async () => (await publicEventsApi.list({ per_page: 5 })) as { data: any[] } });
  const { data: users } = useQuery({ queryKey: ['pub-users'], queryFn: async () => (await publicUsersApi.list({ per_page: 5 })) as { data: any[] } });

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Portail public — Tableau de bord" description="Vue d'ensemble du contenu public (portail OPAC)." />
      <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
        <StatCard label="Actualités" value={news?.data?.length ?? '…'} />
        <StatCard label="Événements" value={events?.data?.length ?? '…'} />
        <StatCard label="Utilisateurs publics" value={users?.data?.length ?? '…'} />
        <StatCard label="Templates" value="—" />
      </div>
      <InfoPanel title="Statistiques" items={[['Périmètre', 'Compteurs bruts issus des endpoints publics ; les statistiques détaillées seront exposées ultérieurement.']]} />
    </div>
  );
}

/** Fonctionnalités publiques sans endpoint API → écran informatif. */
export function PublicInfo({ title, description, note }: { title: string; description: string; note: string }) {
  return (
    <InfoScreen
      title={title}
      description={description}
      sections={[['Statut', note]]}
    />
  );
}
