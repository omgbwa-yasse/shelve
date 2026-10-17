'use client';

import { OpacHome } from '@/features/opac-builder/render/OpacHome';

/**
 * Accueil OPAC — rendu de la page construite avec le constructeur de pages
 * (version publiée de la page `home`). Repli sur un état « en construction »
 * tant qu'aucune version n'est publiée.
 */
export default function OpacHomePage() {
  return <OpacHome />;
}
