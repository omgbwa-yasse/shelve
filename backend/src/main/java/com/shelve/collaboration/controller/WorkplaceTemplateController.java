package com.shelve.collaboration.controller;

import com.shelve.collaboration.entity.WorkplaceTemplate;
import com.shelve.collaboration.repository.WorkplaceTemplateRepository;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
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
@RequestMapping(value = {"/api/v1/workplace-templates"})
public class WorkplaceTemplateController extends GenericCrudController<WorkplaceTemplate> {
  private final WorkplaceTemplateRepository repo;

  public WorkplaceTemplateController(WorkplaceTemplateRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<WorkplaceTemplate> entityClass() {
    return WorkplaceTemplate.class;
  }

  @Override
  protected JpaSpecificationExecutor<WorkplaceTemplate> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "workplace_template";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "code",
        "name",
        "description",
        "icon",
        "category",
        "default_settings",
        "default_structure",
        "default_permissions",
        "is_active",
        "is_system",
        "usage_count",
        "display_order",
        "created_by");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected WorkplaceTemplate newEntity() {
    return new WorkplaceTemplate();
  }

  @Override
  protected String location(WorkplaceTemplate e) {
    return "/api/v1/workplace-templates/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (WorkplaceTemplateController.str(body.get("code")) == null) {
      v.add("code", "The code field is required.");
    }
    if (WorkplaceTemplateController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, WorkplaceTemplate e) {
    if (body.containsKey("code")) {
      e.setCode(WorkplaceTemplateController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(WorkplaceTemplateController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(WorkplaceTemplateController.str(body.get("description")));
    }
    if (body.containsKey("icon")) {
      e.setIcon(WorkplaceTemplateController.str(body.get("icon")));
    }
    if (body.containsKey("category")) {
      e.setCategory(WorkplaceTemplateController.str(body.get("category")));
    }
    if (body.containsKey("default_settings")) {
      e.setDefaultSettings(WorkplaceTemplateController.str(body.get("default_settings")));
    }
    if (body.containsKey("default_structure")) {
      e.setDefaultStructure(WorkplaceTemplateController.str(body.get("default_structure")));
    }
    if (body.containsKey("default_permissions")) {
      e.setDefaultPermissions(WorkplaceTemplateController.str(body.get("default_permissions")));
    }
    if (body.containsKey("is_active")) {
      e.setIsActive(WorkplaceTemplateController.bool(body.get("is_active")));
    }
    if (body.containsKey("is_system")) {
      e.setIsSystem(WorkplaceTemplateController.bool(body.get("is_system")));
    }
    if (body.containsKey("usage_count")) {
      e.setUsageCount(WorkplaceTemplateController.intOf(body.get("usage_count")));
    }
    if (body.containsKey("display_order")) {
      e.setDisplayOrder(WorkplaceTemplateController.intOf(body.get("display_order")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(WorkplaceTemplateController.longOf(body.get("created_by")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, WorkplaceTemplate e) {
    if (body.containsKey("code")) {
      e.setCode(WorkplaceTemplateController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(WorkplaceTemplateController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(WorkplaceTemplateController.str(body.get("description")));
    }
    if (body.containsKey("icon")) {
      e.setIcon(WorkplaceTemplateController.str(body.get("icon")));
    }
    if (body.containsKey("category")) {
      e.setCategory(WorkplaceTemplateController.str(body.get("category")));
    }
    if (body.containsKey("default_settings")) {
      e.setDefaultSettings(WorkplaceTemplateController.str(body.get("default_settings")));
    }
    if (body.containsKey("default_structure")) {
      e.setDefaultStructure(WorkplaceTemplateController.str(body.get("default_structure")));
    }
    if (body.containsKey("default_permissions")) {
      e.setDefaultPermissions(WorkplaceTemplateController.str(body.get("default_permissions")));
    }
    if (body.containsKey("is_active")) {
      e.setIsActive(WorkplaceTemplateController.bool(body.get("is_active")));
    }
    if (body.containsKey("is_system")) {
      e.setIsSystem(WorkplaceTemplateController.bool(body.get("is_system")));
    }
    if (body.containsKey("usage_count")) {
      e.setUsageCount(WorkplaceTemplateController.intOf(body.get("usage_count")));
    }
    if (body.containsKey("display_order")) {
      e.setDisplayOrder(WorkplaceTemplateController.intOf(body.get("display_order")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(WorkplaceTemplateController.longOf(body.get("created_by")));
    }
  }

  @Override
  protected Map<String, Object> mapper(WorkplaceTemplate e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("code", e.getCode());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("icon", e.getIcon());
    map.put("category", e.getCategory());
    map.put("default_settings", e.getDefaultSettings());
    map.put("default_structure", e.getDefaultStructure());
    map.put("default_permissions", e.getDefaultPermissions());
    map.put("is_active", e.getIsActive() != null && e.getIsActive() != false);
    map.put("is_system", e.getIsSystem() != null && e.getIsSystem() != false);
    map.put("usage_count", e.getUsageCount());
    map.put("display_order", e.getDisplayOrder());
    map.put("created_by", e.getCreatedBy());
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
