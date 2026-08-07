'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useWorkplace } from '@/features/workplaces/context';
import { archiveWorkplace, destroyWorkplace } from '@/features/workplaces/services/workplace.service';
import { Icon } from '@/components/icons';

/**
 * Paramètres d'un workplace — reproduit `workplaces/settings.blade.php` :
 * informations générales, gestion des membres et zone de danger.
 */
export default function WorkplaceSettingsPage() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const { code, workplace } = useWorkplace();

  const archive = useMutation({
    mutationFn: () => archiveWorkplace(code),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['workplace', code] });
      queryClient.invalidateQueries({ queryKey: ['workplaces'] });
    },
  });

  const destroy = useMutation({
    mutationFn: () => destroyWorkplace(code),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['workplaces'] });
      router.push('/workplaces');
    },
  });

  if (!workplace) return null;

  const wPath = `/workplace/${encodeURIComponent(code)}`;

  return (
    <div className="flex flex-col gap-4">
      <header className="flex items-center justify-between">
        <h3 className="text-lg font-semibold">Paramètres : {workplace.name}</h3>
        <Link href={wPath} className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted">
          <Icon name="arrowRightSquare" className="mr-1 inline h-4 w-4 rotate-180" />
          Retour
        </Link>
      </header>

      <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
        <nav className="flex flex-col gap-1 text-sm md:col-span-1">
          <a href="#general" className="rounded border border-primary bg-primary/10 px-3 py-2 font-medium text-primary">Général</a>
          <a href="#members" className="rounded border border-border px-3 py-2 text-muted-foreground hover:bg-muted">Membres</a>
          <a href="#danger" className="rounded border border-border px-3 py-2 text-danger hover:bg-danger/5">Zone de danger</a>
        </nav>

        <div className="flex flex-col gap-4 md:col-span-3">
          <section id="general" className="rounded-xl border border-border bg-surface p-4 shadow-sm">
            <header className="border-b border-border pb-2 text-sm font-semibold">Informations générales</header>
            <p className="mt-3 text-sm text-muted-foreground">
              Modifiez le nom, le code (adresse de l'espace), la description et les paramètres de visibilité de votre espace de travail.
            </p>
            <Link href={`${wPath}/edit`} className="mt-3 inline-flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">
              <Icon name="save" className="h-4 w-4" />
              Modifier les informations
            </Link>
          </section>

          <section id="members" className="rounded-xl border border-border bg-surface p-4 shadow-sm">
            <header className="border-b border-border pb-2 text-sm font-semibold">Gestion des membres</header>
            <p className="mt-3 text-sm text-muted-foreground">Gérez les membres, les invitations et les rôles au sein de cet espace de travail.</p>
            <div className="mt-3 flex items-center justify-between">
              <strong className="text-sm">{workplace.members_count} membre(s) actuel(s)</strong>
              <Link href={`${wPath}/members`} className="inline-flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground">
                <Icon name="users" className="h-4 w-4" />
                Gérer les membres
              </Link>
            </div>
          </section>

          <section id="danger" className="rounded-xl border border-danger/40 bg-surface p-4 shadow-sm">
            <header className="border-b border-danger/30 pb-2 text-sm font-semibold text-danger">Zone de danger</header>

            {workplace.status !== 'archived' && (
              <div className="mt-3 border-b border-border pb-4">
                <h5 className="text-sm font-semibold">Archiver l'espace de travail</h5>
                <p className="mt-1 text-sm text-muted-foreground">
                  L'archivage rendra l'espace de travail en lecture seule pour tous les membres.
                </p>
                <button
                  type="button"
                  disabled={archive.isPending}
                  onClick={() => {
                    if (window.confirm('Êtes-vous sûr de vouloir archiver cet espace de travail ?')) archive.mutate();
                  }}
                  className="mt-2 inline-flex items-center gap-1.5 rounded border border-amber-500 bg-amber-100 px-3 py-1.5 text-sm text-amber-700 hover:bg-amber-200 disabled:opacity-60"
                >
                  <Icon name="archive" className="h-4 w-4" />
                  Archiver
                </button>
              </div>
            )}

            <div className="mt-4">
              <h5 className="text-sm font-semibold">Supprimer l'espace de travail</h5>
              <p className="mt-1 text-sm text-muted-foreground">
                Cette action est irréversible. Toutes les données associées seront supprimées définitivement.
              </p>
              <button
                type="button"
                disabled={destroy.isPending}
                onClick={() => {
                  if (window.confirm('Supprimer DÉFINITIVEMENT cet espace de travail ? Cette action est irréversible.')) destroy.mutate();
                }}
                className="mt-2 inline-flex items-center gap-1.5 rounded border border-danger bg-danger px-3 py-1.5 text-sm text-white hover:opacity-90 disabled:opacity-60"
              >
                <Icon name="trash" className="h-4 w-4" />
                Supprimer définitivement
              </button>
            </div>
          </section>
        </div>
      </div>
    </div>
  );
}
