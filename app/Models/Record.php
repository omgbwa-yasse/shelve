<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

/**
 * Notice unique (alignement FicheDocument IntelliGID).
 *
 * Remplace RecordPhysical + RecordDigitalFolder + RecordDigitalDocument pour la partie
 * "notice". Le support (papier/numérique) est porté par record_mediums (Phase 3), le
 * versionnage par records.version_number/is_current_version + record_relations.
 */
class Record extends Model
{
    use BelongsToOrganisation, HasFactory, Searchable, SoftDeletes;

    protected $table = 'records';

    protected $fillable = [
        'code',
        'name',
        'description',
        // Cycle de vie
        'opening_date',
        'closing_date',
        'processing_date',
        'transfer_approved_date',
        'transfer_effective_date',
        'deposit_approved_date',
        'deposit_effective_date',
        'destruction_approved_date',
        'destruction_effective_date',
        'last_reminder_date',
        'old_record_number',
        'archival_status_gvaa',
        'unavailable',
        'annual_opening',
        'is_essential',
        // Prêt notice
        'loaned_to',
        'loaned_at',
        'loan_planned_return_at',
        'loan_actual_return_at',
        'modified_after_loan',
        // Confidentialité
        'confidentiality_id',
        'access_limit_id',
        'publication_date',
        'sent_date',
        'received_date',
        'signature_date',
        'final_version_creation',
        'location_before_add',
        // Structure
        'type_id',
        'level_id',
        'status_id',
        'activity_id',
        'parent_id',
        'organisation_id',
        'creator_id',
        'assigned_to',
        'access_level',
        'requires_approval',
        'approved_by',
        'approved_at',
        'metadata',
        'linear_measure_cm',
        'start_date',
        'end_date',
        'date_exact',
        'date_format',
        'version_number',
        'is_current_version',
        'legacy_source',
        'legacy_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'unavailable' => 'boolean',
        'annual_opening' => 'boolean',
        'is_essential' => 'boolean',
        'modified_after_loan' => 'boolean',
        'requires_approval' => 'boolean',
        'is_current_version' => 'boolean',
        'final_version_creation' => 'boolean',
        'opening_date' => 'date',
        'closing_date' => 'date',
        'processing_date' => 'date',
        'transfer_approved_date' => 'date',
        'transfer_effective_date' => 'date',
        'deposit_approved_date' => 'date',
        'deposit_effective_date' => 'date',
        'destruction_approved_date' => 'date',
        'destruction_effective_date' => 'date',
        'last_reminder_date' => 'date',
        'loaned_at' => 'datetime',
        'loan_planned_return_at' => 'datetime',
        'loan_actual_return_at' => 'datetime',
        'publication_date' => 'date',
        'sent_date' => 'datetime',
        'received_date' => 'datetime',
        'signature_date' => 'datetime',
        'approved_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'date_exact' => 'date',
        'version_number' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations structurelles
    |--------------------------------------------------------------------------
    */
    public function type(): BelongsTo
    {
        return $this->belongsTo(RecordType::class, 'type_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(RecordLevel::class, 'level_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(RecordStatus::class, 'status_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Record::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Record::class, 'parent_id');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function confidentiality(): BelongsTo
    {
        return $this->belongsTo(RecordConfidentiality::class, 'confidentiality_id');
    }

    public function accessLimit(): BelongsTo
    {
        return $this->belongsTo(ReferenceList::class, 'access_limit_id');
    }

    /**
     * Supports (0, 1 ou plusieurs) de la notice — Phase 3.
     */
    public function mediums(): HasMany
    {
        return $this->hasMany(RecordMedium::class, 'record_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Relations pivots (noms unifiés — tables créées en Phase 4)
    |--------------------------------------------------------------------------
    */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'record_author', 'record_id', 'author_id')
            ->withTimestamps();
    }

    /**
     * Pièces jointes (table `attachments`, partagée avec D06), via la pivot
     * `record_physical_attachment` (FK `record_id` repointée vers `records` en phase 4).
     */
    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(Attachment::class, 'record_physical_attachment', 'record_id', 'attachment_id')
            ->withTimestamps();
    }

    /**
     * Contenants de la notice (pivot `record_physical_container`). Depuis l'unification
     * (2026-08-04), la colonne `record_physical_id` porte l'id de la notice (`records.id`).
     */
    public function containers(): BelongsToMany
    {
        return $this->belongsToMany(Container::class, 'record_physical_container', 'record_physical_id', 'container_id')
            ->withPivot(['description', 'creator_id'])
            ->withTimestamps();
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'record_keyword', 'record_id', 'keyword_id');
    }

    public function getKeywordsStringAttribute(): string
    {
        return $this->keywords->pluck('name')->implode(';');
    }

    public function thesaurusConcepts(): BelongsToMany
    {
        return $this->belongsToMany(
            ThesaurusConcept::class,
            'record_thesaurus_concept',
            'record_id',
            'concept_id'
        )
            ->withPivot('weight', 'context', 'extraction_note')
            ->withTimestamps();
    }

    public function thesaurusConceptsByWeight(): BelongsToMany
    {
        return $this->thesaurusConcepts()->orderBy('weight', 'desc');
    }

    public function mainThesaurusConcepts(): BelongsToMany
    {
        return $this->thesaurusConcepts()->wherePivot('weight', '>=', 0.7);
    }

    public function secondaryThesaurusConcepts(): BelongsToMany
    {
        return $this->thesaurusConcepts()->wherePivot('weight', '<', 0.7);
    }

    /**
     * Déclassement / réactivation (FK renommées en record_id en Phase 4).
     */
    public function declassementRecords(): HasMany
    {
        return $this->hasMany(DeclassementRecord::class, 'record_id');
    }

    public function reactivations(): HasMany
    {
        return $this->hasMany(RecordReactivation::class, 'record_id');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /*
    |--------------------------------------------------------------------------
    | Collaboration (étape 8 — partage, favoris, commentaires, raccourcis)
    |--------------------------------------------------------------------------
    */
    public function shares(): HasMany
    {
        return $this->hasMany(RecordShare::class, 'record_id');
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RecordComment::class, 'record_id');
    }

    public function shortcuts(): HasMany
    {
        return $this->hasMany(RecordShortcut::class, 'record_id');
    }

    /**
     * La notice est-elle partagée avec l'utilisateur courant ?
     */
    public function isSharedWith(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        if (! $userId) {
            return false;
        }

        return $this->shares()
            ->where('user_id', $userId)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }

    public function dollies(): BelongsToMany
    {
        return $this->belongsToMany(Dolly::class, 'dolly_records', 'record_id', 'dolly_id')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations (record_relations — versionnement et relations génériques)
    |--------------------------------------------------------------------------
    */
    public function relationsFrom(): HasMany
    {
        return $this->hasMany(RecordRelation::class, 'source_id');
    }

    public function relationsTo(): HasMany
    {
        return $this->hasMany(RecordRelation::class, 'target_id');
    }

    public function previousVersion(): ?Record
    {
        $relation = $this->relationsFrom()->where('type', RecordRelation::TYPE_VERSION_OF)->first();

        return $relation?->target;
    }

    public function nextVersions(): Collection
    {
        return RecordRelation::where('type', RecordRelation::TYPE_VERSION_OF)
            ->where('target_id', $this->id)
            ->with('source')
            ->get()
            ->pluck('source');
    }

    public function getAllVersions(): Collection
    {
        $versions = collect([$this]);
        $current = $this->previousVersion();

        while ($current) {
            $versions->push($current);
            $current = $current->previousVersion();
        }

        return $versions->sortBy('version_number')->values();
    }

    public function createNewVersion(array $attributes = []): self
    {
        $new = $this->replicate();
        $new->version_number = ($this->version_number ?? 1) + 1;
        $new->is_current_version = true;
        $new->fill($attributes);
        $new->save();

        $this->is_current_version = false;
        $this->save();

        RecordRelation::create([
            'source_id' => $new->id,
            'target_id' => $this->id,
            'type' => RecordRelation::TYPE_VERSION_OF,
        ]);

        return $new;
    }

    /*
    |--------------------------------------------------------------------------
    | Métadonnées dynamiques (profil du type)
    |--------------------------------------------------------------------------
    */
    public function getMetadataValue(string $metadataCode, $default = null)
    {
        return $this->metadata[$metadataCode] ?? $default;
    }

    public function setMetadataValue(string $metadataCode, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$metadataCode] = $value;
        $this->metadata = $metadata;
    }

    public function setMultipleMetadata(array $metadataArray): void
    {
        $metadata = $this->metadata ?? [];
        foreach ($metadataArray as $code => $value) {
            $metadata[$code] = $value;
        }
        $this->metadata = $metadata;
    }

    public function getRequiredMetadataFields(): array
    {
        if (! $this->type) {
            return [];
        }

        return $this->type->getMandatoryMetadataDefinitions()
            ->map(fn ($definition) => [
                'code' => $definition->code,
                'name' => $definition->name,
                'data_type' => $definition->data_type,
                'required' => true,
            ])
            ->toArray();
    }

    public function getVisibleMetadataFields(): array
    {
        if (! $this->type) {
            return [];
        }

        return $this->type->getVisibleMetadataDefinitions()
            ->filter(fn ($definition) => ! $this->isMetadataRestrictedForCurrentUser($definition))
            ->map(function ($definition) {
                return [
                    'code' => $definition->code,
                    'name' => $definition->name,
                    'data_type' => $definition->data_type,
                    'value' => $this->getMetadataValue($definition->code),
                    'required' => (bool) $definition->pivot->mandatory,
                    'readonly' => (bool) $definition->pivot->readonly,
                    'group' => $definition->pivot->group,
                    'sortable' => $definition->sortable,
                    'highlightable' => $definition->highlightable,
                    'autocomplete' => $definition->autocomplete,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Codes de métadonnées masquées pour l'utilisateur courant (étape 5).
     * Superadmin et détenteurs d'un rôle autorisé voient tout.
     */
    public function getRestrictedMetadataCodes(): array
    {
        if (! $this->type) {
            return [];
        }

        return $this->type->getVisibleMetadataDefinitions()
            ->filter(fn ($definition) => $this->isMetadataRestrictedForCurrentUser($definition))
            ->pluck('code')
            ->values()
            ->toArray();
    }

    /**
     * Une métadonnée marquée `restricted_to_roles` est-elle invisible pour le
     * rôle courant ? (étape 5 — filtrée en affichage et en indexation).
     */
    private function isMetadataRestrictedForCurrentUser($definition): bool
    {
        $rawRoles = $definition->pivot->restricted_to_roles ?? [];

        if (empty($rawRoles)) {
            return false;
        }

        $roles = is_array($rawRoles) ? $rawRoles : json_decode((string) $rawRoles, true);

        if (empty($roles)) {
            return false;
        }

        $user = auth()->user();

        if (! $user) {
            return true;
        }

        if ($user->isSuperAdmin()) {
            return false;
        }

        return ! $user->roles()->whereIn('name', $roles)->exists();
    }

    public function validateMetadata(): array
    {
        if (! $this->type) {
            return ['type' => 'Record type not set'];
        }

        $service = app(\App\Services\MetadataValidationService::class);

        try {
            $service->validateRecordMetadata($this->type, $this->metadata ?? [], $this->id);

            return [];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $e->errors();
        }
    }

    public function hasCompleteMetadata(): bool
    {
        return empty($this->validateMetadata());
    }

    /*
    |--------------------------------------------------------------------------
    | Arbre
    |--------------------------------------------------------------------------
    */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }

    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    public function getRoot(): ?Record
    {
        if ($this->isRoot()) {
            return $this;
        }

        return $this->parent?->getRoot();
    }

    public function getAncestors(): Collection
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    public function getDescendants(): Collection
    {
        $descendants = collect();

        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }

    public function getSiblings(): Collection
    {
        if ($this->isRoot()) {
            return static::roots()->where('id', '!=', $this->id)->get();
        }

        return $this->parent->children()->where('id', '!=', $this->id)->get();
    }

    public function getPath(string $separator = ' / '): string
    {
        $path = collect([$this->name]);

        foreach ($this->getAncestors()->reverse() as $ancestor) {
            $path->prepend($ancestor->name);
        }

        return $path->implode($separator);
    }

    /**
     * Vérifie si un record donné est un descendant de la notice courante.
     */
    public function isDescendantOf(int $recordId): bool
    {
        $ancestors = $this->getAncestors();

        return $ancestors->contains(fn ($ancestor) => $ancestor->id === $recordId);
    }

    /*
    |--------------------------------------------------------------------------
    | Comportement métier
    |--------------------------------------------------------------------------
    */
    /**
     * Un type conteneur joue le rôle de "dossier" (Fonds, Série, Dossier, ...).
     */
    public function isContainer(): bool
    {
        return $this->type?->isContainer() ?? false;
    }

    public function generateCode(): string
    {
        if (! $this->type) {
            throw new \Exception('Cannot generate code without a type');
        }

        return $this->type->generateCode();
    }

    public function approve(?int $userId): void
    {
        $this->update([
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeCurrentVersion(Builder $query): Builder
    {
        return $query->where('is_current_version', true);
    }

    /**
     * Portée organisation (R03) : les notices sont org-scopées par `organisation_id`
     * (trait BelongsToOrganisation). Une notice hors de l'organisation courante répond
     * 404, jamais 403 (motif D03).
     */
    public function scopeInOrganisation(Builder $query, int $organisationId): Builder
    {
        return $query->where($this->getTable().'.organisation_id', $organisationId);
    }

    public function scopeOfType(Builder $query, string $typeCode): Builder
    {
        return $query->whereHas('type', fn ($q) => $q->where('code', $typeCode));
    }

    /*
    |--------------------------------------------------------------------------
    | Recherche (Scout)
    |--------------------------------------------------------------------------
    */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'date_start' => $this->start_date,
            'date_end' => $this->end_date,
            'date_exact' => $this->date_exact,
            'metadata_text' => $this->flattenMetadataForSearch(),
        ];
    }

    /**
     * Les ~27 anciennes colonnes descriptives (ISAD(G) + non-ISAD) sont désormais
     * des MetadataDefinition système stockées dans `metadata` (JSON). Un aplatissement
     * en texte simple indexe mieux que le JSON brut (guillemets/accolades nuisent à
     * la pertinence du full-text Scout).
     */
    private function flattenMetadataForSearch(): string
    {
        if (empty($this->metadata) || ! is_array($this->metadata)) {
            return '';
        }

        $excluded = $this->getRestrictedMetadataCodes();

        return collect($this->metadata)
            ->reject(fn ($value, $code) => in_array($code, $excluded, true))
            ->filter(fn ($value) => is_scalar($value))
            ->implode(' ');
    }

    /*
    |--------------------------------------------------------------------------
    | Étape 4 — métadonnées copiées (parent → enfant) et calculées
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::saving(function (Record $record) {
            $record->applyCopiedAndComputedMetadata();
        });
    }

    /**
     * Recalcule les champs copiés depuis le parent et les champs calculés
     * (gabarit interpolé) à chaque sauvegarde. Uniquement si le champ cible
     * est vide (copie) — le calcul remplace toujours la valeur (étape 4).
     */
    public function applyCopiedAndComputedMetadata(): void
    {
        if (! $this->type) {
            return;
        }

        $profiles = $this->type->metadataProfiles()->with('metadataDefinition')->get();

        foreach ($profiles as $profile) {
            $definition = $profile->metadataDefinition;

            if ($definition->computed_template) {
                $this->setMetadataValue($definition->code, $this->interpolateComputedTemplate($definition->computed_template));
            } elseif ($definition->copy_source_type === 'parent'
                && $definition->copy_source_field
                && blank($this->getMetadataValue($definition->code))) {
                $value = $this->resolveCopiedParentValue($definition->copy_source_field);
                $this->setMetadataValue($definition->code, $value);
            }
        }
    }

    protected function resolveCopiedParentValue(string $sourceField)
    {
        $parent = $this->parent;

        if (! $parent) {
            return null;
        }

        if (in_array($sourceField, ['name', 'code', 'description'], true)) {
            return $parent->{$sourceField};
        }

        return $parent->getMetadataValue($sourceField);
    }

    protected function interpolateComputedTemplate(string $template): string
    {
        return preg_replace_callback('/\$(\w+)/', function ($matches) {
            $code = $matches[1];

            if (in_array($code, ['name', 'code', 'description'], true)) {
                return (string) $this->{$code};
            }

            $value = $this->getMetadataValue($code);

            return is_scalar($value) ? (string) $value : '';
        }, $template);
    }

    /*
    |--------------------------------------------------------------------------
    | Étape 9 — duplication de notices
    |--------------------------------------------------------------------------
    */
    /**
     * Duplique la notice : métadonnées seules, ou fiche + arborescence (sans documents).
     */
    public function duplicate(bool $withChildren = false, array $overrides = []): self
    {
        $copy = $this->replicate();

        $copy->version_number = 1;
        $copy->is_current_version = true;
        $copy->parent_id = $this->parent_id;
        $copy->code = $copy->type
            ? $this->type->generateCode()
            : $this->code.'-copie';

        $copy->fill($overrides);
        $copy->save();

        $this->attachDuplicatedRelations($copy, $this);

        if ($withChildren) {
            foreach ($this->children as $child) {
                // La récursion duplique déjà les relations de chaque enfant — ne pas les réattacher ici.
                $child->duplicate(true, ['parent_id' => $copy->id]);
            }
        }

        return $copy;
    }

    /**
     * Reproduit les relations pivots liées à la fiche (auteurs, mots-clés, concepts,
     * pièces jointes). Ne duplique pas les documents.
     */
    public function attachDuplicatedRelations(Record $copy, Record $original): void
    {
        foreach ($original->authors as $author) {
            $copy->authors()->attach($author->id);
        }
        foreach ($original->keywords as $keyword) {
            $copy->keywords()->attach($keyword->id);
        }
        foreach ($original->thesaurusConcepts as $concept) {
            $copy->thesaurusConcepts()->attach($concept->id);
        }
        foreach ($original->attachments as $attachment) {
            $copy->attachments()->attach($attachment->id);
        }
    }
}
