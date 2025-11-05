# Plan d'Implémentation - Refonte Système Records
**Projet** : Système de Gestion Documentaire Multi-Types avec Attachments Centralisés  
**Framework** : Laravel  
**Base de données** : MySQL 9.1.0 / MariaDB  
**Date de création** : 5 novembre 2025

---

## 📋 Vue d'Ensemble du Projet

### Objectif
Transformer le système monolithique actuel (`records`) en une architecture modulaire supportant 6 types de ressources documentaires distinctes avec système d'attachments centralisé.

### Architecture Cible
```
record_physicals (existant renommé)
├── record_digital_folders (NOUVEAU)
│   └── record_digital_documents (NOUVEAU)
├── record_artifacts (NOUVEAU)
├── record_books (NOUVEAU)
└── record_periodics (NOUVEAU)
```

### Contraintes Techniques
- ✅ Conservation de toutes les données existantes
- ✅ Gestion centralisée des fichiers via table `attachments`
- ✅ Aucun champ `file_*` dans les tables principales
- ✅ Compatibilité ascendante maintenue
- ✅ Système de métadonnées flexible via templates

---

## 🎯 Phase 0 : Préparation et Audit (Durée : 1-2 semaines)

### Tâche 0.1 : Audit de la base de données existante
**Priorité** : CRITIQUE  
**Complexité** : Moyenne  
**Durée estimée** : 2 jours

**Description** :
Analyser l'état actuel de la base de données pour identifier les dépendances et risques.

**Critères d'acceptation** :
- [ ] Liste complète des tables affectées identifiée
- [ ] Toutes les foreign keys recensées
- [ ] Volume de données mesuré (nombre de records, attachments)
- [ ] Dépendances applicatives documentées
- [ ] Points de blocage identifiés

**Commandes** :
```bash
# Générer un diagramme de la BDD actuelle
php artisan db:show --database=mysql

# Compter les enregistrements
php artisan tinker
>>> DB::table('records')->count();
>>> DB::table('attachments')->count();
>>> DB::table('record_attachment')->count();
```

**Livrables** :
- `docs/audit-database.md` : Rapport d'audit complet
- `docs/schema-current.png` : Diagramme ERD actuel

---

### Tâche 0.2 : Backup et stratégie de rollback
**Priorité** : CRITIQUE  
**Complexité** : Faible  
**Durée estimée** : 1 jour

**Description** :
Mettre en place une stratégie de sauvegarde complète avant toute modification.

**Critères d'acceptation** :
- [ ] Script de backup automatique créé
- [ ] Backup complet de la BDD effectué
- [ ] Procédure de rollback documentée
- [ ] Test de restauration validé

**Scripts** :
```bash
# Backup complet
mysqldump -u root -p shelve_db > backup_pre_refonte_$(date +%Y%m%d).sql

# Backup des tables critiques
mysqldump -u root -p shelve_db records attachments record_attachment > backup_critical_$(date +%Y%m%d).sql
```

**Livrables** :
- `scripts/backup.sh` : Script de backup automatisé
- `docs/rollback-procedure.md` : Procédure de restauration

---

### Tâche 0.3 : Configuration environnement de développement
**Priorité** : HAUTE  
**Complexité** : Faible  
**Durée estimée** : 1 jour

**Description** :
Préparer un environnement de développement dédié pour tester les migrations.

**Critères d'acceptation** :
- [ ] Base de données de test créée avec copie des données
- [ ] Configuration `.env.testing` validée
- [ ] PHPUnit configuré pour tests de migration
- [ ] Seeds de test préparés

**Commandes** :
```bash
# Créer BDD de test
mysql -u root -p -e "CREATE DATABASE shelve_test;"
mysqldump -u root -p shelve_db | mysql -u root -p shelve_test

# Configurer Laravel pour tests
php artisan config:clear
php artisan test --env=testing
```

**Livrables** :
- `.env.testing` : Configuration environnement de test
- `phpunit.xml` : Configuration PHPUnit mise à jour

---

## 🔧 Phase 1 : Extension de la Table Attachments (Durée : 1 semaine)

### Tâche 1.1 : Créer migration extension attachments
**Priorité** : CRITIQUE  
**Complexité** : Moyenne  
**Durée estimée** : 2 jours  
**Dépendances** : Tâche 0.1, 0.2

**Description** :
Étendre la table `attachments` pour supporter les nouveaux types de documents et métadonnées.

**Critères d'acceptation** :
- [ ] Migration créée avec ajout des types ENUM
- [ ] Colonnes métadonnées ajoutées (OCR, pages, etc.)
- [ ] Index de performance créés
- [ ] Migration testée en environnement de test
- [ ] Rollback validé

**Fichier à créer** :
`database/migrations/2025_11_06_000001_extend_attachments_table.php`

**Code de la migration** :
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ajouter les nouveaux types ENUM
        DB::statement("ALTER TABLE attachments MODIFY COLUMN type ENUM(
            'mail',
            'record',
            'communication',
            'transferting',
            'bulletinboardpost',
            'bulletinboard',
            'bulletinboardevent',
            'digital_folder',
            'digital_document',
            'artifact',
            'book',
            'periodic'
        ) NOT NULL");
        
        // 2. Ajouter les colonnes de métadonnées
        Schema::table('attachments', function (Blueprint $table) {
            $table->string('ocr_language', 10)->nullable()->after('content_text');
            $table->decimal('ocr_confidence', 5, 2)->nullable()->after('ocr_language')->comment('Score qualité OCR 0-100');
            $table->string('file_encoding', 50)->nullable()->after('mime_type');
            $table->integer('page_count')->nullable()->after('ocr_confidence')->comment('Nombre de pages PDF');
            $table->integer('word_count')->nullable()->after('page_count');
            $table->string('file_hash_md5', 32)->nullable()->after('crypt_sha512');
            $table->string('file_extension', 10)->nullable()->after('mime_type');
            $table->boolean('is_primary')->default(false)->after('type')->comment('Fichier principal');
            $table->integer('display_order')->default(0)->after('is_primary');
            $table->text('description')->nullable()->after('name');
            
            // Index de performance
            $table->index(['type', 'is_primary'], 'idx_type_primary');
            $table->index('file_hash_md5', 'idx_file_hash');
            $table->index('file_extension', 'idx_extension');
            $table->index('display_order', 'idx_display_order');
        });
    }
    
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndex('idx_type_primary');
            $table->dropIndex('idx_file_hash');
            $table->dropIndex('idx_extension');
            $table->dropIndex('idx_display_order');
            
            $table->dropColumn([
                'ocr_language',
                'ocr_confidence',
                'file_encoding',
                'page_count',
                'word_count',
                'file_hash_md5',
                'file_extension',
                'is_primary',
                'display_order',
                'description',
            ]);
        });
        
        // Restaurer l'ENUM original
        DB::statement("ALTER TABLE attachments MODIFY COLUMN type ENUM(
            'mail',
            'record',
            'communication',
            'transferting',
            'bulletinboardpost',
            'bulletinboard',
            'bulletinboardevent'
        ) NOT NULL");
    }
};
```

**Tests** :
```bash
# Tester la migration
php artisan migrate --path=database/migrations/2025_11_06_000001_extend_attachments_table.php

# Vérifier la structure
php artisan db:show attachments

# Tester le rollback
php artisan migrate:rollback --step=1
```

**Livrables** :
- Migration fonctionnelle et testée
- Documentation des nouveaux champs dans `docs/attachments-schema.md`

---

### Tâche 1.2 : Mettre à jour le modèle Attachment
**Priorité** : HAUTE  
**Complexité** : Faible  
**Durée estimée** : 1 jour  
**Dépendances** : Tâche 1.1

**Description** :
Adapter le modèle Eloquent `Attachment` pour refléter les nouveaux champs.

**Critères d'acceptation** :
- [ ] Propriété `$fillable` mise à jour
- [ ] Casts de types définis
- [ ] Accessors/Mutators créés si nécessaire
- [ ] Documentation PHPDoc complète

**Fichier à modifier** :
`app/Models/Attachment.php`

**Code** :
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'path',
        'name',
        'description',
        'crypt',
        'thumbnail_path',
        'size',
        'crypt_sha512',
        'file_hash_md5',
        'type',
        'mime_type',
        'file_extension',
        'file_encoding',
        'is_primary',
        'display_order',
        'content_text',
        'ocr_language',
        'ocr_confidence',
        'page_count',
        'word_count',
        'creator_id',
    ];
    
    protected $casts = [
        'size' => 'integer',
        'page_count' => 'integer',
        'word_count' => 'integer',
        'ocr_confidence' => 'decimal:2',
        'is_primary' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Types d'attachments disponibles
     */
    const TYPE_MAIL = 'mail';
    const TYPE_RECORD = 'record';
    const TYPE_COMMUNICATION = 'communication';
    const TYPE_TRANSFERRING = 'transferring';
    const TYPE_DIGITAL_FOLDER = 'digital_folder';
    const TYPE_DIGITAL_DOCUMENT = 'digital_document';
    const TYPE_ARTIFACT = 'artifact';
    const TYPE_BOOK = 'book';
    const TYPE_PERIODIC = 'periodic';
    
    /**
     * Relations
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    
    /**
     * Accessors
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->path . $this->crypt);
    }
    
    /**
     * Scopes
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
    
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
    
    public function scopeOrderedByDisplay($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at');
    }
}
```

**Tests** :
```php
// tests/Unit/AttachmentTest.php
public function test_attachment_has_new_fields()
{
    $attachment = Attachment::factory()->create([
        'type' => Attachment::TYPE_DIGITAL_DOCUMENT,
        'is_primary' => true,
        'page_count' => 10,
        'ocr_confidence' => 95.50,
    ]);
    
    $this->assertTrue($attachment->is_primary);
    $this->assertEquals(10, $attachment->page_count);
    $this->assertEquals(95.50, $attachment->ocr_confidence);
}
```

**Livrables** :
- Modèle `Attachment` mis à jour
- Tests unitaires passant

---

### Tâche 1.3 : Tests d'intégrité des attachments
**Priorité** : HAUTE  
**Complexité** : Moyenne  
**Durée estimée** : 2 jours  
**Dépendances** : Tâche 1.2

**Description** :
Créer une suite de tests complète pour valider l'extension de la table attachments.

**Critères d'acceptation** :
- [ ] Tests unitaires sur le modèle créés
- [ ] Tests de migration créés
- [ ] Tests d'intégrité référentielle créés
- [ ] Tests de performance sur les nouveaux index
- [ ] Tous les tests passent

**Fichier à créer** :
`tests/Feature/AttachmentExtensionTest.php`

**Code des tests** :
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttachmentExtensionTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_create_digital_document_attachment()
    {
        $user = User::factory()->create();
        
        $attachment = Attachment::create([
            'name' => 'Contrat.pdf',
            'description' => 'Contrat commercial 2025',
            'path' => 'documents/2025/',
            'crypt' => 'abc123xyz',
            'type' => Attachment::TYPE_DIGITAL_DOCUMENT,
            'mime_type' => 'application/pdf',
            'file_extension' => 'pdf',
            'size' => 1024000,
            'is_primary' => true,
            'page_count' => 15,
            'creator_id' => $user->id,
        ]);
        
        $this->assertDatabaseHas('attachments', [
            'name' => 'Contrat.pdf',
            'type' => 'digital_document',
            'is_primary' => true,
            'page_count' => 15,
        ]);
    }
    
    public function test_can_set_ocr_metadata()
    {
        $attachment = Attachment::factory()->create([
            'type' => Attachment::TYPE_DIGITAL_DOCUMENT,
            'content_text' => 'Texte extrait du document',
            'ocr_language' => 'fr',
            'ocr_confidence' => 98.75,
        ]);
        
        $this->assertEquals('fr', $attachment->ocr_language);
        $this->assertEquals(98.75, $attachment->ocr_confidence);
    }
    
    public function test_primary_scope_filters_correctly()
    {
        Attachment::factory()->create(['is_primary' => true, 'type' => 'digital_document']);
        Attachment::factory()->create(['is_primary' => false, 'type' => 'digital_document']);
        Attachment::factory()->create(['is_primary' => true, 'type' => 'artifact']);
        
        $primaryDocs = Attachment::ofType('digital_document')->primary()->get();
        
        $this->assertCount(1, $primaryDocs);
        $this->assertTrue($primaryDocs->first()->is_primary);
    }
    
    public function test_file_hash_index_improves_performance()
    {
        // Créer 1000 attachments
        Attachment::factory()->count(1000)->create();
        
        $start = microtime(true);
        $result = Attachment::where('file_hash_md5', 'test_hash')->first();
        $duration = microtime(true) - $start;
        
        // L'index devrait rendre la requête très rapide
        $this->assertLessThan(0.01, $duration, 'Query should be fast with index');
    }
}
```

**Commandes de test** :
```bash
# Exécuter les tests
php artisan test --filter=AttachmentExtensionTest

# Avec couverture de code
php artisan test --filter=AttachmentExtensionTest --coverage
```

**Livrables** :
- Suite de tests complète et passante
- Rapport de couverture de code > 80%

---

## 🏗️ Phase 2 : Renommage et Migration de `records` (Durée : 1 semaine)

### Tâche 2.1 : Créer migration de renommage vers record_physicals
**Priorité** : CRITIQUE  
**Complexité** : HAUTE  
**Durée estimée** : 3 jours  
**Dépendances** : Phase 1 complète

**Description** :
Renommer la table `records` en `record_physicals` en préservant toutes les données et relations.

**Critères d'acceptation** :
- [ ] Migration créée avec RENAME TABLE
- [ ] Toutes les foreign keys mises à jour
- [ ] Tables pivot renommées (record_author → record_physical_author, etc.)
- [ ] Triggers et procédures stockées mis à jour si existants
- [ ] Test de migration validé sur copie de production
- [ ] Rollback testé et fonctionnel

**Fichier à créer** :
`database/migrations/2025_11_07_000001_rename_records_to_record_physicals.php`

**Code de la migration** :
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Désactiver temporairement les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // 2. Renommer la table principale
        Schema::rename('records', 'record_physicals');
        
        // 3. Renommer les tables pivot
        Schema::rename('record_author', 'record_physical_author');
        Schema::rename('record_attachment', 'record_physical_attachment');
        Schema::rename('record_keyword', 'record_physical_keyword');
        Schema::rename('record_thesaurus_concept', 'record_physical_thesaurus_concept');
        Schema::rename('record_container', 'record_physical_container');
        
        // 4. Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        // 5. Mettre à jour les colonnes de foreign keys dans d'autres tables
        // (à adapter selon votre schéma exact)
        
        // Log de la migration
        DB::table('migrations_log')->insert([
            'migration' => '2025_11_07_000001_rename_records_to_record_physicals',
            'executed_at' => now(),
            'records_count' => DB::table('record_physicals')->count(),
        ]);
    }
    
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Restaurer les noms originaux
        Schema::rename('record_physicals', 'records');
        Schema::rename('record_physical_author', 'record_author');
        Schema::rename('record_physical_attachment', 'record_attachment');
        Schema::rename('record_physical_keyword', 'record_keyword');
        Schema::rename('record_physical_thesaurus_concept', 'record_thesaurus_concept');
        Schema::rename('record_physical_container', 'record_container');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
```

**Procédure de test** :
```bash
# 1. Backup avant migration
mysqldump -u root -p shelve_db > backup_before_rename_$(date +%Y%m%d_%H%M%S).sql

# 2. Compter les enregistrements AVANT
php artisan tinker
>>> $recordsCount = DB::table('records')->count();
>>> $pivotAuthorCount = DB::table('record_author')->count();

# 3. Exécuter la migration
php artisan migrate --path=database/migrations/2025_11_07_000001_rename_records_to_record_physicals.php

# 4. Vérifier APRÈS
>>> $physicalCount = DB::table('record_physicals')->count();
>>> $pivotPhysicalAuthorCount = DB::table('record_physical_author')->count();
>>> assert($recordsCount === $physicalCount);

# 5. Tester le rollback
php artisan migrate:rollback --step=1

# 6. Vérifier que tout est restauré
>>> DB::table('records')->count();
```

**Points d'attention** :
- ⚠️ Cette migration peut prendre du temps sur de grosses bases
- ⚠️ Prévoir une fenêtre de maintenance
- ⚠️ Tester en environnement de pré-production d'abord
- ⚠️ Documenter toutes les applications/scripts qui référencent la table `records`

**Livrables** :
- Migration fonctionnelle et testée
- Documentation de la procédure de migration dans `docs/migration-records-to-physicals.md`
- Checklist de validation post-migration

---

### Tâche 2.2 : Mettre à jour le modèle Record vers RecordPhysical
**Priorité** : CRITIQUE  
**Complexité** : Moyenne  
**Durée estimée** : 2 jours  
**Dépendances** : Tâche 2.1

**Description** :
Renommer le modèle `Record` en `RecordPhysical` et mettre à jour toutes les références.

**Critères d'acceptation** :
- [ ] Fichier modèle renommé : `Record.php` → `RecordPhysical.php`
- [ ] Propriété `$table = 'record_physicals'` définie
- [ ] Toutes les relations mises à jour
- [ ] Controllers mis à jour
- [ ] Routes mises à jour
- [ ] Tests mis à jour
- [ ] Recherche globale effectuée pour trouver toutes les références

**Fichier à renommer et modifier** :
`app/Models/Record.php` → `app/Models/RecordPhysical.php`

**Code du modèle** :
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecordPhysical extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'record_physicals';
    
    protected $fillable = [
        'code',
        'name',
        'date_format',
        'date_start',
        'date_end',
        'date_exact',
        'level_id',
        'status_id',
        'support_id',
        'activity_id',
        'width',
        'width_description',
        'biographical_history',
        'archival_history',
        'acquisition_source',
        'content',
        'appraisal',
        'arrangement',
        'access_conditions',
        'reproduction_conditions',
        'language_material',
        'characteristic',
        'finding_aids',
        'location_original',
        'location_copy',
        'related_unit',
        'publication_note',
        'note',
        'archivist_note',
        'rule_convention',
        'parent_id',
        'user_id',
    ];
    
    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
        'date_exact' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Relations
     */
    public function level()
    {
        return $this->belongsTo(RecordLevel::class, 'level_id');
    }
    
    public function status()
    {
        return $this->belongsTo(RecordStatus::class, 'status_id');
    }
    
    public function support()
    {
        return $this->belongsTo(RecordSupport::class, 'support_id');
    }
    
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function parent()
    {
        return $this->belongsTo(RecordPhysical::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(RecordPhysical::class, 'parent_id');
    }
    
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'record_physical_author');
    }
    
    public function attachments()
    {
        return $this->belongsToMany(Attachment::class, 'record_physical_attachment')
            ->withPivot(['is_primary', 'display_order', 'description'])
            ->withTimestamps();
    }
    
    public function keywords()
    {
        return $this->belongsToMany(Keyword::class, 'record_physical_keyword');
    }
    
    public function thesaurusConcepts()
    {
        return $this->belongsToMany(ThesaurusConcept::class, 'record_physical_thesaurus_concept');
    }
    
    public function containers()
    {
        return $this->belongsToMany(Container::class, 'record_physical_container');
    }
}
```

**Script de recherche et remplacement** :
```bash
# Rechercher toutes les occurrences de "Record::" ou "use App\Models\Record"
grep -r "use App\\Models\\Record;" app/
grep -r "Record::" app/
grep -r "new Record" app/

# Remplacer automatiquement (avec précaution !)
find app/ -type f -name "*.php" -exec sed -i 's/use App\\Models\\Record;/use App\\Models\\RecordPhysical;/g' {} \;
find app/ -type f -name "*.php" -exec sed -i 's/Record::/RecordPhysical::/g' {} \;
```

**Fichiers à vérifier et mettre à jour** :
- [ ] `app/Http/Controllers/RecordController.php` → `RecordPhysicalController.php`
- [ ] `routes/web.php` et `routes/api.php`
- [ ] Tous les services dans `app/Services/`
- [ ] Tous les tests dans `tests/`
- [ ] Les factories dans `database/factories/`
- [ ] Les seeders dans `database/seeders/`

**Livrables** :
- Modèle `RecordPhysical` opérationnel
- Toutes les références mises à jour
- Documentation des changements dans `docs/record-to-recordphysical-changelog.md`

---

### Tâche 2.3 : Tests de régression après renommage
**Priorité** : CRITIQUE  
**Complexité** : Moyenne  
**Durée estimée** : 2 jours  
**Dépendances** : Tâche 2.2

**Description** :
Valider que toutes les fonctionnalités existantes fonctionnent après le renommage.

**Critères d'acceptation** :
- [ ] Tous les tests existants passent
- [ ] Tests de CRUD sur RecordPhysical créés
- [ ] Tests des relations validés
- [ ] Tests d'API validés
- [ ] Tests de performance comparés (avant/après)

**Fichier de test** :
`tests/Feature/RecordPhysicalMigrationTest.php`

**Code** :
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\RecordPhysical;
use App\Models\Author;
use App\Models\Attachment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecordPhysicalMigrationTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_create_record_physical()
    {
        $record = RecordPhysical::create([
            'code' => 'TEST-2025-001',
            'name' => 'Dossier de test',
            'content' => 'Contenu du dossier physique',
        ]);
        
        $this->assertDatabaseHas('record_physicals', [
            'code' => 'TEST-2025-001',
        ]);
    }
    
    public function test_record_physical_has_authors_relation()
    {
        $record = RecordPhysical::factory()->create();
        $author = Author::factory()->create();
        
        $record->authors()->attach($author);
        
        $this->assertCount(1, $record->authors);
        $this->assertEquals($author->id, $record->authors->first()->id);
    }
    
    public function test_record_physical_has_attachments_relation()
    {
        $record = RecordPhysical::factory()->create();
        $attachment = Attachment::factory()->create(['type' => 'record']);
        
        $record->attachments()->attach($attachment, [
            'is_primary' => true,
            'display_order' => 1,
        ]);
        
        $this->assertCount(1, $record->attachments);
        $this->assertTrue($record->attachments->first()->pivot->is_primary);
    }
    
    public function test_hierarchical_relations_work()
    {
        $parent = RecordPhysical::factory()->create();
        $child = RecordPhysical::factory()->create(['parent_id' => $parent->id]);
        
        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertCount(1, $parent->children);
    }
}
```

**Commandes de test** :
```bash
# Exécuter tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=RecordPhysicalMigrationTest

# Avec rapport détaillé
php artisan test --testdox
```

**Livrables** :
- Suite de tests de régression passante
- Rapport de validation dans `docs/regression-test-report.md`

---

## 📁 Phase 3 : Système de Types pour Dossiers et Documents Numériques (Durée : 1 semaine)

### Tâche 3.1 : Créer les tables de types personnalisés
**Priorité** : HAUTE  
**Complexité** : Moyenne  
**Durée estimée** : 2 jours  
**Dépendances** : Phase 2 complète

**Description** :
Créer les tables `record_digital_folder_types` et `record_digital_document_types` pour permettre la personnalisation des catégories.

**Critères d'acceptation** :
- [ ] Migration créée pour les deux tables
- [ ] Relation avec `metadata_templates` établie
- [ ] Index de performance créés
- [ ] Données de seed préparées
- [ ] Documentation complète

**Fichier à créer** :
`database/migrations/2025_11_08_000001_create_digital_types_tables.php`

**Code de la migration** :
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des types de dossiers numériques
        Schema::create('record_digital_folder_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code technique : CONTRACTS, HR, PROJECTS');
            $table->string('name', 200)->comment('Nom du type');
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable()->comment('Icône FontAwesome');
            $table->string('color', 7)->nullable()->comment('Code couleur hexa');
            
            // Relation avec les templates de métadonnées
            $table->unsignedBigInteger('metadata_template_id')->nullable();
            $table->foreign('metadata_template_id')->references('id')->on('metadata_templates')->onDelete('set null');
            
            // Configuration du code généré
            $table->string('code_prefix', 10)->nullable()->comment('Préfixe du code : CTR, HR, PRJ');
            $table->string('code_pattern', 100)->default('{{PREFIX}}-{{YEAR}}-{{SEQ}}');
            
            // Règles métier
            $table->enum('default_access_level', ['public', 'internal', 'restricted', 'confidential', 'secret'])->default('internal');
            $table->boolean('requires_approval')->default(false)->comment('Nécessite une approbation');
            $table->json('mandatory_metadata')->nullable()->comment('Métadonnées obligatoires');
            $table->json('allowed_document_types')->nullable()->comment('Types de documents autorisés');
            
            // Système
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)->comment('Type système non modifiable');
            $table->integer('display_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('code');
            $table->index('is_active');
            $table->index('display_order');
        });
        
        // Table des types de documents numériques
        Schema::create('record_digital_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code technique : INVOICE, QUOTE, CONTRACT_DOC');
            $table->string('name', 200)->comment('Nom du type');
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 7)->nullable();
            
            // Relation avec les templates de métadonnées
            $table->unsignedBigInteger('metadata_template_id')->nullable();
            $table->foreign('metadata_template_id')->references('id')->on('metadata_templates')->onDelete('set null');
            
            // Configuration du code généré
            $table->string('code_prefix', 10)->nullable();
            $table->string('code_pattern', 100)->default('{{PREFIX}}-{{YEAR}}-{{SEQ}}');
            
            // Règles métier
            $table->enum('default_access_level', ['public', 'internal', 'restricted', 'confidential', 'secret'])->default('internal');
            $table->json('allowed_mime_types')->nullable()->comment('Types MIME autorisés : ["application/pdf"]');
            $table->json('allowed_extensions')->nullable()->comment('Extensions autorisées : [".pdf", ".docx"]');
            $table->bigInteger('max_file_size')->nullable()->comment('Taille max en octets');
            $table->boolean('requires_signature')->default(false);
            $table->boolean('requires_approval')->default(false);
            $table->json('mandatory_metadata')->nullable();
            $table->integer('retention_years')->nullable()->comment('Durée de conservation en années');
            
            // Versioning
            $table->boolean('enable_versioning')->default(true);
            $table->integer('max_versions')->nullable()->comment('Nombre max de versions conservées');
            
            // Système
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->integer('display_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('code');
            $table->index('is_active');
            $table->index('display_order');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('record_digital_document_types');
        Schema::dropIfExists('record_digital_folder_types');
    }
};
```

**Livrables** :
- Migration fonctionnelle
- Documentation dans `docs/digital-types-schema.md`

---

### Tâche 3.2 : Créer les modèles des types
**Priorité** : HAUTE  
**Complexité** : Faible  
**Durée estimée** : 1 jour  
**Dépendances** : Tâche 3.1

**Description** :
Créer les modèles Eloquent pour `RecordDigitalFolderType` et `RecordDigitalDocumentType`.

**Fichiers à créer** :
- `app/Models/RecordDigitalFolderType.php`
- `app/Models/RecordDigitalDocumentType.php`

**Code** :
```php
<?php
// app/Models/RecordDigitalFolderType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordDigitalFolderType extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'color',
        'metadata_template_id',
        'code_prefix',
        'code_pattern',
        'default_access_level',
        'requires_approval',
        'mandatory_metadata',
        'allowed_document_types',
        'is_active',
        'is_system',
        'display_order',
    ];
    
    protected $casts = [
        'mandatory_metadata' => 'array',
        'allowed_document_types' => 'array',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'display_order' => 'integer',
    ];
    
    public function metadataTemplate()
    {
        return $this->belongsTo(MetadataTemplate::class);
    }
    
    public function folders()
    {
        return $this->hasMany(RecordDigitalFolder::class, 'folder_type_id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
```

**Livrables** :
- Modèles créés et fonctionnels
- Tests unitaires basiques

---

### Tâche 3.3 : Seeder pour types prédéfinis
**Priorité** : HAUTE  
**Complexité** : Faible  
**Durée estimée** : 2 jours  
**Dépendances** : Tâche 3.2

**Description** :
Créer un seeder avec des types de dossiers et documents prédéfinis pour les cas d'usage courants.

**Fichier à créer** :
`database/seeders/DigitalTypesSeeder.php`

**Code** :
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RecordDigitalFolderType;
use App\Models\RecordDigitalDocumentType;
use App\Models\MetadataTemplate;

class DigitalTypesSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // TYPES DE DOSSIERS
        // ==========================================
        
        // 1. Dossier Contrats
        RecordDigitalFolderType::create([
            'code' => 'CONTRACTS',
            'name' => 'Contrats',
            'description' => 'Dossiers de gestion des contrats commerciaux et juridiques',
            'icon' => 'file-contract',
            'color' => '#dc3545',
            'code_prefix' => 'CTR',
            'default_access_level' => 'restricted',
            'requires_approval' => true,
            'mandatory_metadata' => ['contract_number', 'contractor', 'contract_date'],
            'is_system' => true,
            'display_order' => 1,
        ]);
        
        // 2. Dossier RH
        RecordDigitalFolderType::create([
            'code' => 'HUMAN_RESOURCES',
            'name' => 'Ressources Humaines',
            'description' => 'Dossiers du personnel et gestion RH',
            'icon' => 'people',
            'color' => '#6f42c1',
            'code_prefix' => 'HR',
            'default_access_level' => 'confidential',
            'requires_approval' => true,
            'mandatory_metadata' => ['employee_id', 'department'],
            'is_system' => true,
            'display_order' => 2,
        ]);
        
        // 3. Factures Fournisseurs
        RecordDigitalFolderType::create([
            'code' => 'SUPPLIER_INVOICES',
            'name' => 'Factures Fournisseurs',
            'description' => 'Dossiers de facturation fournisseurs',
            'icon' => 'receipt',
            'color' => '#198754',
            'code_prefix' => 'FRN',
            'default_access_level' => 'restricted',
            'mandatory_metadata' => ['supplier_name', 'fiscal_year'],
            'is_system' => true,
            'display_order' => 3,
        ]);
        
        // 4. Comptabilité
        RecordDigitalFolderType::create([
            'code' => 'ACCOUNTING',
            'name' => 'Comptabilité',
            'description' => 'Dossiers comptables et financiers',
            'icon' => 'calculator',
            'color' => '#ffc107',
            'code_prefix' => 'ACC',
            'default_access_level' => 'restricted',
            'mandatory_metadata' => ['fiscal_year', 'account_number'],
            'is_system' => true,
            'display_order' => 4,
        ]);
        
        // 5. Projets
        RecordDigitalFolderType::create([
            'code' => 'PROJECTS',
            'name' => 'Projets',
            'description' => 'Dossiers de gestion de projets',
            'icon' => 'diagram-3',
            'color' => '#0dcaf0',
            'code_prefix' => 'PRJ',
            'default_access_level' => 'internal',
            'mandatory_metadata' => ['project_code', 'project_manager'],
            'is_system' => true,
            'display_order' => 5,
        ]);
        
        // ==========================================
        // TYPES DE DOCUMENTS
        // ==========================================
        
        // 1. Facture
        RecordDigitalDocumentType::create([
            'code' => 'INVOICE',
            'name' => 'Facture',
            'description' => 'Facture fournisseur ou client',
            'icon' => 'file-invoice',
            'color' => '#198754',
            'code_prefix' => 'INV',
            'default_access_level' => 'restricted',
            'allowed_mime_types' => ['application/pdf', 'image/jpeg', 'image/png'],
            'allowed_extensions' => ['.pdf', '.jpg', '.jpeg', '.png'],
            'max_file_size' => 10485760, // 10 MB
            'requires_approval' => true,
            'mandatory_metadata' => ['invoice_number', 'invoice_date', 'total_amount'],
            'retention_years' => 10,
            'enable_versioning' => true,
            'is_system' => true,
            'display_order' => 1,
        ]);
        
        // 2. Devis
        RecordDigitalDocumentType::create([
            'code' => 'QUOTE',
            'name' => 'Devis',
            'description' => 'Devis commercial',
            'icon' => 'file-text',
            'color' => '#0dcaf0',
            'code_prefix' => 'QTE',
            'default_access_level' => 'internal',
            'allowed_mime_types' => ['application/pdf'],
            'allowed_extensions' => ['.pdf'],
            'mandatory_metadata' => ['quote_number', 'quote_date', 'client_name'],
            'retention_years' => 5,
            'is_system' => true,
            'display_order' => 2,
        ]);
        
        // 3. Contrat
        RecordDigitalDocumentType::create([
            'code' => 'CONTRACT_DOC',
            'name' => 'Contrat',
            'description' => 'Document contractuel',
            'icon' => 'file-contract',
            'color' => '#dc3545',
            'code_prefix' => 'CON',
            'default_access_level' => 'confidential',
            'allowed_mime_types' => ['application/pdf'],
            'requires_signature' => true,
            'requires_approval' => true,
            'mandatory_metadata' => ['contract_number', 'signing_date', 'parties'],
            'retention_years' => 30,
            'enable_versioning' => true,
            'max_versions' => 50,
            'is_system' => true,
            'display_order' => 3,
        ]);
        
        // 4. Rapport
        RecordDigitalDocumentType::create([
            'code' => 'REPORT',
            'name' => 'Rapport',
            'description' => 'Rapport technique ou d\'activité',
            'icon' => 'file-earmark-text',
            'color' => '#6610f2',
            'code_prefix' => 'RPT',
            'default_access_level' => 'internal',
            'allowed_mime_types' => ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'allowed_extensions' => ['.pdf', '.docx'],
            'enable_versioning' => true,
            'is_system' => true,
            'display_order' => 4,
        ]);
        
        // 5. Note de service
        RecordDigitalDocumentType::create([
            'code' => 'MEMO',
            'name' => 'Note de service',
            'description' => 'Communication interne officielle',
            'icon' => 'file-earmark-medical',
            'color' => '#fd7e14',
            'code_prefix' => 'MEM',
            'default_access_level' => 'internal',
            'allowed_mime_types' => ['application/pdf'],
            'retention_years' => 3,
            'is_system' => true,
            'display_order' => 5,
        ]);
        
        $this->command->info('✅ Types de dossiers et documents créés avec succès !');
    }
}
```

**Commande d'exécution** :
```bash
php artisan db:seed --class=DigitalTypesSeeder
```

**Livrables** :
- Seeder fonctionnel
- Documentation des types dans `docs/digital-types-catalog.md`

---

## 📄 Phase 4 : Création des Dossiers Numériques (RecordDigitalFolder) (Durée : 1-2 semaines)

### Tâche 4.1 : Créer la table record_digital_folders
**Priorité** : CRITIQUE  
**Complexité** : HAUTE  
**Durée estimée** : 3 jours  
**Dépendances** : Phase 3 complète

**Description** :
Créer la table pour les dossiers numériques avec structure hiérarchique et support des métadonnées.

**Critères d'acceptation** :
- [ ] Migration créée avec tous les champs
- [ ] Structure hiérarchique (parent_id) implémentée
- [ ] Contraintes d'intégrité définies
- [ ] Index de performance créés
- [ ] Triggers créés si nécessaires

**Fichier à créer** :
`database/migrations/2025_11_09_000001_create_record_digital_folders_table.php`

**Code de la migration** :
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_digital_folders', function (Blueprint $table) {
            $table->id();
            
            // Identification
            $table->string('code', 50)->unique()->comment('Code unique : DF-YYYY-NNNN');
            $table->string('name', 250);
            $table->text('description')->nullable();
            
            // Hiérarchie (parent/enfant)
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Dossier parent');
            $table->foreign('parent_id')->references('id')->on('record_digital_folders')->onDelete('restrict');
            
            // Type de dossier
            $table->unsignedBigInteger('folder_type_id')->nullable();
            $table->foreign('folder_type_id')->references('id')->on('record_digital_folder_types')->onDelete('set null');
            
            // Statistiques
            $table->integer('children_count')->default(0)->comment('Nb documents directs');
            $table->bigInteger('total_size')->default(0)->comment('Taille totale en octets');
            
            // Personnalisation
            $table->string('color', 7)->nullable()->comment('Code couleur hexa');
            $table->string('icon', 50)->nullable();
            
            // Sécurité
            $table->enum('access_level', ['public', 'internal', 'restricted', 'confidential', 'secret'])->default('internal');
            $table->string('access_password')->nullable()->comment('Mot de passe chiffré si protégé');
            
            // Verrouillage
            $table->boolean('is_locked')->default(false);
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->timestamp('locked_at')->nullable();
            
            // Archivage
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();
            
            // Configuration d'affichage
            $table->string('order_criteria', 50)->default('name')->comment('Critère de tri');
            $table->enum('display_mode', ['list', 'grid', 'timeline', 'tree'])->default('list');
            
            // Métadonnées
            $table->unsignedBigInteger('metadata_template_id')->nullable();
            $table->foreign('metadata_template_id')->references('id')->on('metadata_templates')->onDelete('set null');
            
            // Statut
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
            
            // Relations communes
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('organisation_id')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys audit
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('locked_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
            
            // Index de performance
            $table->index('parent_id', 'idx_parent');
            $table->index('folder_type_id', 'idx_folder_type');
            $table->index(['organisation_id', 'status'], 'idx_organisation_status');
            $table->index('status', 'idx_status');
            $table->index('is_archived', 'idx_archived');
            $table->index('created_at', 'idx_created');
        });
        
        // Table pivot pour attachments (icônes, miniatures)
        Schema::create('record_digital_folder_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id');
            $table->unsignedBigInteger('attachment_id');
            
            $table->enum('attachment_role', ['icon', 'thumbnail', 'cover', 'other'])->default('other');
            $table->boolean('is_primary')->default(false);
            $table->integer('display_order')->default(0);
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            $table->foreign('folder_id')->references('id')->on('record_digital_folders')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
            
            $table->unique(['folder_id', 'attachment_id']);
            $table->index('attachment_role');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('record_digital_folder_attachments');
        Schema::dropIfExists('record_digital_folders');
    }
};
```

**Livrables** :
- Migration fonctionnelle et testée
- Documentation du schéma

---

## 📄 Phase 5 : Documents Numériques (RecordDigitalDocument) (Durée : 1-2 semaines)

### Tâche 5.1 : Créer la table record_digital_documents
**Priorité** : CRITIQUE  
**Complexité** : HAUTE  
**Durée estimée** : 3 jours  
**Dépendances** : Phase 4 complète

**Description** :
Créer la table pour les documents numériques avec versioning, checkout/checkin, et gestion des signatures.

**Critères d'acceptation** :
- [ ] Migration créée avec tous les champs
- [ ] Support du versioning implémenté
- [ ] Système checkout/checkin fonctionnel
- [ ] Gestion des signatures électroniques
- [ ] Index de performance créés

**Fichier à créer** :
`database/migrations/2025_11_10_000001_create_record_digital_documents_table.php`

**Code de la migration** :
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_digital_documents', function (Blueprint $table) {
            $table->id();
            
            // Identification
            $table->string('code', 50)->unique()->comment('Code unique : DD-YYYY-NNNN');
            $table->string('name', 250);
            $table->text('description')->nullable();
            
            // Type de document
            $table->unsignedBigInteger('document_type_id')->nullable();
            $table->foreign('document_type_id')->references('id')->on('record_digital_document_types')->onDelete('set null');
            
            // Rattachement au dossier
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->foreign('folder_id')->references('id')->on('record_digital_folders')->onDelete('cascade');
            
            // Fichier physique via Attachments
            $table->unsignedBigInteger('current_attachment_id')->nullable()->comment('Version actuelle');
            $table->foreign('current_attachment_id')->references('id')->on('attachments')->onDelete('set null');
            
            // Versioning
            $table->integer('version_number')->default(1);
            $table->integer('total_versions')->default(1);
            $table->boolean('is_latest_version')->default(true);
            
            // Checkout / Checkin
            $table->boolean('is_checked_out')->default(false);
            $table->unsignedBigInteger('checked_out_by')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->text('checkout_reason')->nullable();
            
            // Statut du document
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'archived', 'obsolete'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            
            // Signature électronique
            $table->boolean('is_signed')->default(false);
            $table->text('signature_data')->nullable()->comment('JSON : signatures multiples');
            $table->timestamp('signed_at')->nullable();
            
            // Métadonnées personnalisées
            $table->json('metadata')->nullable();
            
            // Dates importantes
            $table->date('document_date')->nullable()->comment('Date du document');
            $table->date('received_date')->nullable();
            $table->date('expiry_date')->nullable();
            
            // Sécurité
            $table->enum('access_level', ['public', 'internal', 'restricted', 'confidential', 'secret'])->default('internal');
            $table->boolean('requires_approval')->default(false);
            
            // Conservation
            $table->integer('retention_years')->nullable();
            $table->date('destruction_date')->nullable();
            
            // Statistiques
            $table->integer('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            
            // Indexation full-text
            $table->text('full_text_content')->nullable()->comment('Contenu extrait pour recherche');
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('code');
            $table->index('folder_id');
            $table->index('document_type_id');
            $table->index('status');
            $table->index('is_checked_out');
            $table->index('document_date');
            $table->index('created_at');
            $table->fullText(['name', 'description', 'full_text_content'], 'documents_fulltext');
        });
        
        // Table des versions
        Schema::create('record_digital_document_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_id');
            $table->foreign('document_id')->references('id')->on('record_digital_documents')->onDelete('cascade');
            
            $table->integer('version_number');
            $table->unsignedBigInteger('attachment_id');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('restrict');
            
            $table->text('version_notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->unique(['document_id', 'version_number']);
            $table->index('document_id');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('record_digital_document_versions');
        Schema::dropIfExists('record_digital_documents');
    }
};
```

**Commande de test** :
```bash
php artisan migrate --path=database/migrations/2025_11_10_000001_create_record_digital_documents_table.php
php artisan migrate:rollback --step=1
```

**Livrables** :
- Migration fonctionnelle
- Table des versions créée
- Documentation du système de versioning

### Tâche 5.2 : Créer le modèle RecordDigitalDocument
**Priorité** : CRITIQUE  
**Complexité** : HAUTE  
**Durée estimée** : 2 jours  
**Dépendances** : Tâche 5.1

**Description** :
Créer le modèle Eloquent avec relations, scopes, et méthodes métier pour checkout/checkin.

**Fichier à créer** :
`app/Models/RecordDigitalDocument.php`

**Code du modèle** :
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RecordDigitalDocument extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'code', 'name', 'description', 'document_type_id', 'folder_id',
        'current_attachment_id', 'version_number', 'total_versions',
        'status', 'metadata', 'document_date', 'received_date', 'expiry_date',
        'access_level', 'retention_years', 'destruction_date',
        'full_text_content', 'created_by', 'updated_by'
    ];
    
    protected $casts = [
        'metadata' => 'array',
        'signature_data' => 'array',
        'document_date' => 'date',
        'received_date' => 'date',
        'expiry_date' => 'date',
        'destruction_date' => 'date',
        'is_checked_out' => 'boolean',
        'is_signed' => 'boolean',
        'is_latest_version' => 'boolean',
        'requires_approval' => 'boolean',
        'checked_out_at' => 'datetime',
        'approved_at' => 'datetime',
        'signed_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
    ];
    
    // Relations
    public function folder(): BelongsTo
    {
        return $this->belongsTo(RecordDigitalFolder::class, 'folder_id');
    }
    
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(RecordDigitalDocumentType::class, 'document_type_id');
    }
    
    public function currentAttachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'current_attachment_id');
    }
    
    public function versions(): HasMany
    {
        return $this->hasMany(RecordDigitalDocumentVersion::class, 'document_id')->orderBy('version_number', 'desc');
    }
    
    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }
    
    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_checked_out', false);
    }
    
    public function scopeCheckedOut($query)
    {
        return $query->where('is_checked_out', true);
    }
    
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
    
    // Checkout/Checkin Methods
    public function checkout(?string $reason = null): bool
    {
        if ($this->is_checked_out) {
            throw new \Exception("Document already checked out by {$this->checkedOutBy->name}");
        }
        
        $this->is_checked_out = true;
        $this->checked_out_by = Auth::id();
        $this->checked_out_at = now();
        $this->checkout_reason = $reason;
        
        return $this->save();
    }
    
    public function checkin(Attachment $newVersion, ?string $notes = null): bool
    {
        if (!$this->is_checked_out) {
            throw new \Exception("Document is not checked out");
        }
        
        if ($this->checked_out_by !== Auth::id()) {
            throw new \Exception("Only the user who checked out can check in");
        }
        
        \DB::transaction(function () use ($newVersion, $notes) {
            // Create version record
            $this->versions()->create([
                'version_number' => $this->version_number + 1,
                'attachment_id' => $newVersion->id,
                'version_notes' => $notes,
                'created_by' => Auth::id(),
            ]);
            
            // Update document
            $this->version_number++;
            $this->total_versions++;
            $this->current_attachment_id = $newVersion->id;
            $this->is_checked_out = false;
            $this->checked_out_by = null;
            $this->checked_out_at = null;
            $this->checkout_reason = null;
            
            $this->save();
        });
        
        return true;
    }
    
    public function cancelCheckout(): bool
    {
        if (!$this->is_checked_out) {
            return false;
        }
        
        $this->is_checked_out = false;
        $this->checked_out_by = null;
        $this->checked_out_at = null;
        $this->checkout_reason = null;
        
        return $this->save();
    }
    
    // Signature Methods
    public function sign(array $signatureData): bool
    {
        $signatures = $this->signature_data ?? [];
        $signatures[] = array_merge($signatureData, [
            'signed_by' => Auth::id(),
            'signed_at' => now()->toISOString(),
        ]);
        
        $this->signature_data = $signatures;
        $this->is_signed = true;
        $this->signed_at = now();
        
        return $this->save();
    }
    
    // Auto-generate code
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($document) {
            if (!$document->code) {
                $year = date('Y');
                $prefix = $document->documentType?->code_prefix ?? 'DD';
                $count = static::whereYear('created_at', $year)->count() + 1;
                $document->code = sprintf('%s-%s-%04d', $prefix, $year, $count);
            }
        });
    }
}
```

**Livrables** :
- Modèle avec checkout/checkin
- Méthodes de signature
- Relations complètes

---

## 📄 Phase 6 : Objets de Musée (RecordArtifact) (Durée : 1-2 semaines)

### Tâche 6.1 : Créer la table record_artifacts
**Priorité** : HAUTE  
**Complexité** : MOYENNE  
**Durée estimée** : 2 jours  
**Dépendances** : Phase 2 complète

**Description** :
Créer la table pour la gestion des objets de musée avec expositions, prêts, et états de conservation.

**Fichier à créer** :
`database/migrations/2025_11_11_000001_create_record_artifacts_table.php`

**Code de la migration** :
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_artifacts', function (Blueprint $table) {
            $table->id();
            
            // Identification
            $table->string('code', 50)->unique()->comment('Numéro d\'inventaire');
            $table->string('name', 250);
            $table->text('description')->nullable();
            
            // Classification
            $table->string('category', 100)->nullable()->comment('Catégorie (peinture, sculpture, etc.)');
            $table->string('sub_category', 100)->nullable();
            $table->string('material', 200)->nullable()->comment('Matériaux constitutifs');
            $table->string('technique', 200)->nullable();
            
            // Dimensions
            $table->decimal('height', 10, 2)->nullable()->comment('Hauteur en cm');
            $table->decimal('width', 10, 2)->nullable()->comment('Largeur en cm');
            $table->decimal('depth', 10, 2)->nullable()->comment('Profondeur en cm');
            $table->decimal('weight', 10, 3)->nullable()->comment('Poids en kg');
            $table->string('dimensions_notes', 500)->nullable();
            
            // Origine et datation
            $table->string('origin', 200)->nullable()->comment('Provenance géographique');
            $table->string('period', 100)->nullable()->comment('Période historique');
            $table->integer('date_start')->nullable()->comment('Année de début');
            $table->integer('date_end')->nullable()->comment('Année de fin');
            $table->string('date_precision', 50)->nullable()->comment('circa, exact, avant, après');
            
            // Auteur/Créateur
            $table->string('author', 250)->nullable();
            $table->string('author_role', 100)->nullable()->comment('artiste, sculpteur, etc.');
            $table->date('author_birth_date')->nullable();
            $table->date('author_death_date')->nullable();
            
            // Acquisition
            $table->string('acquisition_method', 100)->nullable()->comment('achat, don, legs, etc.');
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_price', 12, 2)->nullable();
            $table->string('acquisition_source', 250)->nullable();
            
            // Conservation
            $table->enum('conservation_state', ['excellent', 'good', 'fair', 'poor', 'critical'])->default('good');
            $table->text('conservation_notes')->nullable();
            $table->date('last_conservation_check')->nullable();
            $table->date('next_conservation_check')->nullable();
            
            // Localisation
            $table->string('current_location', 250)->nullable()->comment('Salle/Réserve actuelle');
            $table->string('storage_location', 250)->nullable()->comment('Emplacement de stockage');
            $table->boolean('is_on_display')->default(false);
            $table->boolean('is_on_loan')->default(false);
            
            // Valeurs
            $table->decimal('estimated_value', 12, 2)->nullable()->comment('Valeur estimée');
            $table->decimal('insurance_value', 12, 2)->nullable();
            $table->date('valuation_date')->nullable();
            
            // Statut
            $table->enum('status', ['active', 'in_restoration', 'on_loan', 'deaccessioned', 'lost', 'destroyed'])->default('active');
            
            // Métadonnées
            $table->json('metadata')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('code');
            $table->index('category');
            $table->index('status');
            $table->index('is_on_display');
            $table->index('is_on_loan');
            $table->fullText(['name', 'description', 'author'], 'artifacts_fulltext');
        });
        
        // Table des expositions
        Schema::create('record_artifact_exhibitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artifact_id');
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            
            $table->string('exhibition_name', 250);
            $table->string('venue', 250)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('artifact_id');
            $table->index('start_date');
        });
        
        // Table des prêts
        Schema::create('record_artifact_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artifact_id');
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            
            $table->string('borrower_name', 250);
            $table->string('borrower_contact', 250)->nullable();
            $table->date('loan_date');
            $table->date('return_date')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue', 'extended'])->default('active');
            $table->text('conditions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('artifact_id');
            $table->index('status');
        });
        
        // Table des rapports de conservation
        Schema::create('record_artifact_condition_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artifact_id');
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            
            $table->date('report_date');
            $table->enum('overall_condition', ['excellent', 'good', 'fair', 'poor', 'critical']);
            $table->text('observations');
            $table->text('recommendations')->nullable();
            $table->unsignedBigInteger('inspector_id')->nullable();
            $table->timestamps();
            
            $table->index('artifact_id');
            $table->index('report_date');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('record_artifact_condition_reports');
        Schema::dropIfExists('record_artifact_loans');
        Schema::dropIfExists('record_artifact_exhibitions');
        Schema::dropIfExists('record_artifacts');
    }
};
```

**Commande de test** :
```bash
php artisan migrate --path=database/migrations/2025_11_11_000001_create_record_artifacts_table.php
```

**Livrables** :
- Migration des artifacts complète
- Tables annexes (expositions, prêts, conservation)

---

## 📄 Phase 7 : Livres (RecordBook) (Durée : 1-2 semaines)

### Tâche 7.1 : Créer la table record_books
**Priorité** : HAUTE  
**Complexité** : MOYENNE  
**Durée estimée** : 2 jours  
**Dépendances** : Phase 2 complète

**Description** :
Créer la table pour la gestion des livres avec exemplaires, prêts, et réservations.

**Fichier à créer** :
`database/migrations/2025_11_12_000001_create_record_books_table.php`

**Code de la migration** :
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_books', function (Blueprint $table) {
            $table->id();
            
            // Identification bibliographique
            $table->string('isbn', 20)->nullable()->unique();
            $table->string('title', 500);
            $table->string('subtitle', 500)->nullable();
            $table->text('authors')->nullable()->comment('JSON array of authors');
            
            // Édition
            $table->string('publisher', 250)->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('edition', 100)->nullable();
            $table->string('place_of_publication', 200)->nullable();
            
            // Classification
            $table->string('dewey', 20)->nullable()->comment('Classification Dewey');
            $table->string('lcc', 50)->nullable()->comment('Library of Congress');
            $table->text('subjects')->nullable()->comment('JSON array of subjects');
            
            // Description physique
            $table->integer('pages')->nullable();
            $table->string('format', 50)->nullable()->comment('in-8, in-4, etc.');
            $table->string('binding', 50)->nullable()->comment('broché, relié, etc.');
            $table->string('language', 10)->default('fr');
            
            // Contenu
            $table->text('description')->nullable();
            $table->text('table_of_contents')->nullable();
            $table->text('notes')->nullable();
            
            // Collection/Série
            $table->string('series', 250)->nullable();
            $table->integer('series_number')->nullable();
            
            // Statistiques
            $table->integer('total_copies')->default(0);
            $table->integer('available_copies')->default(0);
            $table->integer('loan_count')->default(0);
            $table->integer('reservation_count')->default(0);
            
            // Métadonnées
            $table->json('metadata')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('isbn');
            $table->index('publication_year');
            $table->index('dewey');
            $table->fullText(['title', 'subtitle', 'description'], 'books_fulltext');
        });
        
        // Table des exemplaires
        Schema::create('record_book_copies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')->references('id')->on('record_books')->onDelete('cascade');
            
            $table->string('barcode', 50)->unique()->comment('Code-barres unique');
            $table->string('call_number', 100)->nullable()->comment('Cote');
            
            // Localisation
            $table->string('location', 200)->nullable()->comment('Bibliothèque/Salle');
            $table->string('shelf', 100)->nullable()->comment('Étagère');
            
            // État
            $table->enum('status', ['available', 'on_loan', 'reserved', 'in_repair', 'lost', 'withdrawn'])->default('available');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            
            // Acquisition
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_price', 10, 2)->nullable();
            $table->string('acquisition_source', 250)->nullable();
            
            // Prêt en cours
            $table->boolean('is_on_loan')->default(false);
            $table->unsignedBigInteger('current_loan_id')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('book_id');
            $table->index('barcode');
            $table->index('status');
            $table->index('is_on_loan');
        });
        
        // Table des prêts
        Schema::create('record_book_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('copy_id');
            $table->foreign('copy_id')->references('id')->on('record_book_copies')->onDelete('cascade');
            
            $table->unsignedBigInteger('borrower_id');
            $table->foreign('borrower_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->date('loan_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            
            $table->enum('status', ['active', 'returned', 'overdue', 'renewed', 'lost'])->default('active');
            $table->integer('renewal_count')->default(0);
            
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->boolean('fee_paid')->default(false);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('copy_id');
            $table->index('borrower_id');
            $table->index('status');
            $table->index('due_date');
        });
        
        // Table des réservations
        Schema::create('record_book_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->foreign('book_id')->references('id')->on('record_books')->onDelete('cascade');
            
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->date('reservation_date');
            $table->date('expiry_date')->nullable();
            
            $table->enum('status', ['pending', 'available', 'fulfilled', 'cancelled', 'expired'])->default('pending');
            $table->integer('queue_position')->default(0);
            
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            
            $table->index('book_id');
            $table->index('user_id');
            $table->index('status');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('record_book_reservations');
        Schema::dropIfExists('record_book_loans');
        Schema::dropIfExists('record_book_copies');
        Schema::dropIfExists('record_books');
    }
};
```

**Commande de test** :
```bash
php artisan migrate --path=database/migrations/2025_11_12_000001_create_record_books_table.php
```

**Livrables** :
- Migration des books complète
- Tables annexes (exemplaires, prêts, réservations)

---

## 📄 Phase 8 : Publications Périodiques (RecordPeriodic) (Durée : 1-2 semaines)

### Tâche 8.1 : Créer la table record_periodics
**Priorité** : MOYENNE  
**Complexité** : MOYENNE  
**Durée estimée** : 2 jours  
**Dépendances** : Phase 2 complète

**Description** :
Créer la table pour la gestion des publications périodiques (revues, magazines) avec numéros et articles.

**Fichier à créer** :
`database/migrations/2025_11_13_000001_create_record_periodics_table.php`

**Code de la migration** :
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_periodics', function (Blueprint $table) {
            $table->id();
            
            // Identification
            $table->string('issn', 20)->nullable()->unique();
            $table->string('title', 500);
            $table->string('subtitle', 500)->nullable();
            
            // Éditeur
            $table->string('publisher', 250)->nullable();
            $table->string('place_of_publication', 200)->nullable();
            
            // Périodicité
            $table->enum('frequency', [
                'daily', 'weekly', 'biweekly', 'monthly', 
                'bimonthly', 'quarterly', 'semiannual', 'annual', 'irregular'
            ])->default('monthly');
            
            // Classification
            $table->text('subjects')->nullable()->comment('JSON array');
            $table->string('language', 10)->default('fr');
            
            // Description
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            
            // Statut de publication
            $table->enum('publication_status', ['active', 'ceased', 'suspended'])->default('active');
            $table->integer('first_year')->nullable();
            $table->integer('last_year')->nullable();
            
            // Statistiques
            $table->integer('total_issues')->default(0);
            $table->integer('total_articles')->default(0);
            
            // Métadonnées
            $table->json('metadata')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index
            $table->index('issn');
            $table->index('frequency');
            $table->index('publication_status');
            $table->fullText(['title', 'subtitle', 'description'], 'periodics_fulltext');
        });
        
        // Table des numéros (issues)
        Schema::create('record_periodic_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('periodic_id');
            $table->foreign('periodic_id')->references('id')->on('record_periodics')->onDelete('cascade');
            
            // Identification du numéro
            $table->integer('volume')->nullable();
            $table->integer('number')->nullable();
            $table->string('special_issue', 200)->nullable()->comment('Numéro spécial');
            
            // Date de parution
            $table->integer('year');
            $table->integer('month')->nullable();
            $table->integer('day')->nullable();
            $table->date('publication_date')->nullable();
            
            // Description
            $table->string('title', 500)->nullable()->comment('Titre du numéro');
            $table->text('description')->nullable();
            $table->integer('pages')->nullable();
            
            // Disponibilité
            $table->boolean('is_available')->default(true);
            $table->string('location', 200)->nullable();
            
            // Fichier attaché (PDF)
            $table->unsignedBigInteger('attachment_id')->nullable();
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('set null');
            
            // Statistiques
            $table->integer('article_count')->default(0);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('periodic_id');
            $table->index(['year', 'volume', 'number']);
            $table->index('publication_date');
        });
        
        // Table des articles
        Schema::create('record_periodic_articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('issue_id');
            $table->foreign('issue_id')->references('id')->on('record_periodic_issues')->onDelete('cascade');
            
            // Identification
            $table->string('title', 500);
            $table->string('subtitle', 500)->nullable();
            $table->text('authors')->nullable()->comment('JSON array');
            
            // Localisation dans le numéro
            $table->integer('start_page')->nullable();
            $table->integer('end_page')->nullable();
            
            // Contenu
            $table->text('abstract')->nullable();
            $table->text('keywords')->nullable()->comment('JSON array');
            $table->text('full_text')->nullable()->comment('Texte intégral extrait');
            
            // DOI et identifiants
            $table->string('doi', 100)->nullable();
            $table->string('external_url', 500)->nullable();
            
            // Fichier attaché (PDF de l'article)
            $table->unsignedBigInteger('attachment_id')->nullable();
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('set null');
            
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('issue_id');
            $table->index('doi');
            $table->fullText(['title', 'subtitle', 'abstract', 'full_text'], 'articles_fulltext');
        });
        
        // Table des abonnements
        Schema::create('record_periodic_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('periodic_id');
            $table->foreign('periodic_id')->references('id')->on('record_periodics')->onDelete('cascade');
            
            $table->string('subscriber_name', 250);
            $table->string('subscriber_contact', 250)->nullable();
            
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended'])->default('active');
            
            $table->decimal('annual_cost', 10, 2)->nullable();
            $table->string('payment_method', 100)->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('periodic_id');
            $table->index('status');
            $table->index('end_date');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('record_periodic_subscriptions');
        Schema::dropIfExists('record_periodic_articles');
        Schema::dropIfExists('record_periodic_issues');
        Schema::dropIfExists('record_periodics');
    }
};
```

**Commande de test** :
```bash
php artisan migrate --path=database/migrations/2025_11_13_000001_create_record_periodics_table.php
```

**Livrables** :
- Migration des periodics complète
- Tables annexes (numéros, articles, abonnements)

---

## 📄 Phase 9 : Services Métier & API (Durée : 2 semaines)

### Tâche 9.1 : Créer les services métier
**Priorité** : HAUTE  
**Complexité** : HAUTE  
**Durée estimée** : 5 jours  
**Dépendances** : Phases 4-8 complètes

**Description** :
Créer les services métier pour centraliser la logique applicative.

**Fichiers à créer** :
- `app/Services/RecordDigitalFolderService.php`
- `app/Services/RecordDigitalDocumentService.php`
- `app/Services/RecordArtifactService.php`
- `app/Services/RecordBookService.php`
- `app/Services/RecordPeriodicService.php`

**Exemple de service** :
`app/Services/RecordDigitalDocumentService.php`

```php
<?php

namespace App\Services;

use App\Models\RecordDigitalDocument;
use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class RecordDigitalDocumentService
{
    public function createDocument(array $data, ?UploadedFile $file = null): RecordDigitalDocument
    {
        return \DB::transaction(function () use ($data, $file) {
            // Create attachment if file provided
            $attachment = null;
            if ($file) {
                $attachment = $this->createAttachment($file, $data);
            }
            
            // Create document
            $document = RecordDigitalDocument::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'folder_id' => $data['folder_id'] ?? null,
                'document_type_id' => $data['document_type_id'] ?? null,
                'current_attachment_id' => $attachment?->id,
                'metadata' => $data['metadata'] ?? [],
                'document_date' => $data['document_date'] ?? now(),
                'access_level' => $data['access_level'] ?? 'internal',
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);
            
            // Create version record
            if ($attachment) {
                $document->versions()->create([
                    'version_number' => 1,
                    'attachment_id' => $attachment->id,
                    'version_notes' => 'Initial version',
                    'created_by' => auth()->id(),
                ]);
            }
            
            return $document->fresh(['currentAttachment', 'documentType', 'folder']);
        });
    }
    
    public function updateDocument(RecordDigitalDocument $document, array $data): RecordDigitalDocument
    {
        $document->update($data);
        return $document->fresh();
    }
    
    public function createNewVersion(RecordDigitalDocument $document, UploadedFile $file, ?string $notes = null): RecordDigitalDocument
    {
        if (!$document->is_checked_out) {
            throw new \Exception('Document must be checked out before creating new version');
        }
        
        $attachment = $this->createAttachment($file, [
            'entity_type' => 'record_digital_document',
            'entity_id' => $document->id,
        ]);
        
        $document->checkin($attachment, $notes);
        
        return $document->fresh();
    }
    
    public function approveDocument(RecordDigitalDocument $document): RecordDigitalDocument
    {
        $document->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        
        return $document;
    }
    
    public function archiveDocument(RecordDigitalDocument $document): RecordDigitalDocument
    {
        $document->update(['status' => 'archived']);
        return $document;
    }
    
    public function extractFullText(RecordDigitalDocument $document): void
    {
        if (!$document->currentAttachment) {
            return;
        }
        
        $filePath = Storage::disk($document->currentAttachment->disk)
            ->path($document->currentAttachment->path);
        
        $extension = strtolower($document->currentAttachment->extension);
        
        $text = match ($extension) {
            'pdf' => $this->extractTextFromPdf($filePath),
            'docx' => $this->extractTextFromDocx($filePath),
            'txt' => file_get_contents($filePath),
            default => null,
        };
        
        if ($text) {
            $document->update(['full_text_content' => $text]);
        }
    }
    
    private function createAttachment(UploadedFile $file, array $data): Attachment
    {
        $disk = config('filesystems.default', 'local');
        $path = $file->store('documents/' . date('Y/m'), $disk);
        
        return Attachment::create([
            'entity_type' => $data['entity_type'] ?? 'record_digital_document',
            'entity_id' => $data['entity_id'] ?? null,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => $disk,
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'created_by' => auth()->id(),
        ]);
    }
    
    private function extractTextFromPdf(string $filePath): ?string
    {
        // Use smalot/pdfparser
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        } catch (\Exception $e) {
            \Log::error('PDF text extraction failed: ' . $e->getMessage());
            return null;
        }
    }
    
    private function extractTextFromDocx(string $filePath): ?string
    {
        // Implementation using PhpWord or similar
        return null;
    }
}
```

**Livrables** :
- 5 services métier fonctionnels
- Documentation des méthodes

### Tâche 9.2 : Créer les contrôleurs API
**Priorité** : HAUTE  
**Complexité** : MOYENNE  
**Durée estimée** : 3 jours  
**Dépendances** : Tâche 9.1

**Description** :
Créer les contrôleurs API RESTful pour tous les types de records.

**Fichiers à créer** :
- `app/Http/Controllers/Api/RecordDigitalFolderController.php`
- `app/Http/Controllers/Api/RecordDigitalDocumentController.php`
- `app/Http/Controllers/Api/RecordArtifactController.php`
- `app/Http/Controllers/Api/RecordBookController.php`
- `app/Http/Controllers/Api/RecordPeriodicController.php`

**Exemple** :
`app/Http/Controllers/Api/RecordDigitalDocumentController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalDocument;
use App\Services\RecordDigitalDocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RecordDigitalDocumentController extends Controller
{
    public function __construct(
        private RecordDigitalDocumentService $documentService
    ) {}
    
    public function index(Request $request): JsonResponse
    {
        $query = RecordDigitalDocument::with(['documentType', 'folder', 'currentAttachment']);
        
        // Filters
        if ($request->has('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search')) {
            $query->whereFullText(['name', 'description', 'full_text_content'], $request->search);
        }
        
        // Pagination
        $documents = $query->paginate($request->get('per_page', 20));
        
        return response()->json($documents);
    }
    
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:250',
            'description' => 'nullable|string',
            'folder_id' => 'nullable|exists:record_digital_folders,id',
            'document_type_id' => 'nullable|exists:record_digital_document_types,id',
            'file' => 'nullable|file|max:51200', // 50MB
            'metadata' => 'nullable|array',
            'document_date' => 'nullable|date',
            'access_level' => 'nullable|in:public,internal,restricted,confidential,secret',
        ]);
        
        $document = $this->documentService->createDocument(
            $validated,
            $request->file('file')
        );
        
        return response()->json($document, 201);
    }
    
    public function show(RecordDigitalDocument $document): JsonResponse
    {
        $document->load(['documentType', 'folder', 'currentAttachment', 'versions']);
        return response()->json($document);
    }
    
    public function update(Request $request, RecordDigitalDocument $document): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:250',
            'description' => 'nullable|string',
            'metadata' => 'nullable|array',
            'access_level' => 'nullable|in:public,internal,restricted,confidential,secret',
        ]);
        
        $document = $this->documentService->updateDocument($document, $validated);
        
        return response()->json($document);
    }
    
    public function destroy(RecordDigitalDocument $document): JsonResponse
    {
        $document->delete();
        return response()->json(null, 204);
    }
    
    public function checkout(Request $request, RecordDigitalDocument $document): JsonResponse
    {
        $document->checkout($request->input('reason'));
        return response()->json($document);
    }
    
    public function checkin(Request $request, RecordDigitalDocument $document): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|max:51200',
            'notes' => 'nullable|string',
        ]);
        
        $document = $this->documentService->createNewVersion(
            $document,
            $request->file('file'),
            $validated['notes'] ?? null
        );
        
        return response()->json($document);
    }
    
    public function approve(RecordDigitalDocument $document): JsonResponse
    {
        $document = $this->documentService->approveDocument($document);
        return response()->json($document);
    }
}
```

**Fichier à modifier** :
`routes/api.php`

```php
use App\Http\Controllers\Api\RecordDigitalDocumentController;

Route::middleware('auth:sanctum')->group(function () {
    // Digital Documents
    Route::apiResource('digital-documents', RecordDigitalDocumentController::class);
    Route::post('digital-documents/{document}/checkout', [RecordDigitalDocumentController::class, 'checkout']);
    Route::post('digital-documents/{document}/checkin', [RecordDigitalDocumentController::class, 'checkin']);
    Route::post('digital-documents/{document}/approve', [RecordDigitalDocumentController::class, 'approve']);
    
    // Similar routes for folders, artifacts, books, periodics...
});
```

**Livrables** :
- 5 contrôleurs API RESTful
- Routes API configurées
- Documentation API (Swagger/OpenAPI)

### Tâche 9.3 : Créer les menus et sous-menus pour Library et Museum
**Priorité** : HAUTE  
**Complexité** : FAIBLE  
**Durée estimée** : 2 jours  
**Dépendances** : Phases 6, 7, 8

**Description** :
Ajouter les boutons de menu "Library" et "Museum" dans le layout principal, et créer leurs sous-menus respectifs pour naviguer vers les différentes sections.

**Critères d'acceptation** :
- [ ] Bouton "Library" ajouté au menu principal avec icône
- [ ] Bouton "Museum" ajouté au menu principal avec icône
- [ ] Sous-menu Library créé avec liens vers Books et Periodics
- [ ] Sous-menu Museum créé avec liens vers Artifacts
- [ ] Navigation fonctionnelle et responsive
- [ ] Gestion des permissions d'accès par rôle

**Fichier à modifier** :
`resources/views/layouts/app.blade.php` (ou le layout principal utilisé)

**Code à ajouter dans le menu principal** :

```blade
<!-- Menu existant -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <!-- Logo et autres éléments -->
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <!-- Menus existants -->
                
                <!-- LIBRARY MENU -->
                @can('access-library')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="libraryDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-book"></i> Library
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="libraryDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('books.index') }}">
                                <i class="bi bi-book-fill"></i> Books
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('book-copies.index') }}">
                                <i class="bi bi-bookshelf"></i> Book Copies
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('book-loans.index') }}">
                                <i class="bi bi-arrow-left-right"></i> Loans
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('book-reservations.index') }}">
                                <i class="bi bi-bookmark"></i> Reservations
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('periodics.index') }}">
                                <i class="bi bi-journal-text"></i> Periodicals
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('periodic-issues.index') }}">
                                <i class="bi bi-journals"></i> Issues
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('periodic-articles.index') }}">
                                <i class="bi bi-file-text"></i> Articles
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('periodic-subscriptions.index') }}">
                                <i class="bi bi-calendar-check"></i> Subscriptions
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                
                <!-- MUSEUM MENU -->
                @can('access-museum')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="museumDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-building"></i> Museum
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="museumDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('artifacts.index') }}">
                                <i class="bi bi-gem"></i> Artifacts
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('artifacts.create') }}">
                                <i class="bi bi-plus-circle"></i> New Artifact
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('artifact-exhibitions.index') }}">
                                <i class="bi bi-easel"></i> Exhibitions
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('artifact-loans.index') }}">
                                <i class="bi bi-box-arrow-right"></i> Loans
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('artifact-condition-reports.index') }}">
                                <i class="bi bi-clipboard-check"></i> Condition Reports
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('artifacts.statistics') }}">
                                <i class="bi bi-graph-up"></i> Statistics
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
                
                <!-- Autres menus -->
            </ul>
        </div>
    </div>
</nav>
```

**Fichiers de sous-menus à créer** :

**1. Sous-menu Library** :
`resources/views/submenu/library.blade.php`

```blade
<div class="submenu-section">
    <div class="container-fluid py-3 bg-light border-bottom">
        <div class="row">
            <div class="col-12">
                <div class="btn-group" role="group" aria-label="Library submenu">
                    <!-- Books Section -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-book"></i> Books
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('books.index') }}">
                                <i class="bi bi-list"></i> All Books
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('books.create') }}">
                                <i class="bi bi-plus"></i> Add Book
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('book-copies.index') }}">
                                <i class="bi bi-bookshelf"></i> Copies Management
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('books.import') }}">
                                <i class="bi bi-upload"></i> Import Books
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('books.export') }}">
                                <i class="bi bi-download"></i> Export Books
                            </a></li>
                        </ul>
                    </div>
                    
                    <!-- Loans Section -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-success dropdown-toggle" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-arrow-left-right"></i> Loans
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('book-loans.index') }}">
                                <i class="bi bi-list"></i> All Loans
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('book-loans.active') }}">
                                <i class="bi bi-clock"></i> Active Loans
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('book-loans.overdue') }}">
                                <i class="bi bi-exclamation-triangle text-danger"></i> Overdue
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('book-loans.create') }}">
                                <i class="bi bi-plus"></i> New Loan
                            </a></li>
                        </ul>
                    </div>
                    
                    <!-- Periodicals Section -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-info dropdown-toggle" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-journal-text"></i> Periodicals
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('periodics.index') }}">
                                <i class="bi bi-journals"></i> All Periodicals
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('periodic-issues.index') }}">
                                <i class="bi bi-journal-bookmark"></i> Issues
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('periodic-articles.index') }}">
                                <i class="bi bi-file-text"></i> Articles
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('periodic-subscriptions.index') }}">
                                <i class="bi bi-calendar-check"></i> Subscriptions
                            </a></li>
                        </ul>
                    </div>
                    
                    <!-- Reports Section -->
                    <a href="{{ route('library.reports') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-graph-up"></i> Reports
                    </a>
                    
                    <!-- Search -->
                    <a href="{{ route('library.search') }}" class="btn btn-outline-dark">
                        <i class="bi bi-search"></i> Search
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
```

**2. Sous-menu Museum** :
`resources/views/submenu/museum.blade.php`

```blade
<div class="submenu-section">
    <div class="container-fluid py-3 bg-light border-bottom">
        <div class="row">
            <div class="col-12">
                <div class="btn-group" role="group" aria-label="Museum submenu">
                    <!-- Artifacts Section -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gem"></i> Artifacts
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('artifacts.index') }}">
                                <i class="bi bi-list"></i> All Artifacts
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('artifacts.create') }}">
                                <i class="bi bi-plus"></i> New Artifact
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('artifacts.on-display') }}">
                                <i class="bi bi-eye"></i> On Display
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('artifacts.in-storage') }}">
                                <i class="bi bi-box"></i> In Storage
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('artifacts.by-category') }}">
                                <i class="bi bi-tags"></i> By Category
                            </a></li>
                        </ul>
                    </div>
                    
                    <!-- Exhibitions Section -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-success dropdown-toggle" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-easel"></i> Exhibitions
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('artifact-exhibitions.index') }}">
                                <i class="bi bi-list"></i> All Exhibitions
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('artifact-exhibitions.current') }}">
                                <i class="bi bi-calendar-event"></i> Current
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('artifact-exhibitions.upcoming') }}">
                                <i class="bi bi-calendar-plus"></i> Upcoming
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('artifact-exhibitions.past') }}">
                                <i class="bi bi-calendar-x"></i> Past
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('artifact-exhibitions.create') }}">
                                <i class="bi bi-plus"></i> New Exhibition
                            </a></li>
                        </ul>
                    </div>
                    
                    <!-- Loans Section -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-warning dropdown-toggle" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-box-arrow-right"></i> Loans
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('artifact-loans.index') }}">
                                <i class="bi bi-list"></i> All Loans
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('artifact-loans.active') }}">
                                <i class="bi bi-clock"></i> Active
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('artifact-loans.overdue') }}">
                                <i class="bi bi-exclamation-triangle text-danger"></i> Overdue
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('artifact-loans.create') }}">
                                <i class="bi bi-plus"></i> New Loan
                            </a></li>
                        </ul>
                    </div>
                    
                    <!-- Conservation Section -->
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-info dropdown-toggle" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-clipboard-check"></i> Conservation
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('artifact-condition-reports.index') }}">
                                <i class="bi bi-file-earmark-text"></i> All Reports
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('artifact-condition-reports.create') }}">
                                <i class="bi bi-plus"></i> New Report
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('artifacts.needs-inspection') }}">
                                <i class="bi bi-flag text-warning"></i> Needs Inspection
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('artifacts.in-restoration') }}">
                                <i class="bi bi-tools"></i> In Restoration
                            </a></li>
                        </ul>
                    </div>
                    
                    <!-- Reports & Statistics -->
                    <a href="{{ route('museum.statistics') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-graph-up"></i> Statistics
                    </a>
                    
                    <!-- Search -->
                    <a href="{{ route('museum.search') }}" class="btn btn-outline-dark">
                        <i class="bi bi-search"></i> Search
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
```

**3. Fichier pour inclure les sous-menus** :
`resources/views/layouts/submenu.blade.php`

```blade
@if(request()->is('library*'))
    @include('submenu.library')
@elseif(request()->is('museum*') || request()->is('artifacts*'))
    @include('submenu.museum')
@endif
```

**Utilisation dans les vues** :

Dans vos vues de pages (ex: `resources/views/books/index.blade.php`), ajoutez :

```blade
@extends('layouts.app')

@section('content')
    @include('layouts.submenu')
    
    <div class="container mt-4">
        <!-- Contenu de la page -->
    </div>
@endsection
```

**Styles CSS à ajouter** :
`public/css/submenu.css`

```css
.submenu-section {
    position: sticky;
    top: 56px; /* Hauteur du navbar principal */
    z-index: 1020;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.submenu-section .btn-group {
    margin-right: 10px;
}

.submenu-section .btn {
    border-radius: 4px;
}

.submenu-section .dropdown-menu {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.submenu-section .dropdown-item {
    padding: 8px 20px;
    transition: all 0.2s;
}

.submenu-section .dropdown-item:hover {
    background-color: #f8f9fa;
    padding-left: 25px;
}

.submenu-section .dropdown-item i {
    width: 20px;
    margin-right: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .submenu-section .btn-group {
        margin-bottom: 10px;
        width: 100%;
    }
    
    .submenu-section .btn {
        width: 100%;
    }
}
```

**Permissions à ajouter** :
`database/seeders/PermissionsSeeder.php`

```php
// Ajouter ces permissions
$permissions = [
    // Library permissions
    'access-library',
    'view-books',
    'create-books',
    'edit-books',
    'delete-books',
    'manage-book-loans',
    'view-periodics',
    'manage-periodics',
    
    // Museum permissions
    'access-museum',
    'view-artifacts',
    'create-artifacts',
    'edit-artifacts',
    'delete-artifacts',
    'manage-exhibitions',
    'manage-artifact-loans',
    'create-condition-reports',
];
```

**Commandes de test** :
```bash
# Compiler les assets
npm run dev

# Vérifier les routes
php artisan route:list --path=library
php artisan route:list --path=museum
php artisan route:list --path=artifacts

# Vérifier les permissions
php artisan permission:show
```

**Livrables** :
- Menu principal avec boutons Library et Museum
- Sous-menus Library et Museum fonctionnels
- Fichiers de vues dans `resources/views/submenu/`
- Styles CSS pour les sous-menus
- Permissions et contrôle d'accès configurés
- Navigation responsive
- Documentation utilisateur

---

## 📄 Phase 10 : Migration des Données (Durée : 1 semaine)

### Tâche 10.1 : Créer le script de migration des données existantes
**Priorité** : CRITIQUE  
**Complexité** : HAUTE  
**Durée estimée** : 5 jours  
**Dépendances** : Phase 9 complète

**Description** :
Créer une commande Artisan pour migrer les données de l'ancien système vers le nouveau.

**Fichier à créer** :
`app/Console/Commands/MigrateRecordsData.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\RecordPhysical;
use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalDocument;

class MigrateRecordsData extends Command
{
    protected $signature = 'records:migrate 
                            {--dry-run : Run without making changes}
                            {--batch-size=100 : Number of records per batch}';
    
    protected $description = 'Migrate data from old records structure to new structure';
    
    public function handle(): int
    {
        $this->info('🚀 Starting data migration...');
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
        }
        
        try {
            DB::beginTransaction();
            
            // Step 1: Migrate attachments metadata
            $this->info('Step 1: Migrating attachments...');
            $this->migrateAttachments($dryRun);
            
            // Step 2: Create digital folders from existing structure
            $this->info('Step 2: Creating digital folders...');
            $this->createDigitalFolders($dryRun);
            
            // Step 3: Migrate existing documents
            $this->info('Step 3: Migrating documents...');
            $this->migrateDocuments($dryRun);
            
            if (!$dryRun) {
                DB::commit();
                $this->info('✅ Migration completed successfully!');
            } else {
                DB::rollBack();
                $this->info('✅ Dry run completed - no changes made');
            }
            
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Migration failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
    
    private function migrateAttachments(bool $dryRun): void
    {
        $count = DB::table('attachments')
            ->whereNull('attachment_type')
            ->count();
        
        $this->info("Found {$count} attachments to migrate");
        
        if (!$dryRun) {
            DB::table('attachments')
                ->whereNull('attachment_type')
                ->update([
                    'attachment_type' => 'document',
                    'file_category' => 'general',
                ]);
        }
        
        $this->info("✓ Attachments migrated");
    }
    
    private function createDigitalFolders(bool $dryRun): void
    {
        // Example: Create folders based on existing classification
        $records = DB::table('record_physicals')
            ->select('classification', DB::raw('count(*) as count'))
            ->groupBy('classification')
            ->get();
        
        $this->info("Found {$records->count()} classifications");
        
        $bar = $this->output->createProgressBar($records->count());
        
        foreach ($records as $record) {
            if (!$dryRun) {
                RecordDigitalFolder::firstOrCreate(
                    ['name' => $record->classification ?? 'Uncategorized'],
                    [
                        'description' => "Auto-created from classification: {$record->classification}",
                        'created_by' => 1,
                    ]
                );
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("✓ Digital folders created");
    }
    
    private function migrateDocuments(bool $dryRun): void
    {
        // Implement document migration logic
        $this->info("Document migration not yet implemented");
    }
}
```

**Commande de test** :
```bash
php artisan records:migrate --dry-run
php artisan records:migrate --batch-size=50
```

**Livrables** :
- Commande de migration complète
- Script de rollback en cas d'erreur
- Logs détaillés de migration

---

## 📄 Phase 11 : Tests & Validation (Durée : 2 semaines)

### Tâche 11.1 : Créer les tests unitaires
**Priorité** : HAUTE  
**Complexité** : MOYENNE  
**Durée estimée** : 5 jours  
**Dépendances** : Phases 1-9

**Description** :
Créer une suite complète de tests unitaires pour tous les modèles et services.

**Fichiers à créer** :
- `tests/Unit/Models/RecordDigitalDocumentTest.php`
- `tests/Unit/Services/RecordDigitalDocumentServiceTest.php`

**Exemple** :
`tests/Unit/Models/RecordDigitalDocumentTest.php`

```php
<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecordDigitalDocumentTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_create_document(): void
    {
        $document = RecordDigitalDocument::factory()->create([
            'name' => 'Test Document',
            'status' => 'draft',
        ]);
        
        $this->assertDatabaseHas('record_digital_documents', [
            'name' => 'Test Document',
            'status' => 'draft',
        ]);
    }
    
    public function test_document_has_folder_relationship(): void
    {
        $folder = RecordDigitalFolder::factory()->create();
        $document = RecordDigitalDocument::factory()->create([
            'folder_id' => $folder->id,
        ]);
        
        $this->assertInstanceOf(RecordDigitalFolder::class, $document->folder);
        $this->assertEquals($folder->id, $document->folder->id);
    }
    
    public function test_can_checkout_document(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $document = RecordDigitalDocument::factory()->create([
            'is_checked_out' => false,
        ]);
        
        $document->checkout('Testing checkout');
        
        $this->assertTrue($document->is_checked_out);
        $this->assertEquals($user->id, $document->checked_out_by);
        $this->assertNotNull($document->checked_out_at);
    }
    
    public function test_cannot_checkout_already_checked_out_document(): void
    {
        $this->expectException(\Exception::class);
        
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $document = RecordDigitalDocument::factory()->create([
            'is_checked_out' => true,
            'checked_out_by' => $user1->id,
        ]);
        
        $this->actingAs($user2);
        $document->checkout();
    }
    
    public function test_generates_code_automatically(): void
    {
        $document = RecordDigitalDocument::factory()->create();
        
        $this->assertNotNull($document->code);
        $this->assertMatchesRegularExpression('/^DD-\d{4}-\d{4}$/', $document->code);
    }
}
```

**Commande de test** :
```bash
php artisan test --testsuite=Unit
php artisan test --coverage
```

**Livrables** :
- Tests unitaires (>80% coverage)
- Tests d'intégration
- Tests API

### Tâche 11.2 : Tests de performance
**Priorité** : MOYENNE  
**Complexité** : MOYENNE  
**Durée estimée** : 3 jours

**Description** :
Effectuer des tests de performance et optimiser les requêtes.

**Outils** :
- Laravel Debugbar
- Laravel Telescope
- Query performance analysis

**Livrables** :
- Rapport de performance
- Optimisations appliquées

---

## 📄 Phase 12 : Déploiement (Durée : 1 semaine)

### Tâche 12.1 : Préparation du déploiement
**Priorité** : CRITIQUE  
**Complexité** : MOYENNE  
**Durée estimée** : 2 jours

**Description** :
Préparer l'environnement de production et créer les scripts de déploiement.

**Checklist de déploiement** :
- [ ] Backup complet de la base de données
- [ ] Configuration des variables d'environnement (.env)
- [ ] Configuration du serveur web (Apache/Nginx)
- [ ] Configuration de la file d'attente (Queue)
- [ ] Configuration du cache (Redis/Memcached)
- [ ] Configuration du stockage (S3/Local)
- [ ] Tests de sécurité
- [ ] Optimisation des assets (npm run build)
- [ ] Optimisation de l'autoload (composer dump-autoload)
- [ ] Migration de la base de données
- [ ] Seeds de production
- [ ] Configuration SSL/TLS
- [ ] Configuration du monitoring

**Script de déploiement** :
`deploy.sh`

```bash
#!/bin/bash

echo "🚀 Starting deployment..."

# Maintenance mode
php artisan down

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Restart services
php artisan queue:restart

# Exit maintenance mode
php artisan up

echo "✅ Deployment complete!"
```

**Livrables** :
- Script de déploiement automatisé
- Documentation de déploiement
- Plan de rollback

### Tâche 12.2 : Monitoring et support post-déploiement
**Priorité** : HAUTE  
**Complexité** : FAIBLE  
**Durée estimée** : 3 jours

**Description** :
Mettre en place le monitoring et assurer le support initial.

**Outils de monitoring** :
- Laravel Horizon (queues)
- Laravel Telescope (debugging)
- Sentry (error tracking)
- New Relic / Datadog (APM)

**Livrables** :
- Monitoring configuré
- Documentation utilisateur
- Support initial (J+7)

---

## 📊 Résumé des Phases

| Phase | Description | Durée | Dépendances | Statut |
|-------|-------------|-------|-------------|--------|
| 0 | Préparation et Audit | 1-2 semaines | - | 🔴 À démarrer |
| 1 | Extension Attachments | 1 semaine | Phase 0 | 🔴 À démarrer |
| 2 | Renommage Records → RecordPhysicals | 1 semaine | Phase 1 | 🔴 À démarrer |
| 3 | Système de Types | 1 semaine | Phase 2 | 🔴 À démarrer |
| 4 | Dossiers Numériques | 1-2 semaines | Phase 3 | 🔴 À démarrer |
| 5 | Documents Numériques | 1-2 semaines | Phase 4 | 🔴 À démarrer |
| 6 | Artifacts (Objets Musée) | 1-2 semaines | Phase 2 | 🔴 À démarrer |
| 7 | Books (Livres) | 1-2 semaines | Phase 2 | 🔴 À démarrer |
| 8 | Periodics (Publications) | 1-2 semaines | Phase 2 | 🔴 À démarrer |
| 9 | Services & API | 2 semaines | Phases 4-8 | 🔴 À démarrer |
| 10 | Migration Données | 1 semaine | Phase 9 | 🔴 À démarrer |
| 11 | Tests & Validation | 2 semaines | Phase 10 | 🔴 À démarrer |
| 12 | Déploiement | 1 semaine | Phase 11 | 🔴 À démarrer |

**Durée totale estimée** : 14-18 semaines (3,5 à 4,5 mois)

---

## 🎯 Prochaines Étapes

1. ✅ **Valider ce plan avec l'équipe**
2. ✅ **Commencer Phase 0 : Audit et backup**
3. ✅ **Configurer l'environnement de test**
4. ✅ **Lancer Phase 1 : Extension attachments**

---

## 📚 Références

- [Documentation complète] : `docs/refonte_records.md`
- [Schéma de base de données] : `docs/database-schema.md`
- [Guide de migration] : `docs/migration-guide.md`

