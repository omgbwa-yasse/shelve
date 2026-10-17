package com.shelve.settings.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.settings.entity.SettingCategory;
import com.shelve.settings.repository.SettingCategoryRepository;
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
@RequestMapping(value = {"/api/v1/setting-categories"})
public class SettingCategoryController extends GenericCrudController<SettingCategory> {
  private final SettingCategoryRepository repo;

  public SettingCategoryController(SettingCategoryRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<SettingCategory> entityClass() {
    return SettingCategory.class;
  }

  @Override
  protected JpaSpecificationExecutor<SettingCategory> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "setting_category";
  }

  @Override
  protected List<String> filterable() {
    return List.of("name", "description", "parent_id");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected SettingCategory newEntity() {
    return new SettingCategory();
  }

  @Override
  protected String location(SettingCategory e) {
    return "/api/v1/setting-categories/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (SettingCategoryController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, SettingCategory e) {
    if (body.containsKey("name")) {
      e.setName(SettingCategoryController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(SettingCategoryController.str(body.get("description")));
    }
    if (body.containsKey("parent_id")) {
      e.setParentId(SettingCategoryController.longOf(body.get("parent_id")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, SettingCategory e) {
    if (body.containsKey("name")) {
      e.setName(SettingCategoryController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(SettingCategoryController.str(body.get("description")));
    }
    if (body.containsKey("parent_id")) {
      e.setParentId(SettingCategoryController.longOf(body.get("parent_id")));
    }
  }

  @Override
  protected Map<String, Object> mapper(SettingCategory e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("parent_id", e.getParentId());
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
