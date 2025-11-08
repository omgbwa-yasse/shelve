# Structure des vues créées pour les modules Library et Museum

## Module Library (resources/views/library/)

### 1. Books (Livres)
- **books/index.blade.php** - Liste des livres avec filtres (recherche, catégorie, statut)
  - Affichage en tableau avec colonnes : Cote, Titre, Auteur, ISBN, Catégorie, Statut, Actions
  - Boutons : Nouvel ouvrage, Exporter
  - Filtres : Recherche, Catégorie, Statut (disponible/emprunté/réservé)

### 2. Loans (Prêts)
- **loans/index.blade.php** - Gestion des prêts
  - Statistiques en haut : Prêts en cours, Retards, Retours du jour, Prêts du mois
  - Onglets : En cours, Retards, Historique
  - Tableau avec : N° Prêt, Lecteur, Ouvrage, Dates, Statut, Actions
  - Filtres : Recherche, Statut, Date

### 3. Readers (Lecteurs)
- **readers/index.blade.php** - Gestion des lecteurs
  - Statistiques : Lecteurs actifs, Nouvelles inscriptions, Cartes à renouveler
  - Tableau avec : N° Carte, Nom, Email, Téléphone, Catégorie, Statut, Expiration
  - Filtres : Recherche, Statut (actif/inactif/expiré), Catégorie (étudiant/enseignant/personnel/externe)

### 4. Periodicals (Périodiques)
- **periodicals/index.blade.php** - Gestion des périodiques et revues
  - Affichage en cartes avec informations : ISSN, Éditeur, Périodicité, Numéros disponibles
  - Bouton recherche d'articles
  - Section "Derniers numéros reçus" en tableau
  - Filtres : Recherche (titre, ISSN), Périodicité (quotidien/hebdo/mensuel/trimestriel/annuel)

## Module Museum (resources/views/museum/)

### 1. Artifacts (Pièces de collection)
- **artifacts/index.blade.php** - Catalogue des pièces
  - Double vue : Galerie (cartes avec images) / Liste (tableau)
  - Bouton bascule Vue Galerie/Vue Liste
  - Filtres : Recherche, Catégorie (peinture/sculpture/artefact/photo/document), Collection, Statut
  - Vue galerie : Cartes avec image, code, description, badge statut
  - Vue liste : Tableau avec Code, Nom, Catégorie, Collection, Date acquisition, Valeur, Statut

### 2. Collections
- **collections/index.blade.php** - Gestion des collections
  - Statistiques globales : Collections, Total pièces, En exposition, Valorisation totale
  - Affichage en cartes avec :
    - Nom de la collection
    - Description
    - Statistiques (Pièces, Expositions, Valeur)
    - Actions (Voir, Éditer, Supprimer)

### 3. Exhibitions (Expositions)
- **exhibitions/index.blade.php** - Gestion des expositions
  - Onglets : En cours, À venir, Passées
  - Cartes d'exposition avec :
    - Titre et dates
    - Image
    - Description
    - Lieu, Nombre de pièces, Nombre de visiteurs
    - Badge statut
    - Actions (Détails, Éditer, Supprimer)
  - Section calendrier des expositions (à implémenter)

## Vues à créer (non encore créées)

### Library
- books/create.blade.php - Formulaire création livre
- books/edit.blade.php - Formulaire édition livre
- books/show.blade.php - Détails d'un livre
- loans/create.blade.php - Formulaire nouveau prêt
- loans/show.blade.php - Détails d'un prêt
- readers/create.blade.php - Formulaire nouveau lecteur
- readers/edit.blade.php - Formulaire édition lecteur
- readers/show.blade.php - Détails lecteur + historique prêts
- readers/card.blade.php - Carte de lecteur à imprimer
- periodicals/show.blade.php - Détails périodique + numéros
- periodicals/articles.blade.php - Recherche d'articles

### Museum
- artifacts/create.blade.php - Formulaire nouvelle pièce
- artifacts/edit.blade.php - Formulaire édition pièce
- artifacts/show.blade.php - Fiche détaillée pièce
- artifacts/exhibitions.blade.php - Expositions de la pièce
- artifacts/loans.blade.php - Prêts de la pièce
- collections/create.blade.php - Formulaire nouvelle collection
- collections/edit.blade.php - Formulaire édition collection
- collections/show.blade.php - Détails collection + pièces
- exhibitions/create.blade.php - Formulaire nouvelle exposition
- exhibitions/edit.blade.php - Formulaire édition exposition
- exhibitions/show.blade.php - Détails exposition + pièces + stats visiteurs

## Routes associées

### Library
- Préfixe : `/library`
- Namespace : `App\Http\Controllers\Library\`
- Routes principales créées dans web.php pour :
  - Books (CRUD + import/export)
  - Periodicals (index, show, articles)
  - Loans (CRUD + return, overdue, history)
  - Readers (CRUD + card)
  - Authors, Categories, Statistics, Reports

### Museum
- Préfixe : `/museum`
- Namespace : `App\Http\Controllers\Museum\`
- Routes principales créées dans web.php pour :
  - Artifacts (CRUD + exhibitions, loans, images)
  - Collections (CRUD)
  - Exhibitions (CRUD)
  - Conservation (index, create, store, show)
  - Inventory (index, recolement)
  - Search, Reports

## Contrôleurs créés

### Web Controllers (existants)
- `App\Http\Controllers\Web\ArtifactController` - Utilisé pour museum.artifacts
- `App\Http\Controllers\Web\PeriodicalController` - Utilisé pour library.periodicals (index, show)

### Library Controllers (✅ CRÉÉS)
- ✅ `App\Http\Controllers\Library\BookController` - Gestion complète des livres (CRUD + import/export)
- ✅ `App\Http\Controllers\Library\LoanController` - Gestion des prêts (CRUD + retour + historique)
- ✅ `App\Http\Controllers\Library\ReaderController` - Gestion des lecteurs (CRUD + carte)
- ✅ `App\Http\Controllers\Library\AuthorController` - Gestion des auteurs (CRUD)
- ✅ `App\Http\Controllers\Library\CategoryController` - Gestion des catégories (CRUD)
- ✅ `App\Http\Controllers\Library\SearchController` - Recherche (simple, avancée, populaire, récente)
- ✅ `App\Http\Controllers\Library\StatisticsController` - Statistiques (prêts, catégories)
- ✅ `App\Http\Controllers\Library\ReportController` - Rapports
- ✅ `App\Http\Controllers\Library\PeriodicalController` - Extension pour issues (numéros)

### Museum Controllers (✅ CRÉÉS)
- ✅ `App\Http\Controllers\Museum\CollectionController` - Gestion des collections (CRUD)
- ✅ `App\Http\Controllers\Museum\ExhibitionController` - Gestion des expositions (CRUD)
- ✅ `App\Http\Controllers\Museum\ConservationController` - Rapports de conservation (liste + création)
- ✅ `App\Http\Controllers\Museum\InventoryController` - Inventaire et récolement
- ✅ `App\Http\Controllers\Museum\SearchController` - Recherche (simple, avancée)
- ✅ `App\Http\Controllers\Museum\ReportController` - Rapports (statistiques, valorisation)

## Styles et composants utilisés

- Bootstrap 5 (cartes, tableaux, badges, boutons, formulaires)
- Bootstrap Icons (icônes)
- Layout : `layouts.app` (menu principal déjà mis à jour)
- Sous-menus : `submenu/library.blade.php` et `submenu/museum.blade.php` (déjà créés)

## État d'avancement

### ✅ Complété
1. ✅ Contrôleurs créés pour Library (9 contrôleurs)
2. ✅ Contrôleurs créés pour Museum (6 contrôleurs)
3. ✅ Routes configurées pour Library et Museum
4. ✅ Menus et sous-menus créés
5. ✅ Vues index créées (books, loans, readers, periodicals, artifacts, collections, exhibitions)

### 🔄 En cours / À faire

#### Priorité HAUTE
1. Créer les modèles nécessaires :
   - Library : `Book`, `Loan`, `Reader`, `Author`, `Category`, `PeriodicalIssue`
   - Museum : `Collection`, `Exhibition`, `ConservationReport`, `InventoryRecord`

2. Créer les migrations pour les tables :
   - `library_books`, `library_loans`, `library_readers`, `library_authors`, `library_categories`
   - `museum_collections`, `museum_exhibitions`, `museum_conservation`, `museum_inventory`

3. Compléter les vues de formulaires (create.blade.php, edit.blade.php)
4. Compléter les vues de détails (show.blade.php)

#### Priorité MOYENNE
5. Implémenter la logique métier dans les contrôleurs (remplacer les TODO)
6. Ajouter la validation des formulaires (Request classes)
7. Implémenter les permissions et politiques d'accès
8. Créer les seeders de test pour Library et Museum

#### Priorité BASSE
9. Ajouter les fonctionnalités avancées (import/export, statistiques)
10. Tests unitaires et fonctionnels
