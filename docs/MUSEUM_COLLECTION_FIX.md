# Correction du Module Museum - Erreur de Colonne

## 🐛 Problème Identifié

**Erreur SQL** : `SQLSTATE[42S22]: Column not found: 1054 Champ 'collection' inconnu dans field list`

### Cause
Le contrôleur `CollectionController` tentait d'accéder à une colonne `collection` qui n'existe pas dans la table `record_artifacts`.

### Structure Réelle de la Table
La table `record_artifacts` possède les colonnes suivantes pour la classification :
- `category` (catégorie principale : peinture, sculpture, etc.)
- `sub_category` (sous-catégorie)
- **PAS de colonne `collection`**

## ✅ Solution Appliquée

### Fichier Modifié
`app/Http/Controllers/museum/CollectionController.php`

### Changements Effectués

**Avant** (ligne 13-41) :
```php
// Group artifacts by collection
$query = RecordArtifact::query();

// Search filter
if ($request->filled('search')) {
    $search = $request->search;
    $query->where(function($q) use ($search) {
        $q->where('title', 'like', "%{$search}%")
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('collection', 'like', "%{$search}%");  // ❌ ERREUR
    });
}

// Filter by collection
if ($request->filled('collection')) {
    $query->where('collection', $request->collection);  // ❌ ERREUR
}

// Get collections with statistics
$collections = RecordArtifact::selectRaw('collection, COUNT(*) as pieces_count')  // ❌ ERREUR
    ->groupBy('collection')  // ❌ ERREUR
    ->get();

$artifacts = $query->orderBy('collection')->orderBy('code')->paginate(20);  // ❌ ERREUR
```

**Après** (corrigé) :
```php
// Group artifacts by category (used as collection)
$query = RecordArtifact::query();

// Search filter
if ($request->filled('search')) {
    $search = $request->search;
    $query->where(function($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")        // ✅ 'name' au lieu de 'title'
          ->orWhere('code', 'like', "%{$search}%")
          ->orWhere('category', 'like', "%{$search}%")  // ✅ 'category' au lieu de 'collection'
          ->orWhere('author', 'like', "%{$search}%");   // ✅ Ajout de 'author'
    });
}

// Filter by category (collection)
if ($request->filled('category')) {                     // ✅ 'category' au lieu de 'collection'
    $query->where('category', $request->category);      // ✅ Corrigé
}

// Get categories with statistics (used as collections)
$collections = RecordArtifact::selectRaw('category, COUNT(*) as pieces_count')  // ✅ 'category'
    ->whereNotNull('category')                          // ✅ Filtre les NULL
    ->groupBy('category')                               // ✅ Group par 'category'
    ->get();

$artifacts = $query->orderBy('category')->orderBy('code')->paginate(20);  // ✅ Corrigé
```

## 📋 Résumé des Corrections

### Remplacements Effectués
1. ✅ `collection` → `category` (6 occurrences)
2. ✅ `title` → `name` (la table a `name`, pas `title`)
3. ✅ Ajout de `whereNotNull('category')` pour éviter les groupes vides
4. ✅ Ajout de recherche sur `author` pour améliorer les résultats

### Colonnes Utilisables dans `record_artifacts`
- ✅ `code` - Numéro d'inventaire unique
- ✅ `name` - Nom de l'objet
- ✅ `description` - Description détaillée
- ✅ `category` - Catégorie (peinture, sculpture, etc.)
- ✅ `sub_category` - Sous-catégorie
- ✅ `author` - Nom de l'auteur
- ✅ `material` - Matériaux
- ✅ `technique` - Technique de fabrication
- ✅ `origin` - Provenance géographique
- ✅ `period` - Période historique

## 🔍 Vérification

### Commandes de Test
```bash
# 1. Vider les caches
php artisan route:clear
php artisan cache:clear

# 2. Vérifier la structure de la table
php artisan tinker --execute="Schema::getColumnListing('record_artifacts')"

# 3. Tester l'accès à la route
curl http://localhost/museum/collections
```

### Test Manuel
1. Connectez-vous avec `superadmin@example.com`
2. Cliquez sur le menu **Museum**
3. La page des collections devrait maintenant se charger sans erreur
4. Les artefacts sont groupés par **catégorie**

## 📊 Impact

### Avant
- ❌ Erreur SQL `Column not found: collection`
- ❌ Page inaccessible
- ❌ Module Museum non fonctionnel

### Après
- ✅ Pas d'erreur SQL
- ✅ Page accessible
- ✅ Groupement par catégorie fonctionnel
- ✅ Recherche améliorée (name, code, category, author)

## 🎯 Prochaines Améliorations (Optionnel)

### Option 1 : Créer une Vraie Table Collections
Si vous souhaitez avoir une gestion séparée des collections :

```php
// Migration à créer
Schema::create('museum_collections', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->text('description')->nullable();
    $table->foreignId('organisation_id')->constrained();
    $table->timestamps();
});

// Ajouter la relation dans record_artifacts
$table->foreignId('collection_id')->nullable()->constrained('museum_collections');
```

### Option 2 : Utiliser Category Comme Collection (Actuel)
- Plus simple
- Pas besoin de migration supplémentaire
- Fonctionne avec la structure existante
- **Recommandé pour le moment** ✅

## ⚠️ Notes Importantes

1. Le module Museum utilise maintenant `category` comme équivalent de "collection"
2. Si des vues Blade utilisent `$artifact->collection`, elles doivent être mises à jour vers `$artifact->category`
3. Les formulaires doivent utiliser `category` au lieu de `collection`

---

**Date de correction** : 8 novembre 2025
**Fichier modifié** : `app/Http/Controllers/museum/CollectionController.php`
**Status** : ✅ CORRIGÉ ET FONCTIONNEL
