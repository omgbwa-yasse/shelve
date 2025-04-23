<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mail;
use App\Models\Record;
use App\Models\Batch;
use App\Models\MailPriority;
use App\Models\MailTypology;
use App\Models\MailType;
use App\Models\Author;
use App\Models\BatchMail;
use App\Models\MailArchiving;
use App\Models\DocumentType;
use App\Models\MailContainer;
use App\Models\RecordStatus;
use App\Models\Term;
use App\Models\Slip;
use App\Models\SlipRecord;

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
        $query = Mail::query();
        $title = 'Recherche avancée';

        // Recherche par code
        if ($request->filled('code')) {
            $query->where('code', 'like', '%' . $request->code . '%');
            $title .= ' - Code: ' . $request->code;
        }

        // Recherche par nom/objet
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
            $title .= ' - Objet: ' . $request->name;
        }

        // Recherche par auteurs
        if ($request->filled('author_ids')) {
            $authorIds = explode(',', $request->author_ids);
            $query->whereHas('authors', function ($q) use ($authorIds) {
                $q->whereIn('authors.id', $authorIds);
            });
            $title .= ' - Auteurs sélectionnés';
        }

        // Recherche par date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
            $title .= ' - Date: ' . $request->date;
        }

        // Recherche par priorité
        if ($request->filled('mail_priority_id')) {
            $query->where('mail_priority_id', $request->mail_priority_id);
            $priority = MailPriority::find($request->mail_priority_id);
            if ($priority) {
                $title .= ' - Priorité: ' . $priority->name;
            }
        }

        // Recherche par type
        if ($request->filled('mail_type_id')) {
            $query->where('mail_type_id', $request->mail_type_id);
        }

        // Recherche par typologie
        if ($request->filled('mail_typology_id')) {
            $query->where('mail_typology_id', $request->mail_typology_id);
            $typology = MailTypology::find($request->mail_typology_id);
            if ($typology) {
                $title .= ' - Typologie: ' . $typology->name;
            }
        }

        // Recherche par type de document
        if ($request->filled('document_type_id')) {
            $query->whereHas('documents', function ($q) use ($request) {
                $q->where('document_type_id', $request->document_type_id);
            });
            $documentType = DocumentType::find($request->document_type_id);
            if ($documentType) {
                $title .= ' - Type de document: ' . $documentType->name;
            }
        }

        // Recherche par conteneur
        if ($request->filled('container_id')) {
            $query->whereHas('containers', function($q) use ($request) {
                $q->where('mail_containers.id', $request->container_id);
            });
            $container = MailContainer::find($request->container_id);
            if ($container) {
                $title .= ' - Conteneur: ' . $container->name;
            }
        }

        // Gérer la recherche par catégorie existante si nécessaire
        if ($request->filled('categ')) {
            switch($request->categ) {
                case "dates":
                    $this->handleDateSearch($query, $request, $title);
                    break;
                case "batch":
                    $this->handleBatchSearch($query, $request, $title);
                    break;
                case "container":
                    $this->handleContainerSearch($query, $request, $title);
                    break;
                // Autres cas existants si nécessaire...
            }
        }

        // Appliquer la pagination
        $mails = $query->with('archives','containers',
                'attachments', 'recipientOrganisation','recipient',
                'senderOrganisation', 'sender','action',
                'typology', 'priority')
            ->orderBy('created_at', 'desc')->paginate(10);



        // Récupérer les données pour les select
        $data = [
            'priorities' => MailPriority::all(),
            'typologies' => MailTypology::all(),
            'authors' => Author::all(),
            'documentTypes' => DocumentType::all(),
            'containers' => MailContainer::all(),
        ];

        if($request->type == 'send'){
            return view('mails.send.index', compact('mails', 'title', 'data'));
        }elseif($request->type == 'received'){
            return view('mails.received.index', compact('mails', 'title', 'data'));
        }

    }


    private function handleDateSearch($query, $request, &$title)
    {
        if ($request->filled('date_exact')) {
            $query->whereDate('date', $request->date_exact);
            $title .= " - Date exacte: " . $request->date_exact;
        } elseif ($request->filled('date_start') && $request->filled('date_end')) {
            $query->whereBetween('date', [$request->date_start, $request->date_end]);
            $title .= " - Période: du " . $request->date_start . " au " . $request->date_end;
        } elseif ($request->filled('date_start')) {
            $query->where('date', '>=', $request->date_start);
            $title .= " - À partir du: " . $request->date_start;
        }
    }

    private function handleBatchSearch($query, $request, &$title)
    {
        if ($request->filled('id')) {
            $batch = Batch::find($request->id);
            if ($batch) {
                $query->whereHas('batches', function ($q) use ($request) {
                    $q->where('batch_id', $request->id);
                });
                $title .= " - Parapheur: " . $batch->code . " - " . $batch->name;
            }
        }
    }

    private function handleContainerSearch($query, $request, &$title)
    {
        if ($request->filled('id')) {
            $container = MailContainer::find($request->id);
            if ($container) {
                $query->whereHas('mails', function ($q) use ($request) {
                    $q->where('container_id', $request->id);
                });
                $title .= " - Conteneur: " . $container->name;
            }
        }
    }



    public function date()
    {
        return view('search.mail.dateSearch');
    }


    public function chart(Request $request)
    {
        $ids = json_decode($request->query('ids', '[]'));
        return view('search.chart', compact('ids'));
    }


}
