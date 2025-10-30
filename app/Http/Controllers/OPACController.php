<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Activity;
use App\Models\Organisation;
use App\Models\OpacConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class OPACController extends Controller
{
    /**
     * Page d'accueil de l'OPAC
     */
    public function index(Request $request)
    {
        $organisationId = $this->getCurrentOrganisationId($request);
        $config = $this->getOPACConfig($organisationId);

        // Statistiques à afficher selon la configuration
        $stats = [];
        if ($config->show_statistics) {
            $stats = [
                'total_records' => Record::whereHas('activity.organisations', function($query) use ($config) {
                    $query->whereIn('organisations.id', $config->visible_organisations ?? []);
                })->count(),
                'recent_records' => Record::whereHas('activity.organisations', function($query) use ($config) {
                    $query->whereIn('organisations.id', $config->visible_organisations ?? []);
                })->where('created_at', '>=', now()->subDays(30))->count(),
                'total_activities' => Activity::whereHas('organisations', function($query) use ($config) {
                    $query->whereIn('organisations.id', $config->visible_organisations ?? []);
                })->count(),
            ];
        }

        // Documents récents selon la configuration
        $recentRecords = collect();
        if ($config->show_recent_records) {
            $recentRecords = Record::with(['activity', 'authors'])
                ->whereHas('activity.organisations', function($query) use ($config) {
                    $query->whereIn('organisations.id', $config->visible_organisations ?? []);
                })
                ->where('date_exact', '>=', now()->subMonths(6))
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get();
        }

        // Documents pour le carousel selon la configuration
        $carouselRecords = collect();
        if ($config->enable_carousel) {
            $carouselRecords = $this->getCarouselRecords($config);
        }

        return view('opac.index', compact('stats', 'recentRecords', 'carouselRecords', 'config'));
    }

    /**
     * Page de recherche avancée
     */
    public function search(Request $request)
    {
        $organisationId = $this->getCurrentOrganisationId($request);
        $config = $this->getOPACConfig($organisationId);
        $query = $request->get('q', '');
        $activity_id = $request->get('activity_id');
        $date_from = $request->get('date_from');
        $date_to = $request->get('date_to');
        $author = $request->get('author');

        $results = collect();
        $total = 0;

        if ($query || $activity_id || $date_from || $date_to || $author) {
            $queryBuilder = Record::with(['activity', 'authors'])
                ->whereHas('activity.organisations', function($q) use ($config) {
                    $q->whereIn('organisations.id', $config->visible_organisations ?? []);
                });

            // Recherche textuelle
            if ($query) {
                $queryBuilder->where(function($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('content', 'LIKE', "%{$query}%")
                      ->orWhere('biographical_history', 'LIKE', "%{$query}%")
                      ->orWhere('note', 'LIKE', "%{$query}%");
                });
            }

            // Filtrage par activité
            if ($activity_id) {
                $queryBuilder->where('activity_id', $activity_id);
            }

            // Filtrage par dates
            if ($date_from) {
                $queryBuilder->where('date_exact', '>=', $date_from);
            }
            if ($date_to) {
                $queryBuilder->where('date_exact', '<=', $date_to);
            }

            // Filtrage par auteur
            if ($author) {
                $queryBuilder->whereHas('authors', function($q) use ($author) {
                    $q->where('name', 'LIKE', "%{$author}%");
                });
            }

            $total = $queryBuilder->count();
            $results = $queryBuilder->orderBy('created_at', 'desc')->paginate(20);
        }

        // Activités disponibles pour le filtre
        $activities = Activity::whereHas('organisations', function($q) use ($config) {
            $q->whereIn('organisations.id', $config->visible_organisations ?? []);
        })->orderBy('name')->get();

        $breadcrumbs = [
            ['title' => __('Search'), 'url' => '']
        ];

        return view('opac.search', compact('results', 'total', 'query', 'activities', 'breadcrumbs', 'request'));
    }

    /**
     * Affichage d'un document spécifique
     */
    public function show(Request $request, $id)
    {
        $organisationId = $this->getCurrentOrganisationId($request);
        $config = $this->getOPACConfig($organisationId);

        $record = Record::with(['activity', 'authors', 'attachments', 'parent', 'children'])
            ->whereHas('activity.organisations', function($query) use ($config) {
                $query->whereIn('organisations.id', $config->visible_organisations ?? []);
            })
            ->findOrFail($id);

        $breadcrumbs = [
            ['title' => __('Search'), 'url' => route('opac.search')],
            ['title' => $record->name, 'url' => '']
        ];

        return view('opac.show', compact('record', 'breadcrumbs', 'config'));
    }

    /**
     * Navigation par catégories/activités
     */
    public function browse(Request $request)
    {
        $organisationId = $this->getCurrentOrganisationId($request);
        $config = $this->getOPACConfig($organisationId);
        $activity_id = $request->get('activity');

        // Activités racines disponibles
        $rootActivities = Activity::whereNull('parent_id')
            ->whereHas('organisations', function($q) use ($config) {
                $q->whereIn('organisations.id', $config->visible_organisations ?? []);
            })
            ->orderBy('name')
            ->get();

        $currentActivity = null;
        $records = collect();
        $subActivities = collect();

        if ($activity_id) {
            $currentActivity = Activity::whereHas('organisations', function($q) use ($config) {
                $q->whereIn('organisations.id', $config->visible_organisations ?? []);
            })->findOrFail($activity_id);

            // Sous-activités
            $subActivities = Activity::where('parent_id', $activity_id)
                ->whereHas('organisations', function($q) use ($config) {
                    $q->whereIn('organisations.id', $config->visible_organisations ?? []);
                })
                ->orderBy('name')
                ->get();

            // Documents de cette activité
            $records = Record::with(['authors'])
                ->where('activity_id', $activity_id)
                ->orderBy('name')
                ->paginate(20);
        }

        $breadcrumbs = [
            ['title' => __('Browse'), 'url' => route('opac.browse')]
        ];

        if ($currentActivity) {
            $breadcrumbs[] = ['title' => $currentActivity->name, 'url' => ''];
        }

        return view('opac.browse', compact('rootActivities', 'currentActivity', 'subActivities', 'records', 'breadcrumbs'));
    }

    /**
     * Page d'aide
     */
    public function help()
    {
        $breadcrumbs = [
            ['title' => __('Help'), 'url' => '']
        ];

        return view('opac.help', compact('breadcrumbs'));
    }

    /**
     * API de recherche pour l'autocomplétion
     */
    public function searchApi(Request $request)
    {
        $organisationId = $this->getCurrentOrganisationId($request);
        $config = $this->getOPACConfig($organisationId);
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = Record::whereHas('activity.organisations', function($q) use ($config) {
            $q->whereIn('organisations.id', $config->visible_organisations ?? []);
        })
        ->where('name', 'LIKE', "%{$query}%")
        ->limit(10)
        ->get(['id', 'name', 'date_exact']);

        return response()->json($results);
    }

    /**
     * Téléchargement de fichier (si autorisé)
     */
    public function downloadAttachment(Request $request, $recordId, $attachmentId)
    {
        $organisationId = $this->getCurrentOrganisationId($request);
        $config = $this->getOPACConfig($organisationId);

        if (!$config->allow_downloads) {
            abort(403, 'Downloads not allowed');
        }

        $record = Record::whereHas('activity.organisations', function($query) use ($config) {
            $query->whereIn('organisations.id', $config->visible_organisations ?? []);
        })->findOrFail($recordId);

        $attachment = $record->attachments()->findOrFail($attachmentId);

        // Vérifier les restrictions de format
        if (!empty($config->allowed_file_types)) {
            $extension = pathinfo($attachment->name, PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), $config->allowed_file_types)) {
                abort(403, 'File type not allowed for download');
            }
        }

        return response()->download(storage_path('app/' . $attachment->path), $attachment->name);
    }

    /**
     * Détermine l'organisation courante à partir de la requête
     */
    private function getCurrentOrganisationId(Request $request)
    {
        // 1. Vérifier s'il y a un paramètre organisation dans l'URL
        $orgId = $request->get('organisation_id');
        if ($orgId && Organisation::where('id', $orgId)->exists()) {
            return $orgId;
        }

        // 2. Vérifier la session
        $sessionOrgId = $request->session()->get('opac_organisation_id');
        if ($sessionOrgId && Organisation::where('id', $sessionOrgId)->exists()) {
            return $sessionOrgId;
        }

        // 3. Utiliser la première organisation par défaut
        $defaultOrg = Organisation::first();
        if ($defaultOrg) {
            $request->session()->put('opac_organisation_id', $defaultOrg->id);
            return $defaultOrg->id;
        }

        return null;
    }

    /**
     * Récupère la configuration OPAC pour une organisation
     */
    private function getOPACConfig($organisationId = null)
    {
        // Utiliser la première organisation si aucune n'est spécifiée
        if (!$organisationId) {
            $organisationId = Organisation::first()?->id;
        }

        if (!$organisationId) {
            return $this->getDefaultConfig();
        }

        // Récupérer toutes les configurations pour cette organisation
        $configurations = OpacConfiguration::getConfigurationsForOrganisation($organisationId);

        // Convertir en objet pour maintenir la compatibilité avec l'ancien code
        $config = new \stdClass();

        // Appliquer les configurations (qui sont déjà des valeurs, pas des objets)
        $config->visible_organisations = $configurations['visible_organisations'] ?? [$organisationId];
        $config->show_statistics = $configurations['show_statistics'] ?? true;
        $config->show_recent_records = $configurations['show_recent_records'] ?? true;
        $config->allow_downloads = $configurations['allow_downloads'] ?? false;
        $config->records_per_page = $configurations['records_per_page'] ?? 15;
        $config->allowed_file_types = $configurations['allowed_file_types'] ?? ['pdf', 'jpg', 'jpeg', 'png'];
        $config->show_full_record_details = $configurations['show_full_record_details'] ?? true;
        $config->show_attachments = $configurations['show_attachments'] ?? false;
        $config->opac_title = $configurations['opac_title'] ?? 'Catalogue en ligne';
        $config->opac_subtitle = 'Recherchez dans nos collections';
        $config->contact_email = $configurations['contact_email'] ?? '';
        $config->enable_help = true;
        $config->enable_advanced_search = true;
        $config->searchable_fields = ['code', 'name', 'biographical_history', 'note'];
        $config->min_search_length = 3;
        $config->theme_color = $configurations['primary_color'] ?? '#007bff';
        $config->primary_color = $configurations['primary_color'] ?? '#007bff';
        $config->secondary_color = $configurations['secondary_color'] ?? '#6c757d';
        $config->public_access = true;
        $config->require_registration = false;
        $config->logo_url = $configurations['logo_url'] ?? '';
        $config->footer_text = $configurations['footer_text'] ?? '';
        $config->help_url = $configurations['help_url'] ?? '';
        $config->theme = $configurations['theme'] ?? 'default';

        // Valeurs par défaut pour le carousel
        $config->enable_carousel = true;
        $config->carousel_items_count = 6;
        $config->carousel_auto_slide = true;
        $config->carousel_slide_interval = 5000;
        $config->carousel_selection_method = 'recent';
        $config->carousel_show_metadata = true;
        $config->carousel_title = 'Documents à découvrir';

        return $config;
    }

    /**
     * Configuration par défaut en cas d'absence de configuration
     */
    private function getDefaultConfig()
    {
        $config = new \stdClass();
        $config->visible_organisations = Organisation::pluck('id')->toArray();
        $config->show_statistics = true;
        $config->show_recent_records = true;
        $config->allow_downloads = false;
        $config->records_per_page = 15;
        $config->allowed_file_types = ['pdf', 'jpg', 'jpeg', 'png'];
        $config->show_full_record_details = true;
        $config->show_attachments = false;
        $config->opac_title = 'Catalogue en ligne';
        $config->opac_subtitle = 'Recherchez dans nos collections';
        $config->contact_email = '';
        $config->enable_help = true;
        $config->enable_advanced_search = true;
        $config->searchable_fields = ['code', 'name', 'biographical_history', 'note'];
        $config->min_search_length = 3;
        $config->theme_color = 'primary';
        $config->public_access = true;
        $config->require_registration = false;

        return $config;
    }

    /**
     * Récupérer les documents pour le carousel selon la configuration
     */
    private function getCarouselRecords($config)
    {
        $count = $config->carousel_items_count ?? 6;
        $selectionMethod = $config->carousel_selection_method ?? 'recent';

        $queryBuilder = Record::with(['activity', 'authors', 'attachments'])
            ->whereHas('activity.organisations', function($query) use ($config) {
                $query->whereIn('organisations.id', $config->visible_organisations ?? []);
            });

        switch ($selectionMethod) {
            case 'recent':
                return $queryBuilder->orderBy('created_at', 'desc')
                    ->limit($count)
                    ->get();

            case 'featured':
                // Documents mis en avant (on peut ajouter un champ 'is_featured' plus tard)
                return $queryBuilder->orderBy('updated_at', 'desc')
                    ->limit($count)
                    ->get();

            case 'popular':
                // Documents populaires (basé sur les vues si on implémente un système de comptage)
                return $queryBuilder->orderBy('created_at', 'desc')
                    ->limit($count)
                    ->get();

            case 'random':
                return $queryBuilder->inRandomOrder()
                    ->limit($count)
                    ->get();

            default:
                return $queryBuilder->orderBy('created_at', 'desc')
                    ->limit($count)
                    ->get();
        }
    }
}
