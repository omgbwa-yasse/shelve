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
                case 'skos':
                    return $this->exportSkosAjax($request);
                case 'csv':
                    return $this->exportCsvAjax($request);
                case 'rdf':
                    return $this->exportRdfAjax($request);
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
        $termsQuery = $this->buildSearchQuery($request);
        $terms = $termsQuery->get();

        // Générer le fichier SKOS
        $filename = 'thesaurus_export_' . date('Y-m-d_H-i-s') . '.xml';
        $filepath = storage_path('app/exports/' . $filename);

        // Créer le dossier s'il n'existe pas
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        // Générer le contenu SKOS
        $xml = $this->generateSkosXml($terms);
        file_put_contents($filepath, $xml);

        return response()->json([
            'success' => true,
            'message' => count($terms) . ' termes exportés avec succès au format SKOS',
            'download_url' => route('thesaurus.download.export', ['filename' => $filename])
        ]);
    }

    /**
     * Export CSV via AJAX
     */
    private function exportCsvAjax(Request $request)
    {
        $termsQuery = $this->buildSearchQuery($request);
        $terms = $termsQuery->get();

        $filename = 'thesaurus_export_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);

        // Créer le dossier s'il n'existe pas
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        // Générer le contenu CSV
        $csvContent = $this->generateCsvContent($terms);
        file_put_contents($filepath, $csvContent);

        return response()->json([
            'success' => true,
            'message' => count($terms) . ' termes exportés avec succès au format CSV',
            'download_url' => route('thesaurus.download.export', ['filename' => $filename])
        ]);
    }

    /**
     * Export RDF via AJAX
     */
    private function exportRdfAjax(Request $request)
    {
        $termsQuery = $this->buildSearchQuery($request);
        $terms = $termsQuery->get();

        $filename = 'thesaurus_export_' . date('Y-m-d_H-i-s') . '.rdf';
        $filepath = storage_path('app/exports/' . $filename);

        // Créer le dossier s'il n'existe pas
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        // Générer le contenu RDF
        $rdf = $this->generateRdfXml($terms);
        file_put_contents($filepath, $rdf);

        return response()->json([
            'success' => true,
            'message' => count($terms) . ' termes exportés avec succès au format RDF',
            'download_url' => route('thesaurus.download.export', ['filename' => $filename])
        ]);
    }

    /**
     * Prévisualisation d'import via AJAX
     */
    public function importPreview(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'import_format' => 'required|in:skos,csv,rdf',
                'import_file' => 'required|file',
                'import_mode' => 'required|in:add,update,replace,merge'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $format = $request->input('import_format');
            $file = $request->file('import_file');
            $mode = $request->input('import_mode');

            // Analyser le fichier selon le format
            $previewData = $this->analyzeImportFile($file, $format);

            if (!$previewData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d\'analyser le fichier'
                ]);
            }

            // Générer le HTML de prévisualisation
            $previewHtml = view('thesaurus.import.preview', [
                'data' => $previewData,
                'format' => $format,
                'mode' => $mode,
                'filename' => $file->getClientOriginalName()
            ])->render();

            return response()->json([
                'success' => true,
                'preview' => $previewHtml
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la prévisualisation d\'import: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'analyse: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Traitement d'import via AJAX
     */
    public function importProcess(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'import_format' => 'required|in:skos,csv,rdf',
                'import_file' => 'required|file',
                'import_mode' => 'required|in:add,update,replace,merge'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $format = $request->input('import_format');
            $file = $request->file('import_file');
            $mode = $request->input('import_mode');

            // Traiter l'import selon le format
            $result = $this->processImportFile($file, $format, $mode);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'stats' => $result['stats'] ?? null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'import: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Téléchargement des fichiers d'export
     */
    public function downloadExport($filename)
    {
        $filepath = storage_path('app/exports/' . $filename);

        if (!file_exists($filepath)) {
            abort(404, 'Fichier non trouvé');
        }

        return response()->download($filepath)->deleteFileAfterSend(true);
    }

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
                    $term->preferred_label = $preferred_label;
                    $term->language = $language;
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
                if ($columnMap['non_descriptors'] !== false && !empty($data[$columnMap['non_descripteurs']])) {
                    $termNonDescriptors[$term->id] = explode(';', $data[$columnMap['non_descripteurs']]);
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
            // Log pour débogage
            Log::info('Début de l\'import RDF traditionnel', [
                'request_data' => $request->all(),
                'has_file' => $request->hasFile('file')
            ]);

            // Validation de base
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xml,rdf,txt|max:10240', // 10MB max, formats acceptés
            ]);

            if ($validator->fails()) {
                Log::warning('Validation a échoué pour import RDF traditionnel', [
                    'errors' => $validator->errors()->toArray()
                ]);

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Charger le fichier RDF
            $file = $request->file('file');
            Log::info('Fichier RDF reçu', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);

            $content = file_get_contents($file->getPathname());

            // Essayer de détecter l'encodage et convertir en UTF-8 si nécessaire
            $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'ASCII'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                Log::info('Conversion d\'encodage', ['from' => $encoding, 'to' => 'UTF-8']);
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }

            // Analyser le fichier RDF
            try {
                // Options pour éviter les problèmes d'analyse XML courants
                $libxmlOpts = LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NOCDATA | LIBXML_PARSEHUGE | LIBXML_BIGLINES;

                // Conserver les erreurs de libxml pour le débogage
                libxml_use_internal_errors(true);

                // Essayer de nettoyer le contenu XML si nécessaire (BOM, espaces, etc.)
                $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // Suppression BOM UTF-8
                $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $content); // Suppression caractères de contrôle

                $xml = new \SimpleXMLElement($content, $libxmlOpts);

                // Vérifier s'il y a eu des erreurs lors de l'analyse
                $errors = libxml_get_errors();
                if (!empty($errors)) {
                    Log::warning('Avertissements XML lors de l\'analyse du fichier RDF', [
                        'errors' => array_map(function($error) {
                            return [
                                'level' => $error->level,
                                'code' => $error->code,
                                'message' => trim($error->message),
                                'line' => $error->line,
                            ];
                        }, $errors)
                    ]);

                    // Si erreurs graves, afficher les détails
                    $fatalErrors = array_filter($errors, function($error) {
                        return $error->level === LIBXML_ERR_FATAL;
                    });

                    if (!empty($fatalErrors)) {
                        $errorMessages = array_map(function($error) {
                            return "Ligne {$error->line}: " . trim($error->message);
                        }, $fatalErrors);

                        libxml_clear_errors();
                        return redirect()->back()
                            ->with('error', 'Erreurs critiques dans le fichier XML: ' . implode('; ', $errorMessages));
                    }

                    libxml_clear_errors();
                }

                // Enregistrer tous les namespaces possibles pour SKOS
                $namespaces = [
                    'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
                    'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
                    'owl' => 'http://www.w3.org/2002/07/owl#',
                    'dc' => 'http://purl.org/dc/elements/1.1/',
                    'dcterms' => 'http://purl.org/dc/terms/',
                    'skos' => 'http://www.w3.org/2004/02/skos/core#',
                    'xl' => 'http://www.w3.org/2008/05/skos-xl#'
                ];

                // Enregistrer les namespaces pour XPath
                foreach ($namespaces as $prefix => $uri) {
                    $xml->registerXPathNamespace($prefix, $uri);
                }

                // Détecter le type de structure RDF (il y a différentes façons d'encoder SKOS en RDF)
                $structureInfo = $this->detectRdfStructure($xml);
                Log::info('Structure RDF détectée', $structureInfo);

                // Statistiques pour le rapport d'import
                $stats = [
                    'created' => 0,
                    'updated' => 0,
                    'errors' => 0,
                    'relationships' => 0
                ];

                // Pour stocker les relations à traiter après la création des termes
                $relationships = [];

                // Transaction DB pour s'assurer de l'intégrité des données
                DB::beginTransaction();

                try {
                    // Rechercher les concepts selon la structure détectée
                    if ($structureInfo['structure'] === 'standard') {
                        // Format standard
                        $concepts = $xml->xpath('//skos:Concept|//rdf:Description[rdf:type/@rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"]');
                    } else if ($structureInfo['structure'] === 'alternate') {
                        // Format alternatif
                        $concepts = $xml->xpath($structureInfo['conceptPath']);
                    } else {
                        // Essayer plusieurs approches
                        $concepts = $xml->xpath('//skos:Concept|//rdf:Description[rdf:type/@rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"]|//rdf:Description[skos:prefLabel]');
                    }

                    Log::info('Concepts RDF trouvés', ['count' => count($concepts)]);

                    if (count($concepts) === 0) {
                        DB::rollBack();
                        Log::warning('Aucun concept trouvé dans le fichier RDF');
                        return redirect()->back()->with('error', 'Aucun concept SKOS n\'a été trouvé dans le fichier. Vérifiez que le fichier est au format RDF/SKOS valide.');
                    }

                    // Premier passage : créer tous les termes
                    foreach ($concepts as $concept) {
                        // Obtenir l'URI du concept (plusieurs façons possibles)
                        $uri = '';
                        if (isset($concept->attributes('rdf', true)->about)) {
                            $uri = (string)$concept->attributes('rdf', true)->about;
                        } elseif (isset($concept->attributes('rdf', true)->ID)) {
                            $uri = (string)$concept->attributes('rdf', true)->ID;
                        } elseif (isset($concept->attributes('rdf', true)->resource)) {
                            $uri = (string)$concept->attributes('rdf', true)->resource;
                        } elseif (isset($concept['about'])) {
                            $uri = (string)$concept['about'];
                        }

                        // Rechercher les propriétés du concept avec différentes structures possibles
                        $prefLabels = $concept->xpath('.//skos:prefLabel');
                        if (empty($prefLabels)) {
                            $stats['errors']++;
                            Log::warning('Concept sans prefLabel ignoré', ['uri' => $uri]);
                            continue;
                        }                        $prefLabel = '';
                        $language = 'fr'; // Par défaut

                        // Trouver le label préféré dans la langue désirée (fr par défaut)
                        foreach ($prefLabels as $label) {
                            $lang = '';
                            if (isset($label->attributes('xml', true)->lang)) {
                                $lang = (string)$label->attributes('xml', true)->lang;
                            } elseif (isset($label['lang'])) {
                                $lang = (string)$label['lang'];
                            }

                            // Standardiser le code de langue
                            $shortLang = $this->standardizeLanguageCode($lang);

                            if ($shortLang == 'fr') {
                                $prefLabel = (string)$label;
                                $language = 'fr';
                                break;
                            } else if (empty($prefLabel)) {
                                $prefLabel = (string)$label;
                                $language = $shortLang;
                            }
                        }

                        // Tronquer le label préféré s'il est trop long pour la base de données
                        $prefLabel = $this->truncateForDatabase($prefLabel, 100, true, 'preferred_label');

                        // Log des informations sur la langue
                        Log::debug('Traitement de la langue', [
                            'original_lang' => $lang ?? 'non définie',
                            'language_utilisée' => $language,
                            'prefLabel' => $prefLabel
                        ]);

                        if (empty($prefLabel)) {
                            $stats['errors']++;
                            Log::warning('Concept avec prefLabel vide ignoré', ['uri' => $uri]);
                            continue;
                        }

                        // Vérifier si le terme existe déjà
                        $term = Term::where('preferred_label', $prefLabel)
                                    ->where('language', $language)
                                    ->first();

                        if (!$term) {
                            // Créer un nouveau terme
                            $term = new Term();
                            $term->preferred_label = $prefLabel;
                            $term->language = $language;
                            $term->status = 'candidate'; // Par défaut
                            $stats['created']++;
                        } else {
                            // Le terme existe déjà, mettre à jour
                            $term->preferred_label = $prefLabel;
                            $term->language = $language;
                            $stats['updated']++;
                        }                        // Traiter les notes et définitions
                        $definitions = $concept->xpath('.//skos:definition');
                        if (!empty($definitions)) {
                            foreach ($definitions as $def) {
                                $lang = '';
                                if (isset($def->attributes('xml', true)->lang)) {
                                    $lang = (string)$def->attributes('xml', true)->lang;
                                } elseif (isset($def['lang'])) {
                                    $lang = (string)$def['lang'];
                                }

                                // Standardiser le code de langue
                                $shortLang = $this->standardizeLanguageCode($lang);

                                if (empty($lang) || $shortLang == substr($language, 0, 2)) {
                                    // Pas de limite stricte pour TEXT mais on limite quand même pour éviter les problèmes
                                    $term->definition = $this->truncateForDatabase((string)$def, 65535, true, 'definition');
                                    break;
                                }
                            }
                        }                        // Notes d'application
                        $scopeNotes = $concept->xpath('.//skos:scopeNote');
                        if (!empty($scopeNotes)) {
                            foreach ($scopeNotes as $note) {
                                $lang = '';
                                if (isset($note->attributes('xml', true)->lang)) {
                                    $lang = (string)$note->attributes('xml', true)->lang;
                                } elseif (isset($note['lang'])) {
                                    $lang = (string)$note['lang'];
                                }

                                // Standardiser le code de langue
                                $shortLang = $this->standardizeLanguageCode($lang);

                                if (empty($lang) || $shortLang == substr($language, 0, 2)) {
                                    $term->scope_note = $this->truncateForDatabase((string)$note, 65535, true, 'scope_note');
                                    break;
                                }
                            }
                        }                        // Notes historiques
                        $historyNotes = $concept->xpath('.//skos:historyNote');
                        if (!empty($historyNotes)) {
                            foreach ($historyNotes as $note) {
                                $lang = '';
                                if (isset($note->attributes('xml', true)->lang)) {
                                    $lang = (string)$note->attributes('xml', true)->lang;
                                } elseif (isset($note['lang'])) {
                                    $lang = (string)$note['lang'];
                                }

                                // Standardiser le code de langue
                                $shortLang = $this->standardizeLanguageCode($lang);

                                if (empty($lang) || $shortLang == substr($language, 0, 2)) {
                                    $term->history_note = $this->truncateForDatabase((string)$note, 65535, true, 'history_note');
                                    break;
                                }
                            }
                        }                        // Notes éditoriales
                        $editorialNotes = $concept->xpath('.//skos:editorialNote');
                        if (!empty($editorialNotes)) {
                            foreach ($editorialNotes as $note) {
                                $lang = '';
                                if (isset($note->attributes('xml', true)->lang)) {
                                    $lang = (string)$note->attributes('xml', true)->lang;
                                } elseif (isset($note['lang'])) {
                                    $lang = (string)$note['lang'];
                                }

                                // Standardiser le code de langue
                                $shortLang = $this->standardizeLanguageCode($lang);

                                if (empty($lang) || $shortLang == substr($language, 0, 2)) {
                                    $term->editorial_note = $this->truncateForDatabase((string)$note, 65535, true, 'editorial_note');
                                    break;
                                }
                            }
                        }                        // Exemples
                        $examples = $concept->xpath('.//skos:example');
                        if (!empty($examples)) {
                            foreach ($examples as $example) {
                                $lang = '';
                                if (isset($example->attributes('xml', true)->lang)) {
                                    $lang = (string)$example->attributes('xml', true)->lang;
                                } elseif (isset($example['lang'])) {
                                    $lang = (string)$example['lang'];
                                }

                                // Standardiser le code de langue
                                $shortLang = $this->standardizeLanguageCode($lang);

                                if (empty($lang) || $shortLang == substr($language, 0, 2)) {
                                    $term->example = $this->truncateForDatabase((string)$example, 65535, true, 'example');
                                    break;
                                }
                            }
                        }

                        // Vérifier si c'est un terme de tête (plusieurs structures possibles)
                        $isTopTerm = false;
                        $topConcepts = $xml->xpath('//skos:hasTopConcept/@rdf:resource|//skos:ConceptScheme/skos:hasTopConcept/@rdf:resource');
                        $isTopTerm = in_array($uri, array_map('strval', $topConcepts));

                        // Autre façon de détecter les termes de tête
                        if (!$isTopTerm) {
                            $inScheme = $concept->xpath('.//skos:topConceptOf');
                            $isTopTerm = count($inScheme) > 0;
                        }

                        $term->is_top_term = $isTopTerm;

                        // Sauvegarder le terme
                        $term->save();

                        // Collecter les relations pour traitement ultérieur
                        $broaderUris = array_merge(
                            array_map('strval', $concept->xpath('.//skos:broader/@rdf:resource')),
                            array_map('strval', $concept->xpath('./skos:broader/@rdf:resource'))
                        );

                        $narrowerUris = array_merge(
                            array_map('strval', $concept->xpath('.//skos:narrower/@rdf:resource')),
                            array_map('strval', $concept->xpath('./skos:narrower/@rdf:resource'))
                        );

                        $relatedUris = array_merge(
                            array_map('strval', $concept->xpath('.//skos:related/@rdf:resource')),
                            array_map('strval', $concept->xpath('./skos:related/@rdf:resource'))
                        );

                        $relationships[] = [
                            'termId' => $term->id,
                            'uri' => $uri,
                            'broaderUris' => $broaderUris,
                            'narrowerUris' => $narrowerUris,
                            'relatedUris' => $relatedUris,                            'altLabels' => array_map(function($label) {
                                $lang = '';
                                if (isset($label->attributes('xml', true)->lang)) {
                                    $lang = (string)$label->attributes('xml', true)->lang;
                                } elseif (isset($label['lang'])) {
                                    $lang = (string)$label['lang'];
                                }

                                return [
                                    'label' => (string)$label,
                                    'lang' => $lang // On garde la langue originale, la standardisation se fera au traitement
                                ];
                            }, $concept->xpath('.//skos:altLabel')),
                            'exactMatches' => array_merge(
                                array_map('strval', $concept->xpath('.//skos:exactMatch/@rdf:resource')),
                                array_map('strval', $concept->xpath('./skos:exactMatch/@rdf:resource'))
                            ),
                            'closeMatches' => array_merge(
                                array_map('strval', $concept->xpath('.//skos:closeMatch/@rdf:resource')),
                                array_map('strval', $concept->xpath('./skos:closeMatch/@rdf:resource'))
                            ),
                            'broadMatches' => array_merge(
                                array_map('strval', $concept->xpath('.//skos:broadMatch/@rdf:resource')),
                                array_map('strval', $concept->xpath('./skos:broadMatch/@rdf:resource'))
                            ),
                            'narrowMatches' => array_merge(
                                array_map('strval', $concept->xpath('.//skos:narrowMatch/@rdf:resource')),
                                array_map('strval', $concept->xpath('./skos:narrowMatch/@rdf:resource'))
                            ),
                            'relatedMatches' => array_merge(
                                array_map('strval', $concept->xpath('.//skos:relatedMatch/@rdf:resource')),
                                array_map('strval', $concept->xpath('./skos:relatedMatch/@rdf:resource'))
                            )
                        ];
                    }

                    // Créer un mapping URI -> ID de terme
                    $uriToTermMap = [];
                    foreach ($relationships as $rel) {
                        if (!empty($rel['uri'])) {
                            $uriToTermMap[$rel['uri']] = $rel['termId'];
                        }
                    }

                    // Traiter les relations entre termes
                    foreach ($relationships as $rel) {
                        $termId = $rel['termId'];
                        $term = Term::find($termId);

                        if (!$term) {
                            continue;
                        }

                        // Traiter les termes génériques (broader)
                        foreach ($rel['broaderUris'] as $broaderUri) {
                            if (isset($uriToTermMap[$broaderUri])) {
                                $broaderId = $uriToTermMap[$broaderUri];
                                // Vérifier si la relation n'existe pas déjà
                                $exists = DB::table('term_hierarchical_relations')
                                    ->where('broader_term_id', $broaderId)
                                    ->where('narrower_term_id', $termId)
                                    ->exists();

                                if (!$exists) {
                                    DB::table('term_hierarchical_relations')->insert([
                                        'broader_term_id' => $broaderId,
                                        'narrower_term_id' => $termId,
                                        'relation_type' => 'generic', // type par défaut
                                        'created_at' => now(),
                                        'updated_at' => now()
                                    ]);
                                    $stats['relationships']++;
                                }
                            }
                        }

                        // Traiter les termes spécifiques (narrower)
                        foreach ($rel['narrowerUris'] as $narrowerUri) {
                            if (isset($uriToTermMap[$narrowerUri])) {
                                $narrowerId = $uriToTermMap[$narrowerUri];
                                // Vérifier si la relation n'existe pas déjà
                                $exists = DB::table('term_hierarchical_relations')
                                    ->where('broader_term_id', $termId)
                                    ->where('narrower_term_id', $narrowerId)
                                    ->exists();

                                if (!$exists) {
                                    DB::table('term_hierarchical_relations')->insert([
                                        'broader_term_id' => $termId,
                                        'narrower_term_id' => $narrowerId,
                                        'relation_type' => 'generic', // type par défaut
                                        'created_at' => now(),
                                        'updated_at' => now()
                                    ]);
                                    $stats['relationships']++;
                                }
                            }
                        }

                        // Traiter les termes associés (related)
                        foreach ($rel['relatedUris'] as $relatedUri) {
                            if (isset($uriToTermMap[$relatedUri])) {
                                $relatedId = $uriToTermMap[$relatedUri];
                                // Éviter les duplications (vérifier dans les deux sens)
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
                                    $stats['relationships']++;
                                }
                            }
                        }

                        // Traiter les non-descripteurs (altLabel)
                        foreach ($rel['altLabels'] as $altLabel) {                            $label = $altLabel['label'];
                            $rawLang = $altLabel['lang'] ?: $term->language; // Utiliser la langue du terme si non spécifiée

                            // Standardiser le code de langue
                            $lang = $this->standardizeLanguageCode($rawLang);

                            // Tronquer le label si nécessaire (limité à 100 caractères comme dans la migration)
                            $label = $this->truncateForDatabase($label, 100, true, 'non_descriptor_label');

                            // Vérifier si le non-descripteur n'existe pas déjà
                            $exists = NonDescriptor::where('non_descriptor_label', $label)
                                ->where('language', $lang)
                                ->exists();

                            if (!$exists && !empty($label)) {
                                NonDescriptor::create([
                                    'term_id' => $termId,
                                    'non_descriptor_label' => $label,
                                    'language' => $lang
                                ]);
                                $stats['relationships']++;
                            }
                        }

                        // Traiter les alignements externes
                        $this->processExternalAlignments($term, [
                            'exactMatches' => ['uris' => $rel['exactMatches'], 'type' => 'exactMatch'],
                            'closeMatches' => ['uris' => $rel['closeMatches'], 'type' => 'closeMatch'],
                            'broadMatches' => ['uris' => $rel['broadMatches'], 'type' => 'broadMatch'],
                            'narrowMatches' => ['uris' => $rel['narrowMatches'], 'type' => 'narrowMatch'],
                            'relatedMatches' => ['uris' => $rel['relatedMatches'], 'type' => 'relatedMatch']
                        ]);
                    }

                    // Si tout s'est bien passé, valider la transaction
                    DB::commit();

                    Log::info('Import RDF terminé avec succès', ['stats' => $stats]);

                    // Message de succès avec statistiques
                    $message = "Import RDF réussi. {$stats['created']} termes créés, {$stats['updated']} termes mis à jour, {$stats['relationships']} relations établies, {$stats['errors']} erreurs.";
                    return redirect()->route('thesaurus.export-import')->with('success', $message);

                } catch (\Exception $e) {
                    // Erreur pendant le traitement, annuler les modifications
                    DB::rollBack();
                    Log::error('Erreur lors de l\'import RDF: ' . $e->getMessage(), [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    return redirect()->back()->with('error', 'Erreur lors de l\'import RDF: ' . $e->getMessage());
                }
            } catch (\Exception $e) {
                // Erreur de parsing XML
                Log::error('Erreur de parsing du fichier RDF: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->back()->with('error', 'Le fichier n\'est pas un document RDF valide: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            // Erreur générale
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
     * Détecte la structure du fichier RDF
     *
     * @param \SimpleXMLElement $xml
     * @return array Informations sur la structure RDF
     */
    private function detectRdfStructure(\SimpleXMLElement $xml)
    {
        $structure = [
            'structure' => 'standard',
            'conceptPath' => '',
            'namespaces' => []
        ];

        // Tester les différentes structures possibles
        $concepts = $xml->xpath('//skos:Concept');
        if (count($concepts) > 0) {
            $structure['structure'] = 'standard';
            return $structure;
        }

        $concepts = $xml->xpath('//rdf:Description[rdf:type/@rdf:resource="http://www.w3.org/2004/02/skos/core#Concept"]');
        if (count($concepts) > 0) {
            $structure['structure'] = 'standard';
            return $structure;
        }

        // Structure alternative
        $concepts = $xml->xpath('//rdf:Description[skos:prefLabel]');
        if (count($concepts) > 0) {
            $structure['structure'] = 'alternate';
            $structure['conceptPath'] = '//rdf:Description[skos:prefLabel]';
            return $structure;
        }

        // Vérifier les namespaces
        $namespaces = $xml->getNamespaces(true);
        $structure['namespaces'] = $namespaces;

        // Si on ne peut pas déterminer une structure standard
        $structure['structure'] = 'unknown';
        return $structure;
    }

    /**
     * Convertit un code de langue étendu (fr-FR) en code court (fr) et vérifie sa validité
     *
     * @param string $langCode Code de langue original
     * @return string Code de langue standardisé
     */
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
     * Tronque une chaîne de caractères à la longueur spécifiée
     *
     * @param string $text Texte à tronquer
     * @param int $maxLength Longueur maximale
     * @param bool $logWarning Enregistrer un avertissement si tronqué
     * @param string $fieldName Nom du champ pour le log
     * @return string Texte tronqué
     */
    private function truncateForDatabase($text, $maxLength, $logWarning = true, $fieldName = 'field')
    {
        if (empty($text)) {
            return $text;
        }

        if (strlen($text) > $maxLength) {
            if ($logWarning) {
                Log::warning("Texte tronqué car trop long pour la base de données", [
                    'field' => $fieldName,
                    'original_length' => strlen($text),
                    'max_length' => $maxLength,
                    'original_text' => substr($text, 0, 200) . (strlen($text) > 200 ? '...' : ''),
                    'truncated_text' => substr($text, 0, $maxLength)
                ]);
            }
            return substr($text, 0, $maxLength);
        }

        return $text;
    }

    /**
     * Génère le contenu XML SKOS
     */
    private function generateSkosXml($terms)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:skos="http://www.w3.org/2004/02/skos/core#" xmlns:dc="http://purl.org/dc/elements/1.1/"></rdf:RDF>');

        foreach ($terms as $term) {
            $concept = $xml->addChild('skos:Concept');
            $concept->addAttribute('rdf:about', url('/terms/' . $term->id));

            $prefLabel = $concept->addChild('skos:prefLabel', htmlspecialchars($term->preferred_label));
            $prefLabel->addAttribute('xml:lang', $term->language);

            // Ajouter les non-descripteurs
            foreach ($term->nonDescriptors as $nonDescriptor) {
                $altLabel = $concept->addChild('skos:altLabel', htmlspecialchars($nonDescriptor->non_descriptor_label));
                $altLabel->addAttribute('xml:lang', $term->language);
            }

            if ($term->scope_note) {
                $concept->addChild('skos:scopeNote', htmlspecialchars($term->scope_note));
            }
        }

        return $xml->asXML();
    }

    /**
     * Génère le contenu CSV
     */
    private function generateCsvContent($terms)
    {
        $csv = "ID,Preferred Label,Language,Scope Note,Non Descriptors\n";

        foreach ($terms as $term) {
            $nonDescriptors = $term->nonDescriptors->pluck('non_descriptor_label')->implode(';');
            $csv .= sprintf(
                "%d,\"%s\",\"%s\",\"%s\",\"%s\"\n",
                $term->id,
                str_replace('"', '""', $term->preferred_label),
                $term->language,
                str_replace('"', '""', $term->scope_note ?? ''),
                str_replace('"', '""', $nonDescriptors)
            );
        }

        return $csv;
    }

    /**
     * Génère le contenu RDF XML
     */
    private function generateRdfXml($terms)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" xmlns:dc="http://purl.org/dc/elements/1.1/"></rdf:RDF>');

        foreach ($terms as $term) {
            $description = $xml->addChild('rdf:Description');
            $description->addAttribute('rdf:about', url('/terms/' . $term->id));

            $description->addChild('rdfs:label', htmlspecialchars($term->preferred_label));
            $description->addChild('dc:language', $term->language);

            if ($term->scope_note) {
                $description->addChild('rdfs:comment', htmlspecialchars($term->scope_note));
            }
        }

        return $xml->asXML();
    }

    /**
     * Analyse un fichier d'import pour prévisualisation
     */
    private function analyzeImportFile($file, $format)
    {
        switch ($format) {
            case 'csv':
                return $this->analyzeCsvFile($file);
            case 'skos':
                return $this->analyzeSkosFile($file);
            case 'rdf':
                return $this->analyzeRdfFile($file);
            default:
                return null;
        }
    }

    /**
     * Analyse un fichier CSV
     */
    private function analyzeCsvFile($file)
    {
        $content = file_get_contents($file->getPathname());
        $lines = explode("\n", $content);
        $header = str_getcsv(array_shift($lines));

        $data = [];
        $count = 0;
        foreach ($lines as $line) {
            if (trim($line) && $count < 10) { // Limite à 10 lignes pour la préview
                $data[] = array_combine($header, str_getcsv($line));
                $count++;
            }
        }

        return [
            'format' => 'csv',
            'total_lines' => count($lines),
            'header' => $header,
            'sample_data' => $data,
            'estimated_terms' => count($lines) - 1
        ];
    }

    /**
     * Analyse un fichier SKOS
     */
    private function analyzeSkosFile($file)
    {
        $content = file_get_contents($file->getPathname());
        $xml = simplexml_load_string($content);

        if (!$xml) {
            return null;
        }

        $concepts = $xml->xpath('//skos:Concept');
        $sampleData = [];

        foreach (array_slice($concepts, 0, 10) as $concept) {
            $prefLabels = $concept->xpath('skos:prefLabel');
            $altLabels = $concept->xpath('skos:altLabel');

            $sampleData[] = [
                'preferred_label' => (string) ($prefLabels[0] ?? ''),
                'alternative_labels' => array_map('strval', $altLabels),
                'about' => (string) $concept['about']
            ];
        }

        return [
            'format' => 'skos',
            'total_concepts' => count($concepts),
            'sample_data' => $sampleData,
            'estimated_terms' => count($concepts)
        ];
    }

    /**
     * Analyse un fichier RDF
     */
    private function analyzeRdfFile($file)
    {
        $content = file_get_contents($file->getPathname());
        $xml = simplexml_load_string($content);

        if (!$xml) {
            return null;
        }

        $descriptions = $xml->xpath('//rdf:Description');
        $sampleData = [];

        foreach (array_slice($descriptions, 0, 10) as $description) {
            $labels = $description->xpath('rdfs:label');
            $comments = $description->xpath('rdfs:comment');

            $sampleData[] = [
                'label' => (string) ($labels[0] ?? ''),
                'comment' => (string) ($comments[0] ?? ''),
                'about' => (string) $description['about']
            ];
        }

        return [
            'format' => 'rdf',
            'total_descriptions' => count($descriptions),
            'sample_data' => $sampleData,
            'estimated_terms' => count($descriptions)
        ];
    }

    /**
     * Traite un fichier d'import
     */
    private function processImportFile($file, $format, $mode)
    {
        switch ($format) {
            case 'csv':
                return $this->processCsvImport($file, $mode);
            case 'skos':
                return $this->processSkosImport($file, $mode);
            case 'rdf':
                return $this->processRdfImport($file, $mode);
            default:
                return ['success' => false, 'message' => 'Format non supporté'];
        }
    }

    /**
     * Traite l'import CSV
     */
    private function processCsvImport($file, $mode)
    {
        try {
            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);
            $header = str_getcsv(array_shift($lines));

            $imported = 0;
            $updated = 0;
            $errors = [];

            if ($mode === 'replace') {
                Term::truncate();
            }

            foreach ($lines as $lineNumber => $line) {
                if (!trim($line)) continue;

                $data = array_combine($header, str_getcsv($line));

                if (empty($data['Preferred Label'])) {
                    $errors[] = "Ligne " . ($lineNumber + 2) . ": Label requis";
                    continue;
                }

                $existingTerm = Term::where('preferred_label', $data['Preferred Label'])->first();

                if ($existingTerm) {
                    if (in_array($mode, ['update', 'merge'])) {
                        $existingTerm->update([
                            'scope_note' => $data['Scope Note'] ?? null,
                            'language' => $data['Language'] ?? 'fr'
                        ]);
                        $updated++;
                    }
                } else {
                    if (in_array($mode, ['add', 'merge', 'replace'])) {
                        Term::create([
                            'preferred_label' => $data['Preferred Label'],
                            'scope_note' => $data['Scope Note'] ?? null,
                            'language' => $data['Language'] ?? 'fr'
                        ]);
                        $imported++;
                    }
                }
            }

            $message = "Import terminé: {$imported} termes ajoutés, {$updated} mis à jour";
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " erreurs détectées.";
            }

            return [
                'success' => true,
                'message' => $message,
                'stats' => "{$imported} ajoutés, {$updated} mis à jour"
            ];

        } catch (\Exception $e) {
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

            $concepts = $xml->xpath('//skos:Concept');
            $imported = 0;
            $updated = 0;

            if ($mode === 'replace') {
                Term::truncate();
            }

            foreach ($concepts as $concept) {
                $prefLabels = $concept->xpath('skos:prefLabel');
                if (empty($prefLabels)) continue;

                $preferredLabel = (string) $prefLabels[0];
                $scopeNotes = $concept->xpath('skos:scopeNote');
                $scopeNote = !empty($scopeNotes) ? (string) $scopeNotes[0] : null;

                $existingTerm = Term::where('preferred_label', $preferredLabel)->first();

                if ($existingTerm) {
                    if (in_array($mode, ['update', 'merge'])) {
                        $existingTerm->update(['scope_note' => $scopeNote]);
                        $updated++;
                    }
                } else {
                    if (in_array($mode, ['add', 'merge', 'replace'])) {
                        Term::create([
                            'preferred_label' => $preferredLabel,
                            'scope_note' => $scopeNote,
                            'language' => 'fr'
                        ]);
                        $imported++;
                    }
                }
            }

            return [
                'success' => true,
                'message' => "Import SKOS terminé: {$imported} termes ajoutés, {$updated} mis à jour",
                'stats' => "{$imported} ajoutés, {$updated} mis à jour"
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'import SKOS: ' . $e->getMessage()];
        }
    }

    /**
     * Traite l'import RDF
     */
    private function processRdfImport($file, $mode)
    {
        try {
            $content = file_get_contents($file->getPathname());
            $xml = simplexml_load_string($content);

            if (!$xml) {
                return ['success' => false, 'message' => 'Fichier RDF invalide'];
            }

            $descriptions = $xml->xpath('//rdf:Description');
            $imported = 0;
            $updated = 0;

            if ($mode === 'replace') {
                Term::truncate();
            }

            foreach ($descriptions as $description) {
                $labels = $description->xpath('rdfs:label');
                if (empty($labels)) continue;

                $label = (string) $labels[0];
                $comments = $description->xpath('rdfs:comment');
                $comment = !empty($comments) ? (string) $comments[0] : null;

                $existingTerm = Term::where('preferred_label', $label)->first();

                if ($existingTerm) {
                    if (in_array($mode, ['update', 'merge'])) {
                        $existingTerm->update(['scope_note' => $comment]);
                        $updated++;
                    }
                } else {
                    if (in_array($mode, ['add', 'merge', 'replace'])) {
                        Term::create([
                            'preferred_label' => $label,
                            'scope_note' => $comment,
                            'language' => 'fr'
                        ]);
                        $imported++;
                    }
                }
            }

            return [
                'success' => true,
                'message' => "Import RDF terminé: {$imported} termes ajoutés, {$updated} mis à jour",
                'stats' => "{$imported} ajoutés, {$updated} mis à jour"
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur lors de l\'import RDF: ' . $e->getMessage()];
        }
    }

    /**
     * Construit la requête de recherche pour l'export
     */
    private function buildSearchQuery(Request $request)
    {
        $query = Term::query();

        // Ajouter des filtres si nécessaires
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('preferred_label', 'LIKE', "%{$search}%")
                  ->orWhere('scope_note', 'LIKE', "%{$search}%");
        }

        if ($request->has('language')) {
            $query->where('language', $request->input('language'));
        }

        return $query;
    }
}
