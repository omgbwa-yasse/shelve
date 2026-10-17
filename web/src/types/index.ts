/** Types transverses. Les types métier générés depuis l'API vivent dans `lib/api/schema.d.ts`. */

export type Record = {
  id: number;
  code: string;
  name: string;
  description?: string | null;
  typeId: number | null;
  metadata: globalThis.Record<string, unknown>;
};

export type Organisation = {
  id: number;
  name: string;
};

export type NavDomain = {
  key: string;
  label: string;
  href: string;
  icon: string; // nom d'icône lucide-react — voir components/icons
  items: NavItem[];
};

export type NavItem = {
  key: string;
  label: string;
  href: string;
  icon: string;
  /** Titre de section (reproduit les en-têtes repliables du sous-menu Blade). */
  group?: string;
  /**
   * Action spéciale du sous-menu au lieu d'une navigation. `pick-record-type`
   * ouvre la modale de choix de typologie (dossier/document) SUR la vue
   * courante — aucune vue en arrière-plan n'est chargée. Le `kind` à choisir
   * est encodé dans l'URL (ex. `/records/create?kind=folder`).
   */
  action?: 'pick-record-type';
};
