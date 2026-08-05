'use client';

import { useState } from 'react';
import type { Organisation } from '@/types';

/**
 * Placeholder — organisation courante + liste des organisations accessibles
 * (`session.organisations`, voir lib/auth/session.ts). Le changement
 * d'organisation doit invalider le cache TanStack Query (données scopées
 * par organisation côté API, cf. RecordPolicy côté Laravel).
 */
export function OrganisationSwitcher({ organisations = [] }: { organisations?: Organisation[] }) {
  const [open, setOpen] = useState(false);
  const current = organisations[0]?.name ?? 'Organisation';

  return (
    <div className="relative">
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        className="flex items-center gap-2 rounded border border-border px-3 py-1.5 text-sm hover:bg-muted"
        aria-haspopup="listbox"
        aria-expanded={open}
      >
        <span className="max-w-[12rem] truncate">{current}</span>
      </button>

      {open && (
        <ul
          role="listbox"
          className="absolute right-0 z-20 mt-1 w-56 rounded border border-border bg-surface py-1 shadow-lg"
        >
          {organisations.map((org) => (
            <li key={org.id}>
              <button
                type="button"
                className="block w-full px-3 py-2 text-left text-sm hover:bg-muted"
                onClick={() => setOpen(false)}
              >
                {org.name}
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
