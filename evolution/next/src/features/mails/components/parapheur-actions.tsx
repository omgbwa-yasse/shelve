'use client';

import { useQuery } from '@tanstack/react-query';
import { PageHeader } from '@/components/ui/page';
import { batchesApi } from '../services/mail.service';

/**
 * Actions parapheur (feature Mails) : signer / envoyer / recevoir un parapheur.
 */
export function ParapheurActions({ action }: { action: 'sign' | 'send' | 'receive' }) {
  const { data } = useQuery({
    queryKey: ['batches'],
    queryFn: async () => (await batchesApi.list({ per_page: 50 })) as { data: any[] },
  });
  const batches = data?.data ?? [];

  const labels: Record<'sign' | 'send' | 'receive', string> = {
    sign: 'Parapher',
    send: 'Envoyer le parapheur',
    receive: 'Recevoir le parapheur',
  };

  return (
    <div className="flex h-full flex-col gap-4">
      <PageHeader title={labels[action]} description={`Action « ${labels[action]} » sur un parapheur.`} />
      <div className="rounded border border-border bg-surface p-4">
        <h2 className="mb-2 text-sm font-semibold">Parapheurs disponibles</h2>
        {batches.length === 0 ? (
          <p className="text-sm text-muted-foreground">Aucun parapheur trouvé.</p>
        ) : (
          <ul className="divide-y divide-border text-sm">
            {batches.map((b) => (
              <li key={b.id} className="flex items-center justify-between py-2">
                <span>{b.code ?? ''} — {b.name ?? ''}</span>
                <button type="button" className="rounded border border-border px-2 py-1 text-xs hover:bg-muted">
                  {labels[action]}
                </button>
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  );
}
