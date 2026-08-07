package com.shelve.workflow.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.workflow.entity.WorkflowInstance;
import com.shelve.workflow.repository.WorkflowInstanceRepository;
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
@RequestMapping(value = {"/api/v1/workflow-instances"})
public class WorkflowInstanceController extends GenericCrudController<WorkflowInstance> {
  private final WorkflowInstanceRepository repo;

  public WorkflowInstanceController(WorkflowInstanceRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<WorkflowInstance> entityClass() {
    return WorkflowInstance.class;
  }

  @Override
  protected JpaSpecificationExecutor<WorkflowInstance> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "workflow_instance";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "organisation_id",
        "definition_id",
        "name",
        "status",
        "current_state",
        "started_by",
        "started_at",
        "updated_by",
        "completed_by",
        "completed_at");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected WorkflowInstance newEntity() {
    return new WorkflowInstance();
  }

  @Override
  protected String location(WorkflowInstance e) {
    return "/api/v1/workflow-instances/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (WorkflowInstanceController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    if (WorkflowInstanceController.str(body.get("definition_id")) == null) {
      v.add("definition_id", "The definition id field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, WorkflowInstance e) {
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(WorkflowInstanceController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("definition_id")) {
      e.setDefinitionId(WorkflowInstanceController.longOf(body.get("definition_id")));
    }
    if (body.containsKey("name")) {
      e.setName(WorkflowInstanceController.str(body.get("name")));
    }
    if (body.containsKey("status")) {
      e.setStatus(WorkflowInstanceController.str(body.get("status")));
    }
    if (body.containsKey("current_state")) {
      e.setCurrentState(WorkflowInstanceController.str(body.get("current_state")));
    }
    if (body.containsKey("started_by")) {
      e.setStartedBy(WorkflowInstanceController.longOf(body.get("started_by")));
    }
    if (body.containsKey("started_at")) {
      e.setStartedAt(WorkflowInstanceController.instantOf(body.get("started_at")));
    }
    if (body.containsKey("updated_by")) {
      e.setUpdatedBy(WorkflowInstanceController.longOf(body.get("updated_by")));
    }
    if (body.containsKey("completed_by")) {
      e.setCompletedBy(WorkflowInstanceController.longOf(body.get("completed_by")));
    }
    if (body.containsKey("completed_at")) {
      e.setCompletedAt(WorkflowInstanceController.instantOf(body.get("completed_at")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, WorkflowInstance e) {
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(WorkflowInstanceController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("definition_id")) {
      e.setDefinitionId(WorkflowInstanceController.longOf(body.get("definition_id")));
    }
    if (body.containsKey("name")) {
      e.setName(WorkflowInstanceController.str(body.get("name")));
    }
    if (body.containsKey("status")) {
      e.setStatus(WorkflowInstanceController.str(body.get("status")));
    }
    if (body.containsKey("current_state")) {
      e.setCurrentState(WorkflowInstanceController.str(body.get("current_state")));
    }
    if (body.containsKey("started_by")) {
      e.setStartedBy(WorkflowInstanceController.longOf(body.get("started_by")));
    }
    if (body.containsKey("started_at")) {
      e.setStartedAt(WorkflowInstanceController.instantOf(body.get("started_at")));
    }
    if (body.containsKey("updated_by")) {
      e.setUpdatedBy(WorkflowInstanceController.longOf(body.get("updated_by")));
    }
    if (body.containsKey("completed_by")) {
      e.setCompletedBy(WorkflowInstanceController.longOf(body.get("completed_by")));
    }
    if (body.containsKey("completed_at")) {
      e.setCompletedAt(WorkflowInstanceController.instantOf(body.get("completed_at")));
    }
  }

  @Override
  protected Map<String, Object> mapper(WorkflowInstance e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("organisation_id", e.getOrganisationId());
    map.put("definition_id", e.getDefinitionId());
    map.put("name", e.getName());
    map.put("status", e.getStatus());
    map.put("current_state", e.getCurrentState());
    map.put("started_by", e.getStartedBy());
    map.put("started_at", Json.timestamp(e.getStartedAt()));
    map.put("updated_by", e.getUpdatedBy());
    map.put("completed_by", e.getCompletedBy());
    map.put("completed_at", Json.timestamp(e.getCompletedAt()));
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
