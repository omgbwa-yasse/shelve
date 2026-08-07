package com.shelve.dolly.controller;

import com.shelve.common.Json;
import com.shelve.exception.ValidationException;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
import com.shelve.dolly.entity.Dolly;
import com.shelve.dolly.entity.DollyRecord;
import com.shelve.dolly.repository.DollyRecordRepository;
import com.shelve.dolly.repository.DollyRepository;
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
@RequestMapping(value = {"/api/v1/dollies"})
public class DollyController extends GenericCrudController<Dolly> {
  private static final List<String> CATEGORIES =
      List.of(
          "mail",
          "transaction",
          "record",
          "slip",
          "building",
          "shelf",
          "container",
          "communication",
          "room",
          "digital_folder",
          "digital_document",
          "artifact",
          "book",
          "book_series");
  private final DollyRepository dollyRepository;
  private final DollyRecordRepository dollyRecordRepository;

  public DollyController(
      DollyRepository dollyRepository, DollyRecordRepository dollyRecordRepository) {
    this.dollyRepository = dollyRepository;
    this.dollyRecordRepository = dollyRecordRepository;
  }

  @Override
  protected Class<Dolly> entityClass() {
    return Dolly.class;
  }

  @Override
  protected JpaSpecificationExecutor<Dolly> repository() {
    return this.dollyRepository;
  }

  @Override
  protected String resource() {
    return "dolly";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "id",
        "name",
        "category",
        "is_public",
        "owner_organisation_id",
        "created_by",
        "created_at",
        "updated_at");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected Dolly newEntity() {
    return new Dolly();
  }

  @Override
  protected String location(Dolly e) {
    return "/api/v1/dollies/" + e.getId();
  }

  @Override
  protected Specification<Dolly> scope(AuthenticatedUser auth) {
    Long orgId = auth.user().getCurrentOrganisationId();
    return (Specification & Serializable)
        (root, query, cb) -> cb.equal((Expression) root.get("ownerOrganisationId"), (Object) orgId);
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    String name = DollyController.str(body.get("name"));
    String category = DollyController.str(body.get("category"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 70, "name")
            .require("category", category, "The category field is required.");
    if (category != null && !CATEGORIES.contains(category)) {
      v.add("category", "The selected category is invalid.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Dolly d) {
    AuthenticatedUser auth = CurrentUser.get();
    d.setName(DollyController.str(body.get("name")));
    d.setDescription(DollyController.str(body.get("description")));
    d.setCategory(DollyController.str(body.get("category")));
    d.setIsPublic(false);
    d.setCreatedBy(auth.user().getId());
    d.setOwnerOrganisationId(auth.user().getCurrentOrganisationId());
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Dolly d) {
    if (body.containsKey("name")) {
      d.setName(DollyController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      d.setDescription(DollyController.str(body.get("description")));
    }
    if (body.containsKey("category")) {
      d.setCategory(DollyController.str(body.get("category")));
    }
    if (body.containsKey("is_public")) {
      d.setIsPublic(DollyController.bool(body.get("is_public")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Dolly d) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", d.getId());
    map.put("name", d.getName());
    map.put("description", d.getDescription());
    map.put("category", d.getCategory());
    map.put("is_public", d.getIsPublic() != null && d.getIsPublic() != false);
    map.put("created_by", d.getCreatedBy());
    map.put("owner_organisation_id", d.getOwnerOrganisationId());
    map.put("created_at", Json.timestamp(d.getCreatedAt()));
    map.put("updated_at", Json.timestamp(d.getUpdatedAt()));
    return map;
  }

  @GetMapping(value = {"/list"})
  public Map<String, Object> apiList(HttpServletRequest request) {
    return this.index(request);
  }

  @PostMapping(value = {"/store"})
  public ResponseEntity<Map<String, Object>> apiCreate(@RequestBody Map<String, Object> body) {
    return this.store(body);
  }

  @PostMapping(value = {"/{id}/add-record"})
  public Map<String, Object> addRecord(
      @PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Dolly dolly = (Dolly) this.findInScope(id, auth);
    Long recordId = DollyController.longOf(body.get("record_id"));
    if (recordId == null) {
      throw new ValidationException(
          Map.of("record_id", List.of("The record id field is required.")));
    }
    if (!this.dollyRecordRepository.existsByDollyIdAndRecordId(id, recordId)) {
      DollyRecord pivot = new DollyRecord();
      pivot.setDollyId(id);
      pivot.setRecordId(recordId);
      this.dollyRecordRepository.save(pivot);
    }
    return Json.of("data", this.mapper(dolly));
  }

  @DeleteMapping(value = {"/{id}/remove-record/{record}"})
  public ResponseEntity<Void> removeRecord(@PathVariable Long id, @PathVariable Long record) {
    AuthenticatedUser auth = CurrentUser.get();
    this.findInScope(id, auth);
    this.dollyRecordRepository.findAll().stream()
        .filter(p -> p.getDollyId().equals(id) && p.getRecordId().equals(record))
        .forEach(arg_0 -> this.dollyRecordRepository.delete(arg_0));
    return ResponseEntity.noContent().build();
  }

  @PostMapping(value = {"/{id}/rename"})
  public Map<String, Object> rename(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Dolly dolly = (Dolly) this.findInScope(id, auth);
    String name = DollyController.str(body.get("name"));
    if (name == null || name.isBlank()) {
      throw new ValidationException(Map.of("name", List.of("The name field is required.")));
    }
    dolly.setName(name);
    this.dollyRepository.save(dolly);
    return Json.of("data", this.mapper(dolly));
  }

  @PostMapping(value = {"/{id}/clear"})
  public Map<String, Object> clear(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Dolly dolly = (Dolly) this.findInScope(id, auth);
    this.dollyRecordRepository
        .findByDollyId(id)
        .forEach(arg_0 -> this.dollyRecordRepository.delete(arg_0));
    return Json.of("data", this.mapper(dolly));
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
