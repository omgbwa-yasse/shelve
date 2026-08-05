import { redirect } from 'next/navigation';

/**
 * Racine `/` — point d'entrée public : redirige vers le portail OPAC (`/opac`).
 * Le back-office vit sur `/records` et les autres routes de module.
 */
export default function RootPage() {
  redirect('/opac');
}
