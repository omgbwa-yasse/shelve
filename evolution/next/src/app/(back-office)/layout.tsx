import { redirect } from 'next/navigation';
import { Sidebar } from '@/components/layout/Sidebar';
import { Submenu } from '@/components/layout/Submenu';
import { Topbar } from '@/components/layout/Topbar';
import { getSession } from '@/features/auth/services/auth.service';
import { SessionProvider } from '@/features/auth/context';
import { AiAssistantProvider } from '@/features/ai-assistant/context';
import { AiAssistantPanel } from '@/features/ai-assistant/components/AiAssistantPanel';

/**
 * Coquille du back-office : rail + sous-menu + topbar.
 * Guard d'accès : session lue côté serveur (feature Auth) ; redirection `/login`.
 */
export default async function BackOfficeLayout({ children }: { children: React.ReactNode }) {
  const session = await getSession();

  if (!session) {
    redirect('/login');
  }

  return (
    <SessionProvider permissions={session.permissions ?? []}>
      <AiAssistantProvider>
        <div className="flex h-screen">
          <Sidebar />
          <Submenu />
          <div className="flex flex-1 flex-col overflow-hidden">
            <Topbar />
            <div className="flex flex-1 overflow-hidden">
              <main className="flex-1 overflow-y-auto p-6">{children}</main>
              <AiAssistantPanel />
            </div>
          </div>
        </div>
      </AiAssistantProvider>
    </SessionProvider>
  );
}
