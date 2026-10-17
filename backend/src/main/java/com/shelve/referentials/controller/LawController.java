package com.shelve.referentials.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.referentials.entity.Law;
import com.shelve.referentials.repository.LawRepository;
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
@RequestMapping(value = {"/api/v1/laws"})
public class LawController extends GenericCrudController<Law> {
  private final LawRepository repo;

  public LawController(LawRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<Law> entityClass() {
    return Law.class;
  }

  @Override
  protected JpaSpecificationExecutor<Law> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "law";
  }

  @Override
  protected List<String> filterable() {
    return List.of("id", "code", "name", "law_type_id");
  }

  @Override
  protected List<String> sortable() {
    return List.of("id", "code", "name", "publish_date");
  }

  @Override
  protected Law newEntity() {
    return new Law();
  }

  @Override
  protected String location(Law e) {
    return "/api/v1/laws/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (LawController.str(body.get("code")) == null) v.add("code", "The code field is required.");
    if (LawController.str(body.get("name")) == null) v.add("name", "The name field is required.");
    if (LawController.longOf(body.get("law_type_id")) == null) v.add("law_type_id", "The law type id field is required.");
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Law e) {
    if (body.containsKey("code")) e.setCode(LawController.str(body.get("code")));
    if (body.containsKey("name")) e.setName(LawController.str(body.get("name")));
    if (body.containsKey("description")) e.setDescription(LawController.str(body.get("description")));
    if (body.containsKey("publish_date")) e.setPublishDate(LawController.dateOf(body.get("publish_date")));
    if (body.containsKey("law_type_id")) e.setLawTypeId(LawController.longOf(body.get("law_type_id")));
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Law e) {
    if (body.containsKey("code")) e.setCode(LawController.str(body.get("code")));
    if (body.containsKey("name")) e.setName(LawController.str(body.get("name")));
    if (body.containsKey("description")) e.setDescription(LawController.str(body.get("description")));
    if (body.containsKey("publish_date")) e.setPublishDate(LawController.dateOf(body.get("publish_date")));
    if (body.containsKey("law_type_id")) e.setLawTypeId(LawController.longOf(body.get("law_type_id")));
  }

  @Override
  protected Map<String, Object> mapper(Law e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("code", e.getCode());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("publish_date", e.getPublishDate() != null ? e.getPublishDate().toString() : null);
    map.put("law_type_id", e.getLawTypeId());
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
