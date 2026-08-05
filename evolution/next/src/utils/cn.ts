import { clsx, type ClassValue } from 'clsx';

/**
 * Helper Tailwind — fusion de classes conditionnelles (voir structure de
 * référence `src/utils/cn.ts`). Ajouter `tailwind-merge` au besoin pour la
 * résolution de conflits de classes.
 */
export function cn(...inputs: ClassValue[]): string {
  return clsx(inputs);
}
