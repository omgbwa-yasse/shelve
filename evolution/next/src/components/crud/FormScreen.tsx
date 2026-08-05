'use client';

import { useEffect, useMemo, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import type { Field, ResourceConfig } from '@/lib/crud/types';
import type { ApiErrorPayload, Entity } from '@/lib/api/types';

/**
 * Écran FORMULAIRE universel (création / édition) — rendu depuis une
 * `ResourceConfig`. Gère la validation client (règles déclaratives), les erreurs
 * 422 champ par champ, les champs `reference` (options chargées depuis l'API).
 */
export function FormScreen({ config, mode, id }: { config: ResourceConfig; mode: 'create' | 'edit'; id?: string }) {
  const router = useRouter();
  const queryClient = useQueryClient();
  const isEdit = mode === 'edit' && id !== undefined;

  const { data: entity, isPending: loading } = useQuery({
    queryKey: [config.path, id],
    enabled: isEdit,
    queryFn: async () => (await config.api.show(id!)) as { data: Entity },
  });

  const [values, setValues] = useState<Record<string, unknown>>({});
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [serverErrors, setServerErrors] = useState<Record<string, string[]>>({});
  const [submitting, setSubmitting] = useState(false);
  const [notice, setNotice] = useState<string | null>(null);

  useEffect(() => {
    if (entity?.data) {
      const base: Record<string, unknown> = {};
      for (const f of config.fields) {
        const v = (entity.data as Record<string, unknown>)[f.name];
        if (v !== undefined) base[f.name] = v;
      }
      setValues(base);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [entity]);

  function set(name: string, value: unknown) {
    setValues((p) => ({ ...p, [name]: value }));
    setErrors((p) => ({ ...p, [name]: '' }));
    setServerErrors((p) => {
      const next = { ...p };
      delete next[name];
      return next;
    });
  }

  async function submit(e: React.FormEvent) {
    e.preventDefault();
    const nextErrors: Record<string, string> = {};
    for (const f of config.fields) {
      if (f.hidden) continue;
      const v = values[f.name];
      if (f.required && (v === undefined || v === null || v === '')) nextErrors[f.name] = 'Ce champ est obligatoire.';
      if (f.rules) {
        const err = f.rules(v);
        if (err) nextErrors[f.name] = err;
      }
    }
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length > 0) return;

    setSubmitting(true);
    setNotice(null);
    try {
      const payload = Object.fromEntries(
        config.fields.filter((f) => !f.hidden).map((f) => [f.name, values[f.name]]),
      );
      if (isEdit) {
        await config.api.update(id!, payload);
      } else {
        await config.api.create(payload);
      }
      await queryClient.invalidateQueries({ queryKey: [config.path] });
      router.push(config.path);
      router.refresh();
    } catch (err) {
      const payload = (err as { payload?: ApiErrorPayload }).payload;
      if (payload?.errors) {
        setServerErrors(payload.errors);
      } else {
        setNotice(payload?.message ?? 'Une erreur est survenue.');
      }
    } finally {
      setSubmitting(false);
    }
  }

  if (isEdit && loading) {
    return <p className="text-sm text-muted-foreground">Chargement…</p>;
  }

  return (
    <form onSubmit={submit} className="flex w-full flex-col gap-4">
      <header className="flex items-center justify-between">
        <h1 className="text-xl font-semibold">
          {isEdit ? `Modifier — ${config.label}` : `Créer — ${config.label}`}
        </h1>
        <button type="button" onClick={() => router.push(config.path)} className="rounded border border-border px-3 py-1.5 text-sm">
          Annuler
        </button>
      </header>

      {notice && <div className="rounded border border-danger/40 bg-danger/10 px-3 py-2 text-sm text-danger">{notice}</div>}

      <div className="rounded border border-border bg-surface p-4">
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
          {config.fields.filter((f) => !f.hidden).map((field) => (
            <FieldControl
              key={field.name}
              field={field}
              value={values[field.name]}
              onChange={(v) => set(field.name, v)}
              error={errors[field.name] ?? serverErrors[field.name]?.[0]}
            />
          ))}
        </div>
      </div>

      <footer className="flex justify-end gap-2">
        <button type="submit" disabled={submitting} className="rounded bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:opacity-90 disabled:opacity-50">
          {submitting ? 'Enregistrement…' : 'Enregistrer'}
        </button>
      </footer>
    </form>
  );
}

function FieldControl({ field, value, onChange, error }: { field: Field; value: unknown; onChange: (v: unknown) => void; error?: string }) {
  const refOptions = useReferenceOptions(field);

  const id = `field-${field.name}`;
  const baseClass = 'w-full rounded border border-border bg-surface px-3 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary';
  const errorClass = error ? ' border-danger' : '';

  let control: React.ReactNode;

  switch (field.type ?? 'text') {
    case 'textarea':
      control = <textarea id={id} rows={3} className={baseClass + errorClass} placeholder={field.placeholder} value={(value as string) ?? ''} onChange={(e) => onChange(e.target.value)} />;
      break;
    case 'number':
      control = <input id={id} type="number" className={baseClass + errorClass} value={(value as number) ?? ''} onChange={(e) => onChange(e.target.value === '' ? null : Number(e.target.value))} />;
      break;
    case 'date':
      control = <input id={id} type="date" className={baseClass + errorClass} value={(value as string) ?? ''} onChange={(e) => onChange(e.target.value)} />;
      break;
    case 'datetime':
      control = <input id={id} type="datetime-local" className={baseClass + errorClass} value={(value as string) ?? ''} onChange={(e) => onChange(e.target.value)} />;
      break;
    case 'boolean':
      control = (
        <label className="flex items-center gap-2 text-sm">
          <input type="checkbox" checked={Boolean(value)} onChange={(e) => onChange(e.target.checked)} className="h-4 w-4" />
          {field.label}
        </label>
      );
      break;
    case 'select':
      control = (
        <select id={id} className={baseClass + errorClass} value={(value as string) ?? ''} onChange={(e) => onChange(e.target.value)}>
          <option value="">—</option>
          {(field.options ?? []).map((o) => (
            <option key={String(o.value)} value={String(o.value)}>{o.label}</option>
          ))}
        </select>
      );
      break;
    case 'reference':
      control = (
        <select id={id} className={baseClass + errorClass} value={(value as string) ?? ''} onChange={(e) => onChange(e.target.value)}>
          <option value="">—</option>
          {refOptions.map((o) => (
            <option key={String(o.value)} value={String(o.value)}>{o.label}</option>
          ))}
        </select>
      );
      break;
    case 'email':
      control = <input id={id} type="email" className={baseClass + errorClass} value={(value as string) ?? ''} onChange={(e) => onChange(e.target.value)} />;
      break;
    case 'url':
      control = <input id={id} type="url" className={baseClass + errorClass} value={(value as string) ?? ''} onChange={(e) => onChange(e.target.value)} />;
      break;
    default:
      control = <input id={id} type="text" className={baseClass + errorClass} placeholder={field.placeholder} value={(value as string) ?? ''} onChange={(e) => onChange(e.target.value)} />;
  }

  if (field.type === 'boolean') {
    return (
      <div className="flex flex-col gap-1">
        {control}
        {error && <span className="text-xs text-danger">{error}</span>}
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-1">
      <label htmlFor={id} className="text-sm font-medium">
        {field.label} {field.required && <span className="text-danger">*</span>}
      </label>
      {control}
      {field.help && <span className="text-xs text-muted-foreground">{field.help}</span>}
      {error && <span className="text-xs text-danger">{error}</span>}
    </div>
  );
}

/** Charge les options d'un champ `reference` depuis son API. */
function useReferenceOptions(field: Field): { value: string | number; label: string }[] {
  const { data } = useQuery({
    queryKey: [`ref:${field.name}`],
    enabled: field.type === 'reference' && !!field.reference,
    queryFn: async () => {
      const res = await field.reference!.api.list({ per_page: 200 });
      const items = (res as { data: unknown[] }).data ?? [];
      return items.map((item) => ({
        value: String((item as Record<string, unknown>)[field.reference!.valueKey]),
        label: String((item as Record<string, unknown>)[field.reference!.labelKey] ?? '—'),
      }));
    },
  });
  return useMemo(() => data ?? [], [data]);
}
