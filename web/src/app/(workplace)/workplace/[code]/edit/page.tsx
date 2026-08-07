'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { useWorkplace } from '@/features/workplaces/context';
import { workplacesApi } from '@/features/workplaces/services/workplace.service';
import { Icon } from '@/components/icons';

/**
 * Modification d'un workplace — reproduit `workplaces/edit.blade.php` + le champ
 * `code` (slug d'adresse `/workplace/{code}`).
 */
export default function WorkplaceEditPage() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const { code, workplace, isLoading } = useWorkplace();

  const [v, setV] = useState<{
    code: string;
    name: string;
    description: string;
    is_public: boolean;
    allow_external_sharing: boolean;
    max_members: string;
    max_storage_mb: string;
  } | null>(null);

  useEffect(() => {
    if (workplace && !v) {
      setV({
        code: workplace.code ?? '',
        name: workplace.name ?? '',
        description: workplace.description ?? '',
        is_public: Boolean(workplace.is_public),
        allow_external_sharing: Boolean(workplace.allow_external_sharing),
        max_members: workplace.max_members != null ? String(workplace.max_members) : '',
        max_storage_mb: workplace.max_storage_mb != null ? String(workplace.max_storage_mb) : '',
      });
    }
  }, [workplace, v]);

  const update = useMutation({
    mutationFn: () =>
      workplacesApi.update(workplace?.id as number, {
        code: v?.code ?? '',
        name: v?.name ?? '',
        description: v?.description ?? '',
        is_public: v?.is_public ?? false,
        allow_external_sharing: v?.allow_external_sharing ?? false,
        max_members: v?.max_members ? Number(v.max_members) : null,
        max_storage_mb: v?.max_storage_mb ? Number(v.max_storage_mb) : null,
      }),
    onSuccess: async (res) => {
      const newCode = String(res.data?.code ?? code);
      queryClient.invalidateQueries({ queryKey: ['workplace', code] });
      queryClient.invalidateQueries({ queryKey: ['workplace', newCode] });
      queryClient.invalidateQueries({ queryKey: ['workplaces'] });
      router.push(`/workplace/${encodeURIComponent(newCode)}`);
    },
  });

  if (isLoading || !v) {
    return <p className="text-sm text-muted-foreground">Chargement…</p>;
  }

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        update.mutate();
      }}
      className="flex max-w-2xl flex-col gap-4"
    >
      <header className="flex items-center justify-between">
        <h3 className="flex items-center gap-2 text-lg font-semibold">
          <Icon name="save" className="h-5 w-5 text-muted-foreground" />
          Modifier l'espace de travail
        </h3>
        <button
          type="button"
          onClick={() => router.push(`/workplace/${encodeURIComponent(code)}`)}
          className="rounded border border-border bg-surface px-3 py-1.5 text-sm hover:bg-muted"
        >
          Annuler
        </button>
      </header>

      <div className="flex flex-col gap-4 rounded-xl border border-border bg-surface p-4 shadow-sm">
        <Field label="Code * (adresse de l'espace)" hint="Ex. rh, sia2019, dg-sg" value={v.code} onChange={(x) => setV({ ...v, code: x })} required />
        <Field label="Nom *" value={v.name} onChange={(x) => setV({ ...v, name: x })} required />
        <label className="flex flex-col gap-1 text-sm">
          <span>Description</span>
          <textarea value={v.description} onChange={(e) => setV({ ...v, description: e.target.value })} rows={3} className="rounded border border-border bg-background px-2 py-1.5 text-sm" />
        </label>
        <div className="flex flex-wrap gap-6">
          <Checkbox label="Espace de travail public" hint="Visible par tous les membres de l'organisation" checked={v.is_public} onChange={(x) => setV({ ...v, is_public: x })} />
          <Checkbox label="Autoriser le partage externe" hint="Permet d'inviter des utilisateurs externes à l'organisation" checked={v.allow_external_sharing} onChange={(x) => setV({ ...v, allow_external_sharing: x })} />
        </div>
        <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
          <Field label="Nombre max de membres" hint="Laisser vide pour illimité" type="number" value={v.max_members} onChange={(x) => setV({ ...v, max_members: x })} />
          <Field label="Stockage max (MB)" hint="Laisser vide pour illimité" type="number" value={v.max_storage_mb} onChange={(x) => setV({ ...v, max_storage_mb: x })} />
        </div>
      </div>

      {update.isError && (
        <p className="rounded border border-danger/40 bg-danger/5 px-3 py-2 text-sm text-danger">
          La mise à jour a échoué. Vérifiez que le code n'est pas déjà utilisé et qu'il ne contient que lettres, chiffres et tirets.
        </p>
      )}

      <footer className="flex justify-end">
        <button type="submit" disabled={update.isPending} className="rounded bg-primary px-4 py-2 text-sm text-primary-foreground disabled:opacity-60">
          Enregistrer
        </button>
      </footer>
    </form>
  );
}

function Field({
  label,
  value,
  onChange,
  required,
  type = 'text',
  hint,
}: {
  label: string;
  value: string;
  onChange: (v: string) => void;
  required?: boolean;
  type?: string;
  hint?: string;
}) {
  return (
    <label className="flex flex-col gap-1 text-sm">
      <span>
        {label} {required && <span className="text-danger">*</span>}
      </span>
      <input
        type={type}
        value={value}
        required={required}
        onChange={(e) => onChange(e.target.value)}
        className="rounded border border-border bg-background px-2 py-1.5 text-sm"
      />
      {hint && <span className="text-xs text-muted-foreground">{hint}</span>}
    </label>
  );
}

function Checkbox({
  label,
  hint,
  checked,
  onChange,
}: {
  label: string;
  hint: string;
  checked: boolean;
  onChange: (v: boolean) => void;
}) {
  return (
    <label className="flex cursor-pointer items-start gap-2 text-sm">
      <input type="checkbox" checked={checked} onChange={(e) => onChange(e.target.checked)} className="mt-0.5" />
      <span>
        <span className="block font-medium">{label}</span>
        <span className="block text-xs text-muted-foreground">{hint}</span>
      </span>
    </label>
  );
}
