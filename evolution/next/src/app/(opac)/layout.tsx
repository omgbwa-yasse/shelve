/**
 * Coquille du portail public (OPAC) — délibérément distincte de la coquille
 * back-office : pas de rail/sous-menu agent, rendu serveur (RSC) pour le
 * référencement (voir PHASE-2-NEXTJS.md, étape 2.3 — "URLs publiques OPAC :
 * conservées à l'identique").
 */
export default function OpacLayout({ children }: { children: React.ReactNode }) {
  return <div className="min-h-screen bg-background">{children}</div>;
}
