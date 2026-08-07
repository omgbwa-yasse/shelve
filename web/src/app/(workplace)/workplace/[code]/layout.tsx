'use client';

import { useParams } from 'next/navigation';
import { WorkplaceProvider } from '@/features/workplaces/context';
import { WorkplaceShell } from '@/features/workplaces/components/workplace-shell';

/**
 * Layout d'un workplace — le workplace a SA propre coquille (bannière + onglets),
 * par analogie avec `resources/views/layouts/workplace.blade.php` + le partial
 * `site-header`. L'accès se fait par code (slug) : `/workplace/{code}`.
 */
export default function WorkplaceLayout({ children }: { children: React.ReactNode }) {
  const params = useParams<{ code: string }>();
  const code = String(params?.code ?? '');

  return (
    <WorkplaceProvider code={code}>
      <WorkplaceShell>{children}</WorkplaceShell>
    </WorkplaceProvider>
  );
}
