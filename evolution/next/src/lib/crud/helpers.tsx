import type { ResourceApi } from '@/lib/api/resources';
import type { Column, Field, Option } from '@/lib/crud/types';

/**
 * Helpers déclaratifs pour écrire des configs de ressources concises.
 */

export function col(key: string, label: string, extra: Partial<Column> = {}): Column {
  return { key, label, ...extra };
}

export function textField(name: string, label: string, extra: Partial<Field> = {}): Field {
  return { name, label, type: 'text', ...extra };
}

export function textareaField(name: string, label: string, extra: Partial<Field> = {}): Field {
  return { name, label, type: 'textarea', ...extra };
}

export function numberField(name: string, label: string, extra: Partial<Field> = {}): Field {
  return { name, label, type: 'number', ...extra };
}

export function dateField(name: string, label: string, extra: Partial<Field> = {}): Field {
  return { name, label, type: 'date', ...extra };
}

export function datetimeField(name: string, label: string, extra: Partial<Field> = {}): Field {
  return { name, label, type: 'datetime', ...extra };
}

export function boolField(name: string, label: string, extra: Partial<Field> = {}): Field {
  return { name, label, type: 'boolean', ...extra };
}

export function selectField(name: string, label: string, options: Option[], extra: Partial<Field> = {}): Field {
  return { name, label, type: 'select', options, ...extra };
}

/** Champ référence (option issue d'une liste API). */
export function refField(
  name: string,
  label: string,
  api: ResourceApi,
  extra: Partial<Field> = {},
): Field {
  return {
    name,
    label,
    type: 'reference',
    reference: { api, valueKey: 'id', labelKey: 'name' },
    ...extra,
  };
}

export function emailField(name: string, label: string, extra: Partial<Field> = {}): Field {
  return { name, label, type: 'email', ...extra };
}

/** Affiche la valeur d'un champ nom/code comme badge gras. */
export function badge(value: unknown): React.ReactNode {
  if (value === null || value === undefined || value === '') return <span className="text-muted-foreground/60">—</span>;
  return <span className="font-mono text-xs">{String(value)}</span>;
}

export function yesNo(value: unknown): React.ReactNode {
  if (value === null || value === undefined) return <span className="text-muted-foreground/60">—</span>;
  return value ? 'Oui' : 'Non';
}
