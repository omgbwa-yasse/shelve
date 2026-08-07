package com.shelve.records.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.records.entity.RecordSupport;
import com.shelve.records.repository.RecordSupportRepository;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
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
@RequestMapping(value = {"/api/v1/record-supports"})
public class RecordSupportController extends GenericCrudController<RecordSupport> {
  private final RecordSupportRepository repo;

  public RecordSupportController(RecordSupportRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<RecordSupport> entityClass() {
    return RecordSupport.class;
  }

  @Override
  protected JpaSpecificationExecutor<RecordSupport> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "record_support";
  }

  @Override
  protected List<String> filterable() {
    return List.of("name", "description");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected RecordSupport newEntity() {
    return new RecordSupport();
  }

  @Override
  protected String location(RecordSupport e) {
    return "/api/v1/record-supports/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (RecordSupportController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    if (RecordSupportController.str(body.get("name")) != null
        && this.repository()
                .findAll(
                    (Specification & Serializable)
                        (root, q, cb) ->
                            cb.equal(
                                (Expression) root.get("name"),
                                (Object) RecordSupportController.str(body.get("name"))))
                .size()
            > 0) {
      v.add("name", "The name has already been taken.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, RecordSupport e) {
    if (body.containsKey("name")) {
      e.setName(RecordSupportController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(RecordSupportController.str(body.get("description")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, RecordSupport e) {
    if (body.containsKey("name")) {
      e.setName(RecordSupportController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(RecordSupportController.str(body.get("description")));
    }
  }

  @Override
  protected Map<String, Object> mapper(RecordSupport e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
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
