<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mail;
use App\Models\Batch;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\Author;
use App\Models\DocumentType;
use App\Models\MailContainer;
use Illuminate\Support\Facades\Auth;

class SearchMailController extends Controller
{
    public function form()
    {
        $data = [
            'priorities' => MailPriority::all(),
            'typologies' => MailTypology::all(),
            'authors' => Author::all(),
            'documentTypes' => DocumentType::all(),
            'containers' => MailContainer::all(),
        ];

        return view('search.mail.advanced', compact('data'));
    }

    public function advanced(Request $request)
    {
    $query = Mail::query()->excludeFactoryLike();

        // Organisation scoping — SuperAdmin sees all
        if (!Auth::user()->isSuperAdmin()) {
            $query->forOrganisation(Auth::user()->current_organisation_id);
        }

        $title = 'Recherche avancée';
        $filters = [];

        // Appliquer les filtres basés sur les attributs directs
        $directFilters = [
            'code' => ['label' => 'Code', 'type' => 'like'],
            'name' => ['label' => 'Objet', 'type' => 'like'],
            'mail_type' => ['label' => 'Type', 'type' => 'exact'],
            'date' => ['label' => 'Date', 'type' => 'date'],
        ];

        foreach ($directFilters as $field => $options) {
            if ($request->filled($field)) {
                if ($options['type'] === 'like') {
                    $query->where($field, 'like', '%' . $request->input($field) . '%');
                } elseif ($options['type'] === 'date') {
                    $query->whereDate($field, $request->input($field));
                } else {
                    $query->where($field, $request->input($field));
                }
                $filters[$options['label']] = $request->input($field);
            }
        }

        // Filtre facultatif: contenu des pièces jointes (content_text)
        if ($request->filled('attachment_content')) {
            $needle = $request->input('attachment_content');
            $query->whereHas('attachments', function ($q) use ($needle) {
                $q->where('attachments.content_text', 'like', '%' . $needle . '%');
            });
            $filters['Pièces jointes'] = 'contient « ' . $needle . ' »';
        }

        // Filtres basés sur les relations
        $relationFilters = [
            'priority_id' => ['relation' => 'priority', 'model' => MailPriority::class, 'label' => 'Priorité'],
            'typology_id' => ['relation' => 'typology', 'model' => MailTypology::class, 'label' => 'Typologie'],
            'container_id' => ['relation' => 'containers', 'model' => MailContainer::class, 'label' => 'Conteneur', 'foreign_key' => 'mail_containers.id'],
            'document_type_id' => ['relation' => 'documents', 'model' => DocumentType::class, 'label' => 'Type de document', 'foreign_key' => 'document_type'],
        ];

        foreach ($relationFilters as $field => $options) {
            if ($request->filled($field)) {
                $query->whereHas($options['relation'], function ($q) use ($request, $field, $options) {
                    $foreignKey = $options['foreign_key'] ?? 'id';
                    $q->where($foreignKey, $request->input($field));
                });

                // Récupérer le nom pour le titre
                if (isset($options['model'])) {
                    $model = $options['model']::find($request->input($field));
                    if ($model) {
                        $filters[$options['label']] = $model->name;
                    }
                }
            }
        }

        // Recherche par auteurs (cas spécial avec explode)
        if ($request->filled('author_ids')) {
            $authorIds = explode(',', $request->author_ids);
            $query->whereHas('authors', function ($q) use ($authorIds) {
                $q->whereIn('authors.id', $authorIds);
            });
            $filters['Auteurs'] = 'sélectionnés';
        }

        // Utiliser les scopes du modèle si besoin
        if ($request->filled('archived')) {
            if ($request->boolean('archived')) {
                $query->archived();
                $filters['Statut'] = 'Archivé';
            } else {
                $query->notArchived();
                $filters['Statut'] = 'Non archivé';
            }
        }

        // Gérer les recherches par catégorie
        if ($request->filled('categ')) {
            $category = $request->categ;
            switch ($category) {
                case "dates":
                    $this->handleDateSearch($query, $request, $filters);
                    break;
                case "batch":
                    $this->handleBatchSearch($query, $request, $filters);
                    break;
                case "container":
                    $this->handleContainerSearch($query, $request, $filters);
                    break;
                default:
                    break;
            }
        }

        // Construction du titre avec les filtres
        foreach ($filters as $key => $value) {
            $title .= " - {$key}: {$value}";
        }

        // Relations à précharger (eager loading)
        $with = [
            'archives', 'containers', 'attachments',
            'recipientOrganisation', 'recipient',
            'senderOrganisation', 'sender', 'action',
            'typology', 'priority'
        ];

        // Appliquer la pagination avec les relations préchargées
        $mails = $query->with($with)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Données pour les listes déroulantes
        $data = [
            'priorities' => MailPriority::all(),
            'typologies' => MailTypology::all(),
            'authors' => Author::all(),
            'documentTypes' => DocumentType::all(),
            'containers' => MailContainer::all(),
        ];

        $type = in_array($request->type, ['send', 'received']) ? $request->type : 'received';

        return view("mails.index", compact('mails', 'title', 'data', 'type'));
    }



    public function mailTypologies(Request $request)
    {
        $typologies = MailTypology::paginate(20);
        return view('search.mail.typology', compact('typologies'));
    }

    /**
     * Gère la recherche par date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @param array $filters tableau associatif pour les filtres appliqués
     * @return void
     */
    private function handleDateSearch($query, $request, &$filters)
    {
        if ($request->filled('date_exact')) {
            $query->whereDate('date', $request->date_exact);
            $filters['Date exacte'] = $request->date_exact;
        } elseif ($request->filled('date_start') && $request->filled('date_end')) {
            $query->whereBetween('date', [$request->date_start, $request->date_end]);
            $filters['Période'] = "du {$request->date_start} au {$request->date_end}";
        } elseif ($request->filled('date_start')) {
            $query->where('date', '>=', $request->date_start);
            $filters['À partir du'] = $request->date_start;
        }
    }

    /**
     * Gère la recherche par parapheur
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @param array $filters tableau associatif pour les filtres appliqués
     * @return void
     */
    private function handleBatchSearch($query, $request, &$filters)
    {
        $batchId = $request->batch_id ?? $request->id;
        if ($batchId) {
            $batch = Batch::find($batchId);
            if ($batch) {
                $query->whereHas('batches', function ($q) use ($batchId) {
                    $q->where('batch_id', $batchId);
                });
                $filters['Parapheur'] = "{$batch->code} - {$batch->name}";
            }
        }
    }

    /**
     * Gère la recherche par conteneur
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Http\Request $request
     * @param array $filters tableau associatif pour les filtres appliqués
     * @return void
     */
    private function handleContainerSearch($query, $request, &$filters)
    {
        if ($request->filled('container_id')) {
            $containerId = $request->container_id;
            $container = MailContainer::find($containerId);

            if ($container) {
                $query->whereHas('containers', function ($q) use ($containerId) {
                    $q->where('mail_containers.id', $containerId);
                });
                $filters['Conteneur'] = $container->name;
            }
        }
    }

    public function date()
    {
        return view('search.mail.dateSearch');
    }

    public function chart(Request $request)
    {
        $ids = json_decode($request->query('ids', '[]'), true);
        return view('search.chart', compact('ids'));
    }
}
