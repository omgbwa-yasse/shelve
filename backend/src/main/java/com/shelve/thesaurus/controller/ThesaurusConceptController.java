package com.shelve.thesaurus.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.thesaurus.entity.ThesaurusConcept;
import com.shelve.thesaurus.repository.ThesaurusConceptRepository;
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
@RequestMapping(value = {"/api/v1/thesaurus-concepts"})
public class ThesaurusConceptController extends GenericCrudController<ThesaurusConcept> {
  private final ThesaurusConceptRepository repo;

  public ThesaurusConceptController(ThesaurusConceptRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<ThesaurusConcept> entityClass() {
    return ThesaurusConcept.class;
  }

  @Override
  protected JpaSpecificationExecutor<ThesaurusConcept> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "thesaurus_concept";
  }

  @Override
  protected List<String> filterable() {
    return List.of("scheme_id", "uri", "notation", "status");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected ThesaurusConcept newEntity() {
    return new ThesaurusConcept();
  }

  @Override
  protected String location(ThesaurusConcept e) {
    return "/api/v1/thesaurus-concepts/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (ThesaurusConceptController.str(body.get("uri")) == null) {
      v.add("uri", "The uri field is required.");
    }
    if (ThesaurusConceptController.str(body.get("uri")) != null
        && this.repository()
                .findAll(
                    (Specification & Serializable)
                        (root, q, cb) ->
                            cb.equal(
                                (Expression) root.get("uri"),
                                (Object) ThesaurusConceptController.str(body.get("uri"))))
                .size()
            > 0) {
      v.add("uri", "The uri has already been taken.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, ThesaurusConcept e) {
    if (body.containsKey("scheme_id")) {
      e.setSchemeId(ThesaurusConceptController.longOf(body.get("scheme_id")));
    }
    if (body.containsKey("uri")) {
      e.setUri(ThesaurusConceptController.str(body.get("uri")));
    }
    if (body.containsKey("notation")) {
      e.setNotation(ThesaurusConceptController.str(body.get("notation")));
    }
    if (body.containsKey("status")) {
      e.setStatus(ThesaurusConceptController.intOf(body.get("status")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, ThesaurusConcept e) {
    if (body.containsKey("scheme_id")) {
      e.setSchemeId(ThesaurusConceptController.longOf(body.get("scheme_id")));
    }
    if (body.containsKey("uri")) {
      e.setUri(ThesaurusConceptController.str(body.get("uri")));
    }
    if (body.containsKey("notation")) {
      e.setNotation(ThesaurusConceptController.str(body.get("notation")));
    }
    if (body.containsKey("status")) {
      e.setStatus(ThesaurusConceptController.intOf(body.get("status")));
    }
  }

  @Override
  protected Map<String, Object> mapper(ThesaurusConcept e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("scheme_id", e.getSchemeId());
    map.put("uri", e.getUri());
    map.put("notation", e.getNotation());
    map.put("status", e.getStatus());
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
