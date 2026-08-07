package com.shelve.thesaurus.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.thesaurus.entity.ThesaurusScheme;
import com.shelve.thesaurus.repository.ThesaurusSchemeRepository;
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
@RequestMapping(value = {"/api/v1/thesaurus-schemes"})
public class ThesaurusSchemeController extends GenericCrudController<ThesaurusScheme> {
  private final ThesaurusSchemeRepository repo;

  public ThesaurusSchemeController(ThesaurusSchemeRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<ThesaurusScheme> entityClass() {
    return ThesaurusScheme.class;
  }

  @Override
  protected JpaSpecificationExecutor<ThesaurusScheme> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "thesaurus_scheme";
  }

  @Override
  protected List<String> filterable() {
    return List.of("uri", "identifier", "title", "description", "language", "namespace_id");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected ThesaurusScheme newEntity() {
    return new ThesaurusScheme();
  }

  @Override
  protected String location(ThesaurusScheme e) {
    return "/api/v1/thesaurus-schemes/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (ThesaurusSchemeController.str(body.get("uri")) == null) {
      v.add("uri", "The uri field is required.");
    }
    if (ThesaurusSchemeController.str(body.get("uri")) != null
        && this.repository()
                .findAll(
                    (Specification & Serializable)
                        (root, q, cb) ->
                            cb.equal(
                                (Expression) root.get("uri"),
                                (Object) ThesaurusSchemeController.str(body.get("uri"))))
                .size()
            > 0) {
      v.add("uri", "The uri has already been taken.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, ThesaurusScheme e) {
    if (body.containsKey("uri")) {
      e.setUri(ThesaurusSchemeController.str(body.get("uri")));
    }
    if (body.containsKey("identifier")) {
      e.setIdentifier(ThesaurusSchemeController.str(body.get("identifier")));
    }
    if (body.containsKey("title")) {
      e.setTitle(ThesaurusSchemeController.str(body.get("title")));
    }
    if (body.containsKey("description")) {
      e.setDescription(ThesaurusSchemeController.str(body.get("description")));
    }
    if (body.containsKey("language")) {
      e.setLanguage(ThesaurusSchemeController.str(body.get("language")));
    }
    if (body.containsKey("namespace_id")) {
      e.setNamespaceId(ThesaurusSchemeController.longOf(body.get("namespace_id")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, ThesaurusScheme e) {
    if (body.containsKey("uri")) {
      e.setUri(ThesaurusSchemeController.str(body.get("uri")));
    }
    if (body.containsKey("identifier")) {
      e.setIdentifier(ThesaurusSchemeController.str(body.get("identifier")));
    }
    if (body.containsKey("title")) {
      e.setTitle(ThesaurusSchemeController.str(body.get("title")));
    }
    if (body.containsKey("description")) {
      e.setDescription(ThesaurusSchemeController.str(body.get("description")));
    }
    if (body.containsKey("language")) {
      e.setLanguage(ThesaurusSchemeController.str(body.get("language")));
    }
    if (body.containsKey("namespace_id")) {
      e.setNamespaceId(ThesaurusSchemeController.longOf(body.get("namespace_id")));
    }
  }

  @Override
  protected Map<String, Object> mapper(ThesaurusScheme e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("uri", e.getUri());
    map.put("identifier", e.getIdentifier());
    map.put("title", e.getTitle());
    map.put("description", e.getDescription());
    map.put("language", e.getLanguage());
    map.put("namespace_id", e.getNamespaceId());
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
