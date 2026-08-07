<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Filtrage, tri, inclusion et pagination — implémentation unique de CONVENTIONS §3.
 *
 * Écrire cette logique dans chaque contrôleur reviendrait à la répéter près de deux
 * cents fois, avec autant d'occasions de la faire diverger. Elle est donc ici, et les
 * contrôleurs se contentent de déclarer leurs listes blanches.
 *
 * Principe directeur : **un paramètre non reconnu provoque un 400, jamais un silence.**
 * Un filtre ignoré renvoie des données que l'appelant croit filtrées — c'est ainsi que
 * des données d'une autre organisation finissent par fuiter (risque R03).
 */
trait HandlesApiQueries
{
    private const DEFAULT_PAGE_SIZE = 25;
    private const MAX_PAGE_SIZE = 100;

    /** Opérateurs de filtre reconnus (CONVENTIONS §3). */
    private const OPERATORS = ['eq', 'ne', 'gt', 'gte', 'lt', 'lte', 'like', 'in', 'between', 'null'];

    protected function applyFilters(Builder $query, Request $request, array $allowed): Builder
    {
        $filters = $request->input('filter', []);

        if (!is_array($filters)) {
            throw new BadRequestHttpException("Le paramètre `filter` doit être un tableau.");
        }

        foreach ($filters as $field => $condition) {
            if (!in_array($field, $allowed, true)) {
                throw new BadRequestHttpException(
                    "Filtre non autorisé : `$field`. Champs filtrables : " . implode(', ', $allowed) . '.'
                );
            }

            // filter[champ]=valeur → opérateur `eq` implicite
            if (!is_array($condition)) {
                $query->where($field, $condition);
                continue;
            }

            foreach ($condition as $operator => $value) {
                $this->applyOperator($query, $field, (string) $operator, $value);
            }
        }

        return $query;
    }

    private function applyOperator(Builder $query, string $field, string $operator, mixed $value): void
    {
        if (!in_array($operator, self::OPERATORS, true)) {
            throw new BadRequestHttpException(
                "Opérateur inconnu : `$operator`. Opérateurs valides : " . implode(', ', self::OPERATORS) . '.'
            );
        }

        match ($operator) {
            'eq' => $query->where($field, '=', $value),
            'ne' => $query->where($field, '!=', $value),
            'gt' => $query->where($field, '>', $value),
            'gte' => $query->where($field, '>=', $value),
            'lt' => $query->where($field, '<', $value),
            'lte' => $query->where($field, '<=', $value),
            'like' => $query->where($field, 'like', '%' . $value . '%'),
            'in' => $query->whereIn($field, is_array($value) ? $value : explode(',', (string) $value)),
            'between' => $this->applyBetween($query, $field, $value),
            'null' => filter_var($value, FILTER_VALIDATE_BOOLEAN)
                ? $query->whereNull($field)
                : $query->whereNotNull($field),
        };
    }

    private function applyBetween(Builder $query, string $field, mixed $value): void
    {
        $bounds = is_array($value) ? array_values($value) : explode(',', (string) $value);

        if (count($bounds) !== 2) {
            throw new BadRequestHttpException(
                "L'opérateur `between` attend deux bornes pour `$field` (reçu : " . count($bounds) . ').'
            );
        }

        $query->whereBetween($field, $bounds);
    }

    /**
     * Tri : `?sort=-created_at,name`. Le préfixe `-` inverse l'ordre.
     *
     * Le tri est TOUJOURS délégué à la base, jamais fait en mémoire : la collation
     * utf8mb4_unicode_ci de MySQL et le Collator de Java ne classent pas les accents
     * de la même façon, et cette divergence se verrait en phase 3 (risque R14).
     */
    protected function applySorting(Builder $query, Request $request, array $allowed, string $default = 'id'): Builder
    {
        $sort = $request->input('sort');

        if (!$sort) {
            return $query->orderBy($default);
        }

        foreach (explode(',', (string) $sort) as $field) {
            $field = trim($field);
            $direction = str_starts_with($field, '-') ? 'desc' : 'asc';
            $field = ltrim($field, '-+');

            if (!in_array($field, $allowed, true)) {
                throw new BadRequestHttpException(
                    "Tri non autorisé : `$field`. Champs triables : " . implode(', ', $allowed) . '.'
                );
            }

            $query->orderBy($field, $direction);
        }

        return $query;
    }

    /** Inclusion de relations : `?include=organisation,author`. */
    protected function applyIncludes(Builder $query, Request $request, array $allowed): Builder
    {
        $include = $request->input('include');

        if (!$include) {
            return $query;
        }

        $relations = array_map('trim', explode(',', (string) $include));

        foreach ($relations as $relation) {
            if (!in_array($relation, $allowed, true)) {
                throw new BadRequestHttpException(
                    "Relation non incluable : `$relation`. Relations disponibles : "
                    . ($allowed ? implode(', ', $allowed) : 'aucune') . '.'
                );
            }
        }

        return $query->with($relations);
    }

    /**
     * Taille de page bornée : une valeur excessive est ramenée au maximum sans erreur.
     * Le client ne doit pas pouvoir déclencher un SELECT illimité.
     */
    protected function pageSize(Request $request): int
    {
        $size = (int) $request->input('page.size', self::DEFAULT_PAGE_SIZE);

        return max(1, min($size, self::MAX_PAGE_SIZE));
    }

    /** Enveloppe de collection paginée — CONVENTIONS §2. */
    protected function paginatedResponse($paginator, string $resourceClass): array
    {
        return [
            'data' => $resourceClass::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
                'last' => $paginator->url($paginator->lastPage()),
            ],
        ];
    }
}
