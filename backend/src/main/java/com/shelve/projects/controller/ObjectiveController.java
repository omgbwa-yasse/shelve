package com.shelve.projects.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
import com.shelve.projects.entity.Objective;
import com.shelve.projects.repository.ObjectiveRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping(value = {"/api/v1/objectives"})
public class ObjectiveController extends GenericCrudController<Objective> {
  private final ObjectiveRepository repo;

  public ObjectiveController(ObjectiveRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<Objective> entityClass() {
    return Objective.class;
  }

  @Override
  protected JpaSpecificationExecutor<Objective> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "objective";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "project_id",
        "task_id",
        "title",
        "description",
        "period_start",
        "period_end",
        "status",
        "owner_id",
        "organisation_id",
        "created_by",
        "updated_by");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected Objective newEntity() {
    return new Objective();
  }

  @Override
  protected String location(Objective e) {
    return "/api/v1/objectives/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (ObjectiveController.str(body.get("title")) == null) {
      v.add("title", "The title field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Objective e) {
    AuthenticatedUser auth = CurrentUser.get();
    e.setOrganisationId(auth.user().getCurrentOrganisationId());
    e.setCreatedBy(auth.user().getId());
    e.setAttachableType(ObjectiveController.str(body.get("attachable_type")));
    e.setAttachableId(ObjectiveController.longOf(body.get("attachable_id")));
    if (body.containsKey("project_id")) {
      e.setProjectId(ObjectiveController.longOf(body.get("project_id")));
    }
    if (body.containsKey("task_id")) {
      e.setTaskId(ObjectiveController.longOf(body.get("task_id")));
    }
    if (body.containsKey("title")) {
      e.setTitle(ObjectiveController.str(body.get("title")));
    }
    if (body.containsKey("description")) {
      e.setDescription(ObjectiveController.str(body.get("description")));
    }
    if (body.containsKey("period_start")) {
      e.setPeriodStart(ObjectiveController.dateOf(body.get("period_start")));
    }
    if (body.containsKey("period_end")) {
      e.setPeriodEnd(ObjectiveController.dateOf(body.get("period_end")));
    }
    if (body.containsKey("status")) {
      e.setStatus(ObjectiveController.str(body.get("status")));
    }
    if (body.containsKey("owner_id")) {
      e.setOwnerId(ObjectiveController.longOf(body.get("owner_id")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(ObjectiveController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(ObjectiveController.longOf(body.get("created_by")));
    }
    if (body.containsKey("updated_by")) {
      e.setUpdatedBy(ObjectiveController.longOf(body.get("updated_by")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Objective e) {
    if (body.containsKey("project_id")) {
      e.setProjectId(ObjectiveController.longOf(body.get("project_id")));
    }
    if (body.containsKey("task_id")) {
      e.setTaskId(ObjectiveController.longOf(body.get("task_id")));
    }
    if (body.containsKey("title")) {
      e.setTitle(ObjectiveController.str(body.get("title")));
    }
    if (body.containsKey("description")) {
      e.setDescription(ObjectiveController.str(body.get("description")));
    }
    if (body.containsKey("period_start")) {
      e.setPeriodStart(ObjectiveController.dateOf(body.get("period_start")));
    }
    if (body.containsKey("period_end")) {
      e.setPeriodEnd(ObjectiveController.dateOf(body.get("period_end")));
    }
    if (body.containsKey("status")) {
      e.setStatus(ObjectiveController.str(body.get("status")));
    }
    if (body.containsKey("owner_id")) {
      e.setOwnerId(ObjectiveController.longOf(body.get("owner_id")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(ObjectiveController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(ObjectiveController.longOf(body.get("created_by")));
    }
    if (body.containsKey("updated_by")) {
      e.setUpdatedBy(ObjectiveController.longOf(body.get("updated_by")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Objective e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("project_id", e.getProjectId());
    map.put("task_id", e.getTaskId());
    map.put("title", e.getTitle());
    map.put("description", e.getDescription());
    map.put("period_start", e.getPeriodStart() != null ? e.getPeriodStart().toString() : null);
    map.put("period_end", e.getPeriodEnd() != null ? e.getPeriodEnd().toString() : null);
    map.put("status", e.getStatus());
    map.put("owner_id", e.getOwnerId());
    map.put("organisation_id", e.getOrganisationId());
    map.put("created_by", e.getCreatedBy());
    map.put("updated_by", e.getUpdatedBy());
    map.put("created_at", Json.timestamp(e.getCreatedAt()));
    map.put("updated_at", Json.timestamp(e.getUpdatedAt()));
    return map;
  }

  @Override
  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    return super.index(request);
  }

  @Override
  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    return super.show(id);
  }

  @Override
  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    return super.store(body);
  }

  @Override
  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    return super.update(id, body);
  }

  @Override
  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    return super.destroy(id);
  }
}
