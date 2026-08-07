package com.shelve.mails.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.mails.entity.Batch;
import com.shelve.mails.repository.BatchRepository;
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
@RequestMapping(value = {"/api/v1/batches"})
public class BatchController extends GenericCrudController<Batch> {
  private final BatchRepository repo;

  public BatchController(BatchRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<Batch> entityClass() {
    return Batch.class;
  }

  @Override
  protected JpaSpecificationExecutor<Batch> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "batch";
  }

  @Override
  protected List<String> filterable() {
    return List.of("code", "name", "organisation_holder_id");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected Batch newEntity() {
    return new Batch();
  }

  @Override
  protected String location(Batch e) {
    return "/api/v1/batches/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (BatchController.str(body.get("code")) == null) {
      v.add("code", "The code field is required.");
    }
    if (BatchController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    if (BatchController.str(body.get("code")) != null
        && this.repository()
                .findAll(
                    (Specification & Serializable)
                        (root, q, cb) ->
                            cb.equal(
                                (Expression) root.get("code"),
                                (Object) BatchController.str(body.get("code"))))
                .size()
            > 0) {
      v.add("code", "The code has already been taken.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Batch e) {
    if (body.containsKey("code")) {
      e.setCode(BatchController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(BatchController.str(body.get("name")));
    }
    if (body.containsKey("organisation_holder_id")) {
      e.setOrganisationHolderId(BatchController.longOf(body.get("organisation_holder_id")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Batch e) {
    if (body.containsKey("code")) {
      e.setCode(BatchController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(BatchController.str(body.get("name")));
    }
    if (body.containsKey("organisation_holder_id")) {
      e.setOrganisationHolderId(BatchController.longOf(body.get("organisation_holder_id")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Batch e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("code", e.getCode());
    map.put("name", e.getName());
    map.put("organisation_holder_id", e.getOrganisationHolderId());
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
