package com.shelve.referentials.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.referentials.entity.Communicability;
import com.shelve.referentials.repository.CommunicabilityRepository;
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
@RequestMapping(value = {"/api/v1/communicabilities"})
public class CommunicabilityController extends GenericCrudController<Communicability> {
  private final CommunicabilityRepository repo;

  public CommunicabilityController(CommunicabilityRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<Communicability> entityClass() {
    return Communicability.class;
  }

  @Override
  protected JpaSpecificationExecutor<Communicability> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "communicability";
  }

  @Override
  protected List<String> filterable() {
    return List.of("id", "code", "name", "duration");
  }

  @Override
  protected List<String> sortable() {
    return List.of("id", "code", "name", "duration");
  }

  @Override
  protected Communicability newEntity() {
    return new Communicability();
  }

  @Override
  protected String location(Communicability e) {
    return "/api/v1/communicabilities/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (CommunicabilityController.str(body.get("code")) == null) {
      v.add("code", "The code field is required.");
    }
    if (CommunicabilityController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    if (CommunicabilityController.intOf(body.get("duration")) == null) {
      v.add("duration", "The duration field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Communicability e) {
    if (body.containsKey("code")) e.setCode(CommunicabilityController.str(body.get("code")));
    if (body.containsKey("name")) e.setName(CommunicabilityController.str(body.get("name")));
    if (body.containsKey("duration")) e.setDuration(CommunicabilityController.intOf(body.get("duration")));
    if (body.containsKey("description")) e.setDescription(CommunicabilityController.str(body.get("description")));
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Communicability e) {
    if (body.containsKey("code")) e.setCode(CommunicabilityController.str(body.get("code")));
    if (body.containsKey("name")) e.setName(CommunicabilityController.str(body.get("name")));
    if (body.containsKey("duration")) e.setDuration(CommunicabilityController.intOf(body.get("duration")));
    if (body.containsKey("description")) e.setDescription(CommunicabilityController.str(body.get("description")));
  }

  @Override
  protected Map<String, Object> mapper(Communicability e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("code", e.getCode());
    map.put("name", e.getName());
    map.put("duration", e.getDuration());
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
