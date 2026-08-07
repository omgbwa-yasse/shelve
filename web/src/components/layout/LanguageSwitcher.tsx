'use client';

import { Icon } from '@/components/icons';

const LOCALES = [
  { code: 'fr', label: 'FR' },
  { code: 'en', label: 'EN' },
] as const;

/**
 * Dictionnaires importés depuis `lang/fr.json` / `lang/en.json` (source
 * commune avec le Blade existant, voir PHASE-2-NEXTJS.md, mesure R22).
 */
export function LanguageSwitcher({ locale = 'fr' }: { locale?: 'fr' | 'en' }) {
  return (
    <div className="flex items-center gap-1 rounded border border-border px-2 py-1.5 text-sm">
      <Icon name="globe" className="h-4 w-4 text-muted-foreground" />
      {LOCALES.map((l) => (
        <button
          key={l.code}
          type="button"
          className={l.code === locale ? 'font-semibold text-primary' : 'text-muted-foreground hover:text-foreground'}
        >
          {l.label}
        </button>
      ))}
    </div>
  );
}
