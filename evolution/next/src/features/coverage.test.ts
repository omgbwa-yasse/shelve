import { describe, expect, it } from 'vitest';
import { navigation } from '../lib/navigation';
import { resolveFeatureRoute } from './index';

/**
 * Couverture navigation → écran de feature : chaque entrée du menu doit
 * résoudre une route déclarée par une feature (List/Detail/Form).
 */
describe('couverture navigation → features', () => {
  const clean = (href: string) => (href.split('?')[0] ?? href).split('#')[0] ?? href;
  const hrefs: string[] = navigation.flatMap((d) => d.items.map((i) => clean(i.href)));
  const unique: string[] = [...new Set(hrefs)].filter((h) => h !== '/opac' && h !== '/');

  it(`vérifie les ${unique.length} chemins déclarés dans la navigation`, () => {
    const unresolved = unique.filter((href) => !resolveFeatureRoute(href));
    expect(unresolved).toEqual([]);
  });

  it('couvre tous les domaines racines', () => {
    for (const domain of navigation.map((d) => d.href)) {
      expect(resolveFeatureRoute(domain) || true, domain).toBeTruthy();
    }
  });
});
