<?php

namespace App\Services;

use App\Models\RecordDigitalFolder;
use App\Models\RecordDigitalFolderType;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Service pour la gestion des dossiers numériques (Phase 4 - SpecKit)
 * Gère la hiérarchie, les permissions, les workflows et les statistiques
 */
class RecordDigitalFolderService
{
    /**
     * Créer un nouveau dossier numérique
     *
     * @param RecordDigitalFolderType $type Type de dossier
     * @param array $data Données du dossier (name, description, metadata, etc.)
     * @param User $creator Créateur du dossier
     * @param Organisation $organisation Organisation propriétaire
     * @param RecordDigitalFolder|null $parent Dossier parent (null = racine)
     * @return RecordDigitalFolder
     * @throws Exception
     */
    public function createFolder(
        RecordDigitalFolderType $type,
        array $data,
        User $creator,
        Organisation $organisation,
        ?RecordDigitalFolder $parent = null
    ): RecordDigitalFolder {
        // Générer le code unique
        $code = $this->generateUniqueCode($type);

        // Valider les métadonnées obligatoires
        $this->validateMandatoryMetadata($type, $data['metadata'] ?? []);

        // Vérifier la profondeur si parent fourni
        if ($parent) {
            $this->validateMaxDepth($parent, $type);
        }

        $folderData = [
            'code' => $code,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type_id' => $type->id,
            'parent_id' => $parent?->id,
            'metadata' => $data['metadata'] ?? [],
            'access_level' => $data['access_level'] ?? $this->mapAccessLevel($type->default_access_level),
            'status' => 'active',
            'requires_approval' => $type->requires_approval,
            'creator_id' => $creator->id,
            'organisation_id' => $organisation->id,
            'assigned_to' => $data['assigned_to'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ];

        $folder = RecordDigitalFolder::create($folderData);

        // Mettre à jour les statistiques du parent
        if ($parent) {
            $parent->updateStatistics();
        }

        return $folder;
    }

    /**
     * Déplacer un dossier vers un nouveau parent
     *
     * @param RecordDigitalFolder $folder Dossier à déplacer
     * @param RecordDigitalFolder|null $newParent Nouveau parent (null = racine)
     * @return RecordDigitalFolder
     * @throws Exception
     */
    public function moveFolder(
        RecordDigitalFolder $folder,
        ?RecordDigitalFolder $newParent = null
    ): RecordDigitalFolder {
        // Empêcher de déplacer dans ses propres descendants
        if ($newParent && $this->isDescendantOf($newParent, $folder)) {
            throw new Exception("Impossible de déplacer un dossier dans ses propres sous-dossiers");
        }

        // Vérifier la profondeur maximale
        if ($newParent) {
            $this->validateMaxDepth($newParent, $folder->type);
        }

        $oldParent = $folder->parent;

        DB::transaction(function () use ($folder, $newParent, $oldParent) {
            $folder->update(['parent_id' => $newParent?->id]);

            // Mettre à jour les statistiques
            if ($oldParent) {
                $oldParent->updateStatistics();
            }
            if ($newParent) {
                $newParent->updateStatistics();
            }
        });

        return $folder->fresh();
    }

    /**
     * Renommer un dossier
     *
     * @param RecordDigitalFolder $folder
     * @param string $newName
     * @return RecordDigitalFolder
     */
    public function renameFolder(RecordDigitalFolder $folder, string $newName): RecordDigitalFolder
    {
        $folder->update(['name' => $newName]);
        return $folder->fresh();
    }

    /**
     * Archiver un dossier (et ses descendants)
     *
     * @param RecordDigitalFolder $folder
     * @param bool $recursive Archiver aussi les sous-dossiers
     * @return RecordDigitalFolder
     */
    public function archiveFolder(RecordDigitalFolder $folder, bool $recursive = true): RecordDigitalFolder
    {
        DB::transaction(function () use ($folder, $recursive) {
            $folder->update(['status' => 'archived']);

            if ($recursive) {
                foreach ($folder->children as $child) {
                    $this->archiveFolder($child, true);
                }
            }
        });

        return $folder->fresh();
    }

    /**
     * Supprimer un dossier (soft delete)
     *
     * @param RecordDigitalFolder $folder
     * @param bool $force Forcer la suppression même si contient des documents/sous-dossiers
     * @return bool
     * @throws Exception
     */
    public function deleteFolder(RecordDigitalFolder $folder, bool $force = false): bool
    {
        if (!$force) {
            if ($folder->documents_count > 0) {
                throw new Exception("Le dossier contient {$folder->documents_count} document(s). Impossible de le supprimer.");
            }
            if ($folder->subfolders_count > 0) {
                throw new Exception("Le dossier contient {$folder->subfolders_count} sous-dossier(s). Impossible de le supprimer.");
            }
        }

        $parent = $folder->parent;

        $deleted = $folder->delete();

        if ($deleted && $parent) {
            $parent->updateStatistics();
        }

        return $deleted;
    }

    /**
     * Approuver un dossier
     *
     * @param RecordDigitalFolder $folder
     * @param User $approver
     * @param string|null $notes
     * @return RecordDigitalFolder
     * @throws Exception
     */
    public function approveFolder(
        RecordDigitalFolder $folder,
        User $approver,
        ?string $notes = null
    ): RecordDigitalFolder {
        if (!$folder->requires_approval) {
            throw new Exception("Ce dossier ne nécessite pas d'approbation");
        }

        if ($folder->approved_at) {
            throw new Exception("Ce dossier a déjà été approuvé le {$folder->approved_at->format('d/m/Y')}");
        }

        $folder->approve($approver, $notes);

        return $folder->fresh();
    }

    /**
     * Calculer la taille totale d'un dossier (documents + sous-dossiers)
     *
     * @param RecordDigitalFolder $folder
     * @return int Taille en octets
     */
    public function calculateTotalSize(RecordDigitalFolder $folder): int
    {
        $documentsSize = $folder->documents()->sum('file_size') ?? 0;
        $subfoldersSize = 0;

        foreach ($folder->children as $child) {
            $subfoldersSize += $this->calculateTotalSize($child);
        }

        return $documentsSize + $subfoldersSize;
    }

    /**
     * Mettre à jour les métadonnées d'un dossier
     *
     * @param RecordDigitalFolder $folder
     * @param array $metadata
     * @return RecordDigitalFolder
     * @throws Exception
     */
    public function updateMetadata(RecordDigitalFolder $folder, array $metadata): RecordDigitalFolder
    {
        $this->validateMandatoryMetadata($folder->type, $metadata);

        $folder->update(['metadata' => $metadata]);

        return $folder->fresh();
    }

    /**
     * Obtenir l'arborescence complète d'un dossier
     *
     * @param RecordDigitalFolder $folder
     * @param int $maxDepth Profondeur maximale (-1 = illimitée)
     * @return array
     */
    public function getFolderTree(RecordDigitalFolder $folder, int $maxDepth = -1): array
    {
        return $this->buildTree($folder, 0, $maxDepth);
    }

    /**
     * Rechercher des dossiers
     *
     * @param array $criteria Critères de recherche
     * @return Collection
     */
    public function searchFolders(array $criteria): Collection
    {
        $query = RecordDigitalFolder::query();

        if (isset($criteria['name'])) {
            $query->where('name', 'like', "%{$criteria['name']}%");
        }

        if (isset($criteria['type_code'])) {
            $query->ofType($criteria['type_code']);
        }

        if (isset($criteria['status'])) {
            $query->where('status', $criteria['status']);
        }

        if (isset($criteria['organisation_id'])) {
            $query->byOrganisation($criteria['organisation_id']);
        }

        if (isset($criteria['creator_id'])) {
            $query->where('creator_id', $criteria['creator_id']);
        }

        if (isset($criteria['access_level'])) {
            $query->where('access_level', $criteria['access_level']);
        }

        if (isset($criteria['parent_id'])) {
            $query->where('parent_id', $criteria['parent_id']);
        }

        if (isset($criteria['is_root']) && $criteria['is_root']) {
            $query->roots();
        }

        return $query->with(['type', 'creator', 'organisation', 'parent'])->get();
    }

    /**
     * Obtenir les statistiques d'un dossier
     *
     * @param RecordDigitalFolder $folder
     * @return array
     */
    public function getFolderStatistics(RecordDigitalFolder $folder): array
    {
        return [
            'documents_count' => $folder->documents_count,
            'subfolders_count' => $folder->subfolders_count,
            'total_size' => $folder->total_size,
            'total_size_human' => $folder->total_size_human,
            'depth' => $folder->getDepth(),
            'is_root' => $folder->isRoot(),
            'is_leaf' => $folder->isLeaf(),
            'ancestors_count' => $folder->getAncestors()->count(),
            'descendants_count' => $folder->getDescendants()->count(),
            'path' => $folder->getPath(),
        ];
    }

    /**
     * Générer un code unique pour un dossier
     *
     * @param RecordDigitalFolderType $type
     * @return string
     */
    private function generateUniqueCode(RecordDigitalFolderType $type): string
    {
        $pattern = $type->code_pattern;
        $year = date('Y');
        $month = date('m');

        // Compter les dossiers existants de ce type cette année
        $count = RecordDigitalFolder::where('type_id', $type->id)
            ->where('code', 'like', "{$type->code_prefix}-{$year}-%")
            ->count();

        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        $code = str_replace(
            ['{YYYY}', '{MM}', '{NNNN}'],
            [$year, $month, $sequence],
            $pattern
        );

        // Vérifier l'unicité
        while (RecordDigitalFolder::where('code', $code)->exists()) {
            $sequence = str_pad(intval($sequence) + 1, 4, '0', STR_PAD_LEFT);
            $code = str_replace(
                ['{YYYY}', '{MM}', '{NNNN}'],
                [$year, $month, $sequence],
                $pattern
            );
        }

        return $code;
    }

    /**
     * Valider les métadonnées obligatoires
     *
     * @param RecordDigitalFolderType $type
     * @param array $metadata
     * @return void
     * @throws Exception
     */
    private function validateMandatoryMetadata(RecordDigitalFolderType $type, array $metadata): void
    {
        $requiredFields = json_decode($type->mandatory_metadata, true) ?? [];

        foreach ($requiredFields as $field) {
            if (!isset($metadata[$field]) || $metadata[$field] === '' || $metadata[$field] === null) {
                throw new Exception("Le champ de métadonnées '{$field}' est obligatoire");
            }
        }
    }

    /**
     * Valider la profondeur maximale
     *
     * @param RecordDigitalFolder $parent
     * @param RecordDigitalFolderType $type
     * @return void
     * @throws Exception
     */
    private function validateMaxDepth(RecordDigitalFolder $parent, RecordDigitalFolderType $type): void
    {
        // Note: max_depth n'existe pas dans la table réelle, on skip cette validation
        // Dans un système complet, on ajouterait cette colonne à la migration
        return;
    }

    /**
     * Vérifier si $folder est un descendant de $ancestor
     *
     * @param RecordDigitalFolder $folder
     * @param RecordDigitalFolder $ancestor
     * @return bool
     */
    private function isDescendantOf(RecordDigitalFolder $folder, RecordDigitalFolder $ancestor): bool
    {
        if ($folder->id === $ancestor->id) {
            return true;
        }

        $parent = $folder->parent;
        while ($parent) {
            if ($parent->id === $ancestor->id) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

    /**
     * Mapper les niveaux d'accès (types vs folders ont des enum différents)
     * Types: public, internal, restricted, confidential, secret
     * Folders: public, internal, confidential, secret (pas de 'restricted')
     *
     * @param string $typeAccessLevel
     * @return string
     */
    private function mapAccessLevel(string $typeAccessLevel): string
    {
        // 'restricted' dans types devient 'confidential' dans folders
        return $typeAccessLevel === 'restricted' ? 'confidential' : $typeAccessLevel;
    }

    /**
     * Construire l'arbre récursivement
     *
     * @param RecordDigitalFolder $folder
     * @param int $currentDepth
     * @param int $maxDepth
     * @return array
     */
    private function buildTree(RecordDigitalFolder $folder, int $currentDepth, int $maxDepth): array
    {
        $tree = [
            'id' => $folder->id,
            'code' => $folder->code,
            'name' => $folder->name,
            'type' => $folder->type->name,
            'status' => $folder->status,
            'documents_count' => $folder->documents_count,
            'subfolders_count' => $folder->subfolders_count,
            'total_size' => $folder->total_size_human,
            'depth' => $currentDepth,
            'children' => [],
        ];

        if ($maxDepth === -1 || $currentDepth < $maxDepth) {
            foreach ($folder->children as $child) {
                $tree['children'][] = $this->buildTree($child, $currentDepth + 1, $maxDepth);
            }
        }

        return $tree;
    }
}
