import { Source_Serif_4 } from 'next/font/google';
import { OpacShell } from '@/features/opac/components/opac-shell';

/**
 * Police serif du portail public — chargée et auto-hébergée par `next/font`
 * (swap), exposée via la variable CSS `--font-serif` consommée par le token
 * Tailwind `font-serif` (voir `src/styles/tokens.css`). Le back-office garde
 * sa pile système : le serif appartient au monde de lecture du catalogue.
 */
const opacSerif = Source_Serif_4({
  subsets: ['latin'],
  weight: ['400', '600', '700'],
  variable: '--font-serif',
  display: 'swap',
});

/**
 * Coquille du portail public (OPAC) — délibérément distincte de la coquille
 * back-office : pas de rail/sous-menu agent, rendu serveur (RSC) pour le
 * référencement (voir PHASE-2-NEXTJS.md, étape 2.3 — "URLs publiques OPAC :
 * conservées à l'identique"). Reproduit `opac/layouts/app.blade.php`.
 */
export default function OpacLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className={opacSerif.variable}>
      <OpacShell>{children}</OpacShell>
    </div>
  );
}
