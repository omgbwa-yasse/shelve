'use client';

import Link from 'next/link';
import { useWorkplace } from '@/features/workplaces/context';
import { Icon } from '@/components/icons';
import { formatDate } from '@/utils/format-date';

const ROLE_COLORS: Record<string, string> = {
  owner: 'bg-red-100 text-red-700',
  admin: 'bg-amber-100 text-amber-700',
  editor: 'bg-sky-100 text-sky-700',
  viewer: 'bg-slate-200 text-slate-600',
  contributor: 'bg-indigo-100 text-indigo-700',
};

const ACTIVITY_ICONS: Record<string, { icon: 'folderPlus' | 'fileText' | 'folderOpen' | 'personPlus' | 'personCheck' | 'settings'; bg: string; color: string; label: string }> = {
  shared_folder: { icon: 'folderPlus', bg: '#d1fae5', color: '#059669', label: 'Dossier partagé' },
  shared_document: { icon: 'fileText', bg: '#dbeafe', color: '#2563eb', label: 'Document partagé' },
  created_folder: { icon: 'folderPlus', bg: '#d1fae5', color: '#059669', label: 'Dossier créé' },
  created_document: { icon: 'fileText', bg: '#dbeafe', color: '#2563eb', label: 'Document créé' },
  deleted_folder: { icon: 'folderOpen', bg: '#fee2e2', color: '#dc2626', label: 'Dossier retiré' },
  deleted_document: { icon: 'fileText', bg: '#fee2e2', color: '#dc2626', label: 'Document retiré' },
  member_added: { icon: 'personPlus', bg: '#e0e7ff', color: '#4f46e5', label: 'Membre ajouté' },
  member_removed: { icon: 'personCheck', bg: '#fef3c7', color: '#d97706', label: 'Membre retiré' },
  settings_changed: { icon: 'settings', bg: '#f3f4f6', color: '#6b7280', label: 'Paramètres modifiés' },
};

/**
 * Tableau de bord d'un workplace — reproduit `workplaces/show.blade.php` :
 * statistiques (membres, stockage), à-propos, actions rapides, membres et
 * activité récente, fiche d'informations (code compris).
 */
export default function WorkplaceDashboardPage() {
  const { code, workplace } = useWorkplace();

  if (!workplace) return null;

  const wPath = `/workplace/${encodeURIComponent(code)}`;
  const members = workplace.members ?? [];
  const activities = workplace.activities ?? [];

  return (
    <div className="flex flex-col gap-4">
      {/* ===================== STATS ===================== */}
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div className="flex items-center gap-3 rounded-xl border border-border bg-surface p-4 shadow-sm">
          <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10">
            <Icon name="users" className="h-6 w-6 text-primary" />
          </div>
          <div>
            <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Membres</p>
            <p className="text-2xl font-bold leading-none">{workplace.members_count}</p>
            {workplace.max_members ? (
              <p className="mt-1 text-xs text-muted-foreground">
                sur {workplace.max_members} max
              </p>
            ) : null}
          </div>
        </div>
        <div className="flex items-center gap-3 rounded-xl border border-border bg-surface p-4 shadow-sm">
          <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-700">
            <Icon name="hardDrive" className="h-6 w-6" />
          </div>
          <div>
            <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Stockage</p>
            <p className="text-2xl font-bold leading-none">
              {workplace.storage_used_mb ?? 0} <span className="text-sm font-normal text-muted-foreground">MB</span>
            </p>
            {workplace.max_storage_mb ? (
              <p className="mt-1 text-xs text-muted-foreground">sur {workplace.max_storage_mb} MB</p>
            ) : null}
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {/* ===================== COLONNE PRINCIPALE ===================== */}
        <div className="flex flex-col gap-4 lg:col-span-2">
          {workplace.description && (
            <section className="rounded-xl border border-border bg-surface p-4 shadow-sm">
              <h3 className="mb-2 flex items-center gap-2 text-sm font-semibold">
                <Icon name="home" className="h-4 w-4 text-primary" />
                À propos de cet espace
              </h3>
              <p className="text-sm text-muted-foreground">{workplace.description}</p>
              <div className="mt-2 flex flex-wrap gap-4 text-xs text-muted-foreground">
                <span className="flex items-center gap-1">
                  <Icon name="person" className="h-3 w-3" />
                  Créé par <strong className="font-semibold text-foreground">{workplace.owner?.name ?? '—'}</strong>
                </span>
                {workplace.created_at && (
                  <span className="flex items-center gap-1">
                    <Icon name="calendar" className="h-3 w-3" />
                    {formatDate(workplace.created_at)}
                  </span>
                )}
              </div>
            </section>
          )}

          <section className="rounded-xl border border-border bg-surface shadow-sm">
            <header className="flex items-center justify-between border-b border-border px-4 py-2.5 text-sm font-semibold">
              <span className="flex items-center gap-2">
                <Icon name="users" className="h-4 w-4 text-primary" />
                Membres
                <span className="rounded-full bg-primary px-2 py-0.5 text-xs text-primary-foreground">
                  {workplace.members_count}
                </span>
              </span>
              <Link href={`${wPath}/members`} className="text-xs font-normal text-muted-foreground hover:text-foreground">
                Gérer
              </Link>
            </header>
            <ul className="divide-y divide-border">
              {members.slice(0, 6).map((m) => (
                <li key={m.id} className="flex items-center gap-3 px-4 py-2">
                  <span className="flex h-9 w-9 items-center justify-center rounded-full text-xs font-semibold text-white"
                        style={{ background: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'][m.user_id % 6] }}>
                    {((m.user?.name ?? '?').slice(0, 2)).toUpperCase()}
                  </span>
                  <span className="min-w-0 flex-1 truncate text-sm">{m.user?.name ?? '—'}</span>
                  <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${ROLE_COLORS[m.role] ?? 'bg-slate-200 text-slate-600'}`}>
                    {m.role}
                  </span>
                </li>
              ))}
              {members.length === 0 && (
                <li className="px-4 py-6 text-center text-sm text-muted-foreground">Aucun membre.</li>
              )}
            </ul>
          </section>
        </div>

        {/* ===================== COLONNE LATÉRALE ===================== */}
        <div className="flex flex-col gap-4">
          <section className="rounded-xl border border-border bg-surface p-4 shadow-sm">
            <h3 className="mb-2 flex items-center gap-2 text-sm font-semibold">
              <Icon name="zap" className="h-4 w-4 text-amber-500" />
              Actions rapides
            </h3>
            <div className="flex flex-col gap-2">
              <Link href={`${wPath}/members`} className="rounded-lg border border-border px-3 py-2 text-left text-sm hover:bg-muted">
                <Icon name="personPlus" className="mr-2 inline h-4 w-4 text-primary" />
                Inviter un membre
              </Link>
              <Link href={`${wPath}/edit`} className="rounded-lg border border-border px-3 py-2 text-left text-sm hover:bg-muted">
                <Icon name="save" className="mr-2 inline h-4 w-4" />
                Modifier l'espace
              </Link>
            </div>
          </section>

          <section className="rounded-xl border border-border bg-surface shadow-sm">
            <header className="flex items-center justify-between border-b border-border px-4 py-2.5 text-sm font-semibold">
              <span className="flex items-center gap-2">
                <Icon name="history" className="h-4 w-4 text-green-600" />
                Activité récente
              </span>
              <Link href={`${wPath}/activities`} className="text-xs font-normal text-muted-foreground hover:text-foreground">
                Historique
              </Link>
            </header>
            <ul className="divide-y divide-border">
              {activities.slice(0, 5).map((a) => {
                const cfg = ACTIVITY_ICONS[a.activity_type];
                return (
                  <li key={a.id} className="flex items-start gap-3 px-4 py-2.5">
                    <span className="mt-1 flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                          style={{ background: cfg?.bg ?? '#f3f4f6' }}>
                      <span style={{ color: cfg?.color ?? '#6b7280' }}>
                        <Icon name={cfg?.icon ?? 'settings'} className="h-3.5 w-3.5" />
                      </span>
                    </span>
                    <div className="min-w-0 flex-1">
                      <p className="text-xs">
                        <strong className="font-semibold">{a.user?.name ?? '—'}</strong>{' '}
                        <span className="text-muted-foreground">{a.description}</span>
                      </p>
                      <p className="text-[10px] text-muted-foreground">{formatDate(a.created_at)}</p>
                    </div>
                  </li>
                );
              })}
              {activities.length === 0 && (
                <li className="px-4 py-6 text-center text-sm text-muted-foreground">Aucune activité récente.</li>
              )}
            </ul>
          </section>

          <section className="rounded-xl border border-border bg-surface shadow-sm">
            <header className="border-b border-border px-4 py-2.5 text-sm font-semibold">
              <span className="flex items-center gap-2">
                <Icon name="settings" className="h-4 w-4 text-muted-foreground" />
                Informations
              </span>
            </header>
            <dl className="divide-y divide-border text-sm">
              <InfoRow label="Code" value={workplace.code} mono />
              <InfoRow label="Catégorie" value={workplace.category?.name ?? '—'} />
              <InfoRow label="Propriétaire" value={workplace.owner?.name ?? '—'} />
              <InfoRow label="Visibilité" value={workplace.is_public ? 'Public' : 'Privé'} />
              <InfoRow label="Créé le" value={workplace.created_at ? formatDate(workplace.created_at) : '—'} />
              {workplace.start_date && (
                <InfoRow
                  label="Période"
                  value={`${workplace.start_date} — ${workplace.end_date ?? '∞'}`}
                />
              )}
              {workplace.allow_external_sharing && <InfoRow label="Partage externe" value="Activé" />}
            </dl>
          </section>
        </div>
      </div>
    </div>
  );
}

function InfoRow({ label, value, mono }: { label: string; value: string; mono?: boolean }) {
  return (
    <div className="flex items-center justify-between px-4 py-2">
      <dt className="text-muted-foreground">{label}</dt>
      <dd className={mono ? 'font-mono font-semibold' : 'font-semibold'}>{value}</dd>
    </div>
  );
}
