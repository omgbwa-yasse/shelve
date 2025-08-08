# Guide d'implémentation du remappage des colonnes Excel/CSV

## Vue d'ensemble

Ce guide explique comment implémenter un système de remappage des colonnes permettant aux utilisateurs de mapper les colonnes de leurs fichiers Excel/CSV vers les champs de la base de données.

## Architecture de la solution

1. **Étape 1** : Upload du fichier et lecture des en-têtes
2. **Étape 2** : Interface de mapping des colonnes
3. **Étape 3** : Import avec le mapping appliqué

## 1. Modification de la vue d'import

Modifiez le fichier `resources/views/records/import.blade.php` :

```php
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h2><i class="bi bi-file-earmark-arrow-down"></i> {{ __('Import Records') }}</h2>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Étape 1: Upload du fichier -->
                <div id="upload-step" class="import-step">
                    <form id="upload-form" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="file" class="form-label">{{ __('File') }}:</label>
                            <input type="file" name="file" id="file" class="form-control" required accept=".xlsx,.csv">
                        </div>
                        <div class="form-group mb-3">
                            <label for="format" class="form-label">{{ __('Format') }}:</label>
                            <select name="format" id="format" class="form-select" required>
                                <option value="">{{ __('Select a format') }}</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>

                        <div class="form-group mb-3 excel-options" style="display: none;">
                            <div class="card">
                                <div class="card-header">{{ __('Import Options') }}</div>
                                <div class="card-body">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="has_headers" id="has_headers" value="1" checked>
                                        <label class="form-check-label" for="has_headers">
                                            {{ __('File has headers') }}
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="update_existing" id="update_existing" value="1">
                                        <label class="form-check-label" for="update_existing">
                                            {{ __('Update existing records') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('records.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            <button type="button" id="analyze-file" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>{{ __('Analyze File') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Étape 2: Mapping des colonnes -->
                <div id="mapping-step" class="import-step" style="display: none;">
                    <h4>{{ __('Column Mapping') }}</h4>
                    <p class="text-muted">{{ __('Map your file columns to database fields') }}</p>
                    
                    <div id="mapping-container">
                        <!-- Sera rempli dynamiquement -->
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" id="back-to-upload" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
                        </button>
                        <button type="button" id="start-import" class="btn btn-success">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i>{{ __('Start Import') }}
                        </button>
                    </div>
                </div>

                <!-- Étape 3: Progression de l'import -->
                <div id="import-step" class="import-step" style="display: none;">
                    <h4>{{ __('Import in Progress') }}</h4>
                    <div class="progress mb-3">
                        <div id="import-progress" class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="import-status"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formatSelect = document.getElementById('format');
    const excelOptions = document.querySelector('.excel-options');
    const uploadStep = document.getElementById('upload-step');
    const mappingStep = document.getElementById('mapping-step');
    const importStep = document.getElementById('import-step');
    
    let fileHeaders = [];
    let uploadedFile = null;

    // Afficher/masquer les options selon le format
    formatSelect.addEventListener('change', function() {
        if (this.value === 'excel' || this.value === 'csv') {
            excelOptions.style.display = 'block';
        } else {
            excelOptions.style.display = 'none';
        }
    });

    // Analyser le fichier
    document.getElementById('analyze-file').addEventListener('click', function() {
        const fileInput = document.getElementById('file');
        const formatInput = document.getElementById('format');
        
        if (!fileInput.files[0] || !formatInput.value) {
            alert('Veuillez sélectionner un fichier et un format');
            return;
        }

        uploadedFile = fileInput.files[0];
        analyzeFile(uploadedFile, formatInput.value);
    });

    // Retour à l'upload
    document.getElementById('back-to-upload').addEventListener('click', function() {
        showStep('upload');
    });

    // Démarrer l'import
    document.getElementById('start-import').addEventListener('click', function() {
        const mapping = collectMapping();
        startImport(mapping);
    });

    function analyzeFile(file, format) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('format', format);
        formData.append('_token', document.querySelector('input[name="_token"]').value);

        fetch('{{ route("records.analyze-file") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fileHeaders = data.headers;
                createMappingInterface(data.headers);
                showStep('mapping');
            } else {
                alert('Erreur lors de l\'analyse du fichier: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'analyse du fichier');
        });
    }

    function createMappingInterface(headers) {
        const container = document.getElementById('mapping-container');
        const dbFields = {
            'code': { label: 'Code', required: true, description: 'Identifiant unique de l\'enregistrement' },
            'name': { label: 'Nom/Titre', required: true, description: 'Nom ou titre de l\'enregistrement' },
            'date_format': { label: 'Format de date', required: false, description: 'Format de la date (Y/M/D)' },
            'start_date': { label: 'Date de début', required: false, description: 'Date de début (YYYY-MM-DD)' },
            'end_date': { label: 'Date de fin', required: false, description: 'Date de fin (YYYY-MM-DD)' },
            'exact_date': { label: 'Date exacte', required: false, description: 'Date exacte (YYYY-MM-DD)' },
            'level': { label: 'Niveau hiérarchique', required: true, description: 'Niveau dans la hiérarchie' },
            'width': { label: 'Largeur', required: false, description: 'Largeur physique' },
            'width_description': { label: 'Description largeur', required: false, description: 'Description de la largeur' },
            'content': { label: 'Contenu', required: false, description: 'Description du contenu' },
            'status': { label: 'Statut', required: true, description: 'Statut de l\'enregistrement' },
            'support': { label: 'Support', required: true, description: 'Type de support physique' },
            'activity': { label: 'Activité', required: true, description: 'Activité associée' },
            'authors': { label: 'Auteurs', required: false, description: 'Auteurs (séparés par des virgules)' },
            'terms': { label: 'Termes', required: false, description: 'Termes/tags (séparés par des virgules)' }
        };

        let html = '<div class="table-responsive"><table class="table table-bordered">';
        html += '<thead><tr>';
        html += '<th>Champ de la base de données</th>';
        html += '<th>Requis</th>';
        html += '<th>Colonne du fichier</th>';
        html += '<th>Aperçu</th>';
        html += '</tr></thead><tbody>';

        Object.entries(dbFields).forEach(([field, config]) => {
            html += '<tr>';
            html += `<td><strong>${config.label}</strong><br><small class="text-muted">${config.description}</small></td>`;
            html += `<td>${config.required ? '<span class="badge bg-danger">Requis</span>' : '<span class="badge bg-secondary">Optionnel</span>'}</td>`;
            html += '<td>';
            html += `<select class="form-select mapping-select" data-field="${field}" ${config.required ? 'required' : ''}>`;
            html += '<option value="">-- Ignorer --</option>';
            
            headers.forEach((header, index) => {
                const selected = header.toLowerCase().includes(field.toLowerCase()) || 
                                field.toLowerCase().includes(header.toLowerCase()) ? 'selected' : '';
                html += `<option value="${index}" ${selected}>${header}</option>`;
            });
            
            html += '</select>';
            html += '</td>';
            html += '<td><span class="preview-text" data-field="' + field + '">--</span></td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;

        // Ajouter les événements pour la prévisualisation
        document.querySelectorAll('.mapping-select').forEach(select => {
            select.addEventListener('change', updatePreview);
        });
    }

    function updatePreview() {
        // Cette fonction pourrait faire un appel AJAX pour obtenir un aperçu des données
        // Pour l'instant, on met juste un placeholder
        document.querySelectorAll('.preview-text').forEach(span => {
            const field = span.dataset.field;
            const select = document.querySelector(`select[data-field="${field}"]`);
            if (select.value) {
                span.textContent = 'Exemple de données...';
            } else {
                span.textContent = '--';
            }
        });
    }

    function collectMapping() {
        const mapping = {};
        document.querySelectorAll('.mapping-select').forEach(select => {
            const field = select.dataset.field;
            const columnIndex = select.value;
            if (columnIndex !== '') {
                mapping[field] = {
                    column_index: parseInt(columnIndex),
                    column_name: fileHeaders[parseInt(columnIndex)]
                };
            }
        });
        return mapping;
    }

    function startImport(mapping) {
        showStep('import');
        
        const formData = new FormData();
        formData.append('file', uploadedFile);
        formData.append('format', document.getElementById('format').value);
        formData.append('mapping', JSON.stringify(mapping));
        formData.append('has_headers', document.getElementById('has_headers').checked ? '1' : '0');
        formData.append('update_existing', document.getElementById('update_existing').checked ? '1' : '0');
        formData.append('_token', document.querySelector('input[name="_token"]').value);

        fetch('{{ route("records.import") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('import-status').innerHTML = 
                    '<div class="alert alert-success">Import terminé avec succès!</div>';
                document.getElementById('import-progress').style.width = '100%';
                setTimeout(() => {
                    window.location.href = '{{ route("records.index") }}';
                }, 2000);
            } else {
                document.getElementById('import-status').innerHTML = 
                    '<div class="alert alert-danger">Erreur: ' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            document.getElementById('import-status').innerHTML = 
                '<div class="alert alert-danger">Erreur lors de l\'import</div>';
        });
    }

    function showStep(step) {
        document.querySelectorAll('.import-step').forEach(el => el.style.display = 'none');
        document.getElementById(step + '-step').style.display = 'block';
    }
});
</script>
@endpush
```

## 2. Modification du contrôleur

Ajoutez ces méthodes au `RecordController` :

```php
<?php

/**
 * Analyser un fichier pour extraire les en-têtes
 */
public function analyzeFile(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,csv',
        'format' => 'required|in:excel,csv',
    ]);

    try {
        $file = $request->file('file');
        $format = $request->input('format');
        $headers = [];

        if ($format === 'excel') {
            $headers = $this->extractExcelHeaders($file);
        } else if ($format === 'csv') {
            $headers = $this->extractCsvHeaders($file);
        }

        return response()->json([
            'success' => true,
            'headers' => $headers,
            'preview' => [] // Optionnel: retourner quelques lignes d'exemple
        ]);

    } catch (\Exception $e) {
        Log::error('Erreur lors de l\'analyse du fichier: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'analyse du fichier: ' . $e->getMessage()
        ], 400);
    }
}

/**
 * Extraire les en-têtes d'un fichier Excel
 */
private function extractExcelHeaders($file)
{
    $reader = Excel::toCollection(null, $file)->first();
    if ($reader->isEmpty()) {
        throw new \Exception('Le fichier est vide');
    }

    return $reader->first()->toArray();
}

/**
 * Extraire les en-têtes d'un fichier CSV
 */
private function extractCsvHeaders($file)
{
    $handle = fopen($file->getPathname(), 'r');
    if (!$handle) {
        throw new \Exception('Impossible de lire le fichier CSV');
    }

    $headers = fgetcsv($handle);
    fclose($handle);

    if (!$headers) {
        throw new \Exception('Impossible de lire les en-têtes du fichier CSV');
    }

    return $headers;
}

/**
 * Import avec mapping personnalisé
 */
public function import(Request $request)
{
    $this->authorize('import', Record::class);
    
    $request->validate([
        'file' => 'required|file|mimes:xlsx,csv',
        'format' => 'required|in:excel,csv',
        'mapping' => 'required|json',
    ]);

    try {
        $file = $request->file('file');
        $format = $request->input('format');
        $mapping = json_decode($request->input('mapping'), true);
        $hasHeaders = $request->input('has_headers', false);
        $updateExisting = $request->input('update_existing', false);

        // Créer un nouveau Dolly pour cet import
        $dolly = Dolly::create([
            'name' => 'Import ' . now()->format('Y-m-d H:i:s'),
            'description' => 'Import automatique avec mapping personnalisé',
            'type_id' => 1,
        ]);

        // Importer avec le mapping
        $import = new RecordsImport($dolly, $mapping, $hasHeaders, $updateExisting);
        Excel::import($import, $file);

        return response()->json([
            'success' => true,
            'message' => 'Import terminé avec succès',
            'dolly_id' => $dolly->id,
            'records_count' => $import->getImportedCount()
        ]);

    } catch (\Exception $e) {
        Log::error('Erreur lors de l\'import: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'import: ' . $e->getMessage()
        ], 500);
    }
}
```

## 3. Modification de la classe d'import

Créez/modifiez `app/Imports/RecordsImport.php` :

```php
<?php

namespace App\Imports;

use App\Models\Container;
use App\Models\Dolly;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\Activity;
use App\Models\Author;
use App\Models\User;
use App\Models\ThesaurusConcept;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RecordsImport implements ToModel, WithBatchInserts, WithChunkReading
{
    protected $dolly;
    protected $mapping;
    protected $hasHeaders;
    protected $updateExisting;
    protected $currentRow = 0;
    protected $importedCount = 0;

    public function __construct(Dolly $dolly, array $mapping, bool $hasHeaders = true, bool $updateExisting = false)
    {
        $this->dolly = $dolly;
        $this->mapping = $mapping;
        $this->hasHeaders = $hasHeaders;
        $this->updateExisting = $updateExisting;
    }

    public function model(array $row)
    {
        $this->currentRow++;
        
        // Ignorer la première ligne si elle contient les en-têtes
        if ($this->hasHeaders && $this->currentRow === 1) {
            return null;
        }

        try {
            // Mapper les données selon le mapping fourni
            $mappedData = $this->mapRowData($row);
            
            // Valider les données requises
            if (!$this->validateRequiredFields($mappedData)) {
                Log::warning("Ligne {$this->currentRow} ignorée: champs requis manquants", $mappedData);
                return null;
            }

            // Créer ou mettre à jour l'enregistrement
            $record = $this->createOrUpdateRecord($mappedData);
            
            if ($record) {
                // Attacher l'enregistrement au Dolly
                $this->dolly->records()->attach($record->id);
                
                // Traiter les auteurs et termes
                $this->processAuthors($record, $mappedData);
                $this->processTerms($record, $mappedData);
                
                $this->importedCount++;
            }

            return $record;

        } catch (\Exception $e) {
            Log::error("Erreur ligne {$this->currentRow}: " . $e->getMessage(), [
                'row_data' => $row,
                'mapped_data' => $mappedData ?? []
            ]);
            return null;
        }
    }

    protected function mapRowData(array $row): array
    {
        $mappedData = [];
        
        foreach ($this->mapping as $field => $config) {
            $columnIndex = $config['column_index'];
            if (isset($row[$columnIndex])) {
                $value = trim($row[$columnIndex]);
                if (!empty($value)) {
                    $mappedData[$field] = $value;
                }
            }
        }

        return $mappedData;
    }

    protected function validateRequiredFields(array $data): bool
    {
        $requiredFields = ['code', 'name', 'level', 'status', 'support', 'activity'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        
        return true;
    }

    protected function createOrUpdateRecord(array $data): ?Record
    {
        // Préparer les données pour la création
        $recordData = [
            'code' => $data['code'],
            'name' => $data['name'],
            'date_format' => $data['date_format'] ?? 'Y',
            'date_start' => $this->formatDate($data['start_date'] ?? null),
            'date_end' => $this->formatDate($data['end_date'] ?? null),
            'date_exact' => $this->formatDate($data['exact_date'] ?? null),
            'level_id' => $this->getOrCreateLevel($data['level'])->id,
            'width' => is_numeric($data['width'] ?? null) ? $data['width'] : null,
            'width_description' => $data['width_description'] ?? null,
            'content' => $data['content'] ?? null,
            'status_id' => $this->getOrCreateStatus($data['status'])->id,
            'support_id' => $this->getOrCreateSupport($data['support'])->id,
            'activity_id' => $this->getOrCreateActivity($data['activity'])->id,
            'user_id' => Auth::id() ?? 1,
            'organisation_id' => Auth::user()->current_organisation_id ?? 1,
        ];

        // Vérifier si l'enregistrement existe déjà
        if ($this->updateExisting) {
            $existingRecord = Record::where('code', $data['code'])->first();
            if ($existingRecord) {
                $existingRecord->update($recordData);
                return $existingRecord;
            }
        }

        return Record::create($recordData);
    }

    protected function processAuthors(Record $record, array $data): void
    {
        if (!empty($data['authors'])) {
            $authors = array_map('trim', explode(',', $data['authors']));
            $authorIds = [];
            
            foreach ($authors as $authorName) {
                if (!empty($authorName)) {
                    $author = Author::firstOrCreate(['name' => $authorName]);
                    $authorIds[] = $author->id;
                }
            }
            
            if (!empty($authorIds)) {
                $record->authors()->sync($authorIds);
            }
        }
    }

    protected function processTerms(Record $record, array $data): void
    {
        if (!empty($data['terms'])) {
            $terms = array_map('trim', explode(',', $data['terms']));
            $conceptData = [];
            
            foreach ($terms as $termName) {
                if (!empty($termName)) {
                    // Rechercher le concept par son label
                    $concept = ThesaurusConcept::whereHas('labels', function($query) use ($termName) {
                        $query->where('literal_form', 'LIKE', '%' . $termName . '%');
                    })->first();
                    
                    if ($concept) {
                        $conceptData[$concept->id] = [
                            'weight' => 1.0,
                            'context' => 'import',
                            'extraction_note' => 'Importé depuis fichier'
                        ];
                    }
                }
            }
            
            if (!empty($conceptData)) {
                $record->thesaurusConcepts()->sync($conceptData);
            }
        }
    }

    protected function formatDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        // Essayer différents formats de date
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd-m-Y'];
        
        foreach ($formats as $format) {
            $dateObj = \DateTime::createFromFormat($format, $date);
            if ($dateObj && $dateObj->format($format) === $date) {
                return $dateObj->format('Y-m-d');
            }
        }

        return null;
    }

    protected function getOrCreateLevel(string $name): RecordLevel
    {
        return RecordLevel::firstOrCreate(['name' => $name], ['name' => $name]);
    }

    protected function getOrCreateStatus(string $name): RecordStatus
    {
        return RecordStatus::firstOrCreate(['name' => $name], ['name' => $name]);
    }

    protected function getOrCreateSupport(string $name): RecordSupport
    {
        return RecordSupport::firstOrCreate(['name' => $name], ['name' => $name]);
    }

    protected function getOrCreateActivity(string $name): Activity
    {
        return Activity::firstOrCreate(['name' => $name], ['name' => $name]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}
```

## 4. Routes

Ajoutez cette route dans `routes/web.php` :

```php
Route::post('/records/analyze-file', [RecordController::class, 'analyzeFile'])->name('records.analyze-file');
```

## 5. Améliorations optionnelles

### A. Sauvegarde des templates de mapping

Vous pouvez ajouter une fonctionnalité pour sauvegarder et réutiliser les mappings :

```php
// Migration pour la table des templates
Schema::create('import_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->json('mapping');
    $table->foreignId('user_id')->constrained();
    $table->timestamps();
});
```

### B. Validation en temps réel

Ajoutez une méthode pour prévisualiser les données :

```php
public function previewMapping(Request $request)
{
    // Lire quelques lignes du fichier avec le mapping
    // Retourner un aperçu des données mappées
}
```

### C. Gestion des erreurs détaillée

Implémentez un système de log détaillé pour chaque ligne importée :

```php
// Model ImportLog
class ImportLog extends Model
{
    protected $fillable = ['dolly_id', 'row_number', 'status', 'message', 'data'];
}
```

## Utilisation

1. **Upload** : L'utilisateur sélectionne son fichier et le format
2. **Analyse** : Le système lit les en-têtes du fichier
3. **Mapping** : Interface graphique pour mapper les colonnes
4. **Import** : Traitement des données avec le mapping appliqué

## Avantages de cette approche

- **Flexibilité** : Support de n'importe quel format de fichier
- **Convivialité** : Interface graphique intuitive
- **Performance** : Import par chunks et batches
- **Traçabilité** : Logs détaillés des imports
- **Réutilisabilité** : Possibilité de sauvegarder les templates

Cette solution offre une expérience utilisateur complète tout en maintenant la performance et la robustesse du système d'import.