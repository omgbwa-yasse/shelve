<?php

namespace App\Services\AI\Agent;

use App\Models\Activity;
use App\Models\Author;
use App\Models\Communication;
use App\Models\Container;
use App\Models\Dolly;
use App\Models\ExternalContact;
use App\Models\ExternalOrganization;
use App\Models\Law;
use App\Models\Mail;
use App\Models\Organisation;
use App\Models\PublicDocumentRequest;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalFolder;
use App\Models\RecordPhysical;
use App\Models\Reservation;
use App\Models\RetentionLawArticle;
use App\Models\Slip;
use App\Models\SlipRecord;
use App\Models\Task;
use App\Models\ThesaurusConcept;
use App\Models\User;
use App\Models\Workplace;

/**
 * Registre des outils de l'agent IA.
 *
 * Tous les outils sont en LECTURE SEULE et s'exécutent avec les droits de
 * l'utilisateur connecté : les requêtes sont scopées sur son organisation
 * courante via les scopes existants (byOrganisation / forOrganisation).
 * Un superadmin voit toutes les organisations (règle AC-5 du scoping).
 */
class AgentToolRegistry
{
    private const MAX_LIMIT = 20;
    private const TEXT_PREVIEW = 200;

    public function __construct(private SuggestionService $suggestions)
    {
    }

    /**
     * Définitions exposées au modèle dans le prompt système.
     */
    public function definitions(): array
    {
        return [
            'search_contacts' => [
                'description' => "Recherche des personnes : contacts externes ET utilisateurs internes (nom, prénom ou email). Retourne leurs coordonnées (email, téléphone, organisation) et le nombre de courriers liés visibles.",
                'arguments' => ['query' => 'string requis — nom, prénom ou fragment d\'email'],
            ],
            'search_mails' => [
                'description' => "Recherche des courriers (mails). Peut filtrer par mots-clés (objet/contenu), par personne (expéditeur ou destinataire, interne ou externe) et par période.",
                'arguments' => [
                    'query' => 'string optionnel — mots-clés dans code, objet, description',
                    'person' => 'string optionnel — nom d\'un expéditeur/destinataire (utilisateur ou contact externe)',
                    'date_from' => 'string optionnel — AAAA-MM-JJ',
                    'date_to' => 'string optionnel — AAAA-MM-JJ',
                    'mail_type' => "string optionnel — incoming, outgoing ou internal",
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_records' => [
                'description' => "Recherche des archives / documents (records) par mots-clés (index plein texte), producteur, mot-clé d'indexation, classe du plan de classement ou période. Retourne aussi la LOCALISATION physique (conteneur, étagère, salle, bâtiment) — utiliser pour « où se trouve tel document ? ».",
                'arguments' => [
                    'query' => 'string optionnel — mots-clés dans code, intitulé, contenu, note archiviste',
                    'author' => 'string optionnel — nom du producteur/auteur',
                    'keyword' => 'string optionnel — mot-clé d\'indexation attaché aux dossiers',
                    'classification' => 'string optionnel — nom ou code d\'une classe du plan de classement (descendants inclus)',
                    'year' => 'int optionnel — année (date de création du document)',
                    'date_from' => 'string optionnel — AAAA-MM-JJ',
                    'date_to' => 'string optionnel — AAAA-MM-JJ',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_communications' => [
                'description' => "Recherche des communications (consultations/prêts de documents) par mots-clés ou période.",
                'arguments' => [
                    'query' => 'string optionnel — mots-clés dans code, nom, contenu',
                    'date_from' => 'string optionnel — AAAA-MM-JJ',
                    'date_to' => 'string optionnel — AAAA-MM-JJ',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_slips' => [
                'description' => "Recherche des bordereaux de versement/transfert par mots-clés ou période.",
                'arguments' => [
                    'query' => 'string optionnel — mots-clés dans code, nom, description',
                    'date_from' => 'string optionnel — AAAA-MM-JJ',
                    'date_to' => 'string optionnel — AAAA-MM-JJ',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_authors' => [
                'description' => "Recherche dans le référentiel des producteurs/auteurs d'archives par nom.",
                'arguments' => [
                    'query' => 'string requis — nom ou fragment de nom',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_containers' => [
                'description' => "Recherche des conteneurs/boîtes d'archives par code, avec leur localisation physique (étagère, salle, étage, bâtiment).",
                'arguments' => [
                    'query' => 'string optionnel — code du conteneur',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_digital_folders' => [
                'description' => "Recherche des dossiers numériques (records électroniques) par mots-clés.",
                'arguments' => [
                    'query' => 'string optionnel — mots-clés dans code, nom, description',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_digital_documents' => [
                'description' => "Recherche des documents numériques par mots-clés.",
                'arguments' => [
                    'query' => 'string optionnel — mots-clés dans code, nom, description',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_dollies' => [
                'description' => "Recherche des chariots (dollies : paniers de travail regroupant courriers/archives) par nom.",
                'arguments' => [
                    'query' => 'string optionnel — nom ou description',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_tasks' => [
                'description' => "Recherche des tâches de workflow (titre, statut, échéance). Peut se limiter aux tâches assignées à l'utilisateur.",
                'arguments' => [
                    'query' => 'string optionnel — mots-clés dans le titre ou la description',
                    'assigned_to_me' => 'bool optionnel — true pour ne voir que les tâches assignées à l\'utilisateur',
                    'status' => 'string optionnel — statut de tâche',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_organisations' => [
                'description' => "Recherche dans l'annuaire des organisations internes (services, directions) par nom ou code.",
                'arguments' => ['query' => 'string requis — nom ou code'],
            ],
            'search_external_organizations' => [
                'description' => "Recherche des organisations externes (partenaires) par nom, ville ou email.",
                'arguments' => ['query' => 'string requis — nom, ville ou email'],
            ],
            'search_in_files' => [
                'description' => "Recherche PLEIN TEXTE dans le contenu des fichiers numérisés (texte OCR des pièces jointes de courriers et d'archives). Utiliser pour « retrouve le document/fichier qui parle de… ».",
                'arguments' => [
                    'query' => 'string requis — mots ou expression à chercher dans le contenu des fichiers',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_slip_contents' => [
                'description' => "Recherche dans le CONTENU détaillé des versements : les documents décrits à l'intérieur des bordereaux. Utiliser pour « que contenait le versement X ? ».",
                'arguments' => [
                    'query' => 'string optionnel — mots-clés dans code, intitulé, contenu des documents versés',
                    'slip_code' => 'string optionnel — code du bordereau pour lister son contenu',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_thesaurus' => [
                'description' => "Recherche un sujet dans le thésaurus (labels préférentiels et alternatifs) et retourne les concepts + les dossiers d'archives indexés sur ces concepts. Utiliser pour « quels dossiers parlent de tel sujet ? ».",
                'arguments' => [
                    'query' => 'string requis — sujet ou terme',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'browse_classification' => [
                'description' => "Consulte le plan de classement (activités hiérarchiques) avec les règles attachées (communicabilité, durée de rétention, sort final). Sans argument : les classes racines. Utiliser pour « où classe-t-on… ? ».",
                'arguments' => [
                    'query' => 'string optionnel — nom ou code d\'une classe/activité',
                    'parent_id' => 'int optionnel — id d\'une activité pour lister ses sous-classes',
                ],
            ],
            'search_reservations' => [
                'description' => "Recherche des réservations de documents (demandes de consultation à venir) par mots-clés ou période.",
                'arguments' => [
                    'query' => 'string optionnel — mots-clés dans code, nom, contenu',
                    'date_from' => 'string optionnel — AAAA-MM-JJ',
                    'date_to' => 'string optionnel — AAAA-MM-JJ',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_public_requests' => [
                'description' => "Recherche les demandes de documents déposées par les usagers du portail public (à traiter par les archivistes), filtrables par statut.",
                'arguments' => [
                    'status' => 'string optionnel — statut de la demande (ex. pending)',
                    'query' => 'string optionnel — mots-clés dans le motif',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_workplaces' => [
                'description' => "Recherche des espaces de travail collaboratifs par nom ou description.",
                'arguments' => [
                    'query' => 'string optionnel — nom ou description',
                    'limit' => 'int optionnel (défaut 10, max 20)',
                ],
            ],
            'search_laws' => [
                'description' => "Recherche dans le référentiel des lois et articles de loi (bases légales de conservation/communicabilité).",
                'arguments' => ['query' => 'string requis — nom, code ou contenu d\'article'],
            ],
            'check_communicability' => [
                'description' => "Vérifie si un dossier d'archives est communicable : calcule la date d'ouverture à partir de la durée de communicabilité de son activité de classement. Utiliser pour « ce dossier est-il communicable ? ».",
                'arguments' => ['record_id' => 'int requis — id du dossier (retourné par search_records)'],
            ],
            'check_retention' => [
                'description' => "Donne la durée de conservation (rétention), la date de fin calculée, le sort final (conservation/élimination/tri) et la base légale d'un dossier d'archives. Utiliser pour « combien de temps garder / quand éliminer ce dossier ? ».",
                'arguments' => ['record_id' => 'int requis — id du dossier (retourné par search_records)'],
            ],
            'list_eliminable_records' => [
                'description' => "Liste les dossiers du périmètre dont la durée de rétention est échue et dont le sort final est l'élimination (pré-tableau d'élimination). Utiliser pour « que peut-on éliminer cette année ? ».",
                'arguments' => ['year' => 'int optionnel — année de référence (défaut : année en cours)'],
            ],
            'suggest_classification' => [
                'description' => "Propose des classes du plan de classement adaptées à une description de document (lecture seule, rien n'est enregistré).",
                'arguments' => ['description' => 'string requis — description ou intitulé du document à classer'],
            ],
            'suggest_indexing' => [
                'description' => "Propose des mots-clés existants et des concepts du thésaurus adaptés à une description de document (lecture seule).",
                'arguments' => ['description' => 'string requis — description ou termes du document'],
            ],
            'count_items' => [
                'description' => "Compte des éléments sans les lister (pour les questions « combien »), avec regroupement optionnel. Respecte les mêmes droits d'accès que les recherches.",
                'arguments' => [
                    'type' => 'string requis — mails, records, communications, slips, containers, tasks, digital_folders, digital_documents, dollies, reservations ou workplaces',
                    'query' => 'string optionnel — mots-clés',
                    'year' => 'int optionnel — année (date de création)',
                    'date_from' => 'string optionnel — AAAA-MM-JJ',
                    'date_to' => 'string optionnel — AAAA-MM-JJ',
                    'group_by' => 'string optionnel — year ou organisation, pour un tableau agrégé',
                ],
            ],
            'get_details' => [
                'description' => "Affiche le détail complet d'un élément déjà trouvé par un autre outil.",
                'arguments' => [
                    'type' => 'string requis — records, mails, communications, slips, contacts ou authors',
                    'id' => 'int requis — id exact retourné par un outil précédent',
                ],
            ],
        ];
    }

    /**
     * Exécute un outil pour le compte de l'utilisateur donné.
     * Retourne toujours ['count' => int, 'items' => array] ou ['error' => string].
     */
    public function execute(string $tool, array $args, User $user): array
    {
        try {
            return match ($tool) {
                'search_contacts' => $this->searchContacts($args, $user),
                'search_mails' => $this->searchMails($args, $user),
                'search_records' => $this->searchRecords($args, $user),
                'search_communications' => $this->searchCommunications($args, $user),
                'search_slips' => $this->searchSlips($args, $user),
                'search_authors' => $this->searchAuthors($args),
                'search_containers' => $this->searchContainers($args, $user),
                'search_digital_folders' => $this->searchDigitalFolders($args, $user),
                'search_digital_documents' => $this->searchDigitalDocuments($args, $user),
                'search_dollies' => $this->searchDollies($args, $user),
                'search_tasks' => $this->searchTasks($args, $user),
                'search_organisations' => $this->searchOrganisations($args),
                'search_external_organizations' => $this->searchExternalOrganizations($args),
                'search_in_files' => $this->searchInFiles($args, $user),
                'search_slip_contents' => $this->searchSlipContents($args, $user),
                'search_thesaurus' => $this->searchThesaurus($args, $user),
                'browse_classification' => $this->browseClassification($args),
                'search_reservations' => $this->searchReservations($args, $user),
                // Pas de 'search_accessions' : le modèle Accession n'a aucune table
                // en base dans ce schéma (aucune migration ne la crée).
                'search_public_requests' => $this->searchPublicRequests($args, $user),
                'search_workplaces' => $this->searchWorkplaces($args, $user),
                'search_laws' => $this->searchLaws($args),
                'check_communicability' => $this->checkCommunicability($args, $user),
                'check_retention' => $this->checkRetention($args, $user),
                'list_eliminable_records' => $this->listEliminableRecords($args, $user),
                'suggest_classification' => $this->suggestClassification($args),
                'suggest_indexing' => $this->suggestIndexing($args),
                'count_items' => $this->countItems($args, $user),
                'get_details' => $this->getDetails($args, $user),
                default => ['error' => "Outil inconnu : {$tool}. Outils disponibles : " . implode(', ', array_keys($this->definitions()))],
            };
        } catch (\Throwable $e) {
            return ['error' => "Erreur pendant l'exécution de {$tool} : " . $e->getMessage()];
        }
    }

    /**
     * Organisation courante servant au scoping, null = superadmin (pas de filtre).
     * Un utilisateur sans organisation courante obtient 0 : il ne voit rien
     * plutôt que de tout voir.
     */
    private function scopeOrgId(User $user): ?int
    {
        return $user->isSuperAdmin() ? null : (int) ($user->current_organisation_id ?? 0);
    }

    private function limit(array $args, int $default = 10): int
    {
        $limit = (int) ($args['limit'] ?? $default);

        return max(1, min($limit, self::MAX_LIMIT));
    }

    private function preview(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        $text = trim(strip_tags($text));

        return mb_strlen($text) > self::TEXT_PREVIEW
            ? mb_substr($text, 0, self::TEXT_PREVIEW) . '…'
            : $text;
    }

    private function likeTerms($query, array $fields, string $terms)
    {
        $words = preg_split('/\s+/', trim($terms), -1, PREG_SPLIT_NO_EMPTY);

        return $query->where(function ($q) use ($words, $fields) {
            foreach ($words as $word) {
                $q->where(function ($sub) use ($word, $fields) {
                    foreach ($fields as $field) {
                        $sub->orWhere($field, 'LIKE', "%{$word}%");
                    }
                });
            }
        });
    }

    private function searchContacts(array $args, User $user): array
    {
        $query = trim((string) ($args['query'] ?? ''));
        if ($query === '') {
            return ['error' => "L'argument query est requis pour search_contacts."];
        }

        $orgId = $this->scopeOrgId($user);
        $items = [];

        $externals = $this->likeTerms(
            ExternalContact::query()->with('organization:id,name'),
            ['first_name', 'last_name', 'email'],
            $query
        )->limit(self::MAX_LIMIT)->get();

        foreach ($externals as $contact) {
            $mailCount = Mail::query()
                ->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId))
                ->where(function ($q) use ($contact) {
                    $q->where('external_sender_id', $contact->id)
                      ->orWhere('external_recipient_id', $contact->id);
                })
                ->count();

            $items[] = [
                'type' => 'contacts',
                'id' => $contact->id,
                'title' => $contact->full_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'description' => trim(($contact->position ? $contact->position . ' — ' : '') . ($contact->organization->name ?? 'Contact externe')),
                'linked_mails_visible' => $mailCount,
                'url' => url('/external/contacts/' . $contact->id),
            ];
        }

        // Utilisateurs internes : uniquement ceux qui partagent une organisation
        // avec l'utilisateur courant (annuaire interne), tous pour un superadmin.
        $orgIds = $user->organisations()->pluck('organisations.id');
        $internals = $this->likeTerms(User::query(), ['name', 'email'], $query)
            ->when($orgId !== null, function ($q) use ($orgIds) {
                $q->where(function ($sub) use ($orgIds) {
                    $sub->whereHas('organisations', fn ($o) => $o->whereIn('organisations.id', $orgIds))
                        ->orWhereIn('current_organisation_id', $orgIds);
                });
            })
            ->limit(self::MAX_LIMIT)
            ->get();

        foreach ($internals as $internal) {
            $items[] = [
                'type' => 'users',
                'id' => $internal->id,
                'title' => $internal->name,
                'email' => $internal->email,
                'description' => 'Utilisateur interne' . ($internal->organisation ? ' — ' . $internal->organisation->name : ''),
                'url' => null,
            ];
        }

        return ['count' => count($items), 'items' => $items];
    }

    private function searchMails(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $mails = Mail::query()
            ->with(['sender:id,name', 'recipient:id,name', 'externalSender:id,first_name,last_name,email', 'externalRecipient:id,first_name,last_name,email', 'priority:id,name'])
            ->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId))
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['code', 'name', 'description'], $args['query']))
            ->when(!empty($args['person']), function ($q) use ($args) {
                $person = $args['person'];
                $q->where(function ($sub) use ($person) {
                    $sub->whereHas('sender', fn ($u) => $u->where('name', 'LIKE', "%{$person}%"))
                        ->orWhereHas('recipient', fn ($u) => $u->where('name', 'LIKE', "%{$person}%"))
                        ->orWhereHas('externalSender', fn ($c) => $this->likeTerms($c, ['first_name', 'last_name', 'email'], $person))
                        ->orWhereHas('externalRecipient', fn ($c) => $this->likeTerms($c, ['first_name', 'last_name', 'email'], $person));
                });
            })
            ->when(!empty($args['mail_type']), fn ($q) => $q->where('mail_type', $args['mail_type']))
            ->when(!empty($args['date_from']), fn ($q) => $q->whereDate('date', '>=', $args['date_from']))
            ->when(!empty($args['date_to']), fn ($q) => $q->whereDate('date', '<=', $args['date_to']))
            ->orderByDesc('date')
            ->limit($this->limit($args))
            ->get();

        $items = $mails->map(function (Mail $mail) {
            $from = $mail->sender->name ?? $mail->externalSender?->full_name;
            $to = $mail->recipient->name ?? $mail->externalRecipient?->full_name;

            return [
                'type' => 'mails',
                'id' => $mail->id,
                'title' => $mail->name ?: ('Courrier ' . $mail->code),
                'code' => $mail->code,
                'date' => (string) $mail->date,
                'from' => $from,
                'to' => $to,
                'from_email' => $mail->externalSender->email ?? null,
                'to_email' => $mail->externalRecipient->email ?? null,
                'priority' => $mail->priority->name ?? null,
                'description' => $this->preview($mail->description),
                'url' => url('/mails/incoming/' . $mail->id),
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchRecords(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $query = RecordPhysical::query()
            ->with(['authors:id,name', 'status:id,name', 'activity:id,name', 'containers.shelf.room.floor.building'])
            ->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId));

        if (!empty($args['query'])) {
            // Index plein texte TNTSearch (pertinence, tolérance aux fautes),
            // repli sur le LIKE si l'index est absent ou en erreur.
            $scoutIds = [];
            try {
                $scoutIds = RecordPhysical::search($args['query'])->keys()->take(200)->all();
            } catch (\Throwable $e) {
                $scoutIds = [];
            }

            if (!empty($scoutIds)) {
                $query->whereIn('record_physicals.id', $scoutIds);
            } else {
                $this->likeTerms($query, ['code', 'name', 'content', 'archivist_note', 'note'], $args['query']);
            }
        }

        $records = $query
            ->when(!empty($args['author']), fn ($q) => $q->whereHas('authors', fn ($a) => $a->where('name', 'LIKE', '%' . $args['author'] . '%')))
            ->when(!empty($args['keyword']), fn ($q) => $q->whereHas('keywords', fn ($k) => $k->where('name', 'LIKE', '%' . $args['keyword'] . '%')))
            ->when(!empty($args['classification']), fn ($q) => $q->whereIn('activity_id', $this->classificationActivityIds($args['classification'])))
            ->when(!empty($args['year']), fn ($q) => $q->whereYear('created_at', (int) $args['year']))
            ->when(!empty($args['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $args['date_from']))
            ->when(!empty($args['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $args['date_to']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $records->map(function (RecordPhysical $record) {
            return [
                'type' => 'records',
                'id' => $record->id,
                'title' => $record->name ?: ('Archive ' . $record->code),
                'code' => $record->code,
                'authors' => $record->authors->pluck('name')->implode(', ') ?: null,
                'status' => $record->status->name ?? null,
                'activity' => $record->activity->name ?? null,
                'location' => $this->recordLocation($record),
                'description' => $this->preview($record->content),
                'url' => url('/repositories/records/' . $record->id),
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    /**
     * Localisation physique d'un dossier : conteneur → étagère → salle → bâtiment.
     */
    private function recordLocation(RecordPhysical $record): ?string
    {
        $container = $record->containers->first();
        if (!$container) {
            return null;
        }

        $parts = array_filter([
            'Conteneur ' . $container->code,
            isset($container->shelf->code) ? 'Étagère ' . $container->shelf->code : null,
            $container->shelf->room->name ?? $container->shelf->room->code ?? null,
            $container->shelf->room->floor->building->name ?? null,
        ]);

        return implode(' • ', $parts);
    }

    /**
     * Ids d'activités correspondant à une classe du plan de classement,
     * descendants inclus (3 niveaux).
     */
    private function classificationActivityIds(string $classification): array
    {
        $ids = Activity::query()
            ->where('name', 'LIKE', "%{$classification}%")
            ->orWhere('code', 'LIKE', "%{$classification}%")
            ->pluck('id')
            ->all();

        $frontier = $ids;
        for ($depth = 0; $depth < 3 && !empty($frontier); $depth++) {
            $frontier = Activity::query()->whereIn('parent_id', $frontier)->pluck('id')->all();
            $ids = array_merge($ids, $frontier);
        }

        return array_unique($ids);
    }

    private function searchCommunications(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $communications = Communication::query()
            ->with(['operator:id,name', 'user:id,name'])
            ->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId))
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['code', 'name', 'content'], $args['query']))
            ->when(!empty($args['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $args['date_from']))
            ->when(!empty($args['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $args['date_to']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $communications->map(function (Communication $communication) {
            $status = $communication->status;

            return [
                'type' => 'communications',
                'id' => $communication->id,
                'title' => $communication->name ?: ('Communication ' . $communication->code),
                'code' => $communication->code,
                'status' => is_object($status) ? ($status->value ?? null) : $status,
                'operator' => $communication->operator->name ?? null,
                'requester' => $communication->user->name ?? null,
                'return_date' => (string) $communication->return_date,
                'description' => $this->preview($communication->content),
                'url' => url('/communications/transactions/' . $communication->id),
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchSlips(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $slips = Slip::query()
            ->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId))
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['code', 'name', 'description'], $args['query']))
            ->when(!empty($args['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $args['date_from']))
            ->when(!empty($args['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $args['date_to']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $slips->map(function (Slip $slip) {
            return [
                'type' => 'slips',
                'id' => $slip->id,
                'title' => $slip->name ?: ('Bordereau ' . $slip->code),
                'code' => $slip->code,
                'received' => (bool) $slip->is_received,
                'approved' => (bool) $slip->is_approved,
                'integrated' => (bool) $slip->is_integrated,
                'description' => $this->preview($slip->description),
                'url' => url('/transferrings/slips/' . $slip->id),
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchAuthors(array $args): array
    {
        $query = trim((string) ($args['query'] ?? ''));
        if ($query === '') {
            return ['error' => "L'argument query est requis pour search_authors."];
        }

        $authors = $this->likeTerms(Author::query(), ['name', 'parallel_name', 'other_name'], $query)
            ->limit($this->limit($args))
            ->get();

        $items = $authors->map(function (Author $author) {
            return [
                'type' => 'authors',
                'id' => $author->id,
                'title' => $author->name,
                'description' => $this->preview($author->lifespan),
                'url' => url('/repositories/authors/' . $author->id),
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchContainers(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $containers = Container::query()
            ->with(['shelf.room.floor.building', 'status:id,name', 'property:id,name'])
            ->when($orgId !== null, fn ($q) => $q->where('creator_organisation_id', $orgId))
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['code'], $args['query']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $containers->map(function (Container $container) {
            $location = array_filter([
                $container->shelf->code ?? null,
                $container->shelf->room->name ?? $container->shelf->room->code ?? null,
                $container->shelf->room->floor->building->name ?? null,
            ]);

            return [
                'type' => 'containers',
                'id' => $container->id,
                'title' => 'Conteneur ' . $container->code,
                'status' => $container->status->name ?? null,
                'location' => $location ? implode(' • ', $location) : null,
                'description' => $location ? 'Localisation : ' . implode(' • ', $location) : null,
                'url' => url('/deposits/containers/' . $container->id),
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchDigitalFolders(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $folders = RecordDigitalFolder::query()
            ->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId))
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['code', 'name', 'description'], $args['query']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $folders->map(function (RecordDigitalFolder $folder) {
            return [
                'type' => 'digital_folders',
                'id' => $folder->id,
                'title' => $folder->name ?: ('Dossier ' . $folder->code),
                'code' => $folder->code,
                'status' => $folder->status,
                'description' => $this->preview($folder->description),
                'url' => null,
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchDigitalDocuments(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $documents = RecordDigitalDocument::query()
            ->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId))
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['code', 'name', 'description'], $args['query']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $documents->map(function (RecordDigitalDocument $document) {
            return [
                'type' => 'digital_documents',
                'id' => $document->id,
                'title' => $document->name ?: ('Document ' . $document->code),
                'code' => $document->code,
                'description' => $this->preview($document->description),
                'url' => null,
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchDollies(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $dollies = Dolly::query()
            ->when($orgId !== null, function ($q) use ($orgId, $user) {
                $q->where(function ($sub) use ($orgId, $user) {
                    $sub->where('owner_organisation_id', $orgId)
                        ->orWhere('created_by', $user->id)
                        ->orWhere('is_public', true);
                });
            })
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['name', 'description'], $args['query']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $dollies->map(function (Dolly $dolly) {
            return [
                'type' => 'dollies',
                'id' => $dolly->id,
                'title' => $dolly->name,
                'category' => $dolly->category,
                'description' => $this->preview($dolly->description),
                'url' => url('/dollies/' . $dolly->id),
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchTasks(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $tasks = Task::query()
            ->when($orgId !== null, fn ($q) => $q->where('organisation_id', $orgId))
            ->when(!empty($args['assigned_to_me']), fn ($q) => $q->where('assigned_to', $user->id))
            ->when(!empty($args['status']), fn ($q) => $q->where('status', $args['status']))
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['title', 'description'], $args['query']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $tasks->map(function (Task $task) {
            $status = $task->status;

            return [
                'type' => 'tasks',
                'id' => $task->id,
                'title' => $task->title,
                'status' => is_object($status) ? ($status->value ?? null) : $status,
                'due_date' => $task->due_date ? (string) $task->due_date : null,
                'description' => $this->preview($task->description),
                'url' => null,
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchOrganisations(array $args): array
    {
        $query = trim((string) ($args['query'] ?? ''));
        if ($query === '') {
            return ['error' => "L'argument query est requis pour search_organisations."];
        }

        // Annuaire de référence de la plateforme (visible dans tous les
        // formulaires) : pas de scoping, mais noms et codes uniquement.
        $organisations = $this->likeTerms(Organisation::query()->with('parent:id,name'), ['name', 'code'], $query)
            ->limit(self::MAX_LIMIT)
            ->get();

        $items = $organisations->map(function (Organisation $organisation) {
            return [
                'type' => 'organisations',
                'id' => $organisation->id,
                'title' => $organisation->name,
                'code' => $organisation->code,
                'description' => $organisation->parent ? 'Rattachée à : ' . $organisation->parent->name : null,
                'url' => null,
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchExternalOrganizations(array $args): array
    {
        $query = trim((string) ($args['query'] ?? ''));
        if ($query === '') {
            return ['error' => "L'argument query est requis pour search_external_organizations."];
        }

        $organizations = $this->likeTerms(ExternalOrganization::query(), ['name', 'email', 'city'], $query)
            ->limit(self::MAX_LIMIT)
            ->get();

        $items = $organizations->map(function (ExternalOrganization $organization) {
            return [
                'type' => 'external_organizations',
                'id' => $organization->id,
                'title' => $organization->name,
                'email' => $organization->email,
                'phone' => $organization->phone,
                'description' => trim(implode(' • ', array_filter([$organization->city, $organization->country]))) ?: null,
                'url' => url('/external/organizations/' . $organization->id),
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchInFiles(array $args, User $user): array
    {
        $term = trim((string) ($args['query'] ?? ''));
        if ($term === '') {
            return ['error' => "L'argument query est requis pour search_in_files."];
        }

        $orgId = $this->scopeOrgId($user);
        $limit = $this->limit($args);
        $items = [];

        // Interrogé depuis les parents (mails, records) pour hériter du scoping.
        $mails = Mail::query()
            ->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId))
            ->whereHas('attachments', fn ($a) => $a->where('content_text', 'LIKE', "%{$term}%"))
            ->with(['attachments' => fn ($a) => $a->where('content_text', 'LIKE', "%{$term}%")->select(['attachments.id', 'attachments.name', 'attachments.content_text', 'attachments.page_count'])])
            ->limit($limit)
            ->get();

        foreach ($mails as $mail) {
            $attachment = $mail->attachments->first();
            $items[] = [
                'type' => 'mails',
                'id' => $mail->id,
                'title' => $mail->name ?: ('Courrier ' . $mail->code),
                'file' => $attachment->name ?? null,
                'excerpt' => $attachment ? $this->excerptAround($attachment->content_text, $term) : null,
                'description' => 'Trouvé dans la pièce jointe « ' . ($attachment->name ?? '?') . ' »',
                'url' => url('/mails/incoming/' . $mail->id),
            ];
        }

        $records = RecordPhysical::query()
            ->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId))
            ->whereHas('attachments', fn ($a) => $a->where('content_text', 'LIKE', "%{$term}%"))
            ->with(['attachments' => fn ($a) => $a->where('content_text', 'LIKE', "%{$term}%")->select(['attachments.id', 'attachments.name', 'attachments.content_text', 'attachments.page_count'])])
            ->limit($limit)
            ->get();

        foreach ($records as $record) {
            $attachment = $record->attachments->first();
            $items[] = [
                'type' => 'records',
                'id' => $record->id,
                'title' => $record->name ?: ('Archive ' . $record->code),
                'file' => $attachment->name ?? null,
                'excerpt' => $attachment ? $this->excerptAround($attachment->content_text, $term) : null,
                'description' => 'Trouvé dans le fichier « ' . ($attachment->name ?? '?') . ' »',
                'url' => url('/repositories/records/' . $record->id),
            ];
        }

        return ['count' => count($items), 'items' => array_slice($items, 0, self::MAX_LIMIT)];
    }

    /**
     * Extrait ±120 caractères autour de la première occurrence du terme.
     */
    private function excerptAround(?string $text, string $term): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        $position = mb_stripos($text, $term);
        if ($position === false) {
            return $this->preview($text);
        }

        $start = max(0, $position - 120);
        $excerpt = mb_substr($text, $start, 240 + mb_strlen($term));

        return ($start > 0 ? '…' : '') . trim($excerpt) . '…';
    }

    private function searchSlipContents(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $slipRecords = SlipRecord::query()
            ->with(['slip:id,code,name', 'activity:id,name'])
            ->whereHas('slip', fn ($s) => $orgId !== null ? $s->forOrganisation($orgId) : $s)
            ->when(!empty($args['slip_code']), fn ($q) => $q->whereHas('slip', fn ($s) => $s->where('code', 'LIKE', '%' . $args['slip_code'] . '%')))
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['code', 'name', 'content'], $args['query']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $slipRecords->map(function (SlipRecord $slipRecord) {
            return [
                'type' => 'slip_records',
                'id' => $slipRecord->id,
                'title' => $slipRecord->name ?: ('Document versé ' . $slipRecord->code),
                'code' => $slipRecord->code,
                'slip' => $slipRecord->slip ? ($slipRecord->slip->code . ' — ' . $slipRecord->slip->name) : null,
                'activity' => $slipRecord->activity->name ?? null,
                'dates' => trim(implode(' → ', array_filter([$slipRecord->date_start, $slipRecord->date_end]))) ?: $slipRecord->date_exact,
                'description' => $this->preview($slipRecord->content),
                'url' => $slipRecord->slip_id ? url('/transferrings/slips/' . $slipRecord->slip_id) : null,
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchThesaurus(array $args, User $user): array
    {
        $term = trim((string) ($args['query'] ?? ''));
        if ($term === '') {
            return ['error' => "L'argument query est requis pour search_thesaurus."];
        }

        $orgId = $this->scopeOrgId($user);

        $concepts = ThesaurusConcept::query()
            ->with(['labels' => fn ($l) => $l->where('literal_form', 'LIKE', "%{$term}%")])
            ->whereHas('labels', fn ($l) => $l->where('literal_form', 'LIKE', "%{$term}%"))
            ->limit(10)
            ->get();

        if ($concepts->isEmpty()) {
            return ['count' => 0, 'items' => [], 'message' => "Aucun concept du thésaurus ne correspond à « {$term} »."];
        }

        $items = $concepts->map(function (ThesaurusConcept $concept) {
            return [
                'type' => 'thesaurus_concepts',
                'id' => $concept->id,
                'title' => $concept->preferred_label,
                'matched_labels' => $concept->labels->pluck('literal_form')->unique()->values()->all(),
                'description' => 'Concept du thésaurus',
                'url' => null,
            ];
        })->all();

        // Dossiers indexés sur ces concepts, dans le périmètre de l'utilisateur.
        $records = RecordPhysical::query()
            ->with(['activity:id,name'])
            ->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId))
            ->whereHas('thesaurusConcepts', fn ($c) => $c->whereIn('concept_id', $concepts->pluck('id')))
            ->limit($this->limit($args))
            ->get();

        foreach ($records as $record) {
            $items[] = [
                'type' => 'records',
                'id' => $record->id,
                'title' => $record->name ?: ('Archive ' . $record->code),
                'code' => $record->code,
                'activity' => $record->activity->name ?? null,
                'description' => 'Dossier indexé sur ce sujet',
                'url' => url('/repositories/records/' . $record->id),
            ];
        }

        return ['count' => count($items), 'items' => $items];
    }

    private function browseClassification(array $args): array
    {
        $activities = Activity::query()
            ->with(['parent:id,name', 'communicability:id,name,duration', 'retentions.sort'])
            ->withCount('children')
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['name', 'code', 'observation'], $args['query']))
            ->when(!empty($args['parent_id']), fn ($q) => $q->where('parent_id', (int) $args['parent_id']))
            ->when(empty($args['query']) && empty($args['parent_id']), fn ($q) => $q->whereNull('parent_id'))
            ->orderBy('code')
            ->limit(self::MAX_LIMIT)
            ->get();

        $items = $activities->map(function (Activity $activity) {
            $retention = $activity->retentions->first();

            return [
                'type' => 'activities',
                'id' => $activity->id,
                'title' => trim($activity->code . ' — ' . $activity->name, ' —'),
                'parent' => $activity->parent->name ?? null,
                'children_count' => $activity->children_count,
                'communicability' => $activity->communicability
                    ? $activity->communicability->name . ' (' . $activity->communicability->duration . ' ans)'
                    : null,
                'retention' => $retention
                    ? $retention->duration . ' ans, sort final : ' . ($retention->sort->name ?? '?')
                    : null,
                'description' => $this->preview($activity->observation),
                'url' => null,
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchReservations(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $reservations = Reservation::query()
            ->with(['operator:id,name', 'user:id,name'])
            ->when($orgId !== null, function ($q) use ($orgId) {
                $q->where(function ($sub) use ($orgId) {
                    $sub->where('operator_organisation_id', $orgId)
                        ->orWhere('user_organisation_id', $orgId);
                });
            })
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['code', 'name', 'content'], $args['query']))
            ->when(!empty($args['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $args['date_from']))
            ->when(!empty($args['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $args['date_to']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $reservations->map(function (Reservation $reservation) {
            $status = $reservation->status;

            return [
                'type' => 'reservations',
                'id' => $reservation->id,
                'title' => $reservation->name ?: ('Réservation ' . $reservation->code),
                'code' => $reservation->code,
                'status' => is_object($status) ? ($status->value ?? null) : $status,
                'operator' => $reservation->operator->name ?? null,
                'requester' => $reservation->user->name ?? null,
                'return_date' => $reservation->return_date ? (string) $reservation->return_date : null,
                'description' => $this->preview($reservation->content),
                'url' => null,
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchPublicRequests(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $requests = PublicDocumentRequest::query()
            ->with(['user:id,name,email', 'record'])
            ->when($orgId !== null, fn ($q) => $q->whereHas('record.record', fn ($r) => $r->byOrganisation($orgId)))
            ->when(!empty($args['status']), fn ($q) => $q->where('status', $args['status']))
            ->when(!empty($args['query']), fn ($q) => $q->where('reason', 'LIKE', '%' . $args['query'] . '%'))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $requests->map(function (PublicDocumentRequest $request) {
            return [
                'type' => 'public_requests',
                'id' => $request->id,
                'title' => 'Demande #' . $request->id . ' — ' . ($request->request_type ?? 'document'),
                'status' => $request->status,
                'requester' => $request->user->name ?? null,
                'requester_email' => $request->user->email ?? null,
                'description' => $this->preview($request->reason),
                'url' => null,
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchWorkplaces(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);

        $workplaces = Workplace::query()
            ->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId))
            ->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, ['code', 'name', 'description'], $args['query']))
            ->orderByDesc('created_at')
            ->limit($this->limit($args))
            ->get();

        $items = $workplaces->map(function (Workplace $workplace) {
            return [
                'type' => 'workplaces',
                'id' => $workplace->id,
                'title' => $workplace->name,
                'code' => $workplace->code,
                'members_count' => $workplace->members_count,
                'description' => $this->preview($workplace->description),
                'url' => null,
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    private function searchLaws(array $args): array
    {
        $term = trim((string) ($args['query'] ?? ''));
        if ($term === '') {
            return ['error' => "L'argument query est requis pour search_laws."];
        }

        $laws = Law::query()
            ->with(['articles' => fn ($a) => $a->limit(5)])
            ->where(function ($q) use ($term) {
                $this->likeTerms($q, ['code', 'name', 'description'], $term);
                $q->orWhereHas('articles', fn ($a) => $a->where('name', 'LIKE', "%{$term}%")->orWhere('content', 'LIKE', "%{$term}%"));
            })
            ->limit($this->limit($args))
            ->get();

        $items = $laws->map(function (Law $law) {
            return [
                'type' => 'laws',
                'id' => $law->id,
                'title' => trim($law->code . ' — ' . $law->name, ' —'),
                'publish_date' => $law->publish_date ? (string) $law->publish_date : null,
                'articles' => $law->articles->map(fn ($article) => $article->code . ' ' . $article->name)->all(),
                'description' => $this->preview($law->description),
                'url' => null,
            ];
        })->all();

        return ['count' => count($items), 'items' => $items];
    }

    /**
     * Dossier scopé + année de référence pour les calculs réglementaires.
     */
    private function scopedRecordWithRules(int $recordId, User $user): ?RecordPhysical
    {
        $orgId = $this->scopeOrgId($user);

        return RecordPhysical::query()
            ->with(['activity.communicability', 'activity.retentions.sort'])
            ->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId))
            ->find($recordId);
    }

    /**
     * Année de clôture du dossier (fin de période, date exacte, ou création).
     */
    private function recordBaseYear(RecordPhysical $record): ?int
    {
        foreach ([$record->date_end, $record->date_exact, $record->date_start] as $date) {
            if (!empty($date) && preg_match('/(\d{4})/', (string) $date, $matches)) {
                return (int) $matches[1];
            }
        }

        return $record->created_at ? (int) $record->created_at->format('Y') : null;
    }

    private function checkCommunicability(array $args, User $user): array
    {
        $record = $this->scopedRecordWithRules((int) ($args['record_id'] ?? 0), $user);
        if (!$record) {
            return ['error' => 'Dossier introuvable ou hors de votre périmètre.'];
        }

        $communicability = $record->activity?->communicability;
        if (!$communicability) {
            return ['count' => 0, 'items' => [], 'message' => "Le dossier « {$record->name} » n'a pas de règle de communicabilité (activité « " . ($record->activity->name ?? 'non classée') . " » sans délai défini). Impossible de calculer."];
        }

        $baseYear = $this->recordBaseYear($record);
        if ($baseYear === null) {
            return ['count' => 0, 'items' => [], 'message' => "Le dossier « {$record->name} » n'a aucune date exploitable pour calculer la communicabilité (délai : {$communicability->duration} ans)."];
        }

        $openYear = $baseYear + (int) $communicability->duration;
        $isOpen = (int) now()->format('Y') >= $openYear;

        $message = $isOpen
            ? "Le dossier « {$record->name} » ({$record->code}) est COMMUNICABLE : clos en {$baseYear}, délai de {$communicability->duration} ans ({$communicability->name}), ouvert depuis {$openYear}."
            : "Le dossier « {$record->name} » ({$record->code}) n'est PAS encore communicable : clos en {$baseYear}, délai de {$communicability->duration} ans ({$communicability->name}), communicable à partir de {$openYear}.";

        return ['count' => 1, 'items' => [[
            'type' => 'records',
            'id' => $record->id,
            'title' => $record->name ?: ('Archive ' . $record->code),
            'code' => $record->code,
            'communicable' => $isOpen,
            'closed_year' => $baseYear,
            'communicability_rule' => $communicability->name . ' (' . $communicability->duration . ' ans)',
            'open_from_year' => $openYear,
            'description' => $message,
            'url' => url('/repositories/records/' . $record->id),
        ]], 'message' => $message];
    }

    private function checkRetention(array $args, User $user): array
    {
        $record = $this->scopedRecordWithRules((int) ($args['record_id'] ?? 0), $user);
        if (!$record) {
            return ['error' => 'Dossier introuvable ou hors de votre périmètre.'];
        }

        $retention = $record->activity?->retentions?->first();
        if (!$retention) {
            return ['count' => 0, 'items' => [], 'message' => "Le dossier « {$record->name} » n'a pas de règle de rétention (activité « " . ($record->activity->name ?? 'non classée') . " »)."];
        }

        $baseYear = $this->recordBaseYear($record);
        $untilYear = $baseYear !== null ? $baseYear + (int) $retention->duration : null;
        $sortName = $retention->sort->name ?? 'non défini';

        // Base légale attachée à la règle de rétention.
        $legalBasis = RetentionLawArticle::query()
            ->where('retention_id', $retention->id)
            ->with('lawArticle.law')
            ->get()
            ->map(function ($link) {
                $article = $link->lawArticle;

                return $article ? trim(($article->code ?? '') . ' ' . ($article->name ?? '') . (isset($article->law->name) ? ' (' . $article->law->name . ')' : '')) : null;
            })
            ->filter()
            ->values()
            ->all();

        $message = "Dossier « {$record->name} » ({$record->code}) : rétention « {$retention->name} » de {$retention->duration} ans"
            . ($untilYear !== null ? ", à conserver jusqu'en {$untilYear} (clos en {$baseYear})" : ' (aucune date exploitable pour calculer l\'échéance)')
            . ", sort final : {$sortName}"
            . (!empty($legalBasis) ? '. Base légale : ' . implode(' ; ', $legalBasis) : '.');

        return ['count' => 1, 'items' => [[
            'type' => 'records',
            'id' => $record->id,
            'title' => $record->name ?: ('Archive ' . $record->code),
            'code' => $record->code,
            'retention_rule' => $retention->name . ' (' . $retention->duration . ' ans)',
            'retain_until_year' => $untilYear,
            'final_sort' => $sortName,
            'legal_basis' => $legalBasis,
            'description' => $message,
            'url' => url('/repositories/records/' . $record->id),
        ]], 'message' => $message];
    }

    private function listEliminableRecords(array $args, User $user): array
    {
        $orgId = $this->scopeOrgId($user);
        $referenceYear = (int) ($args['year'] ?? now()->format('Y'));

        // Candidats : dossiers dont l'activité porte une rétention avec sort
        // final « élimination » ; l'échéance est ensuite calculée en PHP car
        // les dates des dossiers sont stockées sous des formats hétérogènes.
        $candidates = RecordPhysical::query()
            ->with(['activity.retentions.sort'])
            ->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId))
            ->whereHas('activity.retentions.sort', function ($q) {
                $q->where('name', 'LIKE', '%limin%')->orWhere('code', 'E');
            })
            ->limit(300)
            ->get();

        $items = [];
        foreach ($candidates as $record) {
            $retention = $record->activity?->retentions
                ?->first(fn ($r) => str_contains(mb_strtolower((string) ($r->sort->name ?? '')), 'limin') || ($r->sort->code ?? '') === 'E');
            if (!$retention) {
                continue;
            }

            $baseYear = $this->recordBaseYear($record);
            if ($baseYear === null) {
                continue;
            }

            $untilYear = $baseYear + (int) $retention->duration;
            if ($untilYear > $referenceYear) {
                continue;
            }

            $items[] = [
                'type' => 'records',
                'id' => $record->id,
                'title' => $record->name ?: ('Archive ' . $record->code),
                'code' => $record->code,
                'closed_year' => $baseYear,
                'retention_expired_year' => $untilYear,
                'description' => "Rétention échue en {$untilYear} — éliminable (sort final : élimination)",
                'url' => url('/repositories/records/' . $record->id),
            ];

            if (count($items) >= self::MAX_LIMIT) {
                break;
            }
        }

        $message = empty($items)
            ? "Aucun dossier de votre périmètre n'est éliminable en {$referenceYear} (rétention échue + sort final élimination)."
            : count($items) . " dossier(s) éliminables en {$referenceYear} (à valider avant tout visa d'élimination).";

        return ['count' => count($items), 'items' => $items, 'message' => $message];
    }

    private function suggestClassification(array $args): array
    {
        $description = trim((string) ($args['description'] ?? ''));
        if ($description === '') {
            return ['error' => "L'argument description est requis pour suggest_classification."];
        }

        $candidates = $this->suggestions->suggestActivities($description);
        if (empty($candidates)) {
            return ['count' => 0, 'items' => [], 'message' => 'Aucune classe du plan de classement ne correspond à cette description.'];
        }

        $items = array_map(fn (array $candidate) => [
            'type' => 'activities',
            'id' => $candidate['id'],
            'title' => trim($candidate['code'] . ' — ' . $candidate['name'], ' —'),
            'score' => $candidate['score'],
            'description' => 'Classe candidate (score ' . $candidate['score'] . ')',
            'url' => null,
        ], $candidates);

        return ['count' => count($items), 'items' => $items];
    }

    private function suggestIndexing(array $args): array
    {
        $description = trim((string) ($args['description'] ?? ''));
        if ($description === '') {
            return ['error' => "L'argument description est requis pour suggest_indexing."];
        }

        $result = $this->suggestions->suggestIndexing($description);
        $items = [];

        foreach ($result['keywords'] as $keyword) {
            $items[] = [
                'type' => 'keywords',
                'id' => $keyword['id'],
                'title' => $keyword['name'],
                'description' => 'Mot-clé existant',
                'url' => null,
            ];
        }

        foreach ($result['concepts'] as $concept) {
            $items[] = [
                'type' => 'thesaurus_concepts',
                'id' => $concept['id'],
                'title' => $concept['preferred_label'],
                'matched_labels' => $concept['matched_labels'],
                'description' => 'Concept du thésaurus',
                'url' => null,
            ];
        }

        return ['count' => count($items), 'items' => $items];
    }

    private function countItems(array $args, User $user): array
    {
        $type = (string) ($args['type'] ?? '');
        $orgId = $this->scopeOrgId($user);

        $searchFields = [
            'mails' => ['code', 'name', 'description'],
            'records' => ['code', 'name', 'content', 'archivist_note'],
            'communications' => ['code', 'name', 'content'],
            'slips' => ['code', 'name', 'description'],
            'containers' => ['code'],
            'tasks' => ['title', 'description'],
            'digital_folders' => ['code', 'name', 'description'],
            'digital_documents' => ['code', 'name', 'description'],
            'dollies' => ['name', 'description'],
            'reservations' => ['code', 'name', 'content'],
            'workplaces' => ['code', 'name', 'description'],
        ];

        // Colonne d'organisation « propriétaire » pour group_by=organisation.
        $orgColumns = [
            'records' => 'organisation_id',
            'containers' => 'creator_organisation_id',
            'tasks' => 'organisation_id',
            'digital_folders' => 'organisation_id',
            'digital_documents' => 'organisation_id',
            'dollies' => 'owner_organisation_id',
            'workplaces' => 'organisation_id',
        ];

        $query = match ($type) {
            'mails' => Mail::query()->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId)),
            'records' => RecordPhysical::query()->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId)),
            'communications' => Communication::query()->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId)),
            'slips' => Slip::query()->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId)),
            'containers' => Container::query()->when($orgId !== null, fn ($q) => $q->where('creator_organisation_id', $orgId)),
            'tasks' => Task::query()->when($orgId !== null, fn ($q) => $q->where('organisation_id', $orgId)),
            'digital_folders' => RecordDigitalFolder::query()->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId)),
            'digital_documents' => RecordDigitalDocument::query()->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId)),
            'dollies' => Dolly::query()->when($orgId !== null, fn ($q) => $q->where(fn ($sub) => $sub->where('owner_organisation_id', $orgId)->orWhere('is_public', true))),
            'reservations' => Reservation::query()->when($orgId !== null, fn ($q) => $q->where(fn ($sub) => $sub->where('operator_organisation_id', $orgId)->orWhere('user_organisation_id', $orgId))),
            'workplaces' => Workplace::query()->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId)),
            default => null,
        };

        if ($query === null) {
            return ['error' => "Type inconnu pour count_items : {$type}. Types possibles : " . implode(', ', array_keys($searchFields))];
        }

        $query->when(!empty($args['query']), fn ($q) => $this->likeTerms($q, $searchFields[$type], $args['query']))
            ->when(!empty($args['year']), fn ($q) => $q->whereYear('created_at', (int) $args['year']))
            ->when(!empty($args['date_from']), fn ($q) => $q->whereDate('created_at', '>=', $args['date_from']))
            ->when(!empty($args['date_to']), fn ($q) => $q->whereDate('created_at', '<=', $args['date_to']));

        $groupBy = (string) ($args['group_by'] ?? '');

        if ($groupBy === 'year') {
            $rows = $query->selectRaw('YEAR(created_at) as grp, COUNT(*) as total')
                ->groupBy('grp')
                ->orderByDesc('grp')
                ->limit(30)
                ->pluck('total', 'grp')
                ->all();

            $lines = [];
            foreach ($rows as $year => $total) {
                $lines[] = "{$year} : {$total}";
            }

            return ['count' => 0, 'items' => [], 'total' => array_sum($rows), 'breakdown' => $rows, 'message' => "Répartition des {$type} par année — " . implode(' | ', $lines)];
        }

        if ($groupBy === 'organisation' && isset($orgColumns[$type])) {
            $column = $orgColumns[$type];
            $rows = $query->selectRaw("{$column} as grp, COUNT(*) as total")
                ->groupBy('grp')
                ->orderByDesc('total')
                ->limit(30)
                ->pluck('total', 'grp')
                ->all();

            $names = Organisation::query()->whereIn('id', array_keys($rows))->pluck('name', 'id');
            $lines = [];
            $breakdown = [];
            foreach ($rows as $orgKey => $total) {
                $label = $names[$orgKey] ?? ('Organisation #' . $orgKey);
                $breakdown[$label] = $total;
                $lines[] = "{$label} : {$total}";
            }

            return ['count' => 0, 'items' => [], 'total' => array_sum($rows), 'breakdown' => $breakdown, 'message' => "Répartition des {$type} par organisation — " . implode(' | ', $lines)];
        }

        $total = $query->count();

        return ['count' => 0, 'items' => [], 'total' => $total, 'message' => "{$total} élément(s) de type {$type} correspondent aux critères."];
    }

    private function getDetails(array $args, User $user): array
    {
        $type = (string) ($args['type'] ?? '');
        $id = (int) ($args['id'] ?? 0);
        if ($type === '' || $id <= 0) {
            return ['error' => 'Les arguments type et id sont requis pour get_details.'];
        }

        if ($type === 'contacts') {
            $contact = ExternalContact::with('organization:id,name')->find($id);
            if (!$contact) {
                return ['error' => "Contact {$id} introuvable."];
            }

            return ['count' => 1, 'items' => [[
                'type' => 'contacts',
                'id' => $contact->id,
                'title' => $contact->full_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'address' => $contact->address,
                'position' => $contact->position,
                'organization' => $contact->organization->name ?? null,
                'description' => $this->preview($contact->notes),
                'url' => url('/external/contacts/' . $contact->id),
            ]]];
        }

        if ($type === 'authors') {
            $author = Author::find($id);
            if (!$author) {
                return ['error' => "Auteur {$id} introuvable."];
            }

            return ['count' => 1, 'items' => [[
                'type' => 'authors',
                'id' => $author->id,
                'title' => $author->name,
                'parallel_name' => $author->parallel_name,
                'other_name' => $author->other_name,
                'lifespan' => $author->lifespan,
                'locations' => $author->locations,
                'url' => url('/repositories/authors/' . $author->id),
            ]]];
        }

        // Requête ciblée toujours scopée : impossible d'atteindre un élément
        // d'une autre organisation en devinant son id.
        $orgId = $this->scopeOrgId($user);
        $found = match ($type) {
            'mails' => Mail::query()->with(['sender:id,name', 'recipient:id,name', 'externalSender', 'externalRecipient', 'priority:id,name', 'attachments:attachments.id,attachments.name,attachments.page_count', 'containers:mail_containers.id,mail_containers.code,mail_containers.name'])
                ->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId))->find($id),
            'records' => RecordPhysical::query()->with(['authors:id,name', 'status:id,name', 'activity.communicability', 'activity.retentions.sort', 'containers.shelf.room.floor.building', 'attachments:attachments.id,attachments.name,attachments.page_count'])
                ->when($orgId !== null, fn ($q) => $q->byOrganisation($orgId))->find($id),
            'communications' => Communication::query()->with(['operator:id,name', 'user:id,name'])
                ->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId))->find($id),
            'slips' => Slip::query()->when($orgId !== null, fn ($q) => $q->forOrganisation($orgId))->find($id),
            default => null,
        };

        if (!$found) {
            return ['error' => "Élément {$type}#{$id} introuvable, type inconnu ou hors de votre périmètre."];
        }

        $urls = [
            'mails' => '/mails/incoming/',
            'records' => '/repositories/records/',
            'communications' => '/communications/transactions/',
            'slips' => '/transferrings/slips/',
        ];

        $item = [
            'type' => $type,
            'id' => $found->id,
            'title' => $found->name ?: ('Élément #' . $found->id),
            'code' => $found->code ?? null,
            'description' => $this->preview($found->content ?? $found->description ?? null),
            'url' => url($urls[$type] . $found->id),
        ];

        if ($type === 'mails') {
            $item['from'] = $found->sender->name ?? $found->externalSender?->full_name;
            $item['to'] = $found->recipient->name ?? $found->externalRecipient?->full_name;
            $item['from_email'] = $found->externalSender->email ?? null;
            $item['to_email'] = $found->externalRecipient->email ?? null;
            $item['date'] = (string) $found->date;
            $item['attachments'] = $found->attachments
                ->map(fn ($attachment) => $attachment->name . ($attachment->page_count ? " ({$attachment->page_count} p.)" : ''))
                ->all() ?: null;
            $archiveContainer = $found->containers->first();
            $item['archived_in'] = $archiveContainer ? trim($archiveContainer->code . ' — ' . $archiveContainer->name, ' —') : null;
        }

        if ($type === 'records') {
            $item['authors'] = $found->authors->pluck('name')->implode(', ') ?: null;
            $item['status'] = $found->status->name ?? null;
            $item['activity'] = $found->activity->name ?? null;
            $item['location'] = $this->recordLocation($found);
            $item['attachments'] = $found->attachments
                ->map(fn ($attachment) => $attachment->name . ($attachment->page_count ? " ({$attachment->page_count} p.)" : ''))
                ->all() ?: null;

            $communicability = $found->activity?->communicability;
            if ($communicability) {
                $baseYear = $this->recordBaseYear($found);
                $item['communicability'] = $communicability->name . ' (' . $communicability->duration . ' ans'
                    . ($baseYear !== null ? ', communicable à partir de ' . ($baseYear + (int) $communicability->duration) : '') . ')';
            }

            $retention = $found->activity?->retentions?->first();
            if ($retention) {
                $baseYear = $baseYear ?? $this->recordBaseYear($found);
                $item['retention'] = $retention->duration . ' ans'
                    . ($baseYear !== null ? ", jusqu'en " . ($baseYear + (int) $retention->duration) : '')
                    . ', sort final : ' . ($retention->sort->name ?? '?');
            }
        }

        return ['count' => 1, 'items' => [$item]];
    }
}
