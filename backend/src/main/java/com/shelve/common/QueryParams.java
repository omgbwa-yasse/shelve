package com.shelve.common;

import com.shelve.exception.ApiException;
import jakarta.servlet.http.HttpServletRequest;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

/**
 * Paramètres de requête communs à l'API — CONVENTIONS §3, miroir de
 * `HandlesApiQueries`.
 *
 * Principe directeur : **un paramètre non reconnu provoque un 400, jamais un
 * silence.** Un filtre ignoré renverrait des données que l'appelant croit
 * filtrées — risque de fuite inter-organisation (R03).
 */
public final class QueryParams {

    public static final int DEFAULT_PAGE_SIZE = 25;
    public static final int MAX_PAGE_SIZE = 100;
    private static final List<String> OPERATORS =
            List.of("eq", "ne", "gt", "gte", "lt", "lte", "like", "in", "between", "null");

    private final Map<String, Map<String, Object>> filters = new LinkedHashMap<>();
    private final List<SortField> sort = new ArrayList<>();
    private final List<String> includes = new ArrayList<>();
    private int pageNumber = 1;
    private int pageSize = DEFAULT_PAGE_SIZE;

    /** (field, operator, value) — miroir du `$query->where($field, $op, $value)`. */
    public record SortField(String field, boolean descending) {}

    public static QueryParams parse(HttpServletRequest request) {
        QueryParams qp = new QueryParams();
        Map<String, String[]> params = request.getParameterMap();

        for (Map.Entry<String, String[]> e : params.entrySet()) {
            String key = e.getKey();
            String[] values = e.getValue();
            String value = values.length > 0 ? values[0] : "";

            if (key.startsWith("filter[")) {
                parseFilter(qp, key, value);
            }
        }

        String sort = first(params, "sort");
        if (sort != null && !sort.isBlank()) {
            for (String f : sort.split(",")) {
                f = f.trim();
                if (f.isEmpty()) continue;
                boolean desc = f.startsWith("-");
                f = f.startsWith("-") || f.startsWith("+") ? f.substring(1) : f;
                qp.sort.add(new SortField(f, desc));
            }
        }

        String include = first(params, "include");
        if (include != null && !include.isBlank()) {
            qp.includes.addAll(Arrays.stream(include.split(",")).map(String::trim).toList());
        }

        String pageNumber = first(params, "page[number]");
        if (pageNumber == null) pageNumber = first(params, "page");
        if (pageNumber != null) {
            try {
                qp.pageNumber = Math.max(1, Integer.parseInt(pageNumber));
            } catch (NumberFormatException ignored) {
                // page invalide → page 1
            }
        }

        String pageSize = first(params, "page[size]");
        if (pageSize != null) {
            try {
                int size = Integer.parseInt(pageSize);
                qp.pageSize = Math.max(1, Math.min(size, MAX_PAGE_SIZE));
            } catch (NumberFormatException ignored) {
                // taille invalide → défaut
            }
        }

        return qp;
    }

    private static void parseFilter(QueryParams qp, String key, String value) {
        // filter[field]  ou  filter[field][operator]
        String inner = key.substring("filter[".length(), key.length() - 1);
        int bracket = inner.indexOf('[');
        String field;
        String operator = "eq";
        if (bracket >= 0) {
            field = inner.substring(0, bracket);
            operator = inner.substring(bracket + 1, inner.length() - 1);
        } else {
            field = inner;
        }

        if (!OPERATORS.contains(operator)) {
            throw ApiException.badRequest(
                    "Opérateur inconnu : `" + operator + "`. Opérateurs valides : " + String.join(", ", OPERATORS) + ".");
        }

        qp.filters.computeIfAbsent(field, k -> new LinkedHashMap<>()).put(operator, value);
    }

    private static String first(Map<String, String[]> params, String key) {
        String[] v = params.get(key);
        return (v != null && v.length > 0) ? v[0] : null;
    }

    /** Vérifie les listes blanches et jette un 400 si un champ est hors liste. */
    public void validate(List<String> filterable, List<String> sortable, List<String> includable) {
        for (String field : filters.keySet()) {
            if (!filterable.contains(field)) {
                throw ApiException.badRequest(
                        "Filtre non autorisé : `" + field + "`. Champs filtrables : " + String.join(", ", filterable) + ".");
            }
        }
        for (SortField sf : sort) {
            if (!sortable.contains(sf.field())) {
                throw ApiException.badRequest(
                        "Tri non autorisé : `" + sf.field() + "`. Champs triables : " + String.join(", ", sortable) + ".");
            }
        }
        for (String rel : includes) {
            if (!includable.contains(rel)) {
                throw ApiException.badRequest(
                        "Relation non incluable : `" + rel + "`. Relations disponibles : "
                                + (includable.isEmpty() ? "aucune" : String.join(", ", includable)) + ".");
            }
        }
    }

    public Map<String, Map<String, Object>> getFilters() { return filters; }
    public List<SortField> getSort() { return sort; }
    public List<String> getIncludes() { return includes; }
    public int getPageNumber() { return pageNumber; }
    public int getPageSize() { return pageSize; }
}
