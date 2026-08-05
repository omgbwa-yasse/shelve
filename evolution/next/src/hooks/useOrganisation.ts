'use client';

import { useState } from 'react';
import type { Organisation } from '@/types';

/**
 * Placeholder — organisation courante, à remplacer par une valeur issue de
 * la session serveur (voir lib/auth/session.ts) et propagée via un contexte
 * une fois branché sur l'API.
 */
export function useOrganisation(initial?: Organisation) {
  const [organisation, setOrganisation] = useState<Organisation | undefined>(initial);
  return { organisation, setOrganisation };
}
