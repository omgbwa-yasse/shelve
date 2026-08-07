import { redirect } from 'next/navigation';
import { getSession } from '@/features/auth/services/auth.service';

/**
 * Coquille des workplaces — délibérément distincte de la coquille back-office,
 * comme `resources/views/layouts/workplace.blade.php` dans Laravel : PAS de
 * rail / sous-menu / topbar agent. Le workplace est un espace « plein écran »
 * (bannière + onglets, rendus par `WorkplaceShell`), avec un bouton « Retour »
 * vers `/workplaces` pour rejoindre l'application principale.
 *
 * Guard d'accès : même session que le back-office ; sans cookie, `/login`.
 */
export default async function WorkplaceLayout({ children }: { children: React.ReactNode }) {
  const session = await getSession();

  if (!session) {
    redirect('/login');
  }

  return <div className="min-h-screen bg-background text-foreground">{children}</div>;
}
