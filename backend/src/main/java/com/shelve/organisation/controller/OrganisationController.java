package com.shelve.organisation.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.organisation.entity.Organisation;
import com.shelve.organisation.repository.OrganisationRepository;
import com.shelve.organisation.dto.OrganisationView;
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
@RequestMapping(value = {"/api/v1/organisations"})
public class OrganisationController {
  private static final List<String> FILTERABLE =
      List.of("id", "code", "name", "parent_id", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("parent", "children");
  private final OrganisationRepository repository;

  public OrganisationController(OrganisationRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "organisations_view");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    return Paging.page(
        this.repository,
        Filters.of(qp.getFilters(), Organisation.class),
        qp,
        SORTABLE,
        "code",
        request,
        OrganisationController::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "organisations_view");
    Organisation org =
        (Organisation) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    return Json.of("data", OrganisationController.view(org));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "organisations_create");
    String code = OrganisationController.str(body.get("code"));
    String name = OrganisationController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("code", code, "The code field is required.")
            .max("code", code, 10, "code")
            .require("name", name, "The name field is required.")
            .max("name", name, 200, "name");
    v.validate();
    Organisation org = new Organisation();
    org.setCode(code);
    org.setName(name);
    org.setDescription(OrganisationController.str(body.get("description")));
    if (body.get("parent_id") != null) {
      org.setParentId(OrganisationController.parseId(body.get("parent_id")));
    }
    this.repository.save(org);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/organisations/" + org.getId()}))
        .body(Json.of("data", OrganisationController.view(org)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "organisations_update");
    Organisation org =
        (Organisation) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("code")) {
      org.setCode(OrganisationController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      org.setName(OrganisationController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      org.setDescription(OrganisationController.str(body.get("description")));
    }
    if (body.containsKey("parent_id")) {
      org.setParentId(
          body.get("parent_id") == null
              ? null
              : OrganisationController.parseId(body.get("parent_id")));
    }
    this.repository.save(org);
    return Json.of("data", OrganisationController.view(org));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "organisations_delete");
    Organisation org =
        (Organisation) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    this.repository.delete(org);
    return ResponseEntity.noContent().build();
  }

  static OrganisationView view(Organisation o) {
    return new OrganisationView(
        o.getId(),
        o.getCode(),
        o.getName(),
        o.getDescription(),
        o.getParentId(),
        o.getCreatedAt(),
        o.getUpdatedAt());
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  private static Long parseId(Object value) {
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
