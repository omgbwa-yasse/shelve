package com.shelve.referentials.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.exception.ValidationException;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.referentials.entity.ReferenceList;
import com.shelve.referentials.repository.ReferenceListRepository;
import com.shelve.referentials.entity.ReferenceValue;
import com.shelve.referentials.repository.ReferenceValueRepository;
import com.shelve.referentials.dto.ReferenceValueView;
import jakarta.persistence.EntityManager;
import jakarta.servlet.http.HttpServletRequest;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.http.ResponseEntity;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@Transactional
@RestController
@RequestMapping(value = {"/api/v1/reference-lists"})
public class ReferenceListController {
  private static final List<String> FILTERABLE =
      List.of("id", "name", "code", "active", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("values", "creator", "updater");
  private final ReferenceListRepository listRepository;
  private final ReferenceValueRepository valueRepository;
  private final EntityManager em;

  public ReferenceListController(
      ReferenceListRepository listRepository,
      ReferenceValueRepository valueRepository,
      EntityManager em) {
    this.listRepository = listRepository;
    this.valueRepository = valueRepository;
    this.em = em;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reference_list_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    Specification<ReferenceList> spec = Filters.of(qp.getFilters(), ReferenceList.class);
    return Paging.page(this.listRepository, spec, qp, SORTABLE, "id", request, this::viewIndex);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reference_list_view");
    ReferenceList list =
        (ReferenceList) this.listRepository.findById(id).orElseThrow(() -> ApiException.notFound());
    List<ReferenceValue> values = this.valueRepository.findByListId(list.getId());
    return Json.of("data", this.viewShow(list, values));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reference_list_create");
    String name = ReferenceListController.str(body.get("name"));
    String code = ReferenceListController.str(body.get("code"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .unique(
                "name",
                name,
                name != null && this.listRepository.existsByName(name),
                "reference_lists",
                "name")
            .require("code", code, "The code field is required.")
            .unique(
                "code",
                code,
                code != null && this.listRepository.existsByCode(code),
                "reference_lists",
                "code");
    v.validate();
    ReferenceList list = new ReferenceList();
    list.setName(name);
    list.setCode(code);
    list.setDescription(ReferenceListController.str(body.get("description")));
    if (body.containsKey("active")) {
      list.setActive(ReferenceListController.bool(body.get("active")));
    }
    list.setCreatedBy(auth.user().getId());
    this.listRepository.save(list);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/reference-lists/" + list.getId()}))
        .body(Json.of("data", this.viewShow(list, List.of())));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reference_list_update");
    ReferenceList list =
        (ReferenceList) this.listRepository.findById(id).orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("name")) {
      list.setName(ReferenceListController.str(body.get("name")));
    }
    if (body.containsKey("code")) {
      list.setCode(ReferenceListController.str(body.get("code")));
    }
    if (body.containsKey("description")) {
      list.setDescription(ReferenceListController.str(body.get("description")));
    }
    if (body.containsKey("active")) {
      list.setActive(ReferenceListController.bool(body.get("active")));
    }
    list.setUpdatedBy(auth.user().getId());
    this.listRepository.save(list);
    return Json.of("data", this.viewShow(list, this.valueRepository.findByListId(list.getId())));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<?> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reference_list_delete");
    ReferenceList list =
        (ReferenceList) this.listRepository.findById(id).orElseThrow(() -> ApiException.notFound());
    long metadataCount =
        (Long)
            this.em
                .createQuery(
                    "select count(m) from com.shelve.referentials.entity.MetadataDefinition m where"
                        + " m.referenceListId = :id",
                    Long.class)
                .setParameter("id", (Object) id)
                .getSingleResult();
    if (metadataCount > 0L) {
      LinkedHashMap<String, Object> body = new LinkedHashMap<String, Object>();
      body.put("type", "about:blank");
      body.put("title", "Conflit d'int\u00e9grit\u00e9");
      body.put("status", 409);
      body.put(
          "detail",
          "Impossible de supprimer une liste de r\u00e9f\u00e9rence utilis\u00e9e par des"
              + " d\u00e9finitions de m\u00e9tadonn\u00e9es.");
      return ResponseEntity.status((int) 409).body(body);
    }
    this.listRepository.delete(list);
    return ResponseEntity.noContent().build();
  }

  @PostMapping(value = {"/{listId}/values"})
  public ResponseEntity<Map<String, Object>> addValue(
      @PathVariable Long listId, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reference_list_create");
    ReferenceList list =
        (ReferenceList)
            this.listRepository.findById(listId).orElseThrow(() -> ApiException.notFound());
    String code = ReferenceListController.str(body.get("code"));
    String value = ReferenceListController.str(body.get("value"));
    Validator v =
        Validator.begin()
            .require("value", value, "The value field is required.")
            .require("code", code, "The code field is required.");
    v.validate();
    boolean exists = this.valueRepository.existsByListIdAndCode(list.getId(), code);
    if (exists) {
      throw ValidationException.single("code", "Ce code existe d\u00e9j\u00e0 dans cette liste.");
    }
    ReferenceValue rv = new ReferenceValue();
    rv.setListId(list.getId());
    rv.setValue(value);
    rv.setCode(code);
    rv.setDescription(ReferenceListController.str(body.get("description")));
    if (body.containsKey("active")) {
      rv.setActive(ReferenceListController.bool(body.get("active")));
    }
    if (body.containsKey("sort_order")) {
      rv.setSortOrder(ReferenceListController.intOf(body.get("sort_order")));
    }
    rv.setCreatedBy(auth.user().getId());
    this.valueRepository.save(rv);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header(
                    "Location",
                    new String[] {
                      "/api/v1/reference-lists/" + list.getId() + "/values/" + rv.getId()
                    }))
        .body(Json.of("data", ReferenceListController.valueView(rv)));
  }

  @PatchMapping(value = {"/{listId}/values/{valueId}"})
  public Map<String, Object> updateValue(
      @PathVariable Long listId,
      @PathVariable Long valueId,
      @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reference_list_update");
    ReferenceValue rv =
        this.valueRepository
            .findByListIdAndId(listId, valueId)
            .orElseThrow(() -> ApiException.notFound());
    String newCode = ReferenceListController.str(body.get("code"));
    if (body.containsKey("code")) {
      boolean exists =
          this.valueRepository
              .findByListIdAndCode(listId, newCode)
              .filter(v -> !v.getId().equals(valueId))
              .isPresent();
      if (exists) {
        throw ValidationException.single("code", "Ce code existe d\u00e9j\u00e0 dans cette liste.");
      }
      rv.setCode(newCode);
    }
    if (body.containsKey("value")) {
      rv.setValue(ReferenceListController.str(body.get("value")));
    }
    if (body.containsKey("description")) {
      rv.setDescription(ReferenceListController.str(body.get("description")));
    }
    if (body.containsKey("active")) {
      rv.setActive(ReferenceListController.bool(body.get("active")));
    }
    if (body.containsKey("sort_order")) {
      rv.setSortOrder(ReferenceListController.intOf(body.get("sort_order")));
    }
    rv.setUpdatedBy(auth.user().getId());
    this.valueRepository.save(rv);
    return Json.of("data", ReferenceListController.valueView(rv));
  }

  @DeleteMapping(value = {"/{listId}/values/{valueId}"})
  public ResponseEntity<Void> deleteValue(@PathVariable Long listId, @PathVariable Long valueId) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reference_list_delete");
    ReferenceValue rv =
        this.valueRepository
            .findByListIdAndId(listId, valueId)
            .orElseThrow(() -> ApiException.notFound());
    this.valueRepository.delete(rv);
    return ResponseEntity.noContent().build();
  }

  private Map<String, Object> viewIndex(ReferenceList list) {
    long count = this.valueRepository.countByListId(list.getId());
    return this.viewMap(list, count, List.of(), false);
  }

  private Map<String, Object> viewShow(ReferenceList list, List<ReferenceValue> values) {
    return this.viewMap(
        list,
        values.size(),
        values.stream().map(ReferenceListController::valueView).toList(),
        true);
  }

  private Map<String, Object> viewMap(
      ReferenceList list, long count, List<ReferenceValueView> values, boolean withValues) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", list.getId());
    map.put("name", list.getName());
    map.put("code", list.getCode());
    map.put("description", list.getDescription());
    map.put("active", list.getActive() != null && list.getActive() != false);
    map.put("created_by", list.getCreatedBy());
    map.put("updated_by", list.getUpdatedBy());
    map.put("values_count", count);
    if (withValues) {
      map.put("values", values);
    }
    map.put("created_at", Json.timestamp(list.getCreatedAt()));
    map.put("updated_at", Json.timestamp(list.getUpdatedAt()));
    return map;
  }

  static ReferenceValueView valueView(ReferenceValue rv) {
    return new ReferenceValueView(
        rv.getId(),
        rv.getListId(),
        rv.getValue(),
        rv.getCode(),
        rv.getDescription(),
        rv.getActive(),
        rv.getSortOrder(),
        rv.getCreatedAt(),
        rv.getUpdatedAt());
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  private static boolean bool(Object value) {
    if (value == null) {
      return false;
    }
    if (value instanceof Boolean) {
      Boolean b = (Boolean) value;
      return b;
    }
    return Boolean.parseBoolean(String.valueOf(value));
  }

  private static int intOf(Object value) {
    if (value instanceof Number) {
      Number n = (Number) value;
      return n.intValue();
    }
    return Integer.parseInt(String.valueOf(value));
  }
}
