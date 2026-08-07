package com.shelve.referentials.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.referentials.entity.Activity;
import com.shelve.referentials.repository.ActivityRepository;
import com.shelve.referentials.repository.CommunicabilityRepository;
import com.shelve.referentials.dto.ActivityView;
import jakarta.persistence.EntityManager;
import jakarta.servlet.http.HttpServletRequest;
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
@RequestMapping(value = {"/api/v1/activities"})
public class ActivityController {
  private static final List<String> FILTERABLE =
      List.of("id", "code", "name", "parent_id", "communicability_id", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of();
  private final ActivityRepository repository;
  private final CommunicabilityRepository communicabilityRepository;
  private final EntityManager em;

  public ActivityController(
      ActivityRepository repository,
      CommunicabilityRepository communicabilityRepository,
      EntityManager em) {
    this.repository = repository;
    this.communicabilityRepository = communicabilityRepository;
    this.em = em;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "activity_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    Specification<Activity> spec = Filters.of(qp.getFilters(), Activity.class);
    return Paging.page(
        this.repository, spec, qp, SORTABLE, "id", request, ActivityController::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "activity_view");
    Activity activity =
        (Activity) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    return Json.of("data", ActivityController.view(activity));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "activity_create");
    String code = ActivityController.str(body.get("code"));
    String name = ActivityController.str(body.get("name"));
    String observation = ActivityController.str(body.get("observation"));
    Validator v =
        Validator.begin()
            .require("code", code, "The code field is required.")
            .max("code", code, 10, "code")
            .unique("code", code, code != null && this.codeExists(code), "activities", "code")
            .require("name", name, "The name field is required.")
            .max("name", name, 100, "name");
    if (body.get("parent_id") != null && !ActivityController.str(body.get("parent_id")).isBlank()) {
      Long parentId = ActivityController.parseId(body.get("parent_id"));
      v.exists(
          "parent_id",
          parentId,
          parentId != null && this.repository.existsById(parentId),
          "activities",
          "parent id");
    }
    if (body.get("communicability_id") != null
        && !ActivityController.str(body.get("communicability_id")).isBlank()) {
      Long cid = ActivityController.parseId(body.get("communicability_id"));
      v.exists(
          "communicability_id",
          cid,
          cid != null && this.communicabilityExists(cid),
          "communicabilities",
          "communicability id");
    }
    v.validate();
    Activity activity = new Activity();
    activity.setCode(code);
    activity.setName(name);
    activity.setObservation(observation);
    if (body.get("parent_id") != null && !ActivityController.str(body.get("parent_id")).isBlank()) {
      activity.setParentId(ActivityController.parseId(body.get("parent_id")));
    }
    if (body.get("communicability_id") != null
        && !ActivityController.str(body.get("communicability_id")).isBlank()) {
      activity.setCommunicabilityId(ActivityController.parseId(body.get("communicability_id")));
    }
    this.repository.save(activity);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/activities/" + activity.getId()}))
        .body(Json.of("data", ActivityController.view(activity)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "activity_update");
    Activity activity =
        (Activity) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("code")) {
      activity.setCode(ActivityController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      activity.setName(ActivityController.str(body.get("name")));
    }
    if (body.containsKey("observation")) {
      activity.setObservation(ActivityController.str(body.get("observation")));
    }
    if (body.containsKey("parent_id")) {
      Object pid = body.get("parent_id");
      activity.setParentId(pid == null ? null : ActivityController.parseId(pid));
    }
    if (body.containsKey("communicability_id")) {
      Object cid = body.get("communicability_id");
      activity.setCommunicabilityId(cid == null ? null : ActivityController.parseId(cid));
    }
    this.repository.save(activity);
    return Json.of("data", ActivityController.view(activity));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "activity_delete");
    Activity activity =
        (Activity) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    this.repository.delete(activity);
    return ResponseEntity.noContent().build();
  }

  private boolean codeExists(String code) {
    return (Long)
            this.em
                .createQuery("select count(a) from Activity a where a.code = :code", Long.class)
                .setParameter("code", (Object) code)
                .getSingleResult()
        > 0L;
  }

  private boolean communicabilityExists(Long id) {
    return this.communicabilityRepository.existsById(id);
  }

  static ActivityView view(Activity a) {
    return new ActivityView(
        a.getId(),
        a.getCode(),
        a.getName(),
        a.getObservation(),
        a.getParentId(),
        a.getCommunicabilityId(),
        a.getCreatedAt(),
        a.getUpdatedAt());
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  private static Long parseId(Object value) {
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
}
