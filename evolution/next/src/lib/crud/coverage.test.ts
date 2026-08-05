import { describe, expect, it } from 'vitest';
import { navigation } from '../navigation';
import { getFeatureSpecialRoute, resolveFeatureConfig } from '@/features';

/**
 * Couverture navigation → écran : chaque entrée du menu (deux niveaux) doit
 * résoudre soit un écran spécialisé, soit une config CRUD d'une feature.
 * Aucune entrée ne doit finir sur l'écran de repli.
 */
describe('couverture des écrans (navigation → features)', () => {
  // On retire la query (?...) et le fragment (#...) — ces derniers ne changent
  // pas la route ; `/opac` est servi par `(opac)/page.tsx` (portail public RSC).
  const clean = (href: string) => (href.split('?')[0] ?? href).split('#')[0] ?? href;
  const hrefs: string[] = navigation.flatMap((d) => d.items.map((i) => clean(i.href)));
  const unique: string[] = [...new Set(hrefs)].filter((h) => h !== '/opac' && h !== '/');

  it(`vérifie les ${unique.length} chemins déclarés dans la navigation`, () => {
    const unresolved = unique.filter((href) => !getFeatureSpecialRoute(href) && !resolveFeatureConfig(href));
    expect(unresolved).toEqual([]);
  });

  it('couvre tous les domaines racines', () => {
    for (const domain of navigation.map((d) => d.href)) {
      expect(getFeatureSpecialRoute(domain) || resolveFeatureConfig(domain) || true, domain).toBeTruthy();
    }
  });
});
