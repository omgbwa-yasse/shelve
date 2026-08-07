<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use DOMElement;
use DOMNode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RdfThesaurusImporter
{
    private const RDF_ABOUT = 'rdf:about';
    private const RDF_RESOURCE = 'rdf:resource';
    private const XML_LANG = 'xml:lang';

    private $namespaces = [
        'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        'skos' => 'http://www.w3.org/2004/02/skos/core#',
        'dc' => 'http://purl.org/dc/elements/1.1/',
        'dct' => 'http://purl.org/dc/terms/',
        'xl' => 'http://www.w3.org/2008/05/skos-xl#',
        'iso-thes' => 'http://purl.org/iso25964/skos-thes#',
        'ginco' => 'http://data.culture.fr/thesaurus/ginco/ns/',
    ];

    /**
     * Import a SKOS RDF file into the thesaurus database
     */
    public function importRdfFile(string $filePath): array
    {
        $stats = [
            'schemes' => 0,
            'concepts' => 0,
            'xl_labels' => 0,
            'alternative_labels' => 0,
            'relations' => 0,
            'errors' => []
        ];

        try {
            $doc = new DOMDocument();
            $doc->load($filePath);
            $xpath = new DOMXPath($doc);

            // Register namespaces
            foreach ($this->namespaces as $prefix => $uri) {
                $xpath->registerNamespace($prefix, $uri);
            }

            // Import concept schemes first
            $schemes = $xpath->query('//skos:ConceptScheme');
            foreach ($schemes as $scheme) {
                if ($scheme instanceof DOMElement) {
                    $this->importConceptScheme($scheme, $xpath, $stats);
                }
            }

            // Import concepts
            $concepts = $xpath->query('//skos:Concept');
            foreach ($concepts as $concept) {
                if ($concept instanceof DOMElement) {
                    $this->importConcept($concept, $xpath, $stats);
                }
            }

            // Import relations in a second pass
            foreach ($concepts as $concept) {
                if ($concept instanceof DOMElement) {
                    $this->importConceptRelations($concept, $xpath, $stats);
                }
            }

        } catch (\Exception $e) {
            $stats['errors'][] = "Import failed: " . $e->getMessage();
            Log::error('RDF Import Error', ['error' => $e->getMessage()]);
        }

        return $stats;
    }

    private function importConceptScheme(DOMElement $schemeNode, DOMXPath $xpath, array &$stats): void
    {
        $uri = $schemeNode->getAttribute(self::RDF_ABOUT);

        // Check if scheme already exists
        $existingScheme = DB::table('concept_schemes')->where('uri', $uri)->first();
        if ($existingScheme) {
            return;
        }

        $title = $this->getNodeValue($xpath, $schemeNode, 'dc:title');
        $description = $this->getNodeValue($xpath, $schemeNode, 'dc:description');
        $creator = $this->getNodeValue($xpath, $schemeNode, 'dc:creator');
        $publisher = $this->getNodeValue($xpath, $schemeNode, 'dc:publisher');
        $type = $this->getNodeValue($xpath, $schemeNode, 'dc:type');
        $rights = $this->getNodeValue($xpath, $schemeNode, 'dc:rights');
        $language = $this->getNodeValue($xpath, $schemeNode, 'dc:language') ?: 'fr';
        $modified = $this->getNodeValue($xpath, $schemeNode, 'dct:modified');

        // Extract identifier from URI (e.g., T3 from the URI)
        $identifier = $this->extractIdentifierFromUri($uri);

        DB::table('concept_schemes')->insert([
            'uri' => $uri,
            'identifier' => $identifier,
            'title' => $title,
            'description' => $description,
            'creator' => $creator,
            'publisher' => $publisher,
            'type' => $type,
            'rights' => $rights,
            'language' => $language,
            'status' => 'active',
            'metadata' => json_encode([
                'modified' => $modified,
                'original_uri' => $uri
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stats['schemes']++;
    }

    private function importConcept(DOMElement $conceptNode, DOMXPath $xpath, array &$stats): void
    {
        $uri = $conceptNode->getAttribute(self::RDF_ABOUT);

        // Check if concept already exists
        $existingConcept = DB::table('concepts')->where('uri', $uri)->first();
        if ($existingConcept) {
            return;
        }

        // Get scheme URI
        $schemeUri = $this->getNodeResource($xpath, $conceptNode, 'skos:inScheme');
        $scheme = DB::table('concept_schemes')->where('uri', $schemeUri)->first();

        if (!$scheme) {
            $stats['errors'][] = "Scheme not found for concept: $uri";
            return;
        }

        $prefLabel = $this->getNodeValue($xpath, $conceptNode, 'skos:prefLabel');
        $language = $this->getNodeAttribute($xpath, $conceptNode, 'skos:prefLabel', self::XML_LANG) ?: 'fr';
        $definition = $this->getNodeValue($xpath, $conceptNode, 'skos:definition');
        $scopeNote = $this->getNodeValue($xpath, $conceptNode, 'skos:scopeNote');
        $notation = $this->extractNotationFromUri($uri);
        $created = $this->getNodeValue($xpath, $conceptNode, 'dct:created');
        $modified = $this->getNodeValue($xpath, $conceptNode, 'dct:modified');
        $isoStatus = $this->getNodeValue($xpath, $conceptNode, 'iso-thes:status');

        // Check if it's a top concept
        $isTopConcept = $this->isTopConcept($schemeUri, $uri, $xpath);

        $conceptId = DB::table('concepts')->insertGetId([
            'concept_scheme_id' => $scheme->id,
            'uri' => $uri,
            'notation' => $notation,
            'preferred_label' => $prefLabel,
            'language' => $language,
            'definition' => $definition,
            'scope_note' => $scopeNote,
            'status' => 'approved',
            'iso_status' => $isoStatus ? (int)$isoStatus : null,
            'is_top_concept' => $isTopConcept,
            'date_created' => $created ? date('Y-m-d H:i:s', strtotime($created)) : null,
            'date_modified' => $modified ? date('Y-m-d H:i:s', strtotime($modified)) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Import XL labels
        $this->importXLLabels($conceptNode, $xpath, $conceptId, $stats);

        // Import alternative labels
        $this->importAlternativeLabels($conceptNode, $xpath, $conceptId, $stats);

        $stats['concepts']++;
    }

    private function importXLLabels(DOMElement $conceptNode, DOMXPath $xpath, int $conceptId, array &$stats): void
    {
        $xlPrefLabels = $xpath->query('.//xl:prefLabel', $conceptNode);
        foreach ($xlPrefLabels as $xlLabel) {
            if ($xlLabel instanceof DOMElement) {
                $labelUri = $xlLabel->getAttribute(self::RDF_RESOURCE);
                if ($labelUri) {
                    // You would need to find the corresponding xl:Label element
                    // This is a simplified version
                    DB::table('xl_labels')->insert([
                        'uri' => $labelUri,
                        'concept_id' => $conceptId,
                        'label_type' => 'prefLabel',
                        'literal_form' => '', // Would need to extract from xl:Label
                        'language' => 'fr',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $stats['xl_labels']++;
                }
            }
        }
    }

    private function importAlternativeLabels(DOMElement $conceptNode, DOMXPath $xpath, int $conceptId, array &$stats): void
    {
        $altLabels = $xpath->query('.//skos:altLabel', $conceptNode);
        foreach ($altLabels as $altLabel) {
            if ($altLabel instanceof DOMElement) {
                $label = $altLabel->nodeValue;
                $language = $altLabel->getAttribute(self::XML_LANG) ?: 'fr';

                DB::table('alternative_labels')->insert([
                    'concept_id' => $conceptId,
                    'label' => $label,
                    'label_type' => 'altLabel',
                    'language' => $language,
                    'relation_type' => 'synonym',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $stats['alternative_labels']++;
            }
        }
    }

    private function importConceptRelations(DOMElement $conceptNode, DOMXPath $xpath, array &$stats): void
    {
        $conceptUri = $conceptNode->getAttribute(self::RDF_ABOUT);
        $concept = DB::table('concepts')->where('uri', $conceptUri)->first();

        if (!$concept) {
            return;
        }

        // Import associative relations (skos:related)
        $relatedConcepts = $xpath->query('.//skos:related', $conceptNode);
        foreach ($relatedConcepts as $related) {
            if ($related instanceof DOMElement) {
                $relatedUri = $related->getAttribute(self::RDF_RESOURCE);
                $relatedConcept = DB::table('concepts')->where('uri', $relatedUri)->first();

                if ($relatedConcept) {
                    // Check if relation already exists
                    $existingRelation = DB::table('associative_relations')
                        ->where(function($query) use ($concept, $relatedConcept) {
                            $query->where('concept1_id', $concept->id)
                                  ->where('concept2_id', $relatedConcept->id);
                        })
                        ->orWhere(function($query) use ($concept, $relatedConcept) {
                            $query->where('concept1_id', $relatedConcept->id)
                                  ->where('concept2_id', $concept->id);
                        })
                        ->first();

                    if (!$existingRelation) {
                        DB::table('associative_relations')->insert([
                            'concept1_id' => $concept->id,
                            'concept2_id' => $relatedConcept->id,
                            'relation_subtype' => 'general',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $stats['relations']++;
                    }
                }
            }
        }

        // Import hierarchical relations would go here (skos:broader, skos:narrower)
        // Similar logic but using hierarchical_relations table
    }

    private function getNodeValue(DOMXPath $xpath, DOMElement $contextNode, string $expression): ?string
    {
        $nodes = $xpath->query($expression, $contextNode);
        return $nodes->length > 0 ? trim($nodes->item(0)->nodeValue) : null;
    }

    private function getNodeResource(DOMXPath $xpath, DOMElement $contextNode, string $expression): ?string
    {
        $nodes = $xpath->query($expression, $contextNode);
        if ($nodes->length > 0) {
            $node = $nodes->item(0);
            if ($node instanceof DOMElement) {
                return $node->getAttribute(self::RDF_RESOURCE);
            }
        }
        return null;
    }

    private function getNodeAttribute(DOMXPath $xpath, DOMElement $contextNode, string $expression, string $attribute): ?string
    {
        $nodes = $xpath->query($expression, $contextNode);
        if ($nodes->length > 0) {
            $node = $nodes->item(0);
            if ($node instanceof DOMElement) {
                return $node->getAttribute($attribute);
            }
        }
        return null;
    }

    private function extractIdentifierFromUri(string $uri): string
    {
        // Extract identifier from URI patterns like .../T3, .../GEO, etc.
        if (preg_match('/\/([^\/]+)$/', $uri, $matches)) {
            return $matches[1];
        }
        return 'UNKNOWN';
    }

    private function extractNotationFromUri(string $uri): ?string
    {
        // Extract notation like T3-139 from URI
        if (preg_match('/\/(T\d+-\d+)$/', $uri, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function isTopConcept(string $schemeUri, string $conceptUri, DOMXPath $xpath): bool
    {
        // Check if this concept is listed as a top concept in the scheme
        $topConcepts = $xpath->query("//skos:ConceptScheme[@rdf:about='$schemeUri']//skos:hasTopConcept[@rdf:resource='$conceptUri']");
        return $topConcepts->length > 0;
    }
}
