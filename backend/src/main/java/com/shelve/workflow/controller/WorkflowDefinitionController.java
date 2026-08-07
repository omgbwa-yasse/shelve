package com.shelve.workflow.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
import com.shelve.workflow.entity.WorkflowDefinition;
import com.shelve.workflow.repository.WorkflowDefinitionRepository;
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
@RequestMapping(value = {"/api/v1/workflow-definitions"})
public class WorkflowDefinitionController extends GenericCrudController<WorkflowDefinition> {
  private final WorkflowDefinitionRepository repo;

  public WorkflowDefinitionController(WorkflowDefinitionRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<WorkflowDefinition> entityClass() {
    return WorkflowDefinition.class;
  }

  @Override
  protected JpaSpecificationExecutor<WorkflowDefinition> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "workflow_definition";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "organisation_id",
        "name",
        "description",
        "bpmn_xml",
        "version",
        "status",
        "visibility",
        "created_by",
        "updated_by");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected WorkflowDefinition newEntity() {
    return new WorkflowDefinition();
  }

  @Override
  protected String location(WorkflowDefinition e) {
    return "/api/v1/workflow-definitions/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (WorkflowDefinitionController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, WorkflowDefinition e) {
    AuthenticatedUser auth = CurrentUser.get();
    e.setOrganisationId(auth.user().getCurrentOrganisationId());
    e.setCreatedBy(auth.user().getId());
    e.setVersion(1);
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(WorkflowDefinitionController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("name")) {
      e.setName(WorkflowDefinitionController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(WorkflowDefinitionController.str(body.get("description")));
    }
    if (body.containsKey("bpmn_xml")) {
      e.setBpmnXml(WorkflowDefinitionController.str(body.get("bpmn_xml")));
    }
    if (body.containsKey("version")) {
      e.setVersion(WorkflowDefinitionController.intOf(body.get("version")));
    }
    if (body.containsKey("status")) {
      e.setStatus(WorkflowDefinitionController.str(body.get("status")));
    }
    if (body.containsKey("visibility")) {
      e.setVisibility(WorkflowDefinitionController.str(body.get("visibility")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(WorkflowDefinitionController.longOf(body.get("created_by")));
    }
    if (body.containsKey("updated_by")) {
      e.setUpdatedBy(WorkflowDefinitionController.longOf(body.get("updated_by")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, WorkflowDefinition e) {
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(WorkflowDefinitionController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("name")) {
      e.setName(WorkflowDefinitionController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(WorkflowDefinitionController.str(body.get("description")));
    }
    if (body.containsKey("bpmn_xml")) {
      e.setBpmnXml(WorkflowDefinitionController.str(body.get("bpmn_xml")));
    }
    if (body.containsKey("version")) {
      e.setVersion(WorkflowDefinitionController.intOf(body.get("version")));
    }
    if (body.containsKey("status")) {
      e.setStatus(WorkflowDefinitionController.str(body.get("status")));
    }
    if (body.containsKey("visibility")) {
      e.setVisibility(WorkflowDefinitionController.str(body.get("visibility")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(WorkflowDefinitionController.longOf(body.get("created_by")));
    }
    if (body.containsKey("updated_by")) {
      e.setUpdatedBy(WorkflowDefinitionController.longOf(body.get("updated_by")));
    }
  }

  @Override
  protected Map<String, Object> mapper(WorkflowDefinition e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("organisation_id", e.getOrganisationId());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("bpmn_xml", e.getBpmnXml());
    map.put("version", e.getVersion());
    map.put("status", e.getStatus());
    map.put("visibility", e.getVisibility());
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
