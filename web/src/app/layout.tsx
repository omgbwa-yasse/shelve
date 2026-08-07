import type { Metadata } from 'next';
import { ModalProvider } from '@/context/ModalProvider';
import { Providers } from './providers';
import './globals.css';

export const metadata: Metadata = {
  title: 'SHELVE',
  description: 'Gestion documentaire et archivistique — frontal Next.js',
};

/**
 * Layout racine : un seul import CSS (`./globals.css`, qui pointe vers le
 * template actif — voir `src/styles/`), un seul `ModalProvider` pour toute
 * l'application, et le `QueryClientProvider` (TanStack Query) global.
 */
export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="fr">
      <body>
        <Providers>
          <ModalProvider>{children}</ModalProvider>
        </Providers>
      </body>
    </html>
  );
}
