'use client';

import { useEffect, useState } from 'react';
import type { Breakpoint } from '../services/opac-builder.service';

const BREAKPOINTS: { key: Breakpoint; max: number }[] = [
  { key: 'mobile', max: 639 },
  { key: 'tablet', max: 1023 },
  { key: 'desktop', max: Infinity },
];

function detect(): Breakpoint {
  if (typeof window === 'undefined') return 'desktop';
  const width = window.innerWidth;
  return BREAKPOINTS.find((bp) => width <= bp.max)?.key ?? 'desktop';
}

/**
 * Breakpoint courant (largeur de la fenêtre). Sert au rendu public des
 * modules : les réglages surchargés par breakpoint sont appliqués selon le
 * viewport réel du visiteur.
 */
export function useBreakpoint(): Breakpoint {
  const [breakpoint, setBreakpoint] = useState<Breakpoint>(detect);

  useEffect(() => {
    const onResize = () => setBreakpoint(detect());
    window.addEventListener('resize', onResize);
    return () => window.removeEventListener('resize', onResize);
  }, []);

  return breakpoint;
}
