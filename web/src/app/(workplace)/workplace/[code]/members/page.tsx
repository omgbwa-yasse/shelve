'use client';

import { useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useWorkplace } from '@/features/workplaces/context';
import { listMembers, inviteMember, updateMemberRole, removeMember } from '@/features/workplaces/services/workplace.service';
import { Icon } from '@/components/icons';
import { formatDate } from '@/utils/format-date';
import type { WorkplaceMember } from '@/features/workplaces/types';

const ROLE_COLORS: Record<string, string> = {
  owner: 'bg-red-100 text-red-700',
  admin: 'bg-amber-100 text-amber-700',
  editor: 'bg-sky-100 text-sky-700',
  viewer: 'bg-slate-200 text-slate-600',
  contributor: 'bg-indigo-100 text-indigo-700',
};

const AVATAR_COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

/**
 * Gestion des membres et invitations — reproduit `workplaces/members/index.blade.php`.
 */
export default function WorkplaceMembersPage() {
  const { code } = useWorkplace();
  const queryClient = useQueryClient();
  const [email, setEmail] = useState('');
  const [role, setRole] = useState('contributor');
  const [message, setMessage] = useState('');

  const { data, isLoading } = useQuery({
    queryKey: ['workplace-members', code],
    queryFn: () => listMembers(code),
    enabled: code.length > 0,
  });
  const members = data?.data ?? [];

  const invalidate = () =>
    queryClient.invalidateQueries({ queryKey: ['workplace-members', code] });

  const invite = useMutation({
    mutationFn: () => inviteMember(code, { email, role, message: message || undefined }),
    onSuccess: () => {
      invalidate();
      setEmail('');
      setMessage('');
      setRole('contributor');
    },
  });

  const changeRole = useMutation({
    mutationFn: ({ memberId, nextRole }: { memberId: number; nextRole: string }) =>
      updateMemberRole(code, memberId, nextRole),
    onSuccess: invalidate,
  });

  const remove = useMutation({
    mutationFn: (memberId: number) => removeMember(code, memberId),
    onSuccess: invalidate,
  });

  return (
    <div className="flex flex-col gap-4">
      <header className="flex items-center justify-between">
        <h3 className="flex items-center gap-2 text-lg font-semibold">
          <Icon name="users" className="h-5 w-5 text-muted-foreground" />
          Gérer les membres et les invitations
        </h3>
      </header>

      {/* ===================== INVITATION ===================== */}
      <form
        onSubmit={(e) => {
          e.preventDefault();
          invite.mutate();
        }}
        className="flex flex-col gap-3 rounded-xl border border-border bg-surface p-4 shadow-sm"
      >
        <h4 className="text-sm font-semibold">Inviter un membre</h4>
        <div className="grid grid-cols-1 gap-3 md:grid-cols-3">
          <label className="flex flex-col gap-1 text-sm">
            <span>Email</span>
            <input
              type="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="rounded border border-border bg-background px-2 py-1.5 text-sm"
              placeholder="collegue@exemple.org"
            />
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span>Rôle</span>
            <select value={role} onChange={(e) => setRole(e.target.value)} className="rounded border border-border bg-background px-2 py-1.5 text-sm">
              <option value="viewer">Lecteur (lecture seule)</option>
              <option value="contributor">Contributeur (peut ajouter du contenu)</option>
              <option value="editor">Éditeur (peut modifier)</option>
              <option value="admin">Administrateur (tous les droits)</option>
            </select>
          </label>
          <label className="flex flex-col gap-1 text-sm">
            <span>Message (optionnel)</span>
            <input
              type="text"
              value={message}
              onChange={(e) => setMessage(e.target.value)}
              className="rounded border border-border bg-background px-2 py-1.5 text-sm"
            />
          </label>
        </div>
        <div className="flex justify-end">
          <button
            type="submit"
            disabled={invite.isPending}
            className="flex items-center gap-1.5 rounded bg-primary px-3 py-1.5 text-sm text-primary-foreground disabled:opacity-60"
          >
            <Icon name="personPlus" className="h-4 w-4" />
            Envoyer l'invitation
          </button>
        </div>
        {invite.isError && (
          <p className="text-sm text-danger">L'invitation a échoué. Vérifiez l'email et vos droits.</p>
        )}
      </form>

      {/* ===================== MEMBRES ===================== */}
      <section className="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
        <header className="border-b border-border px-4 py-2.5 text-sm font-semibold">
          Membres actifs ({members.length})
        </header>
        {isLoading ? (
          <p className="px-4 py-6 text-center text-sm text-muted-foreground">Chargement…</p>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="text-xs uppercase tracking-wide text-muted-foreground">
                <tr className="border-b border-border">
                  <th className="px-4 py-2">Utilisateur</th>
                  <th className="px-4 py-2">Rôle</th>
                  <th className="px-4 py-2">Permissions</th>
                  <th className="px-4 py-2">Rejoint le</th>
                  <th className="px-4 py-2">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-border">
                {members.map((m: WorkplaceMember) => (
                  <MemberRow
                    key={m.id}
                    member={m}
                    onChangeRole={(role) => changeRole.mutate({ memberId: m.id, nextRole: role })}
                    onRemove={() => {
                      if (window.confirm('Retirer ce membre de l’espace ?')) remove.mutate(m.id);
                    }}
                  />
                ))}
                {members.length === 0 && (
                  <tr>
                    <td colSpan={5} className="px-4 py-6 text-center text-muted-foreground">
                      Aucun membre.
                    </td>
                  </tr>
                )}
              </tbody>
            </table>
          </div>
        )}
      </section>
    </div>
  );
}

function MemberRow({
  member,
  onChangeRole,
  onRemove,
}: {
  member: WorkplaceMember;
  onChangeRole: (role: string) => void;
  onRemove: () => void;
}) {
  const isOwner = member.role === 'owner';
  const permissions = [
    { flag: member.can_create_folders, label: 'Dossiers' },
    { flag: member.can_create_documents, label: 'Documents' },
    { flag: member.can_delete, label: 'Supprimer' },
    { flag: member.can_share, label: 'Partager' },
    { flag: member.can_invite, label: 'Inviter' },
  ].filter((p) => p.flag);

  return (
    <tr className="hover:bg-muted/50">
      <td className="px-4 py-2.5">
        <div className="flex items-center gap-3">
          <span
            className="flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold text-white"
            style={{ background: AVATAR_COLORS[member.user_id % AVATAR_COLORS.length] }}
          >
            {((member.user?.name ?? '?').slice(0, 2)).toUpperCase()}
          </span>
          <div>
            <p className="font-medium">{member.user?.name ?? '—'}</p>
            <p className="text-xs text-muted-foreground">{member.user?.email ?? ''}</p>
          </div>
        </div>
      </td>
      <td className="px-4 py-2.5">
        {isOwner ? (
          <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${ROLE_COLORS.owner}`}>Owner</span>
        ) : (
          <select
            value={member.role}
            onChange={(e) => onChangeRole(e.target.value)}
            className="rounded border border-border bg-background px-2 py-1 text-xs"
          >
            {['contributor', 'editor', 'admin', 'viewer'].map((r) => (
              <option key={r} value={r}>{r}</option>
            ))}
          </select>
        )}
      </td>
      <td className="px-4 py-2.5">
        <div className="flex flex-wrap gap-1">
          {permissions.map((p) => (
            <span key={p.label} className="rounded bg-green-100 px-1.5 py-0.5 text-[10px] font-medium text-green-700">
              {p.label}
            </span>
          ))}
        </div>
      </td>
      <td className="px-4 py-2.5 text-xs text-muted-foreground">
        {member.joined_at ? formatDate(member.joined_at) : '—'}
      </td>
      <td className="px-4 py-2.5">
        {!isOwner && (
          <button
            type="button"
            onClick={onRemove}
            className="flex items-center gap-1 rounded border border-danger/40 px-2 py-1 text-xs text-danger hover:bg-danger/10"
          >
            <Icon name="trash" className="h-3.5 w-3.5" />
            Retirer
          </button>
        )}
      </td>
    </tr>
  );
}
