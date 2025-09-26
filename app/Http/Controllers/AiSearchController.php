<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use App\Models\Mail;
use App\Models\Communication;
use App\Models\Slip;
use Illuminate\Support\Facades\Auth;

class AiSearchController extends Controller
{
    public function chat(Request $request)
    {
        $message = $request->input('message');
        $searchType = $request->input('search_type', 'records');
        
        // Auto-détection du type de recherche basé sur le contenu
        $searchType = $this->detectSearchType($message, $searchType);

        if (empty($message)) {
            return response()->json([
                'success' => false,
                'error' => 'Message is required'
            ]);
        }

        // Analyser le message pour extraire les mots-clés de recherche
        $keywords = $this->extractKeywords($message);

        // Effectuer la recherche selon le type
        $results = $this->performSearch($keywords, $searchType);

        // Générer une réponse contextuelle
        $response = $this->generateResponse($message, $results, $searchType);

        return response()->json([
            'success' => true,
            'response' => $response['message'],
            'results' => $response['links']
        ]);
    }

    private function extractKeywords($message)
    {
        // Nettoyer le message et extraire les mots-clés pertinents
        $message = strtolower($message);

        // Retirer les mots de liaison français et anglais
        $stopWords = ['le', 'la', 'les', 'de', 'du', 'des', 'un', 'une', 'et', 'ou', 'mais', 'dans', 'sur', 'avec', 'pour', 'par', 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'with', 'for', 'by', 'trouve', 'cherche', 'recherche', 'veux', 'want', 'find', 'search', 'looking', 'me', 'moi', 'je', 'i', 'am', 'is', 'are'];

        $words = preg_split('/[\s,;.!?]+/', $message, -1, PREG_SPLIT_NO_EMPTY);
        $keywords = array_filter($words, function($word) use ($stopWords) {
            return !in_array($word, $stopWords) && strlen($word) > 2;
        });

        return array_values($keywords);
    }

    private function performSearch($keywords, $searchType)
    {
        $results = [];

        switch ($searchType) {
            case 'records':
                $results = $this->searchRecords($keywords);
                break;
            case 'mails':
                $results = $this->searchMails($keywords);
                break;
            case 'communications':
                $results = $this->searchCommunications($keywords);
                break;
            case 'slips':
                $results = $this->searchSlips($keywords);
                break;
        }

        return $results;
    }

    private function searchRecords($keywords)
    {
        $query = Record::query();

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('code', 'LIKE', "%{$keyword}%")
                      ->orWhere('content', 'LIKE', "%{$keyword}%")
                      ->orWhereHas('authors', function ($subQuery) use ($keyword) {
                          $subQuery->where('name', 'LIKE', "%{$keyword}%");
                      })
                      ->orWhereHas('activity', function ($subQuery) use ($keyword) {
                          $subQuery->where('name', 'LIKE', "%{$keyword}%");
                      });
                }
            });
        }

        $records = $query->limit(5)->get();

        return $records->map(function ($record) {
            return [
                'title' => $record->name ?: $record->code,
                'url' => route('records.show', $record->id),
                'icon' => 'bi-folder',
                'description' => $record->content ? substr($record->content, 0, 100) . '...' : ''
            ];
        })->toArray();
    }

    private function searchMails($keywords)
    {
        $query = Mail::query();

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('code', 'LIKE', "%{$keyword}%")
                      ->orWhere('description', 'LIKE', "%{$keyword}%");
                }
            });
        }

        $mails = $query->limit(5)->get();

        return $mails->map(function ($mail) {
            return [
                'title' => $mail->name ?: $mail->code,
                'url' => route('mails.show', $mail->id),
                'icon' => 'bi-envelope',
                'description' => $mail->description ? substr($mail->description, 0, 100) . '...' : ''
            ];
        })->toArray();
    }

    private function searchCommunications($keywords)
    {
        $query = Communication::query();

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('code', 'LIKE', "%{$keyword}%")
                      ->orWhere('content', 'LIKE', "%{$keyword}%");
                }
            });
        }

        $communications = $query->limit(5)->get();

        return $communications->map(function ($communication) {
            return [
                'title' => $communication->name ?: $communication->code,
                'url' => route('communications.show', $communication->id),
                'icon' => 'bi-chat-dots',
                'description' => $communication->content ? substr($communication->content, 0, 100) . '...' : ''
            ];
        })->toArray();
    }

    private function searchSlips($keywords)
    {
        $query = Slip::query();

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'LIKE', "%{$keyword}%")
                      ->orWhere('code', 'LIKE', "%{$keyword}%")
                      ->orWhere('description', 'LIKE', "%{$keyword}%")
                      ->orWhereHas('officer', function ($subQuery) use ($keyword) {
                          $subQuery->where('name', 'LIKE', "%{$keyword}%");
                      })
                      ->orWhereHas('user', function ($subQuery) use ($keyword) {
                          $subQuery->where('name', 'LIKE', "%{$keyword}%");
                      });
                }
            });
        }

        $slips = $query->limit(5)->get();

        return $slips->map(function ($slip) {
            return [
                'title' => $slip->name ?: $slip->code,
                'url' => route('slips.show', $slip->id),
                'icon' => 'bi-arrow-left-right',
                'description' => $slip->description ? substr($slip->description, 0, 100) . '...' : ''
            ];
        })->toArray();
    }

    private function generateResponse($message, $results, $searchType)
    {
        $count = count($results);
        $typeNames = [
            'records' => 'documents',
            'mails' => 'mails',
            'communications' => 'communications',
            'slips' => 'transferts'
        ];

        $typeName = $typeNames[$searchType] ?? 'éléments';

        if ($count === 0) {
            $response = "Je n'ai pas trouvé de {$typeName} correspondant à votre recherche. Essayez avec d'autres mots-clés ou vérifiez l'orthographe.";
        } elseif ($count === 1) {
            $response = "J'ai trouvé 1 {$typeName} qui correspond à votre recherche :";
        } else {
            $response = "J'ai trouvé {$count} {$typeName} qui correspondent à votre recherche :";
        }

        return [
            'message' => $response,
            'links' => $results
        ];
    }

    /**
     * Détecte automatiquement le type de recherche basé sur le contenu de la requête
     */
    private function detectSearchType(string $message, string $defaultType): string
    {
        $messageLower = strtolower($message);
        
        // Détection des mots-clés pour les auteurs
        $authorKeywords = ['auteur', 'auteurs', 'écrivain', 'rédacteur'];
        foreach ($authorKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                return 'authors';
            }
        }
        
        // Détection des mots-clés pour les mails
        $mailKeywords = ['mail', 'email', 'courrier', 'correspondance', 'message'];
        foreach ($mailKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                return 'mails';
            }
        }
        
        // Détection des mots-clés pour les communications
        $commKeywords = ['communication', 'échange', 'dialogue'];
        foreach ($commKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                return 'communications';
            }
        }
        
        // Détection des mots-clés pour les bordereaux/transferts
        $slipKeywords = ['bordereau', 'transfert', 'borderaux', 'slip', 'envoi'];
        foreach ($slipKeywords as $keyword) {
            if (strpos($messageLower, $keyword) !== false) {
                return 'slips';
            }
        }
        
        // Si aucun mot-clé spécifique trouvé, retourner le type par défaut
        return $defaultType;
    }
}