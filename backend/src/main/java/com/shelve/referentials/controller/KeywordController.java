package com.shelve.referentials.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.referentials.entity.Keyword;
import com.shelve.referentials.repository.KeywordRepository;
import com.shelve.referentials.dto.KeywordView;
import jakarta.servlet.http.HttpServletRequest;
import java.util.List;
import java.util.Map;
import org.springframework.http.ResponseEntity;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@Transactional
@RestController
@RequestMapping(value = {"/api/v1/keywords"})
public class KeywordController {
  private static final List<String> FILTERABLE = List.of("id", "name", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of();
  private final KeywordRepository repository;

  public KeywordController(KeywordRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "keyword_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    return Paging.page(
        this.repository,
        Filters.of(qp.getFilters(), Keyword.class),
        qp,
        SORTABLE,
        "id",
        request,
        KeywordController::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "keyword_view");
    Keyword keyword =
        (Keyword) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    return Json.of("data", KeywordController.view(keyword));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "keyword_create");
    String name = KeywordController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 250, "name")
            .unique(
                "name",
                name,
                name != null && this.repository.existsByName(name),
                "keywords",
                "name");
    v.validate();
    Keyword keyword = new Keyword();
    keyword.setName(name);
    keyword.setDescription(KeywordController.str(body.get("description")));
    this.repository.save(keyword);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/keywords/" + keyword.getId()}))
        .body(Json.of("data", KeywordController.view(keyword)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "keyword_update");
    Keyword keyword =
        (Keyword) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("name")) {
      keyword.setName(KeywordController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      keyword.setDescription(KeywordController.str(body.get("description")));
    }
    this.repository.save(keyword);
    return Json.of("data", KeywordController.view(keyword));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "keyword_delete");
    Keyword keyword =
        (Keyword) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    this.repository.delete(keyword);
    return ResponseEntity.noContent().build();
  }

  @GetMapping(value = {"/search"})
  public Map<String, Object> search(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "keyword_viewAny");
    String query = request.getParameter("q") == null ? "" : request.getParameter("q").trim();
    String string = query;
    if (query.length() < 2) {
      return Json.of("data", List.of());
    }
    List<String> names =
        this.repository.findTop10ByNameContainingIgnoreCaseOrderByNameAsc(query).stream()
            .map(Keyword::getName)
            .toList();
    return Json.of("data", names);
  }

  static KeywordView view(Keyword k) {
    return new KeywordView(
        k.getId(), k.getName(), k.getDescription(), k.getCreatedAt(), k.getUpdatedAt());
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }
}
