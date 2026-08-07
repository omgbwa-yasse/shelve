package com.shelve.common;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.time.Instant;
import java.time.LocalDate;
import java.time.format.DateTimeParseException;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import org.springframework.http.ResponseEntity;

public abstract class GenericCrudController<T> {
  protected abstract Class<T> entityClass();

  protected abstract JpaSpecificationExecutor<T> repository();

  protected abstract String resource();

  protected List<String> filterable() {
    return List.of("id");
  }

  protected List<String> sortable() {
    return List.of("id");
  }

  protected List<String> includable() {
    return List.of();
  }

  protected String defaultSort() {
    return "id";
  }

  protected abstract Map<String, Object> mapper(T var1);

  protected abstract T newEntity();

  protected abstract String location(T var1);

  protected Specification<T> scope(AuthenticatedUser auth) {
    return (root, query, cb) -> cb.conjunction();
  }

  protected void applyCreate(Map<String, Object> body, T entity) {}

  protected void applyUpdate(Map<String, Object> body, T entity) {}

  protected void validateCreate(Map<String, Object> body) {}

  protected void validateUpdate(Map<String, Object> body, T entity) {}

  protected void beforePersist(T entity) {}

  protected void beforeDelete(T entity) {}

  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, this.resource() + "_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(this.filterable(), this.sortable(), this.includable());
    Specification<T> spec = this.scope(auth).and(Filters.of(qp.getFilters(), this.entityClass()));
    return Paging.page(
        this.repository(), spec, qp, this.sortable(), this.defaultSort(), request, this::mapper);
  }

  public Map<String, Object> show(Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, this.resource() + "_view");
    T entity = this.findInScope(id, auth);
    return Json.of("data", this.mapper(entity));
  }

  public ResponseEntity<Map<String, Object>> store(Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, this.resource() + "_create");
    this.validateCreate(body);
    T entity = this.newEntity();
    this.applyCreate(body, entity);
    this.beforePersist(entity);
    this.persist(entity);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {this.location(entity)}))
        .body(Json.of("data", this.mapper(entity)));
  }

  public Map<String, Object> update(Long id, Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, this.resource() + "_update");
    T entity = this.findInScope(id, auth);
    this.validateUpdate(body, entity);
    this.applyUpdate(body, entity);
    this.beforePersist(entity);
    this.persist(entity);
    return Json.of("data", this.mapper(entity));
  }

  public ResponseEntity<Void> destroy(Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, this.resource() + "_delete");
    T entity = this.findInScope(id, auth);
    this.beforeDelete(entity);
    ((JpaRepository) this.repository()).delete(entity);
    return ResponseEntity.noContent().build();
  }

  protected T findInScope(Long id, AuthenticatedUser auth) {
    Specification<T> spec =
        scope(auth).and((root, q, cb) -> cb.equal(root.get("id"), id));
    return repository()
        .findAll(spec).stream()
        .findFirst()
        .orElseThrow(() -> ApiException.notFound());
  }

  protected void persist(T entity) {
    ((JpaRepository) this.repository()).save(entity);
  }

  protected static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  protected static Boolean bool(Object value) {
    if (value == null) {
      return null;
    }
    if (value instanceof Boolean) {
      Boolean b = (Boolean) value;
      return b;
    }
    return Boolean.parseBoolean(String.valueOf(value));
  }

  protected static Long longOf(Object value) {
    if (value == null) {
      return null;
    }
    try {
      return ((Number) value).longValue();
    } catch (ClassCastException e) {
      try {
        return Long.parseLong(String.valueOf(value));
      } catch (NumberFormatException e2) {
        return null;
      }
    }
  }

  protected static Integer intOf(Object value) {
    if (value == null) {
      return null;
    }
    try {
      return ((Number) value).intValue();
    } catch (ClassCastException e) {
      try {
        return Integer.parseInt(String.valueOf(value));
      } catch (NumberFormatException e2) {
        return null;
      }
    }
  }

  protected static Double doubleOf(Object value) {
    if (value == null) {
      return null;
    }
    if (value instanceof Number) {
      Number n = (Number) value;
      return n.doubleValue();
    }
    return Double.parseDouble(String.valueOf(value));
  }

  protected static Instant instantOf(Object value) {
    if (value == null) {
      return null;
    }
    try {
      return Instant.parse(String.valueOf(value));
    } catch (DateTimeParseException e) {
      return null;
    }
  }

  protected static LocalDate dateOf(Object value) {
    if (value == null) {
      return null;
    }
    try {
      return LocalDate.parse(String.valueOf(value));
    } catch (DateTimeParseException e) {
      return null;
    }
  }
}
