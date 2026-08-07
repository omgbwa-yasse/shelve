package com.shelve.projects.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
import com.shelve.projects.entity.Project;
import com.shelve.projects.repository.ProjectRepository;
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
@RequestMapping(value = {"/api/v1/projects"})
public class ProjectController extends GenericCrudController<Project> {
  private final ProjectRepository repo;

  public ProjectController(ProjectRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<Project> entityClass() {
    return Project.class;
  }

  @Override
  protected JpaSpecificationExecutor<Project> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "project";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "code",
        "name",
        "description",
        "status",
        "start_date",
        "end_date",
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
  protected Project newEntity() {
    return new Project();
  }

  @Override
  protected String location(Project e) {
    return "/api/v1/projects/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (ProjectController.str(body.get("code")) == null) {
      v.add("code", "The code field is required.");
    }
    if (ProjectController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Project e) {
    AuthenticatedUser auth = CurrentUser.get();
    e.setOrganisationId(auth.user().getCurrentOrganisationId());
    e.setCreatedBy(auth.user().getId());
    e.setAttachableType(ProjectController.str(body.get("attachable_type")));
    e.setAttachableId(ProjectController.longOf(body.get("attachable_id")));
    if (body.containsKey("code")) {
      e.setCode(ProjectController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(ProjectController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(ProjectController.str(body.get("description")));
    }
    if (body.containsKey("status")) {
      e.setStatus(ProjectController.str(body.get("status")));
    }
    if (body.containsKey("start_date")) {
      e.setStartDate(ProjectController.dateOf(body.get("start_date")));
    }
    if (body.containsKey("end_date")) {
      e.setEndDate(ProjectController.dateOf(body.get("end_date")));
    }
    if (body.containsKey("owner_id")) {
      e.setOwnerId(ProjectController.longOf(body.get("owner_id")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(ProjectController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(ProjectController.longOf(body.get("created_by")));
    }
    if (body.containsKey("updated_by")) {
      e.setUpdatedBy(ProjectController.longOf(body.get("updated_by")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Project e) {
    if (body.containsKey("code")) {
      e.setCode(ProjectController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(ProjectController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(ProjectController.str(body.get("description")));
    }
    if (body.containsKey("status")) {
      e.setStatus(ProjectController.str(body.get("status")));
    }
    if (body.containsKey("start_date")) {
      e.setStartDate(ProjectController.dateOf(body.get("start_date")));
    }
    if (body.containsKey("end_date")) {
      e.setEndDate(ProjectController.dateOf(body.get("end_date")));
    }
    if (body.containsKey("owner_id")) {
      e.setOwnerId(ProjectController.longOf(body.get("owner_id")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(ProjectController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(ProjectController.longOf(body.get("created_by")));
    }
    if (body.containsKey("updated_by")) {
      e.setUpdatedBy(ProjectController.longOf(body.get("updated_by")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Project e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("code", e.getCode());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("status", e.getStatus());
    map.put("start_date", e.getStartDate() != null ? e.getStartDate().toString() : null);
    map.put("end_date", e.getEndDate() != null ? e.getEndDate().toString() : null);
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
