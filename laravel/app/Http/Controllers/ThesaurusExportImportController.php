<?php

namespace App\Http\Controllers;

use App\Models\ThesaurusScheme;
use App\Models\ThesaurusConcept;
use App\Models\ThesaurusLabel;
use App\Models\ThesaurusConceptNote;
use App\Models\ThesaurusConceptRelation;
use App\Models\ThesaurusConceptProperty;
use App\Models\ThesaurusOrganization;
use App\Models\ThesaurusNamespace;
use App\Models\ThesaurusCollection;
use App\Models\ThesaurusCollectionLabel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ThesaurusExportImportController extends Controller
{
    /**
     * Affiche la page d'accueil pour l'import/export du thésaurus
     */
    public function index()
    {
        return view('thesaurus.export_import.ajax_index');
    }

    /**
     * Export AJAX - gère tous les formats d'export via AJAX
     */
    public function exportAjax(Request $request)
    {
        try {
            $format = $request->input('format');

            switch ($format) {
                case 'skos-rdf':
                    return $this->exportSkosAjax($request);
                case 'csv':
                    return $this->exportCsvAjax($request);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Format d\'export non supporté'
                    ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export AJAX: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export SKOS via AJAX
     */
    private function exportSkosAjax(Request $request)
    {
        $conceptsQuery = $this->buildSearchQuery($request);
        $concepts = $conceptsQuery->get();

        $xml = $this->generateSkosXml($concepts);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="thesaurus-export-' . date('Y-m-d') . '.rdf"');
    }

    /**
     * Export CSV via AJAX
     */
    private function exportCsvAjax(Request $request)
    {
        $conceptsQuery = $this->buildSearchQuery($request);
        $concepts = $conceptsQuery->get();

        $csv = $this->generateCsvContent($concepts);

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="thesaurus-export-' . date('Y-m-d') . '.csv"');
    }

    /**
     * Export RDF via AJAX - cette méthode est maintenue pour compatibilité
     * mais redirige vers exportSkosAjax car SKOS est écrit en RDF
     * @deprecated
     */
    private function exportRdfAjax(Request $request)
    {
        // Redirection vers la méthode SKOS car SKOS est écrit en RDF
        return $this->exportSkosAjax($request);
    }
    private function exportRdfAjax(Request $request)
    {
        // Redirection vers la méthode SKOS car SKOS est écrit en RDF
        return $this->exportSkosAjax($request);
    }
            ->header('Content-Disposition', 'attachment; filename="thesaurus-export-' . date('Y-m-d') . '.rdf"');
    }

    /**
     * Affiche le formulaire pour l'import de fichiers CSV
     */
    public function showImportCsvForm()
    {
        return view('thesaurus.import.csv');
    }

    /**
     * Importe un thésaurus au format CSV
     */
    public function importCsv(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'mode' => 'required|in:add,update,merge,replace'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $file = $request->file('file');
        $mode = $request->input('mode');

        $result = $this->processCsvImport($file, $mode);

        if ($result['success']) {
            return redirect()->route('thesaurus.export-import')->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    /**
     * Affiche le formulaire pour l'import de fichiers RDF
     */
    public function showImportRdfForm()
    {
        return view('thesaurus.import.rdf');
    }

    /**
     * Importe un thésaurus au format RDF
     */
    public function importRdf(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xml,rdf|max:10240',
                'mode' => 'required|in:add,update,merge,replace'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Vérifier la connexion à la base de données
            try {
                DB::connection()->getPdo();
                Log::info('Connexion à la base de données établie: ' . DB::connection()->getDatabaseName());
            } catch (\Exception $e) {
                Log::error('Erreur de connexion à la base de données: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Erreur de connexion à la base de données. Vérifiez le fichier .env.');
            }

            // Charger le fichier RDF
            $file = $request->file('file');
            $mode = $request->input('mode');

            // Log le début de l'importation
            Log::info('Début de l\'import RDF', [
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'mode' => $mode
            ]);

            if (strtolower($file->getClientOriginalExtension()) === 'skos') {
                $result = $this->processSkosImport($file, $mode);
            } else {
                $result = $this->processRdfImport($file, $mode);
            }

            if ($result['success']) {
                Log::info('Import RDF réussi', $result);
                return redirect()->route('thesaurus.export-import')->with('success', $result['message']);
            } else {
                Log::warning('Échec de l\'import RDF', $result);
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import RDF: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Erreur lors de l\'import RDF: ' . $e->getMessage());
        }
    }

    /**
     * Génère le contenu XML SKOS
     */
    private function generateSkosXml($concepts)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dc="http://purl.org/dc/elements/1.1/"></rdf:RDF>');

        // Récupérer les schémas concernés par ces concepts
        $schemeIds = $concepts->pluck('scheme_id')->unique();
        $schemes = ThesaurusScheme::whereIn('id', $schemeIds)->get();

        // Ajouter les schémas
        foreach ($schemes as $scheme) {
            $schemeNode = $xml->addChild('skos:ConceptScheme');
            $schemeNode->addAttribute('rdf:about', $scheme->uri);
            $schemeNode->addChild('dc:title', htmlspecialchars($scheme->title));

            if ($scheme->description) {
                $schemeNode->addChild('dc:description', htmlspecialchars($scheme->description));
            }

            // Ajouter les collections appartenant à ce schéma
            $this->addCollectionsToXml($xml, $scheme);
        }

        foreach ($concepts as $concept) {
            $conceptNode = $xml->addChild('skos:Concept');
            $conceptNode->addAttribute('rdf:about', $concept->uri);

            // Ajouter les labels préférés
            $prefLabels = $concept->labels()->where('type', 'prefLabel')->get();
            foreach ($prefLabels as $prefLabel) {
                $labelNode = $conceptNode->addChild('skos:prefLabel', htmlspecialchars($prefLabel->literal_form));
                $labelNode->addAttribute('xml:lang', $prefLabel->language);
            }

            // Ajouter les labels alternatifs
            $altLabels = $concept->labels()->where('type', 'altLabel')->get();
            foreach ($altLabels as $altLabel) {
                $labelNode = $conceptNode->addChild('skos:altLabel', htmlspecialchars($altLabel->literal_form));
                $labelNode->addAttribute('xml:lang', $altLabel->language);
            }

            // Ajouter les labels cachés
            $hiddenLabels = $concept->labels()->where('type', 'hiddenLabel')->get();
            foreach ($hiddenLabels as $hiddenLabel) {
                $labelNode = $conceptNode->addChild('skos:hiddenLabel', htmlspecialchars($hiddenLabel->literal_form));
                $labelNode->addAttribute('xml:lang', $hiddenLabel->language);
            }

            // Ajouter les notes
            $notes = $concept->notes;
            foreach ($notes as $note) {
                $noteType = $note->type ?? $note->note_type; // Compatible avec les deux structures
                $noteText = $note->note ?? $note->note_text; // Compatible avec les deux structures

                switch ($noteType) {
                    case 'definition':
                        $noteNode = $conceptNode->addChild('skos:definition', htmlspecialchars($noteText));
                        break;
                    case 'scopeNote':
                        $noteNode = $conceptNode->addChild('skos:scopeNote', htmlspecialchars($noteText));
                        break;
                    case 'historyNote':
                        $noteNode = $conceptNode->addChild('skos:historyNote', htmlspecialchars($noteText));
                        break;
                    case 'editorialNote':
                        $noteNode = $conceptNode->addChild('skos:editorialNote', htmlspecialchars($noteText));
                        break;
                    case 'example':
                        $noteNode = $conceptNode->addChild('skos:example', htmlspecialchars($noteText));
                        break;
                }
                $noteNode->addAttribute('xml:lang', $note->language);
            }

            // Ajouter notation si présent
            if ($concept->notation) {
                $conceptNode->addChild('skos:notation', htmlspecialchars($concept->notation));
            }

            // Ajouter les relations
            $this->addConceptRelationsToXml($conceptNode, $concept);

            // Ajouter les métadonnées
            $conceptNode->addChild('dc:created', $concept->created_at->toIso8601String());
            $conceptNode->addChild('dc:modified', $concept->updated_at->toIso8601String());
        }

        return $xml->asXML();
    }

    /**
     * Ajoute les collections d'un schéma au XML SKOS
     */
    private function addCollectionsToXml($xml, $scheme)
    {
        // Récupérer toutes les collections de ce schéma
        $collections = $scheme->collections;

        foreach ($collections as $collection) {
            // Créer le nœud de collection
            if ($collection->ordered) {
                $collectionNode = $xml->addChild('skos:OrderedCollection');
            } else {
                $collectionNode = $xml->addChild('skos:Collection');
            }

            $collectionNode->addAttribute('rdf:about', $collection->uri);

            // Ajouter les labels
            foreach ($collection->labels as $label) {
                switch ($label->label_type) {
                    case 'prefLabel':
                        $labelNode = $collectionNode->addChild('skos:prefLabel', htmlspecialchars($label->label));
                        break;
                    case 'altLabel':
                        $labelNode = $collectionNode->addChild('skos:altLabel', htmlspecialchars($label->label));
                        break;
                    case 'hiddenLabel':
                        $labelNode = $collectionNode->addChild('skos:hiddenLabel', htmlspecialchars($label->label));
                        break;
                }
                $labelNode->addAttribute('xml:lang', $label->language);
            }

            // Ajouter les membres (concepts)
            foreach ($collection->members as $member) {
                if ($collection->ordered) {
                    // Pour les collections ordonnées, utiliser skos:memberList
                    $listNode = $collectionNode->addChild('skos:memberList');
                    $listNode->addAttribute('rdf:parseType', 'Collection');

                    // Ajouter les membres dans l'ordre
                    foreach ($collection->members()->orderBy('pivot_position')->get() as $orderedMember) {
                        $memberNode = $listNode->addChild('rdf:Description');
                        $memberNode->addAttribute('rdf:about', $orderedMember->uri);
                    }
                } else {
                    // Pour les collections normales, utiliser skos:member
                    $memberNode = $collectionNode->addChild('skos:member');
                    $memberNode->addAttribute('rdf:resource', $member->uri);
                }
            }

            // Ajouter les sous-collections
            foreach ($collection->childCollections as $childCollection) {
                $memberNode = $collectionNode->addChild('skos:member');
                $memberNode->addAttribute('rdf:resource', $childCollection->uri);
            }
        }
    }

    /**
     * Ajoute les relations d'un concept au nœud XML
     */
    private function addConceptRelationsToXml($conceptNode, $concept)
    {
        // Relations hiérarchiques - broader
        $broaderRelations = $concept->sourceRelations()->where('relation_type', 'broader')->get();
        foreach ($broaderRelations as $relation) {
            $relNode = $conceptNode->addChild('skos:broader');
            $relNode->addAttribute('rdf:resource', ThesaurusConcept::find($relation->target_concept_id)->uri);
        }

        // Relations hiérarchiques - narrower
        $narrowerRelations = $concept->sourceRelations()->where('relation_type', 'narrower')->get();
        foreach ($narrowerRelations as $relation) {
            $relNode = $conceptNode->addChild('skos:narrower');
            $relNode->addAttribute('rdf:resource', ThesaurusConcept::find($relation->target_concept_id)->uri);
        }

        // Relations associatives
        $relatedRelations = $concept->sourceRelations()->where('relation_type', 'related')->get();
        foreach ($relatedRelations as $relation) {
            $relNode = $conceptNode->addChild('skos:related');
            $relNode->addAttribute('rdf:resource', ThesaurusConcept::find($relation->target_concept_id)->uri);
        }

        // Relations de mapping
        $mappingTypes = ['exactMatch', 'closeMatch', 'broadMatch', 'narrowMatch', 'relatedMatch'];
        foreach ($mappingTypes as $type) {
            $mappingRelations = $concept->sourceRelations()->where('relation_type', $type)->get();
            foreach ($mappingRelations as $relation) {
                $relNode = $conceptNode->addChild('skos:' . $type);
                $relNode->addAttribute('rdf:resource', ThesaurusConcept::find($relation->target_concept_id)->uri);
            }
        }
    }

    /**
     * Génère le contenu CSV
     */
    private function generateCsvContent($concepts)
    {
        $csv = "ID,URI,Notation,Preferred Label,Language,Scope Note,Alternative Labels\n";

        foreach ($concepts as $concept) {
            // Récupérer le label préféré
            $prefLabel = $concept->labels()->where('type', 'prefLabel')->first();
            if (!$prefLabel) continue;

            // Récupérer les labels alternatifs
            $altLabels = $concept->labels()->where('type', 'altLabel')->get();
            $altLabelString = $altLabels->pluck('literal_form')->implode(';');

            // Récupérer la note de portée (scope note)
            $scopeNote = $concept->notes()->where('type', 'scopeNote')->orWhere('note_type', 'scopeNote')->first();
            $scopeNoteText = '';
            if ($scopeNote) {
                $scopeNoteText = $scopeNote->note ?? $scopeNote->note_text ?? '';
            }

            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $concept->id,
                str_replace('"', '""', $concept->uri),
                str_replace('"', '""', $concept->notation ?? ''),
                str_replace('"', '""', $prefLabel->literal_form),
                $prefLabel->language,
                str_replace('"', '""', $scopeNoteText),
                str_replace('"', '""', $altLabelString)
            );
        }

        return $csv;
    }

    /**
     * Génère le contenu RDF XML - Cette méthode est maintenue pour compatibilité
     * mais elle redirige vers generateSkosXml car SKOS est écrit en RDF
     * @deprecated Utilisez plutôt generateSkosXml()
     */
    private function generateRdfXml($concepts)
    {
        // Redirection vers la méthode SKOS car SKOS est écrit en RDF
        return $this->generateSkosXml($concepts);

            // Ajouter les propriétés
            $properties = $concept->properties;
            foreach ($properties as $property) {
                $propName = 'dc:' . $property->property_name;
                $propNode = $description->addChild($propName, htmlspecialchars($property->property_value));
                if ($property->language) {
                    $propNode->addAttribute('xml:lang', $property->language);
                }
            }
        }

        return $xml->asXML();
    }

    /**
     * Traite l'import CSV
     */
    private function processCsvImport($file, $mode)
    {
        try {
            // Obtenir le contenu du fichier
            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);
            $header = str_getcsv(array_shift($lines));

            // Compteurs pour le rapport d'import
            $imported = 0;
            $updated = 0;
            $errors = [];

            // Créer un schéma de thésaurus par défaut si nécessaire
            $scheme = ThesaurusScheme::firstOrCreate(
                ['uri' => url('/thesaurus/default')],
                [
                    'title' => 'Thésaurus par défaut',
                    'description' => 'Schéma de thésaurus créé lors de l\'import CSV',
                    'language' => 'fr'
                ]
            );

            // Effacer les concepts existants si mode remplacement
            if ($mode === 'replace') {
                // Supprimer tous les concepts liés à ce schéma
                // Les relations, labels et notes seront supprimés en cascade
                ThesaurusConcept::where('scheme_id', $scheme->id)->delete();
            }

            DB::beginTransaction();

            try {
                foreach ($lines as $lineNumber => $line) {
                    if (!trim($line)) continue;

                    $data = array_combine($header, str_getcsv($line));

                    // Vérifier que le label préféré est présent
                    if (empty($data['Preferred Label'])) {
                        $errors[] = "Ligne " . ($lineNumber + 2) . ": Label préféré requis";
                        continue;
                    }

                    $uri = isset($data['URI']) ? $data['URI'] : url('/concept/' . Str::slug($data['Preferred Label']));
                    $language = isset($data['Language']) ? $this->standardizeLanguageCode($data['Language']) : 'fr';

                    // Chercher si le concept existe déjà (par URI)
                    $existingConcept = ThesaurusConcept::where('uri', $uri)->first();

                    if ($existingConcept) {
                        // Mettre à jour le concept existant
                        if (in_array($mode, ['update', 'merge'])) {
                            // Mettre à jour la notation si fournie
                            if (isset($data['Notation'])) {
                                $existingConcept->notation = $data['Notation'];
                                $existingConcept->save();
                            }

                            // Mettre à jour le label préféré
                            $prefLabel = $existingConcept->labels()->where('type', 'prefLabel')->first();
                            if ($prefLabel) {
                                $prefLabel->literal_form = $data['Preferred Label'];
                                $prefLabel->language = $language;
                                $prefLabel->save();
                            } else {
                                ThesaurusLabel::create([
                                    'concept_id' => $existingConcept->id,
                                    'type' => 'prefLabel',
                                    'literal_form' => $data['Preferred Label'],
                                    'language' => $language
                                ]);
                            }

                            // Mettre à jour ou créer la note de portée (scope note)
                            if (isset($data['Scope Note']) && $data['Scope Note']) {
                                $scopeNote = $existingConcept->notes()->where('type', 'scopeNote')->orWhere('note_type', 'scopeNote')->first();
                                if ($scopeNote) {
                                    // Mise à jour compatible avec les deux structures
                                    if (isset($scopeNote->note)) {
                                        $scopeNote->note = $data['Scope Note'];
                                    } else {
                                        $scopeNote->note_text = $data['Scope Note'];
                                    }
                                    $scopeNote->save();
                                } else {
                                    // Création avec les champs corrects basés sur le modèle
                                    ThesaurusConceptNote::create([
                                        'concept_id' => $existingConcept->id,
                                        'type' => 'scopeNote',
                                        'note_type' => 'scopeNote', // Pour compatibilité
                                        'note' => $data['Scope Note'],
                                        'note_text' => $data['Scope Note'], // Pour compatibilité
                                        'language' => $language
                                    ]);
                                }
                            }

                            // Traiter les labels alternatifs (non descripteurs)
                            if (isset($data['Alternative Labels']) && $data['Alternative Labels']) {
                                $altLabels = explode(';', $data['Alternative Labels']);
                                foreach ($altLabels as $altLabel) {
                                    if (trim($altLabel)) {
                                        ThesaurusLabel::firstOrCreate([
                                            'concept_id' => $existingConcept->id,
                                            'type' => 'altLabel',
                                            'literal_form' => trim($altLabel),
                                            'language' => $language
                                        ]);
                                    }
                                }
                            }

                            $updated++;
                        }
                    } else {
                        // Créer un nouveau concept
                        if (in_array($mode, ['add', 'merge', 'replace'])) {
                            $newConcept = ThesaurusConcept::create([
                                'scheme_id' => $scheme->id,
                                'uri' => $uri,
                                'notation' => $data['Notation'] ?? null,
                                'status' => 1
                            ]);

                            // Ajouter le label préféré
                            ThesaurusLabel::create([
                                'concept_id' => $newConcept->id,
                                'type' => 'prefLabel',
                                'literal_form' => $data['Preferred Label'],
                                'language' => $language
                            ]);

                            // Ajouter la note de portée si présente
                            if (isset($data['Scope Note']) && $data['Scope Note']) {
                                ThesaurusConceptNote::create([
                                    'concept_id' => $newConcept->id,
                                    'type' => 'scopeNote',
                                    'note_type' => 'scopeNote', // Pour compatibilité
                                    'note' => $data['Scope Note'],
                                    'note_text' => $data['Scope Note'], // Pour compatibilité
                                    'language' => $language
                                ]);
                            }

                            // Traiter les labels alternatifs (non descripteurs)
                            if (isset($data['Alternative Labels']) && $data['Alternative Labels']) {
                                $altLabels = explode(';', $data['Alternative Labels']);
                                foreach ($altLabels as $altLabel) {
                                    if (trim($altLabel)) {
                                        ThesaurusLabel::create([
                                            'concept_id' => $newConcept->id,
                                            'type' => 'altLabel',
                                            'literal_form' => trim($altLabel),
                                            'language' => $language
                                        ]);
                                    }
                                }
                            }

                            $imported++;
                        }
                    }
                }

                DB::commit();

                $message = "Import terminé: {$imported} concepts ajoutés, {$updated} mis à jour";
                if (!empty($errors)) {
                    $message .= ". " . count($errors) . " erreurs détectées.";
                }

                return [
                    'success' => true,
                    'message' => $message,
                    'stats' => "{$imported} ajoutés, {$updated} mis à jour"
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de l\'import CSV: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return ['success' => false, 'message' => 'Erreur lors de l\'import CSV: ' . $e->getMessage()];
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import CSV: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Erreur lors de l\'import CSV: ' . $e->getMessage()];
        }
    }

    /**
     * Traite l'import SKOS
     */
    private function processSkosImport($file, $mode)
    {
        try {
            $content = file_get_contents($file->getPathname());
            $xml = simplexml_load_string($content);

            if (!$xml) {
                return ['success' => false, 'message' => 'Fichier SKOS invalide'];
            }

            // Enregistrer les namespaces pour XPath
            $namespaces = $xml->getNamespaces(true);
            foreach ($namespaces as $prefix => $namespace) {
                if (!empty($prefix)) {
                    $xml->registerXPathNamespace($prefix, $namespace);
                }
            }

            // Créer un schéma de thésaurus par défaut si nécessaire
            $schemeNodes = $xml->xpath('//skos:ConceptScheme');
            $scheme = null;

            if (!empty($schemeNodes)) {
                $schemeNode = $schemeNodes[0];
                $schemeUri = (string) $schemeNode->attributes('rdf', true)->about;
                $schemeTitle = (string) $schemeNode->xpath('dc:title')[0] ?? 'Thésaurus importé';
                $schemeDescription = (string) $schemeNode->xpath('dc:description')[0] ?? null;

                $scheme = ThesaurusScheme::firstOrCreate(
                    ['uri' => $schemeUri],
                    [
                        'title' => $schemeTitle,
                        'description' => $schemeDescription,
                        'language' => 'fr'
                    ]
                );
            } else {
                // Si pas de schéma défini dans le SKOS, créer un par défaut
                $scheme = ThesaurusScheme::firstOrCreate(
                    ['uri' => url('/thesaurus/skos-import')],
                    [
                        'title' => 'Thésaurus SKOS importé',
                        'description' => 'Schéma de thésaurus créé lors de l\'import SKOS',
                        'language' => 'fr'
                    ]
                );
            }

            // Obtenir tous les concepts SKOS
            $conceptNodes = $xml->xpath('//skos:Concept');
            $imported = 0;
            $updated = 0;
            $relationshipsToProcess = [];

            // Si mode remplacement, effacer tous les concepts existants liés à ce schéma
            if ($mode === 'replace') {
                ThesaurusConcept::where('scheme_id', $scheme->id)->delete();
            }

            DB::beginTransaction();

            try {
                // Traitement des collections SKOS
            $collectionNodes = $xml->xpath('//skos:Collection|//skos:OrderedCollection');
            foreach ($collectionNodes as $collectionNode) {
                $collectionUri = (string) $collectionNode->attributes('rdf', true)->about;
                if (empty($collectionUri)) continue;

                // Déterminer si c'est une collection ordonnée
                $isOrdered = $collectionNode->getName() === 'OrderedCollection';

                // Récupérer le premier prefLabel
                $prefLabels = $collectionNode->xpath('skos:prefLabel');
                if (empty($prefLabels)) continue;

                $preferredLabel = (string) $prefLabels[0];
                $language = (string) $prefLabels[0]->attributes('xml', true)->lang ?? 'fr';
                $language = $this->standardizeLanguageCode($language);

                // Créer ou mettre à jour la collection
                $collection = ThesaurusCollection::updateOrCreate(
                    ['uri' => $collectionUri],
                    [
                        'scheme_id' => $scheme->id,
                        'ordered' => $isOrdered
                    ]
                );

                // Ajouter les labels
                $this->processCollectionLabels($collection, $collectionNode);

                // Traiter les collections imbriquées (parent-enfant)
                $this->processNestedCollections($collection, $collectionNode);

                // Traiter les membres (à stocker pour traitement ultérieur)
                $memberNodes = $collectionNode->xpath('skos:member');
                $position = 0;
                foreach ($memberNodes as $memberNode) {
                    $memberUri = (string) $memberNode->attributes('rdf', true)->resource;
                    if (!empty($memberUri)) {
                        $relationshipsToProcess[] = [
                            'type' => 'collection_member',
                            'collection_uri' => $collectionUri,
                            'member_uri' => $memberUri,
                            'position' => $position++
                        ];
                    }
                }

                // Traiter les listes ordonnées
                if ($isOrdered) {
                    $memberListNodes = $collectionNode->xpath('skos:memberList/rdf:Description');
                    $position = 0;
                    foreach ($memberListNodes as $memberNode) {
                        $memberUri = (string) $memberNode->attributes('rdf', true)->about;
                        if (!empty($memberUri)) {
                            $relationshipsToProcess[] = [
                                'type' => 'collection_member',
                                'collection_uri' => $collectionUri,
                                'member_uri' => $memberUri,
                                'position' => $position++
                            ];
                        }
                    }
                }
            }

            // Traitement des concepts
                foreach ($conceptNodes as $conceptNode) {
                    $conceptUri = (string) $conceptNode->attributes('rdf', true)->about;
                    if (empty($conceptUri)) continue;

                    $prefLabels = $conceptNode->xpath('skos:prefLabel');
                    if (empty($prefLabels)) continue;

                    $preferredLabel = (string) $prefLabels[0];
                    $language = (string) $prefLabels[0]->attributes('xml', true)->lang ?? 'fr';
                    $language = $this->standardizeLanguageCode($language);

                    $notations = $conceptNode->xpath('skos:notation');
                    $notation = !empty($notations) ? (string) $notations[0] : null;

                    $existingConcept = ThesaurusConcept::where('uri', $conceptUri)->first();

                    if ($existingConcept && in_array($mode, ['update', 'merge'])) {
                        // Mettre à jour le concept existant
                        if ($notation) {
                            $existingConcept->notation = $notation;
                            $existingConcept->save();
                        }

                        $prefLabel = $existingConcept->labels()->where('type', 'prefLabel')
                                                   ->where('language', $language)
                                                   ->first();

                        if ($prefLabel) {
                            $prefLabel->literal_form = $preferredLabel;
                            $prefLabel->save();
                        } else {
                            ThesaurusLabel::create([
                                'concept_id' => $existingConcept->id,
                                'type' => 'prefLabel',
                                'literal_form' => $preferredLabel,
                                'language' => $language
                            ]);
                        }

                        // Traiter les notes
                        $this->processConceptNotes($existingConcept, $conceptNode);

                        // Collecter les relations pour les traiter après
                        $relationshipsToProcess[] = [
                            'concept' => $existingConcept,
                            'node' => $conceptNode
                        ];

                        $updated++;
                    } else if (in_array($mode, ['add', 'merge', 'replace']) && !$existingConcept) {
                        // Créer un nouveau concept
                        $newConcept = ThesaurusConcept::create([
                            'scheme_id' => $scheme->id,
                            'uri' => $conceptUri,
                            'notation' => $notation,
                            'status' => 1
                        ]);

                        // Ajouter le label préféré
                        ThesaurusLabel::create([
                            'concept_id' => $newConcept->id,
                            'type' => 'prefLabel',
                            'literal_form' => $preferredLabel,
                            'language' => $language
                        ]);

                        // Traiter les notes
                        $this->processConceptNotes($newConcept, $conceptNode);

                        // Collecter les relations pour les traiter après
                        $relationshipsToProcess[] = [
                            'concept' => $newConcept,
                            'node' => $conceptNode
                        ];

                        $imported++;
                    }
                }

                // Traiter les relations entre concepts
                foreach ($relationshipsToProcess as $rel) {
                    if (isset($rel['type']) && $rel['type'] === 'collection_member') {
                        // Traiter les relations entre collections et membres
                        $collection = ThesaurusCollection::where('uri', $rel['collection_uri'])->first();
                        $concept = ThesaurusConcept::where('uri', $rel['member_uri'])->first();

                        if ($collection && $concept) {
                            // Vérifier si la relation existe déjà
                            $existingRelation = DB::table('thesaurus_collection_members')
                                ->where('collection_id', $collection->id)
                                ->where('concept_id', $concept->id)
                                ->first();

                            if (!$existingRelation) {
                                // Créer la relation
                                DB::table('thesaurus_collection_members')->insert([
                                    'collection_id' => $collection->id,
                                    'concept_id' => $concept->id,
                                    'position' => $rel['position'],
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        }
                    } else {
                        // Traiter les relations standard entre concepts
                        $sourceConcept = ThesaurusConcept::where('uri', $rel['source_uri'])->first();
                        $targetConcept = ThesaurusConcept::where('uri', $rel['target_uri'])->first();

                        if ($sourceConcept && $targetConcept) {
                            // Vérifier si la relation existe déjà
                            $existingRelation = ThesaurusConceptRelation::where('concept_id', $sourceConcept->id)
                                ->where('target_concept_id', $targetConcept->id)
                                ->where('relation_type', $rel['type'])
                                ->first();

                            if (!$existingRelation) {
                                // Créer la relation
                                ThesaurusConceptRelation::create([
                                    'concept_id' => $sourceConcept->id,
                                    'target_concept_id' => $targetConcept->id,
                                    'relation_type' => $rel['type']
                                ]);

                                // Pour les relations symétriques (related) ou inverses (broader/narrower), créer la relation inverse
                                if ($rel['type'] === 'related') {
                                    ThesaurusConceptRelation::firstOrCreate([
                                        'concept_id' => $targetConcept->id,
                                        'target_concept_id' => $sourceConcept->id,
                                        'relation_type' => 'related'
                                    ]);
                                } else if ($rel['type'] === 'broader') {
                                    ThesaurusConceptRelation::firstOrCreate([
                                        'concept_id' => $targetConcept->id,
                                        'target_concept_id' => $sourceConcept->id,
                                        'relation_type' => 'narrower'
                                    ]);
                                } else if ($rel['type'] === 'narrower') {
                                    ThesaurusConceptRelation::firstOrCreate([
                                        'concept_id' => $targetConcept->id,
                                        'target_concept_id' => $sourceConcept->id,
                                        'relation_type' => 'broader'
                                    ]);
                                }
                            }
                        }
                    }
                }

                DB::commit();

                return [
                    'success' => true,
                    'message' => "Import SKOS terminé: {$imported} concepts ajoutés, {$updated} mis à jour",
                    'stats' => "{$imported} ajoutés, {$updated} mis à jour"
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de l\'import SKOS: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return ['success' => false, 'message' => 'Erreur lors de l\'import SKOS: ' . $e->getMessage()];
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import SKOS: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Erreur lors de l\'import SKOS: ' . $e->getMessage()];
        }
    }

    /**
     * Traite les notes d'un concept SKOS
     */
    private function processConceptNotes($concept, $conceptNode)
    {
        $noteTypes = [
            'skos:definition' => 'definition',
            'skos:scopeNote' => 'scopeNote',
            'skos:example' => 'example',
            'skos:historyNote' => 'historyNote',
            'skos:editorialNote' => 'editorialNote'
        ];

        foreach ($noteTypes as $xpathType => $dbType) {
            $notes = $conceptNode->xpath($xpathType);
            foreach ($notes as $note) {
                $noteText = (string) $note;
                $noteLang = (string) $note->attributes('xml', true)->lang ?? 'fr';

                if (!empty($noteText)) {
                    ThesaurusConceptNote::firstOrCreate([
                        'concept_id' => $concept->id,
                        'type' => $dbType,
                        'note_type' => $dbType, // Pour compatibilité
                        'note' => $noteText,
                        'note_text' => $noteText, // Pour compatibilité
                        'language' => $this->standardizeLanguageCode($noteLang)
                    ]);
                }
            }
        }
    }

    /**
     * Traite les relations d'un concept SKOS
     */
    private function processConceptRelationships($concept, $conceptNode)
    {
        $relationTypes = [
            'skos:broader' => 'broader',
            'skos:narrower' => 'narrower',
            'skos:related' => 'related',
            'skos:exactMatch' => 'exactMatch',
            'skos:closeMatch' => 'closeMatch',
            'skos:broadMatch' => 'broadMatch',
            'skos:narrowMatch' => 'narrowMatch',
            'skos:relatedMatch' => 'relatedMatch'
        ];

        foreach ($relationTypes as $xpathType => $dbType) {
            $relations = $conceptNode->xpath($xpathType);

            foreach ($relations as $relation) {
                $targetUri = (string) $relation->attributes('rdf', true)->resource;
                $targetConcept = ThesaurusConcept::where('uri', $targetUri)->first();

                if ($targetConcept) {
                    ThesaurusConceptRelation::firstOrCreate([
                        'source_concept_id' => $concept->id,
                        'target_concept_id' => $targetConcept->id,
                        'relation_type' => $dbType
                    ]);

                    // Créer la relation inverse pour broader/narrower
                    if ($dbType === 'broader') {
                        ThesaurusConceptRelation::firstOrCreate([
                            'source_concept_id' => $targetConcept->id,
                            'target_concept_id' => $concept->id,
                            'relation_type' => 'narrower'
                        ]);
                    } else if ($dbType === 'narrower') {
                        ThesaurusConceptRelation::firstOrCreate([
                            'source_concept_id' => $targetConcept->id,
                            'target_concept_id' => $concept->id,
                            'relation_type' => 'broader'
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Traite l'import RDF
     */
    /**
     * Process RDF Import - Cette méthode est maintenue pour compatibilité
     * mais elle redirige vers processSkosImport car SKOS est écrit en RDF
     * @deprecated Utilisez plutôt processSkosImport()
     */
    private function processRdfImport($file, $mode)
    {
        // Redirection vers la méthode SKOS car SKOS est écrit en RDF
        return $this->processSkosImport($file, $mode);

            // Enregistrer les namespaces pour XPath
            $namespaces = $xml->getNamespaces(true);
            foreach ($namespaces as $prefix => $namespace) {
                if (!empty($prefix)) {
                    $xml->registerXPathNamespace($prefix, $namespace);
                }
            }

            // Créer un schéma de thésaurus par défaut
            $scheme = ThesaurusScheme::firstOrCreate(
                ['uri' => url('/thesaurus/rdf-import')],
                [
                    'title' => 'Thésaurus RDF importé',
                    'description' => 'Schéma de thésaurus créé lors de l\'import RDF',
                    'language' => 'fr'
                ]
            );

            $descriptions = $xml->xpath('//rdf:Description');
            $imported = 0;
            $updated = 0;

            // Si mode remplacement, effacer tous les concepts liés à ce schéma
            if ($mode === 'replace') {
                ThesaurusConcept::where('scheme_id', $scheme->id)->delete();
            }

            DB::beginTransaction();

            try {
                foreach ($descriptions as $description) {
                    $uri = (string) $description->attributes('rdf', true)->about;
                    if (empty($uri)) continue;

                    // Vérifier les labels
                    $labels = $description->xpath('rdfs:label');
                    if (empty($labels)) continue;

                    $label = (string) $labels[0];
                    $labelLang = (string) $labels[0]->attributes('xml', true)->lang ?? 'fr';
                    $labelLang = $this->standardizeLanguageCode($labelLang);

                    // Récupérer les commentaires comme notes de portée
                    $comments = $description->xpath('rdfs:comment');
                    $comment = !empty($comments) ? (string) $comments[0] : null;
                    $commentLang = !empty($comments) ? (string) $comments[0]->attributes('xml', true)->lang ?? $labelLang : $labelLang;

                    // Vérifier si le concept existe déjà
                    $existingConcept = ThesaurusConcept::where('uri', $uri)->first();

                    if ($existingConcept && in_array($mode, ['update', 'merge'])) {
                        // Mettre à jour le label préféré
                        $prefLabel = $existingConcept->labels()->where('type', 'prefLabel')
                                                   ->where('language', $labelLang)
                                                   ->first();

                        if ($prefLabel) {
                            $prefLabel->literal_form = $label;
                            $prefLabel->save();
                        } else {
                            ThesaurusLabel::create([
                                'concept_id' => $existingConcept->id,
                                'type' => 'prefLabel',
                                'literal_form' => $label,
                                'language' => $labelLang
                            ]);
                        }

                        // Mettre à jour la note de portée si présente
                        if ($comment) {
                            $scopeNote = $existingConcept->notes()->where('type', 'scopeNote')
                                                       ->orWhere('note_type', 'scopeNote')
                                                       ->where('language', $commentLang)
                                                       ->first();

                            if ($scopeNote) {
                                // Mise à jour compatible avec les deux structures
                                if (isset($scopeNote->note)) {
                                    $scopeNote->note = $comment;
                                } else {
                                    $scopeNote->note_text = $comment;
                                }
                                $scopeNote->save();
                            } else {
                                ThesaurusConceptNote::create([
                                    'concept_id' => $existingConcept->id,
                                    'type' => 'scopeNote',
                                    'note_type' => 'scopeNote', // Pour compatibilité
                                    'note' => $comment,
                                    'note_text' => $comment, // Pour compatibilité
                                    'language' => $commentLang
                                ]);
                            }
                        }

                        // Traiter les propriétés Dublin Core
                        $this->processDcProperties($existingConcept, $description);

                        $updated++;
                    } else if (in_array($mode, ['add', 'merge', 'replace']) && !$existingConcept) {
                        // Créer un nouveau concept
                        $newConcept = ThesaurusConcept::create([
                            'scheme_id' => $scheme->id,
                            'uri' => $uri,
                            'status' => 1
                        ]);

                        // Ajouter le label préféré
                        ThesaurusLabel::create([
                            'concept_id' => $newConcept->id,
                            'type' => 'prefLabel',
                            'literal_form' => $label,
                            'language' => $labelLang
                        ]);

                        // Ajouter la note de portée si présente
                        if ($comment) {
                            ThesaurusConceptNote::create([
                                'concept_id' => $newConcept->id,
                                'type' => 'scopeNote',
                                'note_type' => 'scopeNote', // Pour compatibilité
                                'note' => $comment,
                                'note_text' => $comment, // Pour compatibilité
                                'language' => $commentLang
                            ]);
                        }

                        // Traiter les propriétés Dublin Core
                        $this->processDcProperties($newConcept, $description);

                        $imported++;
                    }
                }

                DB::commit();

                return [
                    'success' => true,
                    'message' => "Import RDF terminé: {$imported} concepts ajoutés, {$updated} mis à jour",
                    'stats' => "{$imported} ajoutés, {$updated} mis à jour"
                ];

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de l\'import RDF: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return ['success' => false, 'message' => 'Erreur lors de l\'import RDF: ' . $e->getMessage()];
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import RDF: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Erreur lors de l\'import RDF: ' . $e->getMessage()];
        }
    }

    /**
     * Traite les propriétés Dublin Core d'une description RDF
     */
    private function processDcProperties($concept, $description)
    {
        $dcProperties = [
            'dc:title', 'dc:subject', 'dc:description', 'dc:creator',
            'dc:contributor', 'dc:publisher', 'dc:date', 'dc:type',
            'dc:format', 'dc:identifier', 'dc:source', 'dc:language',
            'dc:relation', 'dc:coverage', 'dc:rights'
        ];

        foreach ($dcProperties as $dcProperty) {
            $values = $description->xpath($dcProperty);
            foreach ($values as $value) {
                $propertyName = str_replace('dc:', '', $dcProperty);
                $propertyValue = (string) $value;
                $propertyLang = (string) $value->attributes('xml', true)->lang ?? null;

                if (!empty($propertyValue)) {
                    ThesaurusConceptProperty::create([
                        'concept_id' => $concept->id,
                        'property_name' => $propertyName,
                        'property_value' => $propertyValue,
                        'language' => $propertyLang ? $this->standardizeLanguageCode($propertyLang) : null
                    ]);
                }
            }
        }
    }

    /**
     * Convertit un code de langue étendu (fr-FR) en code court (fr) et vérifie sa validité
     *
     * @param string $langCode Code de langue original
     * @return string Code de langue standardisé
     */
    /**
     * Traite les labels d'une collection à partir du nœud XML
     */
    private function processCollectionLabels($collection, $collectionNode)
    {
        // Supprimer les labels existants si la collection existe déjà
        ThesaurusCollectionLabel::where('collection_id', $collection->id)->delete();

        // Ajouter les labels préférés
        $prefLabels = $collectionNode->xpath('skos:prefLabel');
        foreach ($prefLabels as $prefLabel) {
            $language = (string) $prefLabel->attributes('xml', true)->lang ?? 'fr';
            $language = $this->standardizeLanguageCode($language);

            ThesaurusCollectionLabel::create([
                'collection_id' => $collection->id,
                'language' => $language,
                'label_type' => 'prefLabel',
                'label' => (string) $prefLabel
            ]);
        }

        // Ajouter les labels alternatifs
        $altLabels = $collectionNode->xpath('skos:altLabel');
        foreach ($altLabels as $altLabel) {
            $language = (string) $altLabel->attributes('xml', true)->lang ?? 'fr';
            $language = $this->standardizeLanguageCode($language);

            ThesaurusCollectionLabel::create([
                'collection_id' => $collection->id,
                'language' => $language,
                'label_type' => 'altLabel',
                'label' => (string) $altLabel
            ]);
        }

        // Ajouter les labels cachés
        $hiddenLabels = $collectionNode->xpath('skos:hiddenLabel');
        foreach ($hiddenLabels as $hiddenLabel) {
            $language = (string) $hiddenLabel->attributes('xml', true)->lang ?? 'fr';
            $language = $this->standardizeLanguageCode($language);

            ThesaurusCollectionLabel::create([
                'collection_id' => $collection->id,
                'language' => $language,
                'label_type' => 'hiddenLabel',
                'label' => (string) $hiddenLabel
            ]);
        }
    }

    /**
     * Traite les relations de collections imbriquées (parent-enfant)
     * @param ThesaurusCollection $parentCollection La collection parente
     * @param SimpleXMLElement $collectionNode Le noeud XML de la collection
     * @return void
     */
    private function processNestedCollections(ThesaurusCollection $parentCollection, $collectionNode)
    {
        // ID de la collection parente
        $parentId = $parentCollection->getKey();

        // Supprimer les relations existantes pour éviter les doublons
        DB::table('thesaurus_nested_collections')
            ->where('parent_collection_id', $parentId)
            ->delete();

        // Chercher les sous-collections avec narrower
        $narrowerNodes = $collectionNode->xpath('skos:narrower');
        $position = 0;

        foreach ($narrowerNodes as $narrowerNode) {
            $childUri = (string) $narrowerNode->attributes('rdf', true)->resource;

            if (!empty($childUri)) {
                // Récupérer la collection enfant par son URI
                $childCollection = ThesaurusCollection::where('uri', $childUri)->first();

                if ($childCollection) {
                    $childId = $childCollection->getKey();

                    // Créer la relation parent-enfant
                    DB::table('thesaurus_nested_collections')->insert([
                        'parent_collection_id' => $parentId,
                        'child_collection_id' => $childId,
                        'position' => $position++,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    private function standardizeLanguageCode($langCode)
    {
        // Si vide, utiliser la langue par défaut
        if (empty($langCode)) {
            return 'fr';
        }

        // Extraire les deux premiers caractères (code ISO 639-1)
        $shortCode = strtolower(substr($langCode, 0, 2));

        // Liste des langues supportées dans la base de données
        $supportedLanguages = ['fr', 'en', 'es', 'de', 'it', 'pt'];

        // Vérifier si le code est supporté
        if (!in_array($shortCode, $supportedLanguages)) {
            // Log pour information
            Log::info("Code de langue non supporté standardisé: {$langCode} -> fr", [
                'original' => $langCode,
                'extracted' => $shortCode,
                'supported' => $supportedLanguages
            ]);
            return 'fr'; // Code par défaut
        }

        return $shortCode;
    }

    /**
     * Construit la requête de recherche pour l'export
     */
    private function buildSearchQuery(Request $request)
    {
        $query = ThesaurusConcept::with(['labels', 'notes', 'properties']);

        // Ajouter des filtres si nécessaires
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('labels', function ($q) use ($search) {
                $q->where('literal_form', 'LIKE', "%{$search}%");
            })->orWhereHas('notes', function ($q) use ($search) {
                $q->where('note', 'LIKE', "%{$search}%")
                  ->orWhere('note_text', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('language')) {
            $language = $request->input('language');
            $query->whereHas('labels', function ($q) use ($language) {
                $q->where('language', $language);
            });
        }

        if ($request->has('scheme_id')) {
            $query->where('scheme_id', $request->input('scheme_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', 1); // Par défaut, seulement les concepts actifs
        }

        return $query;
    }
}
