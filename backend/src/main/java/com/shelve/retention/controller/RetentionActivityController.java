package com.shelve.retention.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.exception.ValidationException;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.QueryParams;
import com.shelve.retention.entity.RetentionActivity;
import com.shelve.retention.entity.RetentionActivityId;
import com.shelve.retention.repository.RetentionActivityRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping(value = {"/api/v1/retention-activities"})
public class RetentionActivityController {
  private static final List<String> FILTERABLE = List.of("retention_id", "activity_id");
  private final RetentionActivityRepository repository;

  public RetentionActivityController(RetentionActivityRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "retention_activity_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, FILTERABLE, List.of());
    Specification<RetentionActivity> spec = Filters.of(qp.getFilters(), RetentionActivity.class);
    List<Map<String, Object>> items = this.repository.findAll(spec).stream().map(this::mapper).toList();
    return Json.of("data", items);
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "retention_activity_create");
    Long retentionId = RetentionActivityController.longOf(body.get("retention_id"));
    Long activityId = RetentionActivityController.longOf(body.get("activity_id"));
    if (retentionId == null || activityId == null) {
      throw new ValidationException(
          Map.of(
              "retention_id",
              List.of("The retention id field is required."),
              "activity_id",
              List.of("The activity id field is required.")));
    }
    RetentionActivityId id = new RetentionActivityId(retentionId, activityId);
    boolean created = !this.repository.existsById(id);
    boolean bl = created;
    if (created) {
      RetentionActivity pivot = new RetentionActivity();
      pivot.setId(id);
      this.repository.save(pivot);
    }
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) (created ? 201 : 200))
                .header(
                    "Location",
                    new String[] {
                      "/api/v1/retention-activities/" + retentionId + "/" + activityId
                    }))
        .body(Json.of("data", this.mapper(this.resolve(retentionId, activityId))));
  }

  @DeleteMapping(value = {"/{retention}/{activity}"})
  public ResponseEntity<Void> destroy(@PathVariable Long retention, @PathVariable Long activity) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "retention_activity_delete");
    this.resolve(retention, activity);
    this.repository.deleteById(new RetentionActivityId(retention, activity));
    return ResponseEntity.noContent().build();
  }

  private RetentionActivity resolve(Long retentionId, Long activityId) {
    return (RetentionActivity)
        this.repository
            .findById(new RetentionActivityId(retentionId, activityId))
            .orElseThrow(() -> ApiException.notFound());
  }

  private Map<String, Object> mapper(RetentionActivity p) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("retention_id", p.getRetentionId());
    map.put("activity_id", p.getActivityId());
    return map;
  }

  private static Long longOf(Object value) {
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
}
