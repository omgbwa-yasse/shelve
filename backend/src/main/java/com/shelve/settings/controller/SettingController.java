package com.shelve.settings.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.settings.entity.Setting;
import com.shelve.settings.repository.SettingRepository;
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
@RequestMapping(value = {"/api/v1/settings"})
public class SettingController extends GenericCrudController<Setting> {
  private final SettingRepository repo;

  public SettingController(SettingRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<Setting> entityClass() {
    return Setting.class;
  }

  @Override
  protected JpaSpecificationExecutor<Setting> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "setting";
  }

  @Override
  protected List<String> filterable() {
    return List.of("name", "category_id", "type", "user_id", "organisation_id", "is_system");
  }

  @Override
  protected List<String> sortable() {
    return List.of("id", "name", "category_id", "type", "created_at", "updated_at");
  }

  @Override
  protected Setting newEntity() {
    return new Setting();
  }

  @Override
  protected String location(Setting e) {
    return "/api/v1/settings/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (SettingController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    if (SettingController.longOf(body.get("category_id")) == null) {
      v.add("category_id", "The category id field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Setting e) {
    if (body.containsKey("category_id")) {
      e.setCategoryId(SettingController.longOf(body.get("category_id")));
    }
    if (body.containsKey("name")) {
      e.setName(SettingController.str(body.get("name")));
    }
    if (body.containsKey("type")) {
      e.setType(SettingController.str(body.get("type")));
    }
    if (body.containsKey("default_value")) {
      e.setDefaultValue(SettingController.toJson(body.get("default_value")));
    }
    if (body.containsKey("description")) {
      e.setDescription(SettingController.str(body.get("description")));
    }
    if (body.containsKey("is_system")) {
      e.setIsSystem(SettingController.bool(body.get("is_system")));
    }
    if (body.containsKey("constraints")) {
      e.setConstraints(SettingController.toJson(body.get("constraints")));
    }
    if (body.containsKey("user_id")) {
      e.setUserId(SettingController.longOf(body.get("user_id")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(SettingController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("value")) {
      e.setValue(SettingController.toJson(body.get("value")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Setting e) {
    if (body.containsKey("category_id")) {
      e.setCategoryId(SettingController.longOf(body.get("category_id")));
    }
    if (body.containsKey("name")) {
      e.setName(SettingController.str(body.get("name")));
    }
    if (body.containsKey("type")) {
      e.setType(SettingController.str(body.get("type")));
    }
    if (body.containsKey("default_value")) {
      e.setDefaultValue(SettingController.toJson(body.get("default_value")));
    }
    if (body.containsKey("description")) {
      e.setDescription(SettingController.str(body.get("description")));
    }
    if (body.containsKey("is_system")) {
      e.setIsSystem(SettingController.bool(body.get("is_system")));
    }
    if (body.containsKey("constraints")) {
      e.setConstraints(SettingController.toJson(body.get("constraints")));
    }
    if (body.containsKey("value")) {
      e.setValue(SettingController.toJson(body.get("value")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Setting e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("category_id", e.getCategoryId());
    map.put("name", e.getName());
    map.put("type", e.getType());
    map.put("default_value", SettingController.parseJson(e.getDefaultValue()));
    map.put("description", e.getDescription());
    map.put("is_system", e.getIsSystem() != null && e.getIsSystem() != false);
    map.put("constraints", SettingController.parseJson(e.getConstraints()));
    map.put("user_id", e.getUserId());
    map.put("organisation_id", e.getOrganisationId());
    map.put("value", SettingController.parseJson(e.getValue()));
    map.put("effective_value", this.effectiveValue(e));
    map.put("created_at", Json.timestamp(e.getCreatedAt()));
    map.put("updated_at", Json.timestamp(e.getUpdatedAt()));
    return map;
  }

  /** Valeur effective = valeur personnalisée sinon défaut (`Setting::getEffectiveValue`). */
  private Object effectiveValue(Setting e) {
    return e.getValue() != null && !e.getValue().isBlank()
        ? SettingController.parseJson(e.getValue())
        : SettingController.parseJson(e.getDefaultValue());
  }

  private static Object parseJson(String json) {
    if (json == null || json.isBlank()) return null;
    try {
      return new com.fasterxml.jackson.databind.ObjectMapper().readValue(json, Object.class);
    } catch (Exception e) {
      return json;
    }
  }

  private static String toJson(Object value) {
    if (value == null) return null;
    try {
      return new com.fasterxml.jackson.databind.ObjectMapper().writeValueAsString(value);
    } catch (Exception e) {
      return String.valueOf(value);
    }
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
