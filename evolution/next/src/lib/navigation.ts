import type { NavDomain } from '@/types';

/**
 * Résout le domaine actif à partir du pathname courant.
 *
 * D'abord par `domain.href` (comportement historique : couvre tous les
 * domaines dont les items vivent sous le même préfixe, ex. `/mails/*`).
 * Repli sur les `href` des items sinon — nécessaire pour "Projets", dont les
 * sections Objectifs/KPI vivent à `/objectives` et `/kpis`, hors du préfixe
 * `/projects` (sans quoi ouvrir KPI/Objectifs ne matche aucun domaine et le
 * sous-menu se vide/change).
 */
export function findActiveDomain(pathname: string | null | undefined): NavDomain | undefined {
  if (!pathname) return undefined;

  const matchesPrefix = (href: string) =>
    pathname === href || pathname.startsWith(`${href}/`) || pathname.startsWith(`${href}?`);

  const byDomainHref = navigation.find((domain) => matchesPrefix(domain.href));
  if (byDomainHref) return byDomainHref;

  return navigation.find((domain) =>
    domain.items.some((item) => matchesPrefix(item.href.split('?')[0] ?? item.href)),
  );
}

/**
 * Source unique de la navigation à deux niveaux — reproduit fidèlement le
 * menu réel de l'application Blade :
 *   - rail (top-level)  : resources/views/layouts/app.blade.php (nav.main-navigation)
 *   - sous-menus        : resources/views/submenu/{domaine}.blade.php
 *
 * Un nouveau domaine ou une nouvelle section = une entrée ici, pas une
 * modification des composants `Sidebar`/`Submenu`.
 *
 * ⚠️ Thésaurus n'est PAS un menu racine : c'est une section du sous-menu
 * "Outils" (voir submenu/tools.blade.php, section "Thésaurus"), reprise ici
 * dans `tools.items` avec `group: 'Thésaurus'`.
 */
export const navigation: NavDomain[] = [
  // ------------------------------------------------------------------
  // 1. Mails — submenu/mails.blade.php
  // ------------------------------------------------------------------
  {
    key: 'mails',
    label: 'Mails',
    href: '/mails',
    icon: 'mails',
    items: [
      { key: 'mails-received', group: 'Consultations — Courrier interne', label: 'Reçus', href: '/mails/received', icon: 'inbox' },
      { key: 'mails-sent', group: 'Consultations — Courrier interne', label: 'Envoyés', href: '/mails/sent', icon: 'send' },
      { key: 'mails-returned', group: 'Consultations — Courrier interne', label: 'Retournés', href: '/mails/returned', icon: 'rotateCcw' },
      { key: 'mails-to-return', group: 'Consultations — Courrier interne', label: 'À retourner', href: '/mails/to-return', icon: 'rotateCcw' },
      { key: 'mails-batches', group: 'Consultations — Courrier interne', label: 'Parapheurs', href: '/mails/batches', icon: 'bookmark' },

      { key: 'mails-ext-send', group: 'Consultations — Externe / Archives / Recherche', label: 'Envoyer', href: '/mails/external/send', icon: 'send' },
      { key: 'mails-ext-receive', group: 'Consultations — Externe / Archives / Recherche', label: 'Recevoir', href: '/mails/external/receive', icon: 'inbox' },
      { key: 'mails-archived', group: 'Consultations — Externe / Archives / Recherche', label: 'Courrier archivé', href: '/mails/archived', icon: 'archive' },
      { key: 'mails-containers', group: 'Consultations — Externe / Archives / Recherche', label: 'Boîtes', href: '/mails/containers', icon: 'archive' },
      { key: 'mails-typologies', group: 'Consultations — Externe / Archives / Recherche', label: 'Typologies', href: '/mails/typologies', icon: 'tags' },
      { key: 'mails-dates', group: 'Consultations — Externe / Archives / Recherche', label: 'Dates', href: '/mails/select/date', icon: 'calendar' },
      { key: 'mails-advanced', group: 'Consultations — Externe / Archives / Recherche', label: 'Recherche avancée', href: '/mails/advanced', icon: 'search' },

      { key: 'mails-create-received', group: 'Création', label: 'Reçus', href: '/mails/received/create', icon: 'plusSquare' },
      { key: 'mails-create-sent', group: 'Création', label: 'Envoyés', href: '/mails/sent/create', icon: 'plusSquare' },
      { key: 'mails-create-ext-out', group: 'Création', label: 'Sortant (externe)', href: '/mails/external/send/create', icon: 'plusSquare' },
      { key: 'mails-create-ext-in', group: 'Création', label: 'Entrant (externe)', href: '/mails/external/receive/create', icon: 'plusSquare' },

      { key: 'mails-admin-sign', group: 'Administration', label: 'Parapher', href: '/mails/batches/sign', icon: 'bookmarkCheck' },
      { key: 'mails-admin-send', group: 'Administration', label: 'Envoyer parapheur', href: '/mails/batches/send', icon: 'arrowRightSquare' },
      { key: 'mails-admin-receive', group: 'Administration', label: 'Recevoir parapheur', href: '/mails/batches/receive', icon: 'arrowLeftSquare' },
      { key: 'mails-admin-container', group: 'Administration', label: 'Boîte chrono', href: '/mails/containers/create', icon: 'archive' },
    ],
  },

  // ------------------------------------------------------------------
  // 2. Workflow — submenu/workflow.blade.php (routes tasks.*, workflows.*)
  // ------------------------------------------------------------------
  {
    key: 'workflow',
    label: 'Workflow',
    href: '/workflow',
    icon: 'workflow',
    items: [
      { key: 'workflow-definitions', group: 'Workflow', label: 'Définitions', href: '/workflow/definitions', icon: 'listCheck' },
      { key: 'workflow-definitions-create', group: 'Workflow', label: 'Nouvelle définition', href: '/workflow/definitions/create', icon: 'plusSquare' },
      { key: 'workflow-instances', group: 'Workflow', label: 'Instances', href: '/workflow/instances', icon: 'playCircle' },
      { key: 'workflow-instances-create', group: 'Workflow', label: 'Démarrer workflow', href: '/workflow/instances/create', icon: 'playCircle' },
      { key: 'workflow-dashboard', group: 'Workflow', label: 'Tableau de bord', href: '/workflow/dashboard', icon: 'gauge' },

      { key: 'workflow-tasks-all', group: 'Tâches', label: 'Toutes les tâches', href: '/workflow/tasks', icon: 'list' },
      { key: 'workflow-tasks-create', group: 'Tâches', label: 'Nouvelle tâche', href: '/workflow/tasks/create', icon: 'plusSquare' },
      { key: 'workflow-tasks-pending', group: 'Tâches', label: 'En attente', href: '/workflow/tasks?status=pending', icon: 'hourglass' },
      { key: 'workflow-tasks-progress', group: 'Tâches', label: 'En cours', href: '/workflow/tasks?status=in_progress', icon: 'refresh' },
      { key: 'workflow-tasks-mine', group: 'Tâches', label: 'Mes tâches', href: '/workflow/tasks?assigned_to=me', icon: 'personCheck' },
    ],
  },

  // ------------------------------------------------------------------
  // Projets — module Projet/Tâche/OKR/KPI (D17), voir
  // evolution/PROJECT-OKR-KPI-PLAN.md. N'a pas d'équivalent Blade : nouveau
  // domaine ajouté directement côté API v1 + Next.
  // ------------------------------------------------------------------
  {
    key: 'projects',
    label: 'Projets',
    href: '/projects',
    icon: 'projects',
    items: [
      { key: 'projects-all', group: 'Projets', label: 'Tous les projets', href: '/projects', icon: 'projects' },
      { key: 'projects-create', group: 'Projets', label: 'Nouveau projet', href: '/projects/create', icon: 'plusSquare' },

      { key: 'projects-milestones', group: 'Suivi global', label: 'Tous les jalons', href: '/projects/milestones', icon: 'milestone' },
      { key: 'projects-deliverables', group: 'Suivi global', label: 'Tous les livrables', href: '/projects/deliverables', icon: 'package' },
      { key: 'projects-alerts', group: 'Suivi global', label: 'Toutes les alertes', href: '/projects/alerts', icon: 'bell' },
      { key: 'projects-resources', group: 'Suivi global', label: 'Ressources', href: '/projects/resources', icon: 'users' },

      { key: 'projects-board', group: 'Visualisations', label: 'Tableau Kanban', href: '/projects/board', icon: 'columns' },
      { key: 'projects-gantt', group: 'Visualisations', label: 'Diagramme de Gantt', href: '/projects/gantt', icon: 'gantt' },

      // OKR et KPI : visualisation seule (tous les projets). La création se fait
      // depuis la fiche projet → Créer une tâche → Ajouter un OKR / KPI (rattachés
      // à la tâche, non créés en singleton).
      { key: 'objectives-all', group: 'Objectifs (OKR)', label: 'Tous les objectifs', href: '/objectives', icon: 'target' },

      { key: 'kpis-all', group: 'KPI', label: 'Tous les KPI', href: '/kpis', icon: 'trendingUp' },
    ],
  },

  // ------------------------------------------------------------------
  // 3. WorkPlaces — submenu/workplaces.blade.php (+ Chat intégré en section)
  // ------------------------------------------------------------------
  {
    key: 'workplaces',
    label: 'WorkPlaces',
    href: '/workplaces',
    icon: 'workplaces',
    items: [
      { key: 'workplaces-all', group: 'Espaces de travail', label: 'Tous les workplaces', href: '/workplaces', icon: 'dashboard' },
      { key: 'workplaces-active', group: 'Espaces de travail', label: 'Actifs', href: '/workplaces?status=active', icon: 'checkCircle' },
      { key: 'workplaces-archived', group: 'Espaces de travail', label: 'Archivés', href: '/workplaces?status=archived', icon: 'archive' },

      { key: 'workplaces-create', group: 'Actions', label: 'Nouveau workplace', href: '/workplaces/create', icon: 'plusSquare' },

      { key: 'workplaces-owner', group: 'Mes espaces', label: 'Propriétaire', href: '/workplaces?owner=me', icon: 'personBadge' },
      { key: 'workplaces-member', group: 'Mes espaces', label: 'Membre', href: '/workplaces?member=me', icon: 'personCheck' },
      { key: 'workplaces-public', group: 'Mes espaces', label: 'Publics', href: '/workplaces?is_public=1', icon: 'globe' },

      // Chat : section du sous-menu WorkPlaces (ancien domaine racine « Chat »).
      { key: 'chats-all', group: 'Chat', label: 'Tous les chats', href: '/chats', icon: 'messageCircle' },
      { key: 'chats-private', group: 'Chat', label: 'Messages privés', href: '/chats#privates', icon: 'mails' },
      { key: 'chats-workplaces', group: 'Chat', label: 'Chats des workplaces', href: '/workplaces', icon: 'workplaces' },
      { key: 'chats-create', group: 'Chat', label: 'Nouveau message privé', href: '/chats?new=1', icon: 'plusCircle' },
    ],
  },

  // ------------------------------------------------------------------
  // 5. Records / Notices — submenu/repositories.blade.php
  // ------------------------------------------------------------------
  {
    key: 'records',
    label: 'Notices',
    href: '/records',
    icon: 'records',
    items: [
      { key: 'records-mine', group: 'Recherche — Archives', label: 'Mes archives', href: '/records', icon: 'listCheck' },
      { key: 'records-physical', group: 'Recherche — Archives', label: 'Notices physiques', href: '/records/physical', icon: 'archive' },
      { key: 'records-trash', group: 'Recherche — Archives', label: 'Corbeille', href: '/records/trash', icon: 'trash' },

      { key: 'records-authors', group: 'Recherche — Critères', label: 'Auteurs', href: '/records/authors', icon: 'person' },
      { key: 'records-select-date', group: 'Recherche — Critères', label: 'Dates', href: '/records/select/date', icon: 'calendar' },
      { key: 'records-select-keyword', group: 'Recherche — Critères', label: 'Mots-clés', href: '/records/select/keyword', icon: 'key' },
      { key: 'records-select-activity', group: 'Recherche — Critères', label: 'Activités', href: '/records/select/activity', icon: 'briefcase' },
      { key: 'records-select-building', group: 'Recherche — Critères', label: 'Locaux', href: '/records/select/building', icon: 'archive' },
      { key: 'records-select-last', group: 'Recherche — Critères', label: 'Récents', href: '/records/select/last', icon: 'history' },
      { key: 'records-advanced', group: 'Recherche — Critères', label: 'Avancée', href: '/records/advanced', icon: 'search' },

      { key: 'records-dossier', group: 'Enregistrement', label: 'Dossier', href: '/records/create?kind=folder', icon: 'folderOpen' },
      { key: 'records-document', group: 'Enregistrement', label: 'Document', href: '/records/create?kind=document', icon: 'fileText' },

      { key: 'records-tree', group: 'Import / Export (EAD, Excel, SEDA)', label: 'Arbre des notices', href: '/records/tree', icon: 'network' },
      { key: 'records-import', group: 'Import / Export (EAD, Excel, SEDA)', label: 'Importer', href: '/records/import', icon: 'download' },
      { key: 'records-export', group: 'Import / Export (EAD, Excel, SEDA)', label: 'Exporter', href: '/records/export', icon: 'upload' },
    ],
  },

  // ------------------------------------------------------------------
  // 7. Transfers / Transferts — submenu/transferrings.blade.php
  //    (intègre Communications & Réservations en sous-sections)
  // ------------------------------------------------------------------
  {
    key: 'transferrings',
    label: 'Transferts',
    href: '/transferrings',
    icon: 'transferrings',
    items: [
      { key: 'transferrings-advanced', group: 'Recherche', label: 'Recherche avancée', href: '/transferrings/search/advanced', icon: 'search' },
      { key: 'transferrings-mine', group: 'Recherche', label: 'Mes bordereaux', href: '/transferrings', icon: 'building2' },
      { key: 'transferrings-select-date', group: 'Recherche', label: 'Dates', href: '/transferrings/select/date', icon: 'list' },
      { key: 'transferrings-select-org', group: 'Recherche', label: 'Organisations', href: '/transferrings/select/organisation', icon: 'list' },

      { key: 'transferrings-project', group: 'Suivi de transfert', label: 'Projets', href: '/transferrings/sort?categ=project', icon: 'records' },
      { key: 'transferrings-received', group: 'Suivi de transfert', label: 'Reçus', href: '/transferrings/sort?categ=received', icon: 'inbox' },
      { key: 'transferrings-approved', group: 'Suivi de transfert', label: 'Approuvés', href: '/transferrings/sort?categ=approved', icon: 'checkCircle' },
      { key: 'transferrings-integrated', group: 'Suivi de transfert', label: 'Intégrés', href: '/transferrings/sort?categ=integrated', icon: 'folderPlus' },

      { key: 'transferrings-create', group: 'Création', label: 'Bordereau', href: '/transferrings/create', icon: 'building2' },
      { key: 'transferrings-containers', group: 'Création', label: 'Boîte chrono', href: '/transferrings/containers', icon: 'archive' },

      // Communications & Réservations : sous-sections de Transferts.
      { key: 'communications-all', group: 'Communications', label: 'Voir toutes les communications', href: '/communications', icon: 'inbox' },
      { key: 'communications-create', group: 'Communications', label: 'Nouvelle communication', href: '/communications/create', icon: 'plusCircle' },
      { key: 'reservations-all', group: 'Réservations', label: 'Voir toutes les réservations', href: '/communications/reservations', icon: 'listOrdered' },
      { key: 'reservations-create', group: 'Réservations', label: 'Nouvelle réservation', href: '/communications/reservations/create', icon: 'calendarCheck' },

      // Cycle de vie fusionné dans Déclassement (même rôle : suivi du sort des
      // dossiers) — plus de section "Cycle de vie" séparée dans le menu. Les
      // entrées `records/to-*` (À transférer, À éliminer, …) sont retirées :
      // elles ne faisaient que relancer la recherche des notices (filtre du
      // cycle de vie) sans écran propre.
      { key: 'declassement-lists', group: 'Déclassement', label: 'Listes de déclassement', href: '/transferrings/declassement-lists', icon: 'listOrdered' },
      { key: 'declassement-lists-create', group: 'Déclassement', label: 'Nouvelle liste', href: '/transferrings/declassement-lists/create', icon: 'plusCircle' },
      { key: 'record-reactivations', group: 'Déclassement', label: 'Réactivations', href: '/transferrings/reactivations', icon: 'rotateCcw' },

      { key: 'transferrings-import', group: 'Import / Export (EAD, Excel, SEDA)', label: 'Import', href: '/transferrings/import', icon: 'download' },
      { key: 'transferrings-export', group: 'Import / Export (EAD, Excel, SEDA)', label: 'Export', href: '/transferrings/export', icon: 'upload' },
    ],
  },

  // ------------------------------------------------------------------
  // 8. Deposits / Dépôts — submenu/deposits.blade.php
  // ------------------------------------------------------------------
  {
    key: 'deposits',
    label: 'Dépôts',
    href: '/deposits',
    icon: 'deposits',
    items: [
      { key: 'deposits-buildings', group: 'Recherche', label: 'Bâtiment', href: '/deposits/buildings', icon: 'deposits' },

      { key: 'deposits-buildings-create', group: 'Création', label: 'Bâtiment', href: '/deposits/buildings/create', icon: 'deposits' },
      { key: 'deposits-rooms-create', group: 'Création', label: 'Salle', href: '/deposits/rooms/create', icon: 'home' },
      { key: 'deposits-shelves-create', group: 'Création', label: 'Étagère', href: '/deposits/shelves/create', icon: 'library' },
      { key: 'deposits-containers-create', group: 'Création', label: 'Contenant', href: '/deposits/containers/create', icon: 'archive' },

      // Chariots : section du sous-menu Dépôts (ancien domaine racine « Chariots »,
      // href hors préfixe /deposits — voir findActiveDomain, repli par item.href).
      { key: 'dollies-all', group: 'Chariots', label: 'Tous les chariots', href: '/dollies', icon: 'dollies' },
      { key: 'dollies-mail', group: 'Chariots', label: 'Courrier', href: '/dollies/sort?categ=mail', icon: 'mails' },
      { key: 'dollies-record', group: 'Chariots', label: 'Archives', href: '/dollies/sort?categ=record', icon: 'archive' },
      { key: 'dollies-communication', group: 'Chariots', label: 'Communication', href: '/dollies/sort?categ=communication', icon: 'communications' },
      { key: 'dollies-room', group: 'Chariots', label: 'Salle', href: '/dollies/sort?categ=room', icon: 'home' },
      { key: 'dollies-shelf', group: 'Chariots', label: 'Étagère', href: '/dollies/sort?categ=shelf', icon: 'library' },
      { key: 'dollies-container', group: 'Chariots', label: "Boîtes d'archives", href: '/dollies/sort?categ=container', icon: 'package' },
      { key: 'dollies-slip-record', group: 'Chariots', label: "Transfert d'archives", href: '/dollies/sort?categ=slip_record', icon: 'fileUp' },
      { key: 'dollies-slip', group: 'Chariots', label: 'Transfert', href: '/dollies/sort?categ=slip', icon: 'transferrings' },
      { key: 'dollies-create', group: 'Chariots', label: 'Nouveau chariot', href: '/dollies/create', icon: 'dollies' },
    ],
  },

  // ------------------------------------------------------------------
  // 9. Tools / Outils — submenu/tools.blade.php (contient le Thésaurus)
  // ------------------------------------------------------------------
  {
    key: 'tools',
    label: 'Outils',
    href: '/tools',
    icon: 'tools',
    items: [
      { key: 'tools-activities', group: 'Plan de classement', label: 'Toutes les classes', href: '/tools/activities', icon: 'listCheck' },
      { key: 'tools-activities-create', group: 'Plan de classement', label: 'Ajouter une classe', href: '/tools/activities/create', icon: 'plusSquare' },

      { key: 'tools-retentions', group: 'Référentiel de conservation', label: 'Toutes les durées', href: '/tools/retentions', icon: 'history' },
      { key: 'tools-retentions-create', group: 'Référentiel de conservation', label: 'Ajouter une règle', href: '/tools/retentions/create', icon: 'plusSquare' },

      { key: 'tools-communicabilities', group: 'Communicabilité', label: 'Toutes les classes', href: '/tools/communicabilities', icon: 'listCheck' },
      { key: 'tools-communicabilities-create', group: 'Communicabilité', label: 'Ajouter une classe', href: '/tools/communicabilities/create', icon: 'plusSquare' },

      { key: 'tools-organisations', group: 'Organigramme', label: 'Toutes les unités', href: '/tools/organisations', icon: 'deposits' },
      { key: 'tools-organisations-create', group: 'Organigramme', label: 'Ajouter une unité', href: '/tools/organisations/create', icon: 'plusSquare' },

      // Thésaurus : section du menu Outils, PAS un menu racine.
      { key: 'thesaurus-home', group: 'Thésaurus', label: 'Accueil', href: '/tools/thesaurus', icon: 'home' },
      { key: 'thesaurus-hierarchy', group: 'Thésaurus', label: 'Voir les branches', href: '/tools/thesaurus/hierarchy', icon: 'network' },
      { key: 'thesaurus-schemes', group: 'Thésaurus', label: 'Schémas de thésaurus', href: '/tools/thesaurus/schemes', icon: 'listOrdered' },
      { key: 'thesaurus-search', group: 'Thésaurus', label: 'Rechercher', href: '/tools/thesaurus/search', icon: 'search' },
      { key: 'thesaurus-export-import', group: 'Thésaurus', label: 'Import / Export', href: '/tools/thesaurus/export-import', icon: 'arrowUpDown' },
      { key: 'thesaurus-add-word', group: 'Thésaurus', label: 'Ajouter un terme', href: '/tools/thesaurus/concepts/create', icon: 'plusSquare' },
      { key: 'thesaurus-term-hierarchy', group: 'Thésaurus', label: 'Hiérarchie des termes', href: '/tools/thesaurus/hierarchy', icon: 'network' },

      { key: 'tools-reference-lists', group: 'Référentiels', label: 'Domaines de valeurs', href: '/tools/reference-lists', icon: 'library' },
      { key: 'tools-record-types', group: 'Référentiels', label: 'Typologies de notices', href: '/tools/record-types', icon: 'tags' },
      { key: 'tools-metadata-definitions', group: 'Référentiels', label: 'Définitions de métadonnées', href: '/tools/metadata-definitions', icon: 'listCheck' },
      { key: 'tools-folder-types', group: 'Référentiels', label: 'Types de dossiers numériques', href: '/tools/folder-types', icon: 'folderOpen' },
      { key: 'tools-document-types', group: 'Référentiels', label: 'Types de documents numériques', href: '/tools/document-types', icon: 'fileText' },
      { key: 'tools-record-statuses', group: 'Référentiels', label: 'Statuts des notices', href: '/tools/record-statuses', icon: 'flag' },
      { key: 'tools-record-supports', group: 'Référentiels', label: 'Supports', href: '/tools/record-supports', icon: 'hardDrive' },
      { key: 'tools-sorts', group: 'Référentiels', label: 'Sorts finaux', href: '/tools/sorts', icon: 'sortAlpha' },

      { key: 'tools-barcode', group: 'Boîte à outils', label: 'Code-barres', href: '/tools/barcode/create', icon: 'scan' },
    ],
  },

  // ------------------------------------------------------------------
  // 11. Contacts (external) — submenu/external.blade.php
  // ------------------------------------------------------------------
  {
    key: 'contacts',
    label: 'Contacts',
    href: '/contacts',
    icon: 'contacts',
    items: [
      { key: 'external-orgs', group: 'Organisations externes', label: 'Liste des organisations', href: '/contacts/organisations', icon: 'deposits' },
      { key: 'external-orgs-create', group: 'Organisations externes', label: 'Nouvelle organisation', href: '/contacts/organisations/create', icon: 'plusCircle' },

      { key: 'external-contacts', group: 'Contacts externes', label: 'Liste des contacts', href: '/contacts', icon: 'person' },
      { key: 'external-contacts-create', group: 'Contacts externes', label: 'Nouveau contact', href: '/contacts/create', icon: 'plusCircle' },
    ],
  },

  // ------------------------------------------------------------------
  // 12. Public — submenu/public.blade.php
  // ------------------------------------------------------------------
  {
    key: 'public',
    label: 'Public',
    href: '/public',
    icon: 'public',
    items: [
      { key: 'public-dashboard', group: 'Tableau de bord & statistiques', label: 'Tableau de bord', href: '/public/dashboard', icon: 'dashboard' },
      { key: 'public-statistics', group: 'Tableau de bord & statistiques', label: 'Statistiques', href: '/public/statistics', icon: 'barChart' },

      { key: 'public-users', group: 'Utilisateurs publics', label: 'Liste des utilisateurs', href: '/public/users', icon: 'contacts' },
      { key: 'public-users-create', group: 'Utilisateurs publics', label: 'Ajouter un utilisateur', href: '/public/users/create', icon: 'personPlus' },

      { key: 'public-config', group: 'Configuration OPAC', label: 'Configuration générale', href: '/public/configurations', icon: 'sliders' },
      { key: 'public-pages-config', group: 'Configuration OPAC', label: 'Pages OPAC', href: '/public/pages', icon: 'fileText' },
      { key: 'public-users-config', group: 'Configuration OPAC', label: 'Utilisateurs OPAC', href: '/public/users', icon: 'contacts' },
      { key: 'public-templates-config', group: 'Configuration OPAC', label: 'Templates OPAC', href: '/public/opac-templates', icon: 'palette' },
      { key: 'public-opac-view', group: 'Configuration OPAC', label: 'Voir le portail OPAC', href: '/opac', icon: 'globe' },

      { key: 'public-news', group: 'Contenu public', label: 'Actualités', href: '/public/news', icon: 'newspaper' },
      { key: 'public-pages', group: 'Contenu public', label: 'Pages', href: '/public/pages', icon: 'fileText' },
      { key: 'public-templates', group: 'Contenu public', label: 'Templates', href: '/public/templates', icon: 'layoutTemplate' },
      { key: 'public-events', group: 'Contenu public', label: 'Événements', href: '/public/events', icon: 'calendarDays' },

      { key: 'public-records', group: 'Documents et archives', label: 'Notices', href: '/public/records', icon: 'records' },
      { key: 'public-document-requests', group: 'Documents et archives', label: 'Demandes de documents', href: '/public/document-requests', icon: 'fileDown' },
      { key: 'public-responses', group: 'Documents et archives', label: 'Réponses', href: '/public/responses', icon: 'reply' },
      { key: 'public-response-attachments', group: 'Documents et archives', label: 'Pièces jointes de réponse', href: '/public/response-attachments', icon: 'paperclip' },

      { key: 'public-chats', group: 'Interaction et communication', label: 'Chats', href: '/public/chats', icon: 'chats' },
      { key: 'public-chat-participants', group: 'Interaction et communication', label: 'Participants aux chats', href: '/public/chat-participants', icon: 'contacts' },
      { key: 'public-event-registrations', group: 'Interaction et communication', label: 'Inscriptions aux événements', href: '/public/event-registrations', icon: 'calendarCheck' },
      { key: 'public-feedback', group: 'Interaction et communication', label: 'Retours', href: '/public/feedback', icon: 'star' },
      { key: 'public-search-logs', group: 'Interaction et communication', label: 'Journaux de recherche', href: '/public/search-logs', icon: 'search' },
    ],
  },

  // ------------------------------------------------------------------
  // 14. Settings / Paramètres — submenu/settings.blade.php
  //    (intègre l'assistant IA en sous-section "Intelligence artificielle")
  // ------------------------------------------------------------------
  {
    key: 'settings',
    label: 'Paramètres',
    href: '/settings',
    icon: 'settings',
    items: [
      { key: 'settings-account', group: 'Mon compte', label: 'Mon compte', href: '/settings/account', icon: 'person' },

      { key: 'settings-categories', group: 'Paramètres', label: 'Catégories', href: '/settings/categories', icon: 'records' },
      { key: 'settings-definitions', group: 'Paramètres', label: 'Paramètres', href: '/settings/definitions', icon: 'settings2' },

      { key: 'settings-users', group: 'Autorisations et postes', label: 'Utilisateurs', href: '/settings/users', icon: 'person' },
      { key: 'settings-user-org-role', group: 'Autorisations et postes', label: 'Postes assignés', href: '/settings/user-organisation-role', icon: 'network' },

      { key: 'settings-roles', group: 'Droits et permissions', label: 'Rôles', href: '/settings/roles', icon: 'personBadge' },
      { key: 'settings-roles-create', group: 'Droits et permissions', label: 'Créer un rôle', href: '/settings/roles/create', icon: 'personPlus' },
      { key: 'settings-role-permissions', group: 'Droits et permissions', label: 'Assigner permissions', href: '/settings/role-permissions', icon: 'key' },
      { key: 'settings-role-permissions-create', group: 'Droits et permissions', label: 'Créer permission', href: '/settings/role-permissions/create', icon: 'key' },

      { key: 'settings-mail-typologies', group: 'Courrier', label: 'Typologies', href: '/settings/mail-typologies', icon: 'tags' },
      { key: 'settings-mail-actions', group: 'Courrier', label: 'Actions', href: '/settings/mail-actions', icon: 'playCircle' },
      { key: 'settings-mail-priorities', group: 'Courrier', label: 'Priorités', href: '/settings/mail-priorities', icon: 'flag' },

      { key: 'settings-record-supports', group: 'Répertoire', label: 'Supports', href: '/settings/record-supports', icon: 'hardDrive' },
      { key: 'settings-record-statuses', group: 'Répertoire', label: 'Statuts', href: '/settings/record-statuses', icon: 'flag' },

      { key: 'settings-folder-types', group: 'Notices numériques', label: 'Types de dossiers', href: '/settings/folder-types', icon: 'folderOpen' },
      { key: 'settings-document-types', group: 'Notices numériques', label: 'Types de documents', href: '/settings/document-types', icon: 'fileText' },
      { key: 'settings-record-types', group: 'Notices numériques', label: 'Typologies de notices', href: '/settings/record-types', icon: 'tags' },
      { key: 'settings-reference-lists', group: 'Notices numériques', label: 'Domaines de valeurs', href: '/settings/reference-lists', icon: 'library' },
      { key: 'settings-metadata-definitions', group: 'Notices numériques', label: 'Définitions de métadonnées', href: '/settings/metadata-definitions', icon: 'listCheck' },

      { key: 'settings-transferring-status', group: 'Transfert', label: 'Statuts', href: '/settings/transferring-status', icon: 'flag' },

      { key: 'settings-container-status', group: 'Dépôt', label: 'Statuts des contenants', href: '/settings/container-status', icon: 'flag' },
      { key: 'settings-container-property', group: 'Dépôt', label: 'Propriétés des contenants', href: '/settings/container-property', icon: 'listCheck' },

      { key: 'settings-ai-skills', group: 'Intelligence artificielle', label: 'Skills', href: '/ai-search/resources?tab=skills', icon: 'sparkles' },
      { key: 'settings-ai-prompts', group: 'Intelligence artificielle', label: 'Prompts', href: '/ai-search/resources?tab=prompts', icon: 'messageCircle' },
      { key: 'settings-ai-templates', group: 'Intelligence artificielle', label: 'Templates', href: '/ai-search/resources?tab=templates', icon: 'layoutTemplate' },
      { key: 'settings-ai-test', group: 'Intelligence artificielle', label: 'Tester le système', href: '/ai-search/test', icon: 'bug' },

      { key: 'settings-sorts', group: 'Outils de gestion', label: 'Sorts finaux', href: '/settings/sorts', icon: 'sortAlpha' },
      { key: 'settings-thesaurus-terms', group: 'Outils de gestion', label: 'Termes du thésaurus', href: '/tools/thesaurus', icon: 'tags' },
      { key: 'settings-thesaurus-search', group: 'Outils de gestion', label: 'Recherche thésaurus', href: '/tools/thesaurus/search', icon: 'search' },
      { key: 'settings-languages', group: 'Outils de gestion', label: 'Langues', href: '/settings/languages', icon: 'languages' },
      { key: 'settings-thesaurus-export', group: 'Outils de gestion', label: 'Import/export thésaurus', href: '/tools/thesaurus/export-import', icon: 'arrowUpDown' },

      { key: 'settings-system-updates', group: 'Système', label: 'Mises à jour', href: '/settings/system-updates', icon: 'refresh' },
      { key: 'settings-backups', group: 'Système', label: 'Mes sauvegardes', href: '/settings/backups', icon: 'save' },
      { key: 'settings-backups-create', group: 'Système', label: 'Nouvelle sauvegarde', href: '/settings/backups/create', icon: 'plusSquare' },
      { key: 'settings-ldap', group: 'Système', label: 'LDAP', href: '/settings/ldap', icon: 'contacts' },
    ],
  },
];
