'use client';

import { useQuery } from '@tanstack/react-query';
import { PageHeader } from '@/components/ui/page';
import { InfoScreen } from '@/components/ui/page';
import { aiSkillsApi, promptsApi } from '../services/ai.service';

/** Centre de ressources IA — onglets Skills / Prompts / Templates. */
export function AiResources({ tab }: { tab?: string }) {
  const { data: skills } = useQuery({ queryKey: ['ai-skills'], queryFn: async () => (await aiSkillsApi.list({ per_page: 100 })) as { data: any[] } });
  const { data: prompts } = useQuery({ queryKey: ['ai-prompts'], queryFn: async () => (await promptsApi.list({ per_page: 100 })) as { data: any[] } });

  const active = tab ?? 'skills';

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title="Ressources IA" description="Skills, prompts et templates du centre de ressources IA." />
      <div className="flex gap-1 border-b border-border text-sm">
        {(['skills', 'prompts', 'templates'] as const).map((t) => (
          <a key={t} href={`/ai-search/resources?tab=${t}`} className={`rounded-t border-b-2 px-3 py-1.5 ${active === t ? 'border-primary' : 'border-transparent text-muted-foreground'}`}>
            {t === 'skills' ? 'Skills' : t === 'prompts' ? 'Prompts' : 'Templates'}
          </a>
        ))}
      </div>
      {active === 'skills' && (
        <ul className="divide-y divide-border rounded border border-border bg-surface text-sm">
          {(skills?.data ?? []).map((s) => (
            <li key={s.id} className="flex items-center justify-between px-3 py-2">
              <span>{s.name}</span>
              <span className="rounded bg-muted px-1.5 py-0.5 text-xs">{s.active ? 'Actif' : 'Inactif'}</span>
            </li>
          ))}
        </ul>
      )}
      {active === 'prompts' && (
        <ul className="divide-y divide-border rounded border border-border bg-surface text-sm">
          {(prompts?.data ?? []).map((p) => (
            <li key={p.id} className="px-3 py-2">{p.name}</li>
          ))}
        </ul>
      )}
      {active === 'templates' && <p className="text-sm text-muted-foreground">Templates IA — à configurer.</p>}
    </div>
  );
}

/** Test du système IA (exécution non exposée en API v1). */
export function AiTest() {
  return (
    <InfoScreen
      title="Tester le système IA"
      description="Test de bout en bout de la chaîne IA (requêtes, skills, prompts)."
      sections={[
        ['Non porté', "L'exécution d'IA (AiSearchController) et le chat IA sont des routes web-session, non consomMables par un client Bearer."],
        ['Prérequis', 'Exposer un endpoint API v1 d’exécution pour activer ce test depuis Next.'],
      ]}
    />
  );
}
