package com.shelve.common;

import com.shelve.exception.ApiException;
import jakarta.persistence.criteria.CriteriaBuilder;
import jakarta.persistence.criteria.Path;
import jakarta.persistence.criteria.Predicate;
import jakarta.persistence.criteria.Root;
import org.springframework.data.jpa.domain.Specification;

import java.util.Arrays;
import java.util.Map;

/**
 * Convertit les filtres CONVENTIONS §3 en Specification JPA.
 * Le tri est délégué à la base via PageRequest/Sort — jamais fait en mémoire
 * (collation MySQL vs Collator Java, risque R14).
 */
public final class Filters {

    private Filters() {}

    public static <T> Specification<T> of(Map<String, Map<String, Object>> filters) {
        return (root, query, cb) -> {
            if (filters.isEmpty()) {
                return cb.conjunction();
            }
            Predicate[] predicates = filters.entrySet().stream()
                    .map(e -> predicatesFor(root, cb, e.getKey(), e.getValue()))
                    .flatMap(Arrays::stream)
                    .toArray(Predicate[]::new);
            return cb.and(predicates);
        };
    }

    /** Variante typée explicite, pour aider l'inférence du générique dans les appels chaînés. */
    public static <T> Specification<T> of(Map<String, Map<String, Object>> filters, Class<T> type) {
        return of(filters);
    }

    private static <T> Predicate[] predicatesFor(Root<T> root, CriteriaBuilder cb,
                                                 String field, Map<String, Object> conditions) {
        return conditions.entrySet().stream()
                .map(e -> predicate(root, cb, field, e.getKey(), e.getValue()))
                .toArray(Predicate[]::new);
    }

    private static <T> Predicate predicate(Root<T> root, CriteriaBuilder cb,
                                           String field, String operator, Object raw) {
        Path<Object> path = root.get(field);
        String value = String.valueOf(raw);

        return switch (operator) {
            case "eq" -> cb.equal(path, coerce(path, value));
            case "ne" -> cb.notEqual(path, coerce(path, value));
            case "gt" -> cb.greaterThan(toComparable(cb, path, value), value);
            case "gte" -> cb.greaterThanOrEqualTo(toComparable(cb, path, value), value);
            case "lt" -> cb.lessThan(toComparable(cb, path, value), value);
            case "lte" -> cb.lessThanOrEqualTo(toComparable(cb, path, value), value);
            case "like" -> cb.like(cb.lower(asString(cb, path)), "%" + value.toLowerCase() + "%");
            case "in" -> {
                var values = Arrays.stream(value.split(",")).map(String::trim).toArray(String[]::new);
                yield path.in((Object[]) values);
            }
            case "between" -> {
                String[] bounds = value.split(",");
                if (bounds.length != 2) {
                    throw ApiException.badRequest(
                            "L'opérateur `between` attend deux bornes pour `" + field + "` (reçu : " + bounds.length + ").");
                }
                var c = toComparable(cb, path, bounds[0]);
                yield cb.between(c, bounds[0], bounds[1]);
            }
            case "null" -> Boolean.parseBoolean(value) ? cb.isNull(path) : cb.isNotNull(path);
            default -> throw ApiException.badRequest("Opérateur inconnu : `" + operator + "`.");
        };
    }

    private static jakarta.persistence.criteria.Expression<String> asString(CriteriaBuilder cb, Path<?> path) {
        return cb.lower(path.as(String.class));
    }

    @SuppressWarnings({"unchecked", "rawtypes"})
    private static jakarta.persistence.criteria.Expression<String> toComparable(
            CriteriaBuilder cb, Path<?> path, String value) {
        return (jakarta.persistence.criteria.Expression<String>) path.as(String.class);
    }

    @SuppressWarnings({"unchecked", "rawtypes"})
    private static Object coerce(Path<Object> path, String value) {
        Class<?> type = path.getJavaType();
        try {
            if (type == Long.class || type == long.class) return Long.parseLong(value);
            if (type == Integer.class || type == int.class) return Integer.parseInt(value);
            if (type == Boolean.class || type == boolean.class) return Boolean.parseBoolean(value);
            if (type == java.math.BigDecimal.class) return new java.math.BigDecimal(value);
            if (type == java.time.Instant.class) return java.time.Instant.parse(value);
            if (type.isEnum()) {
                for (Object c : type.getEnumConstants()) {
                    if (((Enum<?>) c).name().equals(value)) return c;
                }
                return value;
            }
        } catch (Exception ignored) {
            // si la coercition échoue, on compare en chaîne
        }
        return value;
    }
}
