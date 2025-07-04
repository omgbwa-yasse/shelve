<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\NonDescriptor;
use App\Models\ExternalAlignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ThesaurusExportImportController extends Controller
{
    /**
     * Export les termes du thésaurus au format SKOS (XML)
     */
    public function exportSkos(Request $request)
    {
        // Récupérer la requête de recherche du ThesaurusSearchController
        $termsQuery = $this->buildSearchQuery($request);
        $terms = $termsQuery->get();

        // Générer le XML SKOS
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dc="http://purl.org/dc/elements/1.1/"></rdf:RDF>');

        foreach ($terms as $term) {
            // Créer le concept
            $concept = $xml->addChild('skos:Concept');
            $concept->addAttribute('rdf:about', url('/terms/' . $term->id));

            // Ajouter le libellé préféré
            $prefLabel = $concept->addChild('skos:prefLabel', htmlspecialchars($term->preferred_label));
            $prefLabel->addAttribute('xml:lang', $term->language);

            // Ajouter les non-descripteurs (libellés alternatifs)
            foreach ($term->nonDescriptors as $nonDescriptor) {
                $altLabel = $concept->addChild('skos:altLabel', htmlspecialchars($nonDescriptor->non_descriptor_label));
                $altLabel->addAttribute('xml:lang', $term->language);
            }

            // Ajouter les relations hiérarchiques (termes génériques)
            foreach ($term->broaderTerms as $broader) {
                $broaderElement = $concept->addChild('skos:broader');
                $broaderElement->addAttribute('rdf:resource', url('/terms/' . $broader->id));
            }

            // Ajouter les relations hiérarchiques (termes spécifiques)
            foreach ($term->narrowerTerms as $narrower) {
                $narrowerElement = $concept->addChild('skos:narrower');
                $narrowerElement->addAttribute('rdf:resource', url('/terms/' . $narrower->id));
            }

            // Ajouter les relations associatives
            foreach ($term->associatedTerms as $related) {
                $relatedElement = $concept->addChild('skos:related');
                $relatedElement->addAttribute('rdf:resource', url('/terms/' . $related->id));
            }

            // Ajouter les alignements externes
            foreach ($term->externalAlignments as $alignment) {
                $alignElement = null;

                switch ($alignment->relation_type) {
                    case 'exactMatch':
                        $alignElement = $concept->addChild('skos:exactMatch');
                        break;
                    case 'closeMatch':
                        $alignElement = $concept->addChild('skos:closeMatch');
                        break;
                    case 'broadMatch':
                        $alignElement = $concept->addChild('skos:broadMatch');
                        break;
                    case 'narrowMatch':
                        $alignElement = $concept->addChild('skos:narrowMatch');
                        break;
                    case 'relatedMatch':
                        $alignElement = $concept->addChild('skos:relatedMatch');
                        break;
                    default:
                        $alignElement = $concept->addChild('skos:exactMatch');
                }

                if ($alignElement) {
                    $alignElement->addAttribute('rdf:resource', $alignment->external_uri);
                }
            }

            // Ajouter les notes (définition, notes d'application, etc.)
            if (!empty($term->definition)) {
                $definition = $concept->addChild('skos:definition', htmlspecialchars($term->definition));
                $definition->addAttribute('xml:lang', $term->language);
            }

            if (!empty($term->scope_note)) {
                $scopeNote = $concept->addChild('skos:scopeNote', htmlspecialchars($term->scope_note));
                $scopeNote->addAttribute('xml:lang', $term->language);
            }

            if (!empty($term->history_note)) {
                $historyNote = $concept->addChild('skos:historyNote', htmlspecialchars($term->history_note));
                $historyNote->addAttribute('xml:lang', $term->language);
            }

            if (!empty($term->editorial_note)) {
                $editorialNote = $concept->addChild('skos:editorialNote', htmlspecialchars($term->editorial_note));
                $editorialNote->addAttribute('xml:lang', $term->language);
            }

            if (!empty($term->example)) {
                $example = $concept->addChild('skos:example', htmlspecialchars($term->example));
                $example->addAttribute('xml:lang', $term->language);
            }

            // Ajouter des métadonnées supplémentaires
            $concept->addChild('dc:created', $term->created_at->toIso8601String());
            $concept->addChild('dc:modified', $term->updated_at->toIso8601String());

            if (!empty($term->category)) {
                $concept->addChild('dc:subject', htmlspecialchars($term->category));
            }

            // Statut
            $concept->addChild('dc:type', $term->status);
        }

        // Créer la réponse HTTP avec le contenu XML et les en-têtes appropriés
        $filename = 'thesaurus_export_' . date('Y-m-d') . '.rdf';
        $response = response($xml->asXML(), 200)
                    ->header('Content-Type', 'application/rdf+xml')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Export les termes du thésaurus au format CSV
     */
    public function exportCsv(Request $request)
    {
        // Récupérer la requête de recherche du ThesaurusSearchController
        $termsQuery = $this->buildSearchQuery($request);
        $terms = $termsQuery->get();

        // Créer un flux de sortie pour le CSV
        $output = fopen('php://temp', 'r+');

        // Écrire l'en-tête CSV
        fputcsv($output, [
            'ID',
            'Terme préféré',
            'Langue',
            'Catégorie',
            'Statut',
            'Est Top Terme',
            'Définition',
            'Note d\'application',
            'Note historique',
            'Note éditoriale',
            'Exemple',
            'Termes génériques',
            'Termes spécifiques',
            'Termes associés',
            'Non-descripteurs',
            'Alignements externes',
            'Date de création',
            'Date de modification'
        ]);

        // Écrire les données
        foreach ($terms as $term) {
            // Préparer les relations sous forme de chaînes
            $broaderTerms = $term->broaderTerms->pluck('preferred_label')->implode('; ');
            $narrowerTerms = $term->narrowerTerms->pluck('preferred_label')->implode('; ');
            $relatedTerms = $term->associatedTerms->pluck('preferred_label')->implode('; ');
            $nonDescriptors = $term->nonDescriptors->pluck('non_descriptor_label')->implode('; ');

            // Préparer les alignements externes
            $alignments = [];
            foreach ($term->externalAlignments as $alignment) {
                $alignments[] = $alignment->relation_type . ': ' . $alignment->external_vocabulary . ' - ' . $alignment->external_uri;
            }
            $alignmentsStr = implode('; ', $alignments);

            // Écrire la ligne
            fputcsv($output, [
                $term->id,
                $term->preferred_label,
                $term->language,
                $term->category,
                $term->status,
                $term->is_top_term ? 'Oui' : 'Non',
                $term->definition,
                $term->scope_note,
                $term->history_note,
                $term->editorial_note,
                $term->example,
                $broaderTerms,
                $narrowerTerms,
                $relatedTerms,
                $nonDescriptors,
                $alignmentsStr,
                $term->created_at->format('Y-m-d H:i:s'),
                $term->updated_at->format('Y-m-d H:i:s')
            ]);
        }

        // Rembobiner le flux et lire son contenu
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Créer la réponse HTTP avec le contenu CSV et les en-têtes appropriés
        $filename = 'thesaurus_export_' . date('Y-m-d') . '.csv';
        $response = response($csv, 200)
                    ->header('Content-Type', 'text/csv; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Affiche le formulaire d'import SKOS
     */
    public function showImportSkosForm()
    {
        return view('thesaurus.import.skos');
    }

    /**
     * Traite l'import d'un fichier SKOS
     */
    public function importSkos(Request $request)
    {
        // Valider la requête
        $validator = Validator::make($request->all(), [
            'skos_file' => 'required|file|mimes:xml,rdf',
            'import_mode' => 'required|in:add,update,replace'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Charger le fichier XML
        $file = $request->file('skos_file');
        $xml = simplexml_load_file($file->getPathname());

        // Enregistrer les namespaces
        $xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $xml->registerXPathNamespace('skos', 'http://www.w3.org/2004/02/skos/core#');
        $xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');

        // Si le mode est "replace", supprimer tous les termes existants
        $importMode = $request->input('import_mode');
        if ($importMode === 'replace') {
            DB::transaction(function () {
                ExternalAlignment::truncate();
                NonDescriptor::truncate();

                // Supprimer d'abord les relations pour éviter les contraintes de clé étrangère
                DB::table('term_hierarchical_relations')->truncate();
                DB::table('term_associative_relations')->truncate();
                DB::table('term_translations')->truncate();

                Term::truncate();
            });
        }

        // Compteurs pour le rapport d'import
        $stats = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        // Parcourir les concepts
        $concepts = $xml->xpath('//skos:Concept');

        DB::beginTransaction();
        try {
            foreach ($concepts as $concept) {
                // Récupérer l'URI du concept
                $conceptUri = (string)$concept->attributes('rdf', true)->about;

                // Extraire l'ID si l'URI correspond à notre format
                $termId = null;
                if (preg_match('/\/terms\/(\d+)$/', $conceptUri, $matches)) {
                    $termId = $matches[1];
                }

                // Récupérer les informations de base du concept
                $prefLabels = $concept->xpath('./skos:prefLabel');
                if (empty($prefLabels)) {
                    $stats['skipped']++;
                    continue; // Passer au suivant si pas de libellé préféré
                }

                $prefLabel = (string)$prefLabels[0];
                $language = (string)$prefLabels[0]->attributes('xml', true)->lang ?: 'fr';

                // Chercher si le terme existe déjà
                $term = null;
                if ($termId && $importMode !== 'add') {
                    $term = Term::find($termId);
                }

                if (!$term && $importMode !== 'add') {
                    // Essayer de trouver par le libellé préféré et la langue
                    $term = Term::where('preferred_label', $prefLabel)
                                ->where('language', $language)
                                ->first();
                }

                // Déterminer si on crée ou met à jour
                $isNewTerm = false;
                if (!$term) {
                    // Si on est en mode update uniquement et que le terme n'existe pas, on passe
                    if ($importMode === 'update') {
                        $stats['skipped']++;
                        continue;
                    }

                    $isNewTerm = true;
                    $term = new Term();
                    $term->preferred_label = $prefLabel;
                    $term->language = $language;
                    $stats['created']++;
                } else {
                    // Mise à jour du terme existant
                    $term->preferred_label = $prefLabel;
                    $term->language = $language;
                    $stats['updated']++;
                }

                // Définir les propriétés additionnelles
                $term->is_top_term = false; // Par défaut

                // Notes et définitions
                $definitions = $concept->xpath('./skos:definition');
                if (!empty($definitions)) {
                    $term->definition = (string)$definitions[0];
                }

                $scopeNotes = $concept->xpath('./skos:scopeNote');
                if (!empty($scopeNotes)) {
                    $term->scope_note = (string)$scopeNotes[0];
                }

                $historyNotes = $concept->xpath('./skos:historyNote');
                if (!empty($historyNotes)) {
                    $term->history_note = (string)$historyNotes[0];
                }

                $editorialNotes = $concept->xpath('./skos:editorialNote');
                if (!empty($editorialNotes)) {
                    $term->editorial_note = (string)$editorialNotes[0];
                }

                $examples = $concept->xpath('./skos:example');
                if (!empty($examples)) {
                    $term->example = (string)$examples[0];
                }

                // Métadonnées DC
                $subjects = $concept->xpath('./dc:subject');
                if (!empty($subjects)) {
                    $term->category = (string)$subjects[0];
                }

                $types = $concept->xpath('./dc:type');
                if (!empty($types)) {
                    $status = (string)$types[0];
                    if (in_array($status, ['approved', 'candidate', 'deprecated'])) {
                        $term->status = $status;
                    } else {
                        $term->status = 'candidate'; // Valeur par défaut
                    }
                } else {
                    $term->status = 'candidate'; // Valeur par défaut
                }

                // Sauvegarder le terme
                $term->save();

                // Stocker les relations pour traitement ultérieur
                $relationships[] = [
                    'termId' => $term->id,
                    'conceptUri' => $conceptUri,
                    'broaderUris' => $concept->xpath('./skos:broader/@rdf:resource'),
                    'narrowerUris' => $concept->xpath('./skos:narrower/@rdf:resource'),
                    'relatedUris' => $concept->xpath('./skos:related/@rdf:resource'),
                    'altLabels' => $concept->xpath('./skos:altLabel'),
                    'exactMatches' => $concept->xpath('./skos:exactMatch/@rdf:resource'),
                    'closeMatches' => $concept->xpath('./skos:closeMatch/@rdf:resource'),
                    'broadMatches' => $concept->xpath('./skos:broadMatch/@rdf:resource'),
                    'narrowMatches' => $concept->xpath('./skos:narrowMatch/@rdf:resource'),
                    'relatedMatches' => $concept->xpath('./skos:relatedMatch/@rdf:resource')
                ];
            }

            // Traiter les relations après que tous les termes ont été créés
            $uriToTermIdMap = [];
            foreach (Term::all() as $existingTerm) {
                $uri = url('/terms/' . $existingTerm->id);
                $uriToTermIdMap[$uri] = $existingTerm->id;
            }

            foreach ($relationships as $rel) {
                $termId = $rel['termId'];
                $term = Term::find($termId);

                // Traiter les non-descripteurs (altLabels)
                if (!empty($rel['altLabels'])) {
                    foreach ($rel['altLabels'] as $altLabel) {
                        $label = (string)$altLabel;
                        $lang = (string)$altLabel->attributes('xml', true)->lang ?: $term->language;

                        // Vérifier si le non-descripteur existe déjà
                        $nonDescriptor = NonDescriptor::where('term_id', $term->id)
                                                     ->where('non_descriptor_label', $label)
                                                     ->first();

                        if (!$nonDescriptor) {
                            $nonDescriptor = new NonDescriptor();
                            $nonDescriptor->term_id = $term->id;
                            $nonDescriptor->non_descriptor_label = $label;
                            $nonDescriptor->save();
                        }
                    }
                }

                // Traiter les relations hiérarchiques et associatives
                $this->processRelationships($term, $rel, $uriToTermIdMap);

                // Traiter les alignements externes
                $this->processExternalAlignments($term, $rel);
            }

            DB::commit();

            return redirect()->route('thesaurus.import.skos.form')
                             ->with('success', "Import SKOS réussi: {$stats['created']} termes créés, {$stats['updated']} termes mis à jour, {$stats['skipped']} termes ignorés.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'import SKOS : ' . $e->getMessage());

            return redirect()->route('thesaurus.import.skos.form')
                             ->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Affiche le formulaire d'import CSV
     */
    public function showImportCsvForm()
    {
        return view('thesaurus.import.csv');
    }

    /**
     * Traite l'import d'un fichier CSV
     */
    public function importCsv(Request $request)
    {
        // Valider la requête
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt',
            'import_mode' => 'required|in:add,update,replace',
            'delimiter' => 'required|in:comma,semicolon,tab'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Déterminer le délimiteur
        $delimiterMap = [
            'comma' => ',',
            'semicolon' => ';',
            'tab' => "\t"
        ];
        $delimiter = $delimiterMap[$request->input('delimiter')];

        // Si le mode est "replace", supprimer tous les termes existants
        $importMode = $request->input('import_mode');
        if ($importMode === 'replace') {
            DB::transaction(function () {
                ExternalAlignment::truncate();
                NonDescriptor::truncate();

                // Supprimer d'abord les relations pour éviter les contraintes de clé étrangère
                DB::table('term_hierarchical_relations')->truncate();
                DB::table('term_associative_relations')->truncate();
                DB::table('term_translations')->truncate();

                Term::truncate();
            });
        }

        // Lire le fichier CSV
        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');

        // Lire l'en-tête
        $header = fgetcsv($handle, 0, $delimiter);
        if (!$header) {
            return back()->with('error', 'Impossible de lire l\'en-tête du fichier CSV.');
        }

        // Normaliser les noms de colonnes
        $header = array_map(function ($item) {
            return strtolower(trim($item));
        }, $header);

        // Vérifier que les colonnes minimales existent
        $requiredColumns = ['terme préféré', 'langue'];
        foreach ($requiredColumns as $column) {
            if (!in_array($column, $header)) {
                return back()->with('error', "Colonne obligatoire manquante dans le CSV: {$column}");
            }
        }

        // Créer un mapping de colonnes
        $columnMap = [
            'id' => array_search('id', $header),
            'preferred_label' => array_search('terme préféré', $header),
            'language' => array_search('langue', $header),
            'category' => array_search('catégorie', $header),
            'status' => array_search('statut', $header),
            'is_top_term' => array_search('est top terme', $header),
            'definition' => array_search('définition', $header),
            'scope_note' => array_search('note d\'application', $header),
            'history_note' => array_search('note historique', $header),
            'editorial_note' => array_search('note éditoriale', $header),
            'example' => array_search('exemple', $header),
            'broader_terms' => array_search('termes génériques', $header),
            'narrower_terms' => array_search('termes spécifiques', $header),
            'related_terms' => array_search('termes associés', $header),
            'non_descriptors' => array_search('non-descripteurs', $header),
            'external_alignments' => array_search('alignements externes', $header)
        ];

        // Compteurs pour le rapport d'import
        $stats = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        // Préparer des tableaux pour les relations à traiter après l'import initial
        $termRelations = [];
        $termNonDescriptors = [];
        $termAlignments = [];

        // Commencer une transaction
        DB::beginTransaction();
        try {
            // Lire les lignes du CSV
            $lineNumber = 1;
            while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
                $lineNumber++;

                // Vérifier que nous avons suffisamment de colonnes
                if (count($data) < count($requiredColumns)) {
                    $stats['errors']++;
                    Log::warning("Ligne CSV {$lineNumber}: nombre insuffisant de colonnes");
                    continue;
                }

                // Récupérer les valeurs de base
                $preferred_label = $columnMap['preferred_label'] !== false ? trim($data[$columnMap['preferred_label']]) : null;
                $language = $columnMap['language'] !== false ? trim($data[$columnMap['language']]) : 'fr';

                if (empty($preferred_label)) {
                    $stats['skipped']++;
                    Log::warning("Ligne CSV {$lineNumber}: terme préféré manquant");
                    continue;
                }

                // Vérifier si le terme existe déjà
                $term = null;
                $termId = $columnMap['id'] !== false ? $data[$columnMap['id']] : null;

                if ($termId && $importMode !== 'add') {
                    $term = Term::find($termId);
                }

                if (!$term && $importMode !== 'add') {
                    // Essayer de trouver par le libellé préféré et la langue
                    $term = Term::where('preferred_label', $preferred_label)
                                 ->where('language', $language)
                                 ->first();
                }

                // Déterminer si on crée ou met à jour
                $isNewTerm = false;
                if (!$term) {
                    // Si on est en mode update uniquement et que le terme n'existe pas, on passe
                    if ($importMode === 'update') {
                        $stats['skipped']++;
                        continue;
                    }

                    $isNewTerm = true;
                    $term = new Term();
                    $stats['created']++;
                } else {
                    $stats['updated']++;
                }

                // Définir les propriétés
                $term->preferred_label = $preferred_label;
                $term->language = $language;

                // Valeurs optionnelles
                if ($columnMap['category'] !== false && isset($data[$columnMap['category']])) {
                    $term->category = trim($data[$columnMap['category']]);
                }

                if ($columnMap['status'] !== false && isset($data[$columnMap['status']])) {
                    $status = strtolower(trim($data[$columnMap['status']]));
                    if (in_array($status, ['approved', 'candidate', 'deprecated'])) {
                        $term->status = $status;
                    } else {
                        $term->status = 'candidate'; // Valeur par défaut
                    }
                } else {
                    $term->status = 'candidate'; // Valeur par défaut
                }

                if ($columnMap['is_top_term'] !== false && isset($data[$columnMap['is_top_term']])) {
                    $topTerm = strtolower(trim($data[$columnMap['is_top_term']]));
                    $term->is_top_term = in_array($topTerm, ['oui', 'yes', '1', 'true']);
                } else {
                    $term->is_top_term = false;
                }

                // Notes et définitions
                if ($columnMap['definition'] !== false && isset($data[$columnMap['definition']])) {
                    $term->definition = trim($data[$columnMap['definition']]);
                }

                if ($columnMap['scope_note'] !== false && isset($data[$columnMap['scope_note']])) {
                    $term->scope_note = trim($data[$columnMap['scope_note']]);
                }

                if ($columnMap['history_note'] !== false && isset($data[$columnMap['history_note']])) {
                    $term->history_note = trim($data[$columnMap['history_note']]);
                }

                if ($columnMap['editorial_note'] !== false && isset($data[$columnMap['editorial_note']])) {
                    $term->editorial_note = trim($data[$columnMap['editorial_note']]);
                }

                if ($columnMap['example'] !== false && isset($data[$columnMap['example']])) {
                    $term->example = trim($data[$columnMap['example']]);
                }

                // Sauvegarder le terme
                $term->save();

                // Stocker les relations pour traitement ultérieur
                if ($columnMap['broader_terms'] !== false && !empty($data[$columnMap['broader_terms']])) {
                    $termRelations[$term->id]['broader'] = explode(';', $data[$columnMap['broader_terms']]);
                }

                if ($columnMap['narrower_terms'] !== false && !empty($data[$columnMap['narrower_terms']])) {
                    $termRelations[$term->id]['narrower'] = explode(';', $data[$columnMap['narrower_terms']]);
                }

                if ($columnMap['related_terms'] !== false && !empty($data[$columnMap['related_terms']])) {
                    $termRelations[$term->id]['related'] = explode(';', $data[$columnMap['related_terms']]);
                }

                // Stocker les non-descripteurs pour traitement ultérieur
                if ($columnMap['non_descriptors'] !== false && !empty($data[$columnMap['non_descriptors']])) {
                    $termNonDescriptors[$term->id] = explode(';', $data[$columnMap['non_descriptors']]);
                }

                // Stocker les alignements externes pour traitement ultérieur
                if ($columnMap['external_alignments'] !== false && !empty($data[$columnMap['external_alignments']])) {
                    $termAlignments[$term->id] = explode(';', $data[$columnMap['external_alignments']]);
                }
            }

            fclose($handle);

            // Traiter les relations entre termes
            $this->processTermRelationsFromCsv($termRelations);

            // Traiter les non-descripteurs
            $this->processNonDescriptorsFromCsv($termNonDescriptors);

            // Traiter les alignements externes
            $this->processExternalAlignmentsFromCsv($termAlignments);

            DB::commit();

            return redirect()->route('thesaurus.import.csv.form')
                             ->with('success', "Import CSV réussi: {$stats['created']} termes créés, {$stats['updated']} termes mis à jour, {$stats['skipped']} termes ignorés, {$stats['errors']} erreurs.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'import CSV : ' . $e->getMessage());

            return redirect()->route('thesaurus.import.csv.form')
                             ->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Construit une requête de recherche à partir des paramètres de la requête
     */
    private function buildSearchQuery(Request $request)
    {
        // Initialiser la requête de base
        $query = Term::query();

        // Recherche par terme préféré ou non-descripteur
        if ($request->filled('query')) {
            $searchTerm = $request->input('query');

            $query->where(function($q) use ($searchTerm) {
                // Recherche dans le terme préféré
                $q->where('preferred_label', 'LIKE', "%{$searchTerm}%");

                // Recherche dans les non-descripteurs
                $q->orWhereHas('nonDescriptors', function($subQuery) use ($searchTerm) {
                    $subQuery->where('non_descriptor_label', 'LIKE', "%{$searchTerm}%");
                });
            });
        }

        // Filtres additionnels
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_top_term')) {
            $query->where('is_top_term', true);
        }

        // Recherche dans les notes et définitions
        if ($request->filled('content_search')) {
            $contentSearch = $request->content_search;

            $query->where(function($q) use ($contentSearch) {
                $q->where('definition', 'LIKE', "%{$contentSearch}%")
                  ->orWhere('scope_note', 'LIKE', "%{$contentSearch}%")
                  ->orWhere('history_note', 'LIKE', "%{$contentSearch}%")
                  ->orWhere('example', 'LIKE', "%{$contentSearch}%")
                  ->orWhere('editorial_note', 'LIKE', "%{$contentSearch}%");
            });
        }

        // Recherche par URI externe
        if ($request->filled('external_uri')) {
            $externalUri = $request->external_uri;

            $query->whereHas('externalAlignments', function($q) use ($externalUri) {
                $q->where('external_uri', 'LIKE', "%{$externalUri}%");
            });
        }

        // Recherche par vocabulaire externe
        if ($request->filled('external_vocabulary')) {
            $externalVocabulary = $request->external_vocabulary;

            $query->whereHas('externalAlignments', function($q) use ($externalVocabulary) {
                $q->where('external_vocabulary', 'LIKE', "%{$externalVocabulary}%");
            });
        }

        // Recherche par relations
        if ($request->filled('has_narrower')) {
            $query->whereHas('narrowerTerms');
        }

        if ($request->filled('has_broader')) {
            $query->whereHas('broaderTerms');
        }

        if ($request->filled('has_related')) {
            $query->whereHas('associatedTerms');
        }

        if ($request->filled('has_translations')) {
            $query->where(function($q) {
                $q->whereHas('translationsSource')
                  ->orWhereHas('translationsTarget');
            });
        }

        // Tri des résultats
        $sortBy = $request->filled('sort_by') ? $request->sort_by : 'preferred_label';
        $sortDirection = $request->filled('sort_direction') ? $request->sort_direction : 'asc';

        $query->orderBy($sortBy, $sortDirection);

        return $query;
    }

    /**
     * Traite les relations hiérarchiques et associatives lors de l'import SKOS
     */
    private function processRelationships($term, $rel, $uriToTermIdMap)
    {
        // Traiter les relations génériques (broader)
        if (!empty($rel['broaderUris'])) {
            foreach ($rel['broaderUris'] as $uri) {
                $uriStr = (string)$uri;
                if (isset($uriToTermIdMap[$uriStr])) {
                    $broaderId = $uriToTermIdMap[$uriStr];

                    // Vérifier si la relation existe déjà
                    $exists = DB::table('term_hierarchical_relations')
                        ->where('narrower_term_id', $term->id)
                        ->where('broader_term_id', $broaderId)
                        ->exists();

                    if (!$exists) {
                        DB::table('term_hierarchical_relations')->insert([
                            'narrower_term_id' => $term->id,
                            'broader_term_id' => $broaderId,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }
        }

        // Traiter les relations spécifiques (narrower)
        if (!empty($rel['narrowerUris'])) {
            foreach ($rel['narrowerUris'] as $uri) {
                $uriStr = (string)$uri;
                if (isset($uriToTermIdMap[$uriStr])) {
                    $narrowerId = $uriToTermIdMap[$uriStr];

                    // Vérifier si la relation existe déjà
                    $exists = DB::table('term_hierarchical_relations')
                        ->where('narrower_term_id', $narrowerId)
                        ->where('broader_term_id', $term->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('term_hierarchical_relations')->insert([
                            'narrower_term_id' => $narrowerId,
                            'broader_term_id' => $term->id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }
        }

        // Traiter les relations associatives
        if (!empty($rel['relatedUris'])) {
            foreach ($rel['relatedUris'] as $uri) {
                $uriStr = (string)$uri;
                if (isset($uriToTermIdMap[$uriStr])) {
                    $relatedId = $uriToTermIdMap[$uriStr];

                    // Vérifier si la relation existe déjà (dans un sens ou dans l'autre)
                    $exists = DB::table('term_associative_relations')
                        ->where(function($query) use ($term, $relatedId) {
                            $query->where('term_id', $term->id)
                                  ->where('associated_term_id', $relatedId);
                        })
                        ->orWhere(function($query) use ($term, $relatedId) {
                            $query->where('term_id', $relatedId)
                                  ->where('associated_term_id', $term->id);
                        })
                        ->exists();

                    if (!$exists) {
                        DB::table('term_associative_relations')->insert([
                            'term_id' => $term->id,
                            'associated_term_id' => $relatedId,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Traite les alignements externes lors de l'import SKOS
     */
    private function processExternalAlignments($term, $rel)
    {
        // Fonction pour traiter un ensemble d'alignements
        $processAlignments = function ($uris, $relationType, $term) {
            if (!empty($uris)) {
                foreach ($uris as $uri) {
                    $uriStr = (string)$uri;

                    // Extraire le vocabulaire à partir de l'URI (domaine)
                    $vocabulary = '';
                    if (preg_match('/^https?:\/\/([^\/]+)/', $uriStr, $matches)) {
                        $vocabulary = $matches[1];
                    }

                    // Vérifier si l'alignement existe déjà
                    $exists = ExternalAlignment::where('term_id', $term->id)
                        ->where('external_uri', $uriStr)
                        ->exists();

                    if (!$exists) {
                        ExternalAlignment::create([
                            'term_id' => $term->id,
                            'external_vocabulary' => $vocabulary,
                            'external_uri' => $uriStr,
                            'relation_type' => $relationType
                        ]);
                    }
                }
            }
        };

        // Traiter chaque type d'alignement
        $processAlignments($rel['exactMatches'], 'exactMatch', $term);
        $processAlignments($rel['closeMatches'], 'closeMatch', $term);
        $processAlignments($rel['broadMatches'], 'broadMatch', $term);
        $processAlignments($rel['narrowMatches'], 'narrowMatch', $term);
        $processAlignments($rel['relatedMatches'], 'relatedMatch', $term);
    }

    /**
     * Traite les relations entre termes à partir des données CSV
     */
    private function processTermRelationsFromCsv($termRelations)
    {
        // Créer une correspondance label -> id pour rechercher les termes
        $termLabelMap = [];
        foreach (Term::all() as $existingTerm) {
            $key = strtolower($existingTerm->preferred_label) . '_' . $existingTerm->language;
            $termLabelMap[$key] = $existingTerm->id;
        }

        foreach ($termRelations as $termId => $relations) {
            // Traiter les termes génériques
            if (isset($relations['broader'])) {
                foreach ($relations['broader'] as $broaderLabel) {
                    $broaderLabel = trim($broaderLabel);
                    if (!empty($broaderLabel)) {
                        // Rechercher le terme dans différentes langues
                        $broaderId = null;
                        foreach (['fr', 'en', 'es', 'de', 'it', 'pt'] as $lang) {
                            $key = strtolower($broaderLabel) . '_' . $lang;
                            if (isset($termLabelMap[$key])) {
                                $broaderId = $termLabelMap[$key];
                                break;
                            }
                        }

                        if ($broaderId) {
                            // Vérifier si la relation existe déjà
                            $exists = DB::table('term_hierarchical_relations')
                                ->where('narrower_term_id', $termId)
                                ->where('broader_term_id', $broaderId)
                                ->exists();

                            if (!$exists) {
                                DB::table('term_hierarchical_relations')->insert([
                                    'narrower_term_id' => $termId,
                                    'broader_term_id' => $broaderId,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        }
                    }
                }
            }

            // Traiter les termes spécifiques
            if (isset($relations['narrower'])) {
                foreach ($relations['narrower'] as $narrowerLabel) {
                    $narrowerLabel = trim($narrowerLabel);
                    if (!empty($narrowerLabel)) {
                        // Rechercher le terme dans différentes langues
                        $narrowerId = null;
                        foreach (['fr', 'en', 'es', 'de', 'it', 'pt'] as $lang) {
                            $key = strtolower($narrowerLabel) . '_' . $lang;
                            if (isset($termLabelMap[$key])) {
                                $narrowerId = $termLabelMap[$key];
                                break;
                            }
                        }

                        if ($narrowerId) {
                            // Vérifier si la relation existe déjà
                            $exists = DB::table('term_hierarchical_relations')
                                ->where('narrower_term_id', $narrowerId)
                                ->where('broader_term_id', $termId)
                                ->exists();

                            if (!$exists) {
                                DB::table('term_hierarchical_relations')->insert([
                                    'narrower_term_id' => $narrowerId,
                                    'broader_term_id' => $termId,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        }
                    }
                }
            }

            // Traiter les termes associés
            if (isset($relations['related'])) {
                foreach ($relations['related'] as $relatedLabel) {
                    $relatedLabel = trim($relatedLabel);
                    if (!empty($relatedLabel)) {
                        // Rechercher le terme dans différentes langues
                        $relatedId = null;
                        foreach (['fr', 'en', 'es', 'de', 'it', 'pt'] as $lang) {
                            $key = strtolower($relatedLabel) . '_' . $lang;
                            if (isset($termLabelMap[$key])) {
                                $relatedId = $termLabelMap[$key];
                                break;
                            }
                        }

                        if ($relatedId) {
                            // Vérifier si la relation existe déjà (dans un sens ou dans l'autre)
                            $exists = DB::table('term_associative_relations')
                                ->where(function($query) use ($termId, $relatedId) {
                                    $query->where('term_id', $termId)
                                          ->where('associated_term_id', $relatedId);
                                })
                                ->orWhere(function($query) use ($termId, $relatedId) {
                                    $query->where('term_id', $relatedId)
                                          ->where('associated_term_id', $termId);
                                })
                                ->exists();

                            if (!$exists) {
                                DB::table('term_associative_relations')->insert([
                                    'term_id' => $termId,
                                    'associated_term_id' => $relatedId,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Traite les non-descripteurs à partir des données CSV
     */
    private function processNonDescriptorsFromCsv($termNonDescriptors)
    {
        foreach ($termNonDescriptors as $termId => $nonDescriptors) {
            foreach ($nonDescriptors as $label) {
                $label = trim($label);
                if (!empty($label)) {
                    // Vérifier si le non-descripteur existe déjà
                    $exists = NonDescriptor::where('term_id', $termId)
                        ->where('non_descriptor_label', $label)
                        ->exists();

                    if (!$exists) {
                        NonDescriptor::create([
                            'term_id' => $termId,
                            'non_descriptor_label' => $label
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Traite les alignements externes à partir des données CSV
     */
    private function processExternalAlignmentsFromCsv($termAlignments)
    {
        foreach ($termAlignments as $termId => $alignments) {
            foreach ($alignments as $alignmentStr) {
                $alignmentStr = trim($alignmentStr);
                if (!empty($alignmentStr)) {
                    // Format attendu : "type: vocabulaire - uri"
                    if (preg_match('/^(.*?):\s*(.*?)\s*-\s*(.*)$/', $alignmentStr, $matches)) {
                        $relationType = strtolower(trim($matches[1]));
                        $vocabulary = trim($matches[2]);
                        $uri = trim($matches[3]);

                        // Normaliser le type de relation
                        if (!in_array($relationType, ['exactmatch', 'closematch', 'broadmatch', 'narrowmatch', 'relatedmatch'])) {
                            $relationType = 'exactMatch';
                        } else {
                            // Convertir camelCase
                            $relationType = lcfirst(str_replace(' ', '', ucwords(str_replace('match', ' match', $relationType))));
                        }

                        // Vérifier si l'alignement existe déjà
                        $exists = ExternalAlignment::where('term_id', $termId)
                            ->where('external_uri', $uri)
                            ->exists();

                        if (!$exists && !empty($uri)) {
                            ExternalAlignment::create([
                                'term_id' => $termId,
                                'external_vocabulary' => $vocabulary,
                                'external_uri' => $uri,
                                'relation_type' => $relationType
                            ]);
                        }
                    }
                }
            }
        }
    }
}
