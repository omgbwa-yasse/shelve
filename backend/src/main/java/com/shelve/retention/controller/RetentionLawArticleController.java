package com.shelve.retention.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.exception.ValidationException;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.QueryParams;
import com.shelve.retention.entity.RetentionLawArticle;
import com.shelve.retention.entity.RetentionLawArticleId;
import com.shelve.retention.repository.RetentionLawArticleRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
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
@RequestMapping(value = {"/api/v1/retention-law-articles"})
public class RetentionLawArticleController {
  private static final List<String> FILTERABLE = List.of("retention_id", "law_article_id");
  private final RetentionLawArticleRepository repository;

  public RetentionLawArticleController(RetentionLawArticleRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "retention_law_article_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, FILTERABLE, List.of());
    Specification<RetentionLawArticle> spec =
        Filters.of(qp.getFilters(), RetentionLawArticle.class);
    List<Map<String, Object>> items = this.repository.findAll(spec).stream().map(this::mapper).toList();
    return Json.of("data", items);
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "retention_law_article_create");
    Long retentionId = RetentionLawArticleController.longOf(body.get("retention_id"));
    Long lawArticleId = RetentionLawArticleController.longOf(body.get("law_article_id"));
    if (retentionId == null || lawArticleId == null) {
      throw new ValidationException(
          Map.of(
              "retention_id",
              List.of("The retention id field is required."),
              "law_article_id",
              List.of("The law article id field is required.")));
    }
    RetentionLawArticleId id = new RetentionLawArticleId(retentionId, lawArticleId);
    boolean created = !this.repository.existsById(id);
    boolean bl = created;
    if (created) {
      RetentionLawArticle pivot = new RetentionLawArticle();
      pivot.setId(id);
      this.repository.save(pivot);
    }
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) (created ? 201 : 200))
                .header(
                    "Location",
                    new String[] {
                      "/api/v1/retention-law-articles/" + retentionId + "/" + lawArticleId
                    }))
        .body(Json.of("data", this.mapper(this.resolve(retentionId, lawArticleId))));
  }

  @PatchMapping(value = {"/{retention}/{lawArticle}"})
  public Map<String, Object> update(
      @PathVariable Long retention,
      @PathVariable Long lawArticle,
      @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "retention_law_article_update");
    this.resolve(retention, lawArticle);
    return Json.of("data", this.mapper(this.resolve(retention, lawArticle)));
  }

  @DeleteMapping(value = {"/{retention}/{lawArticle}"})
  public ResponseEntity<Void> destroy(@PathVariable Long retention, @PathVariable Long lawArticle) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "retention_law_article_delete");
    this.resolve(retention, lawArticle);
    this.repository.deleteById(new RetentionLawArticleId(retention, lawArticle));
    return ResponseEntity.noContent().build();
  }

  private RetentionLawArticle resolve(Long retentionId, Long lawArticleId) {
    return (RetentionLawArticle)
        this.repository
            .findById(new RetentionLawArticleId(retentionId, lawArticleId))
            .orElseThrow(() -> ApiException.notFound());
  }

  private Map<String, Object> mapper(RetentionLawArticle p) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("retention_id", p.getRetentionId());
    map.put("law_article_id", p.getLawArticleId());
    return map;
  }

  private static Long longOf(Object value) {
    if (value == null) {
      return null;
    }
    try {
      return ((Number) value).longValue();
    } catch (ClassCastException e) {
      try {
        return Long.parseLong(String.valueOf(value));
      } catch (NumberFormatException e2) {
        return null;
      }
    }
  }
}
