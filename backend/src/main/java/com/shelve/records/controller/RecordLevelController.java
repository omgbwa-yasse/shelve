package com.shelve.records.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.records.entity.RecordLevel;
import com.shelve.records.repository.RecordLevelRepository;
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
@RequestMapping(value = {"/api/v1/record-levels"})
public class RecordLevelController extends GenericCrudController<RecordLevel> {
  private final RecordLevelRepository repo;

  public RecordLevelController(RecordLevelRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<RecordLevel> entityClass() {
    return RecordLevel.class;
  }

  @Override
  protected JpaSpecificationExecutor<RecordLevel> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "record_level";
  }

  @Override
  protected List<String> filterable() {
    return List.of("name", "description", "child_id", "has_child");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected RecordLevel newEntity() {
    return new RecordLevel();
  }

  @Override
  protected String location(RecordLevel e) {
    return "/api/v1/record-levels/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (RecordLevelController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, RecordLevel e) {
    if (body.containsKey("name")) {
      e.setName(RecordLevelController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(RecordLevelController.str(body.get("description")));
    }
    if (body.containsKey("child_id")) {
      e.setChildId(RecordLevelController.longOf(body.get("child_id")));
    }
    if (body.containsKey("has_child")) {
      e.setHasChild(RecordLevelController.bool(body.get("has_child")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, RecordLevel e) {
    if (body.containsKey("name")) {
      e.setName(RecordLevelController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(RecordLevelController.str(body.get("description")));
    }
    if (body.containsKey("child_id")) {
      e.setChildId(RecordLevelController.longOf(body.get("child_id")));
    }
    if (body.containsKey("has_child")) {
      e.setHasChild(RecordLevelController.bool(body.get("has_child")));
    }
  }

  @Override
  protected Map<String, Object> mapper(RecordLevel e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("child_id", e.getChildId());
    map.put("has_child", e.getHasChild() != null && e.getHasChild() != false);
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
