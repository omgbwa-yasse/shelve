# Plan de Développement - Système Dolly pour Entités Numériques

## 📋 Analyse du Système Dolly Existant

### Structure Actuelle

Le système Dolly (Chariot) permet de regrouper temporairement des entités pour effectuer des opérations en lot. Il existe actuellement pour:

**Modèle Principal:**
- `Dolly` (table: `dollies`)
  - Champs: `name`, `description`, `category`, `is_public`, `created_by`, `owner_organisation_id`
  - Categories supportées: `mail`, `transaction`, `record`, `slip`, `building`, `shelf`, `container`, `communication`, `room`

**Tables Pivot Existantes:**
- `dolly_mails` (courriers)
- `dolly_mail_transactions` (transactions de courrier)
- `dolly_records` (archives physiques)
- `dolly_slips` (versements)
- `dolly_slip_records` (descriptions de versements)
- `dolly_buildings` (bâtiments)
- `dolly_rooms` (salles)
- `dolly_shelves` (étagères)
- `dolly_containers` (boîtes/conteneurs)
- `dolly_communications` (communications)

**Contrôleurs Existants:**
- `DollyController` (CRUD principal)
- `DollyHandlerController` (API pour manipuler les items)
- `DollyActionController` (Actions spécifiques)
- `DollyExportController` (Export)
- `SearchdollyController` (Recherche/tri)

**Vues Existantes:**
- `resources/views/dollies/index.blade.php`
- `resources/views/dollies/create.blade.php`
- `resources/views/dollies/edit.blade.php`
- `resources/views/dollies/show.blade.php`
- `resources/views/dollies/partials/{category}.blade.php` (vues par catégorie)

---

## 🎯 Objectifs du Projet

Étendre le système Dolly pour supporter les entités numériques suivantes:

1. **Dossiers Numériques** (`RecordDigitalFolder`)
2. **Documents Numériques** (`RecordDigitalDocument`)
3. **Artefacts** (`RecordArtifact`)
4. **Livres** (`RecordBook`)
5. **Séries d'Éditeur** (`RecordBookPublisherSeries`)

---

## 📐 Architecture Proposée

### Phase 1: Migration et Tables Pivot

#### 1.1 Création de la Migration

**Fichier:** `database/migrations/YYYY_MM_DD_HHMMSS_add_digital_entities_to_dolly_system.php`

**Tables à créer:**

```php
// Table pivot pour dossiers numériques
Schema::create('dolly_digital_folders', function(Blueprint $table){
    $table->unsignedBigInteger('folder_id')->nullable(false);
    $table->unsignedBigInteger('dolly_id')->nullable(false);
    $table->foreign('folder_id')->references('id')->on('record_digital_folders')->onDelete('cascade');
    $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
    $table->timestamps();
    $table->unique(['folder_id', 'dolly_id']);
});

// Table pivot pour documents numériques
Schema::create('dolly_digital_documents', function(Blueprint $table){
    $table->unsignedBigInteger('document_id')->nullable(false);
    $table->unsignedBigInteger('dolly_id')->nullable(false);
    $table->foreign('document_id')->references('id')->on('record_digital_documents')->onDelete('cascade');
    $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
    $table->timestamps();
    $table->unique(['document_id', 'dolly_id']);
});

// Table pivot pour artefacts
Schema::create('dolly_artifacts', function(Blueprint $table){
    $table->unsignedBigInteger('artifact_id')->nullable(false);
    $table->unsignedBigInteger('dolly_id')->nullable(false);
    $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
    $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
    $table->timestamps();
    $table->unique(['artifact_id', 'dolly_id']);
});

// Table pivot pour livres
Schema::create('dolly_books', function(Blueprint $table){
    $table->unsignedBigInteger('book_id')->nullable(false);
    $table->unsignedBigInteger('dolly_id')->nullable(false);
    $table->foreign('book_id')->references('id')->on('record_books')->onDelete('cascade');
    $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
    $table->timestamps();
    $table->unique(['book_id', 'dolly_id']);
});

// Table pivot pour séries d'éditeur
Schema::create('dolly_book_series', function(Blueprint $table){
    $table->unsignedBigInteger('series_id')->nullable(false);
    $table->unsignedBigInteger('dolly_id')->nullable(false);
    $table->foreign('series_id')->references('id')->on('record_book_publisher_series')->onDelete('cascade');
    $table->foreign('dolly_id')->references('id')->on('dollies')->onDelete('cascade');
    $table->timestamps();
    $table->unique(['series_id', 'dolly_id']);
});
```

#### 1.2 Modification de la table dollies

**Modifier l'enum category:**

```php
Schema::table('dollies', function (Blueprint $table) {
    $table->enum('category', [
        // Existantes
        'mail', 'transaction', 'record', 'slip', 'building', 'shelf', 
        'container', 'communication', 'room',
        // Nouvelles
        'digital_folder', 'digital_document', 'artifact', 'book', 'book_series'
    ])->change();
});
```

---

### Phase 2: Mise à Jour des Modèles

#### 2.1 Modèle Dolly

**Fichier:** `app/Models/Dolly.php`

**Modifications à apporter:**

```php
// Ajouter dans $fillable si nécessaire (déjà présent)

// Nouvelles relations à ajouter
public function digitalFolders()
{
    return $this->belongsToMany(
        RecordDigitalFolder::class, 
        'dolly_digital_folders', 
        'dolly_id', 
        'folder_id'
    )->withTimestamps();
}

public function digitalDocuments()
{
    return $this->belongsToMany(
        RecordDigitalDocument::class, 
        'dolly_digital_documents', 
        'dolly_id', 
        'document_id'
    )->withTimestamps();
}

public function artifacts()
{
    return $this->belongsToMany(
        RecordArtifact::class, 
        'dolly_artifacts', 
        'dolly_id', 
        'artifact_id'
    )->withTimestamps();
}

public function books()
{
    return $this->belongsToMany(
        RecordBook::class, 
        'dolly_books', 
        'dolly_id', 
        'book_id'
    )->withTimestamps();
}

public function bookSeries()
{
    return $this->belongsToMany(
        RecordBookPublisherSeries::class, 
        'dolly_book_series', 
        'dolly_id', 
        'series_id'
    )->withTimestamps();
}

// Mettre à jour la méthode categories()
public static function categories()
{
    $list = array(
        'mail',
        'communication',
        'building',
        'transferring',
        'room',
        'record',
        'slip',
        'slipRecord',
        'container',
        'shelf',
        // Nouvelles catégories
        'digital_folder',
        'digital_document',
        'artifact',
        'book',
        'book_series'
    );
    
    return collect($list);
}
```

#### 2.2 Modèles des Entités Numériques

**Fichiers à modifier:**
- `app/Models/RecordDigitalFolder.php`
- `app/Models/RecordDigitalDocument.php`
- `app/Models/RecordArtifact.php`
- `app/Models/RecordBook.php`
- `app/Models/RecordBookPublisherSeries.php`

**Pour chaque modèle, ajouter:**

```php
// Dans RecordDigitalFolder.php
public function dollies()
{
    return $this->belongsToMany(
        Dolly::class, 
        'dolly_digital_folders', 
        'folder_id', 
        'dolly_id'
    )->withTimestamps();
}

// Dans RecordDigitalDocument.php
public function dollies()
{
    return $this->belongsToMany(
        Dolly::class, 
        'dolly_digital_documents', 
        'document_id', 
        'dolly_id'
    )->withTimestamps();
}

// Dans RecordArtifact.php
public function dollies()
{
    return $this->belongsToMany(
        Dolly::class, 
        'dolly_artifacts', 
        'artifact_id', 
        'dolly_id'
    )->withTimestamps();
}

// Dans RecordBook.php
public function dollies()
{
    return $this->belongsToMany(
        Dolly::class, 
        'dolly_books', 
        'book_id', 
        'dolly_id'
    )->withTimestamps();
}

// Dans RecordBookPublisherSeries.php
public function dollies()
{
    return $this->belongsToMany(
        Dolly::class, 
        'dolly_book_series', 
        'series_id', 
        'dolly_id'
    )->withTimestamps();
}
```

---

### Phase 3: Mise à Jour des Contrôleurs

#### 3.1 DollyController

**Fichier:** `app/Http/Controllers/DollyController.php`

**Méthodes à ajouter:**

```php
// Ajouter un dossier numérique au dolly
public function addDigitalFolder(Request $request, Dolly $dolly)
{
    $request->validate([
        'folder_id' => 'required|exists:record_digital_folders,id'
    ]);
    
    $dolly->digitalFolders()->syncWithoutDetaching($request->folder_id);
    
    return redirect()->route('dolly.show', $dolly)
        ->with('success', 'Dossier numérique ajouté au chariot');
}

// Retirer un dossier numérique du dolly
public function removeDigitalFolder(Dolly $dolly, RecordDigitalFolder $folder)
{
    $dolly->digitalFolders()->detach($folder->id);
    
    return redirect()->route('dolly.show', $dolly)
        ->with('success', 'Dossier numérique retiré du chariot');
}

// Ajouter un document numérique au dolly
public function addDigitalDocument(Request $request, Dolly $dolly)
{
    $request->validate([
        'document_id' => 'required|exists:record_digital_documents,id'
    ]);
    
    $dolly->digitalDocuments()->syncWithoutDetaching($request->document_id);
    
    return redirect()->route('dolly.show', $dolly)
        ->with('success', 'Document numérique ajouté au chariot');
}

// Retirer un document numérique du dolly
public function removeDigitalDocument(Dolly $dolly, RecordDigitalDocument $document)
{
    $dolly->digitalDocuments()->detach($document->id);
    
    return redirect()->route('dolly.show', $dolly)
        ->with('success', 'Document numérique retiré du chariot');
}

// Ajouter un artefact au dolly
public function addArtifact(Request $request, Dolly $dolly)
{
    $request->validate([
        'artifact_id' => 'required|exists:record_artifacts,id'
    ]);
    
    $dolly->artifacts()->syncWithoutDetaching($request->artifact_id);
    
    return redirect()->route('dolly.show', $dolly)
        ->with('success', 'Artefact ajouté au chariot');
}

// Retirer un artefact du dolly
public function removeArtifact(Dolly $dolly, RecordArtifact $artifact)
{
    $dolly->artifacts()->detach($artifact->id);
    
    return redirect()->route('dolly.show', $dolly)
        ->with('success', 'Artefact retiré du chariot');
}

// Ajouter un livre au dolly
public function addBook(Request $request, Dolly $dolly)
{
    $request->validate([
        'book_id' => 'required|exists:record_books,id'
    ]);
    
    $dolly->books()->syncWithoutDetaching($request->book_id);
    
    return redirect()->route('dolly.show', $dolly)
        ->with('success', 'Livre ajouté au chariot');
}

// Retirer un livre du dolly
public function removeBook(Dolly $dolly, RecordBook $book)
{
    $dolly->books()->detach($book->id);
    
    return redirect()->route('dolly.show', $dolly)
        ->with('success', 'Livre retiré du chariot');
}

// Ajouter une série au dolly
public function addBookSeries(Request $request, Dolly $dolly)
{
    $request->validate([
        'series_id' => 'required|exists:record_book_publisher_series,id'
    ]);
    
    $dolly->bookSeries()->syncWithoutDetaching($request->series_id);
    
    return redirect()->route('dolly.show', $dolly)
        ->with('success', 'Série ajoutée au chariot');
}

// Retirer une série du dolly
public function removeBookSeries(Dolly $dolly, RecordBookPublisherSeries $series)
{
    $dolly->bookSeries()->detach($series->id);
    
    return redirect()->route('dolly.show', $dolly)
        ->with('success', 'Série retirée du chariot');
}
```

**Modifier la méthode show():**

```php
public function show(Dolly $dolly)
{
    // Charger toutes les entités
    $records = RecordPhysical::all();
    $mails = Mail::all();
    $communications = Communication::all();
    $rooms = Room::all();
    $containers = Container::all();
    $shelves = Shelf::all();
    $slip_records = SlipRecord::all();
    
    // Nouvelles entités
    $digitalFolders = RecordDigitalFolder::where('organisation_id', Auth::user()->current_organisation_id)->get();
    $digitalDocuments = RecordDigitalDocument::where('organisation_id', Auth::user()->current_organisation_id)->get();
    $artifacts = RecordArtifact::where('organisation_id', Auth::user()->current_organisation_id)->get();
    $books = RecordBook::where('organisation_id', Auth::user()->current_organisation_id)->get();
    $bookSeries = RecordBookPublisherSeries::all();
    
    $dolly->load('creator','ownerOrganisation');
    
    return view('dollies.show', compact(
        'dolly', 'records', 'mails', 'communications', 'rooms', 
        'containers', 'shelves', 'slip_records',
        'digitalFolders', 'digitalDocuments', 'artifacts', 'books', 'bookSeries'
    ));
}
```

**Modifier la méthode destroy():**

```php
public function destroy(Dolly $dolly)
{
    if ($dolly->mails()->exists() 
        || $dolly->records()->exists() 
        || $dolly->communications()->exists() 
        || $dolly->slips()->exists() 
        || $dolly->slipRecords()->exists() 
        || $dolly->buildings()->exists() 
        || $dolly->rooms()->exists() 
        || $dolly->shelve()->exists()
        || $dolly->digitalFolders()->exists()
        || $dolly->digitalDocuments()->exists()
        || $dolly->artifacts()->exists()
        || $dolly->books()->exists()
        || $dolly->bookSeries()->exists()
    ) {
       return redirect()->route('dolly.index')
           ->with('error', 'Cannot delete Dolly because it has related records in other tables.');
    }
    
    $dolly->delete();
    return redirect()->route('dolly.index')
        ->with('success', 'Dolly deleted successfully.');
}
```

#### 3.2 DollyHandlerController

**Fichier:** `app/Http/Controllers/DollyHandlerController.php`

**Modifications à apporter:**

```php
// Mettre à jour la méthode addItems pour supporter les nouvelles entités
public function addItems(Request $request)
{
    $request->validate([
        'dolly_id' => 'required|exists:dollies,id',
        'items' => 'required|array',
        'items.*' => 'required|integer',
        'category' => 'required|string'
    ]);

    $dolly = Dolly::findOrFail($request->dolly_id);
    
    switch($request->category) {
        case 'digital_folder':
            $dolly->digitalFolders()->syncWithoutDetaching($request->items);
            break;
        case 'digital_document':
            $dolly->digitalDocuments()->syncWithoutDetaching($request->items);
            break;
        case 'artifact':
            $dolly->artifacts()->syncWithoutDetaching($request->items);
            break;
        case 'book':
            $dolly->books()->syncWithoutDetaching($request->items);
            break;
        case 'book_series':
            $dolly->bookSeries()->syncWithoutDetaching($request->items);
            break;
        // ... garder les autres cases existants
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Items ajoutés au chariot'
    ]);
}

// Mettre à jour removeItems de la même manière
public function removeItems(Request $request)
{
    $request->validate([
        'dolly_id' => 'required|exists:dollies,id',
        'items' => 'required|array',
        'items.*' => 'required|integer',
        'category' => 'required|string'
    ]);

    $dolly = Dolly::findOrFail($request->dolly_id);
    
    switch($request->category) {
        case 'digital_folder':
            $dolly->digitalFolders()->detach($request->items);
            break;
        case 'digital_document':
            $dolly->digitalDocuments()->detach($request->items);
            break;
        case 'artifact':
            $dolly->artifacts()->detach($request->items);
            break;
        case 'book':
            $dolly->books()->detach($request->items);
            break;
        case 'book_series':
            $dolly->bookSeries()->detach($request->items);
            break;
        // ... garder les autres cases existants
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Items retirés du chariot'
    ]);
}

// Mettre à jour clean pour vider les nouvelles entités
public function clean(Request $request)
{
    $request->validate([
        'dolly_id' => 'required|exists:dollies,id'
    ]);

    $dolly = Dolly::findOrFail($request->dolly_id);
    
    // Nettoyer toutes les relations
    $dolly->mails()->detach();
    $dolly->records()->detach();
    $dolly->communications()->detach();
    $dolly->slips()->detach();
    $dolly->slipRecords()->detach();
    $dolly->buildings()->detach();
    $dolly->rooms()->detach();
    $dolly->shelve()->detach();
    $dolly->containers()->detach();
    
    // Nouvelles relations
    $dolly->digitalFolders()->detach();
    $dolly->digitalDocuments()->detach();
    $dolly->artifacts()->detach();
    $dolly->books()->detach();
    $dolly->bookSeries()->detach();
    
    return response()->json([
        'success' => true,
        'message' => 'Chariot vidé avec succès'
    ]);
}
```

---

### Phase 4: Routes

**Fichier:** `routes/web.php`

**Routes à ajouter dans le groupe dolly:**

```php
Route::prefix('dolly')->group(function () {
    // ... routes existantes ...
    
    // Routes pour dossiers numériques
    Route::post('{dolly}/add-digital-folder', [DollyController::class, 'addDigitalFolder'])
        ->name('dolly.add-digital-folder');
    Route::delete('{dolly}/remove-digital-folder/{folder}', [DollyController::class, 'removeDigitalFolder'])
        ->name('dolly.remove-digital-folder');
    
    // Routes pour documents numériques
    Route::post('{dolly}/add-digital-document', [DollyController::class, 'addDigitalDocument'])
        ->name('dolly.add-digital-document');
    Route::delete('{dolly}/remove-digital-document/{document}', [DollyController::class, 'removeDigitalDocument'])
        ->name('dolly.remove-digital-document');
    
    // Routes pour artefacts
    Route::post('{dolly}/add-artifact', [DollyController::class, 'addArtifact'])
        ->name('dolly.add-artifact');
    Route::delete('{dolly}/remove-artifact/{artifact}', [DollyController::class, 'removeArtifact'])
        ->name('dolly.remove-artifact');
    
    // Routes pour livres
    Route::post('{dolly}/add-book', [DollyController::class, 'addBook'])
        ->name('dolly.add-book');
    Route::delete('{dolly}/remove-book/{book}', [DollyController::class, 'removeBook'])
        ->name('dolly.remove-book');
    
    // Routes pour séries
    Route::post('{dolly}/add-book-series', [DollyController::class, 'addBookSeries'])
        ->name('dolly.add-book-series');
    Route::delete('{dolly}/remove-book-series/{series}', [DollyController::class, 'removeBookSeries'])
        ->name('dolly.remove-book-series');
});
```

---

### Phase 5: Vues Blade

#### 5.1 Modifier create.blade.php

**Fichier:** `resources/views/dollies/create.blade.php`

**Ajouter les nouvelles options dans le select category:**

```blade
<select name="category" id="category" class="form-select" required>
    @foreach ($categories as $category)
    <option value="{{ $category }}">
        @if($category == 'record')
            Description des archives
        @elseif($category == 'mail')
            Courrier
        @elseif($category == 'communication')
            Communication des archives
        @elseif($category == 'room')
            Salle d'archives
        @elseif($category == 'building')
            Bâtiments d'archives
        @elseif($category == 'container')
            Boites et chronos
        @elseif($category == 'shelf')
            Etagère
        @elseif($category == 'slip')
            Versement
        @elseif($category == 'slip_record')
            Description de versement
        @elseif($category == 'digital_folder')
            Dossiers Numériques
        @elseif($category == 'digital_document')
            Documents Numériques
        @elseif($category == 'artifact')
            Artefacts
        @elseif($category == 'book')
            Livres
        @elseif($category == 'book_series')
            Séries d'Éditeur
        @endif
    </option>
    @endforeach
</select>
```

#### 5.2 Créer les vues partielles

**Fichier:** `resources/views/dollies/partials/digital_folder.blade.php`

```blade
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card action-card">
            <div class="card-body text-center">
                <i class="bi bi-folder-plus action-icon text-primary"></i>
                <h5 class="card-title">Ajouter des Dossiers Numériques</h5>
                <p class="card-text">Ajoutez des dossiers numériques à ce chariot</p>
                <form action="{{ route('dolly.add-digital-folder', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="folder_id" class="form-select" required>
                            <option value="">-- Sélectionner un dossier --</option>
                            @foreach($digitalFolders as $folder)
                                <option value="{{ $folder->id }}">
                                    {{ $folder->code }} - {{ $folder->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-action">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

**Fichier:** `resources/views/dollies/partials/digital_document.blade.php`

```blade
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card action-card">
            <div class="card-body text-center">
                <i class="bi bi-file-earmark-text action-icon text-success"></i>
                <h5 class="card-title">Ajouter des Documents Numériques</h5>
                <p class="card-text">Ajoutez des documents numériques à ce chariot</p>
                <form action="{{ route('dolly.add-digital-document', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="document_id" class="form-select" required>
                            <option value="">-- Sélectionner un document --</option>
                            @foreach($digitalDocuments as $document)
                                <option value="{{ $document->id }}">
                                    {{ $document->code }} - {{ $document->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-action">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

**Fichier:** `resources/views/dollies/partials/artifact.blade.php`

```blade
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card action-card">
            <div class="card-body text-center">
                <i class="bi bi-gem action-icon text-warning"></i>
                <h5 class="card-title">Ajouter des Artefacts</h5>
                <p class="card-text">Ajoutez des artefacts de musée à ce chariot</p>
                <form action="{{ route('dolly.add-artifact', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="artifact_id" class="form-select" required>
                            <option value="">-- Sélectionner un artefact --</option>
                            @foreach($artifacts as $artifact)
                                <option value="{{ $artifact->id }}">
                                    {{ $artifact->code }} - {{ $artifact->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning btn-action">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

**Fichier:** `resources/views/dollies/partials/book.blade.php`

```blade
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card action-card">
            <div class="card-body text-center">
                <i class="bi bi-book action-icon text-info"></i>
                <h5 class="card-title">Ajouter des Livres</h5>
                <p class="card-text">Ajoutez des livres à ce chariot</p>
                <form action="{{ route('dolly.add-book', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="book_id" class="form-select" required>
                            <option value="">-- Sélectionner un livre --</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}">
                                    {{ $book->isbn }} - {{ $book->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-info btn-action">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

**Fichier:** `resources/views/dollies/partials/book_series.blade.php`

```blade
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card action-card">
            <div class="card-body text-center">
                <i class="bi bi-collection action-icon text-secondary"></i>
                <h5 class="card-title">Ajouter des Séries d'Éditeur</h5>
                <p class="card-text">Ajoutez des séries de livres à ce chariot</p>
                <form action="{{ route('dolly.add-book-series', $dolly) }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <select name="series_id" class="form-select" required>
                            <option value="">-- Sélectionner une série --</option>
                            @foreach($bookSeries as $series)
                                <option value="{{ $series->id }}">
                                    {{ $series->publisher->name }} - {{ $series->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary btn-action">
                        <i class="bi bi-plus-circle"></i> Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
```

#### 5.3 Modifier show.blade.php

**Fichier:** `resources/views/dollies/show.blade.php`

**Ajouter après la section des archives physiques:**

```blade
{{-- Dossiers Numériques --}}
@elseif($dolly->category === 'digital_folder' && $dolly->digitalFolders->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-white">
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dolly->digitalFolders as $folder)
                    <tr>
                        <td>{{ $folder->code }}</td>
                        <td>{{ $folder->name }}</td>
                        <td>{{ $folder->type->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-{{ $folder->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($folder->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('folders.show', $folder) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Voir
                            </a>
                            <form action="{{ route('dolly.remove-digital-folder', [$dolly, $folder]) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir retirer ce dossier du chariot ?')">
                                    <i class="bi bi-trash"></i> Retirer
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

{{-- Documents Numériques --}}
@elseif($dolly->category === 'digital_document' && $dolly->digitalDocuments->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-white">
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Type</th>
                    <th>Version</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dolly->digitalDocuments as $document)
                    <tr>
                        <td>{{ $document->code }}</td>
                        <td>{{ $document->name }}</td>
                        <td>{{ $document->type->name ?? 'N/A' }}</td>
                        <td>{{ $document->version_number }}</td>
                        <td>
                            <span class="badge bg-{{ $document->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($document->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Voir
                            </a>
                            <form action="{{ route('dolly.remove-digital-document', [$dolly, $document]) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir retirer ce document du chariot ?')">
                                    <i class="bi bi-trash"></i> Retirer
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

{{-- Artefacts --}}
@elseif($dolly->category === 'artifact' && $dolly->artifacts->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-white">
                <tr>
                    <th>Code</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>État de Conservation</th>
                    <th>Localisation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dolly->artifacts as $artifact)
                    <tr>
                        <td>{{ $artifact->code }}</td>
                        <td>{{ $artifact->name }}</td>
                        <td>{{ $artifact->category }}</td>
                        <td>
                            <span class="badge bg-{{ 
                                $artifact->conservation_state === 'excellent' ? 'success' : 
                                ($artifact->conservation_state === 'good' ? 'primary' : 
                                ($artifact->conservation_state === 'fair' ? 'warning' : 'danger'))
                            }}">
                                {{ ucfirst($artifact->conservation_state) }}
                            </span>
                        </td>
                        <td>{{ $artifact->current_location }}</td>
                        <td>
                            {{-- TODO: Ajouter la route artifacts.show --}}
                            <a href="#" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Voir
                            </a>
                            <form action="{{ route('dolly.remove-artifact', [$dolly, $artifact]) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir retirer cet artefact du chariot ?')">
                                    <i class="bi bi-trash"></i> Retirer
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

{{-- Livres --}}
@elseif($dolly->category === 'book' && $dolly->books->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-white">
                <tr>
                    <th>ISBN</th>
                    <th>Titre</th>
                    <th>Auteur(s)</th>
                    <th>Éditeur</th>
                    <th>Année</th>
                    <th>Disponibilité</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dolly->books as $book)
                    <tr>
                        <td>{{ $book->formatted_isbn }}</td>
                        <td>{{ $book->title }}</td>
                        <td>{{ $book->authors_string }}</td>
                        <td>{{ $book->publisher->name ?? 'N/A' }}</td>
                        <td>{{ $book->publication_year }}</td>
                        <td>
                            <span class="badge bg-{{ $book->available_copies > 0 ? 'success' : 'danger' }}">
                                {{ $book->available_copies }}/{{ $book->total_copies }}
                            </span>
                        </td>
                        <td>
                            {{-- TODO: Ajouter la route books.show --}}
                            <a href="#" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Voir
                            </a>
                            <form action="{{ route('dolly.remove-book', [$dolly, $book]) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir retirer ce livre du chariot ?')">
                                    <i class="bi bi-trash"></i> Retirer
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

{{-- Séries d'Éditeur --}}
@elseif($dolly->category === 'book_series' && $dolly->bookSeries->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-white">
                <tr>
                    <th>Nom</th>
                    <th>Éditeur</th>
                    <th>ISSN</th>
                    <th>Période</th>
                    <th>Volumes</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dolly->bookSeries as $series)
                    <tr>
                        <td>{{ $series->name }}</td>
                        <td>{{ $series->publisher->name }}</td>
                        <td>{{ $series->formatted_issn }}</td>
                        <td>{{ $series->years_range }}</td>
                        <td>{{ $series->total_volumes }}</td>
                        <td>
                            <span class="badge bg-{{ $series->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($series->status) }}
                            </span>
                        </td>
                        <td>
                            {{-- TODO: Ajouter la route book-series.show --}}
                            <a href="#" class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Voir
                            </a>
                            <form action="{{ route('dolly.remove-book-series', [$dolly, $series]) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir retirer cette série du chariot ?')">
                                    <i class="bi bi-trash"></i> Retirer
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
```

---

### Phase 6: Modèles Pivot (Optionnel)

Si vous souhaitez des modèles dédiés pour les tables pivot (pour ajouter des métadonnées supplémentaires):

```php
// app/Models/DollyDigitalFolder.php
class DollyDigitalFolder extends Model
{
    protected $table = 'dolly_digital_folders';
    protected $fillable = ['dolly_id', 'folder_id'];
}

// app/Models/DollyDigitalDocument.php
class DollyDigitalDocument extends Model
{
    protected $table = 'dolly_digital_documents';
    protected $fillable = ['dolly_id', 'document_id'];
}

// app/Models/DollyArtifact.php
class DollyArtifact extends Model
{
    protected $table = 'dolly_artifacts';
    protected $fillable = ['dolly_id', 'artifact_id'];
}

// app/Models/DollyBook.php
class DollyBook extends Model
{
    protected $table = 'dolly_books';
    protected $fillable = ['dolly_id', 'book_id'];
}

// app/Models/DollyBookSeries.php
class DollyBookSeries extends Model
{
    protected $table = 'dolly_book_series';
    protected $fillable = ['dolly_id', 'series_id'];
}
```

---

## 📝 Checklist de Mise en Œuvre

### Phase 1: Base de Données
- [ ] Créer la migration pour les tables pivot
- [ ] Modifier l'enum category dans la table dollies
- [ ] Exécuter les migrations
- [ ] Vérifier l'intégrité de la base de données

### Phase 2: Modèles
- [ ] Mettre à jour le modèle Dolly (relations + categories)
- [ ] Mettre à jour RecordDigitalFolder (relation dolly)
- [ ] Mettre à jour RecordDigitalDocument (relation dolly)
- [ ] Mettre à jour RecordArtifact (relation dolly)
- [ ] Mettre à jour RecordBook (relation dolly)
- [ ] Mettre à jour RecordBookPublisherSeries (relation dolly)
- [ ] (Optionnel) Créer les modèles pivot

### Phase 3: Contrôleurs
- [ ] Mettre à jour DollyController (méthodes add/remove pour chaque entité)
- [ ] Mettre à jour DollyController::show (charger les nouvelles entités)
- [ ] Mettre à jour DollyController::destroy (vérifier les nouvelles relations)
- [ ] Mettre à jour DollyHandlerController::addItems
- [ ] Mettre à jour DollyHandlerController::removeItems
- [ ] Mettre à jour DollyHandlerController::clean

### Phase 4: Routes
- [ ] Ajouter les routes pour digital_folder
- [ ] Ajouter les routes pour digital_document
- [ ] Ajouter les routes pour artifact
- [ ] Ajouter les routes pour book
- [ ] Ajouter les routes pour book_series

### Phase 5: Vues
- [ ] Mettre à jour create.blade.php (options category)
- [ ] Créer digital_folder.blade.php (partials)
- [ ] Créer digital_document.blade.php (partials)
- [ ] Créer artifact.blade.php (partials)
- [ ] Créer book.blade.php (partials)
- [ ] Créer book_series.blade.php (partials)
- [ ] Mettre à jour show.blade.php (affichage des nouvelles entités)

### Phase 6: Tests
- [ ] Tester création dolly avec nouvelles catégories
- [ ] Tester ajout/retrait de dossiers numériques
- [ ] Tester ajout/retrait de documents numériques
- [ ] Tester ajout/retrait d'artefacts
- [ ] Tester ajout/retrait de livres
- [ ] Tester ajout/retrait de séries
- [ ] Tester nettoyage du dolly
- [ ] Tester suppression du dolly avec relations
- [ ] Tester sécurité (organisation filtering)

### Phase 7: Documentation
- [ ] Documenter les nouvelles routes API
- [ ] Mettre à jour la documentation utilisateur
- [ ] Créer des exemples d'utilisation

---

## 🚀 Ordre d'Exécution Recommandé

1. **Étape 1**: Créer et exécuter la migration
2. **Étape 2**: Mettre à jour tous les modèles
3. **Étape 3**: Mettre à jour le contrôleur principal (DollyController)
4. **Étape 4**: Mettre à jour DollyHandlerController
5. **Étape 5**: Ajouter les routes
6. **Étape 6**: Créer/Modifier les vues partielles
7. **Étape 7**: Mettre à jour show.blade.php et create.blade.php
8. **Étape 8**: Tests manuels
9. **Étape 9**: Tests automatisés (si applicable)
10. **Étape 10**: Documentation

---

## ⚠️ Points d'Attention

1. **Sécurité**: S'assurer que le filtrage par organisation fonctionne pour toutes les nouvelles entités
2. **Performance**: Considérer l'utilisation de eager loading pour éviter le problème N+1
3. **Validation**: Ajouter des validations appropriées dans les formulaires
4. **Permissions**: Vérifier les politiques d'accès (Policies) si elles existent
5. **Internationalisation**: Ajouter les traductions dans les fichiers de langue
6. **API**: Si le projet a une API, mettre à jour les endpoints API également
7. **Export**: Mettre à jour DollyExportController si nécessaire

---

## 🔄 Améliorations Futures

1. **Sélection Multiple**: Permettre l'ajout de plusieurs items en une fois
2. **Drag & Drop**: Interface drag-and-drop pour ajouter items au dolly
3. **Actions en lot**: Opérations en masse sur les items du dolly
4. **Historique**: Tracker les modifications apportées au dolly
5. **Partage**: Permettre le partage de dollies entre utilisateurs
6. **Templates**: Créer des templates de dollies prédéfinis
7. **Notifications**: Alertes lors de modifications du dolly
8. **Export avancé**: Export en différents formats (CSV, Excel, PDF)

---

**Fin du Plan**
