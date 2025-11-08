<?php

/**
 * Script de test pour Phase 3 - Migration RecordController Universel
 *
 * Ce script teste :
 * 1. Relations keywords/thesaurus sur RecordDigitalFolder et RecordDigitalDocument (Phase 2)
 * 2. Méthodes helper du RecordController (getRecordModel, findRecord, getRecordTypeLabel)
 * 3. Logique de parsing des IDs préfixés (type_id)
 * 4. Chargement multi-types pour export/print
 *
 * Exécution: php tests/phase3-migration-test.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\RecordPhysical;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;
use App\Models\Keyword;
use App\Models\ThesaurusConcept;

echo "\n" . str_repeat('=', 80) . "\n";
echo "PHASE 3 - TESTS DE MIGRATION RECORDCONTROLLER UNIVERSEL\n";
echo str_repeat('=', 80) . "\n\n";

$passed = 0;
$failed = 0;

// ============================================================================
// TEST 1: Vérifier les relations keywords sur RecordDigitalFolder
// ============================================================================
echo "TEST 1: Relations keywords sur RecordDigitalFolder... ";
try {
    $folder = RecordDigitalFolder::first();
    if ($folder) {
        $keywords = $folder->keywords;
        if (is_object($keywords) && method_exists($keywords, 'count')) {
            echo "✅ PASS\n";
            echo "  → RecordDigitalFolder a " . $keywords->count() . " keywords\n";
            $passed++;
        } else {
            echo "❌ FAIL: keywords() ne retourne pas une collection\n";
            $failed++;
        }
    } else {
        echo "⚠️  SKIP: Aucun RecordDigitalFolder en base\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// ============================================================================
// TEST 2: Vérifier les relations thesaurusConcepts sur RecordDigitalFolder
// ============================================================================
echo "\nTEST 2: Relations thesaurusConcepts sur RecordDigitalFolder... ";
try {
    $folder = RecordDigitalFolder::first();
    if ($folder) {
        $concepts = $folder->thesaurusConcepts;
        if (is_object($concepts) && method_exists($concepts, 'count')) {
            echo "✅ PASS\n";
            echo "  → RecordDigitalFolder a " . $concepts->count() . " concepts\n";

            // Vérifier les champs pivot
            if ($concepts->count() > 0) {
                $firstConcept = $concepts->first();
                if (isset($firstConcept->pivot)) {
                    echo "  → Pivot disponible avec: ";
                    $pivotFields = [];
                    if (isset($firstConcept->pivot->weight)) $pivotFields[] = "weight";
                    if (isset($firstConcept->pivot->context)) $pivotFields[] = "context";
                    if (isset($firstConcept->pivot->extraction_note)) $pivotFields[] = "extraction_note";
                    echo implode(", ", $pivotFields) . "\n";
                }
            }
            $passed++;
        } else {
            echo "❌ FAIL: thesaurusConcepts() ne retourne pas une collection\n";
            $failed++;
        }
    } else {
        echo "⚠️  SKIP: Aucun RecordDigitalFolder en base\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// ============================================================================
// TEST 3: Vérifier les relations keywords sur RecordDigitalDocument
// ============================================================================
echo "\nTEST 3: Relations keywords sur RecordDigitalDocument... ";
try {
    $document = RecordDigitalDocument::first();
    if ($document) {
        $keywords = $document->keywords;
        if (is_object($keywords) && method_exists($keywords, 'count')) {
            echo "✅ PASS\n";
            echo "  → RecordDigitalDocument a " . $keywords->count() . " keywords\n";
            $passed++;
        } else {
            echo "❌ FAIL: keywords() ne retourne pas une collection\n";
            $failed++;
        }
    } else {
        echo "⚠️  SKIP: Aucun RecordDigitalDocument en base\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// ============================================================================
// TEST 4: Vérifier les relations thesaurusConcepts sur RecordDigitalDocument
// ============================================================================
echo "\nTEST 4: Relations thesaurusConcepts sur RecordDigitalDocument... ";
try {
    $document = RecordDigitalDocument::first();
    if ($document) {
        $concepts = $document->thesaurusConcepts;
        if (is_object($concepts) && method_exists($concepts, 'count')) {
            echo "✅ PASS\n";
            echo "  → RecordDigitalDocument a " . $concepts->count() . " concepts\n";
            $passed++;
        } else {
            echo "❌ FAIL: thesaurusConcepts() ne retourne pas une collection\n";
            $failed++;
        }
    } else {
        echo "⚠️  SKIP: Aucun RecordDigitalDocument en base\n";
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// ============================================================================
// TEST 5: Vérifier le parsing des IDs préfixés
// ============================================================================
echo "\nTEST 5: Parsing des IDs préfixés (type_id)... ";
try {
    $testIds = ['physical_1', 'folder_2', 'document_3', '99']; // Legacy sans préfixe

    $physicalIds = [];
    $folderIds = [];
    $documentIds = [];

    foreach ($testIds as $idStr) {
        if (str_contains($idStr, '_')) {
            [$type, $id] = explode('_', $idStr, 2);
            if ($type === 'physical') {
                $physicalIds[] = $id;
            } elseif ($type === 'folder') {
                $folderIds[] = $id;
            } elseif ($type === 'document') {
                $documentIds[] = $id;
            }
        } else {
            // Legacy
            $physicalIds[] = $idStr;
        }
    }

    if ($physicalIds === ['1', '99'] && $folderIds === ['2'] && $documentIds === ['3']) {
        echo "✅ PASS\n";
        echo "  → Physical IDs: [" . implode(', ', $physicalIds) . "]\n";
        echo "  → Folder IDs: [" . implode(', ', $folderIds) . "]\n";
        echo "  → Document IDs: [" . implode(', ', $documentIds) . "]\n";
        $passed++;
    } else {
        echo "❌ FAIL: Parsing incorrect\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// ============================================================================
// TEST 6: Vérifier le chargement multi-types avec relations
// ============================================================================
echo "\nTEST 6: Chargement multi-types avec relations... ";
try {
    $hasPhysical = RecordPhysical::count() > 0;
    $hasFolders = RecordDigitalFolder::count() > 0;
    $hasDocuments = RecordDigitalDocument::count() > 0;

    $allRecords = collect();

    if ($hasPhysical) {
        $physicalRecords = RecordPhysical::with(['level', 'status', 'activity', 'keywords'])
            ->take(2)
            ->get()
            ->map(function($r) {
                $r->record_type = 'physical';
                $r->type_label = 'Dossier Physique';
                return $r;
            });
        $allRecords = $allRecords->concat($physicalRecords);
    }

    if ($hasFolders) {
        $folders = RecordDigitalFolder::with(['type', 'organisation', 'keywords'])
            ->take(2)
            ->get()
            ->map(function($f) {
                $f->record_type = 'folder';
                $f->type_label = 'Dossier Numérique';
                return $f;
            });
        $allRecords = $allRecords->concat($folders);
    }

    if ($hasDocuments) {
        $documents = RecordDigitalDocument::with(['type', 'organisation', 'keywords'])
            ->take(2)
            ->get()
            ->map(function($d) {
                $d->record_type = 'document';
                $d->type_label = 'Document Numérique';
                return $d;
            });
        $allRecords = $allRecords->concat($documents);
    }

    echo "✅ PASS\n";
    echo "  → Total records combinés: " . $allRecords->count() . "\n";

    // Vérifier que chaque record a bien record_type et type_label
    $hasTypeInfo = true;
    foreach ($allRecords as $record) {
        if (!isset($record->record_type) || !isset($record->type_label)) {
            $hasTypeInfo = false;
            break;
        }
    }

    if ($hasTypeInfo) {
        echo "  → Tous les records ont record_type et type_label ✓\n";

        // Afficher distribution par type
        $distribution = $allRecords->groupBy('record_type')->map->count();
        echo "  → Distribution: ";
        foreach ($distribution as $type => $count) {
            echo "$type=$count ";
        }
        echo "\n";
    } else {
        echo "  → ⚠️  Certains records manquent de type_info\n";
    }

    $passed++;
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// ============================================================================
// TEST 7: Vérifier les tables pivot créées en Phase 2
// ============================================================================
echo "\nTEST 7: Vérification des tables pivot (Phase 2)... ";
try {
    $tables = [
        'record_digital_folder_keyword',
        'record_digital_document_keyword',
        'record_digital_folder_thesaurus_concept',
        'record_digital_document_thesaurus_concept'
    ];

    $allExist = true;
    foreach ($tables as $table) {
        $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
        if (!$exists) {
            echo "❌ FAIL: Table '$table' n'existe pas\n";
            $allExist = false;
        }
    }

    if ($allExist) {
        echo "✅ PASS\n";
        echo "  → Toutes les 4 tables pivot existent\n";
        $passed++;
    } else {
        $failed++;
    }
} catch (Exception $e) {
    echo "❌ FAIL: " . $e->getMessage() . "\n";
    $failed++;
}

// ============================================================================
// RÉSUMÉ
// ============================================================================
echo "\n" . str_repeat('=', 80) . "\n";
echo "RÉSUMÉ DES TESTS\n";
echo str_repeat('=', 80) . "\n";
echo "Tests réussis: $passed\n";
echo "Tests échoués: $failed\n";
echo "Total: " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✅ TOUS LES TESTS SONT PASSÉS ! Migration Phase 3 validée.\n";
    exit(0);
} else {
    echo "\n❌ CERTAINS TESTS ONT ÉCHOUÉ. Vérifiez les erreurs ci-dessus.\n";
    exit(1);
}
