package com.shelve.retention.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.retention.entity.Retention;
import com.shelve.retention.repository.RetentionRepository;
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
@RequestMapping(value = {"/api/v1/retentions"})
public class RetentionController extends GenericCrudController<Retention> {
  private final RetentionRepository retentionRepository;

  public RetentionController(RetentionRepository retentionRepository) {
    this.retentionRepository = retentionRepository;
  }

  @Override
  protected Class<Retention> entityClass() {
    return Retention.class;
  }

  @Override
  protected JpaSpecificationExecutor<Retention> repository() {
    return this.retentionRepository;
  }

  @Override
  protected String resource() {
    return "retention";
  }

  @Override
  protected List<String> filterable() {
    return List.of("id", "code", "name", "duration", "sort_id", "created_at", "updated_at");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected Retention newEntity() {
    return new Retention();
  }

  @Override
  protected String location(Retention entity) {
    return "/api/v1/retentions/" + entity.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    String code = RetentionController.str(body.get("code"));
    String name = RetentionController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("code", code, "The code field is required.")
            .max("code", code, 10, "code")
            .require("name", name, "The name field is required.")
            .max("name", name, 200, "name");
    if (RetentionController.intOf(body.get("duration")) == null) {
      v.add("duration", "The duration field is required.");
    }
    if (RetentionController.longOf(body.get("sort_id")) == null) {
      v.add("sort_id", "The sort id field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Retention r) {
    r.setCode(RetentionController.str(body.get("code")));
    r.setName(RetentionController.str(body.get("name")));
    r.setDuration(RetentionController.intOf(body.get("duration")));
    r.setSortId(RetentionController.longOf(body.get("sort_id")));
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Retention r) {
    if (body.containsKey("code")) {
      r.setCode(RetentionController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      r.setName(RetentionController.str(body.get("name")));
    }
    if (body.containsKey("duration")) {
      r.setDuration(RetentionController.intOf(body.get("duration")));
    }
    if (body.containsKey("sort_id")) {
      r.setSortId(RetentionController.longOf(body.get("sort_id")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Retention r) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("code", r.getCode());
    map.put("name", r.getName());
    map.put("duration", r.getDuration());
    map.put("sort_id", r.getSortId());
    map.put("created_at", Json.timestamp(r.getCreatedAt()));
    map.put("updated_at", Json.timestamp(r.getUpdatedAt()));
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
