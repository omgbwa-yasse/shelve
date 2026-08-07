package com.shelve.organisation.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.organisation.entity.UserOrganisationRole;
import com.shelve.organisation.entity.UserOrganisationRoleId;
import com.shelve.organisation.repository.UserOrganisationRoleRepository;
import com.shelve.organisation.dto.UserOrganisationRoleView;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
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
@RequestMapping(value = {"/api/v1/user-organisation-roles"})
public class UserOrganisationRoleController {
  private static final List<String> FILTERABLE =
      List.of("user_id", "organisation_id", "role_id", "creator_id", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("user", "organisation", "role", "creator");
  private final UserOrganisationRoleRepository repository;

  public UserOrganisationRoleController(UserOrganisationRoleRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "user_organisation_role_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    Specification spec =
        Filters.of(qp.getFilters(), UserOrganisationRole.class)
            .and(
                (Specification & Serializable)
                    (root, q, cb) ->
                        cb.equal(
                            (Expression) root.get("id").get("organisationId"),
                            (Object) auth.user().getCurrentOrganisationId()));
    return Paging.page(
        this.repository,
        spec,
        qp,
        SORTABLE,
        "createdAt",
        request,
        UserOrganisationRoleController::view);
  }

  @GetMapping(value = {"/{user}/{organisation}"})
  public Map<String, Object> show(@PathVariable Long user, @PathVariable Long organisation) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.checkOrNotFound(auth, "user_organisation_role_view");
    UserOrganisationRole pivot = this.resolve(user, organisation);
    return Json.of("data", UserOrganisationRoleController.view(pivot));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "user_organisation_role_create");
    Long userId = UserOrganisationRoleController.parseId(body.get("user_id"));
    Long organisationId = UserOrganisationRoleController.parseId(body.get("organisation_id"));
    Long roleId = UserOrganisationRoleController.parseId(body.get("role_id"));
    if (!auth.isSuperAdmin() && !auth.user().getCurrentOrganisationId().equals(organisationId)) {
      throw ApiException.notFound();
    }
    UserOrganisationRoleId id = new UserOrganisationRoleId(userId, organisationId);
    boolean created = !this.repository.existsById(id);
    UserOrganisationRole pivot = new UserOrganisationRole();
    pivot.setId(id);
    pivot.setRoleId(roleId);
    pivot.setCreatorId(auth.user().getId());
    this.repository.save(pivot);
    Map<String, Object> data =
        Json.of("data", UserOrganisationRoleController.view(this.resolve(userId, organisationId)));
    if (created) {
      return ((ResponseEntity.BodyBuilder)
              ResponseEntity.status((int) 201)
                  .header(
                      "Location",
                      new String[] {
                        "/api/v1/user-organisation-roles/" + userId + "/" + organisationId
                      }))
          .body(data);
    }
    return ResponseEntity.ok(data);
  }

  @PatchMapping(value = {"/{user}/{organisation}"})
  public Map<String, Object> update(
      @PathVariable Long user,
      @PathVariable Long organisation,
      @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "user_organisation_role_update");
    UserOrganisationRole pivot = this.resolve(user, organisation);
    if (body.containsKey("role_id")) {
      pivot.setRoleId(UserOrganisationRoleController.parseId(body.get("role_id")));
    }
    this.repository.save(pivot);
    return Json.of("data", UserOrganisationRoleController.view(this.resolve(user, organisation)));
  }

  @DeleteMapping(value = {"/{user}/{organisation}"})
  public ResponseEntity<Void> destroy(@PathVariable Long user, @PathVariable Long organisation) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "user_organisation_role_delete");
    this.resolve(user, organisation);
    this.repository.deleteById(new UserOrganisationRoleId(user, organisation));
    return ResponseEntity.noContent().build();
  }

  private UserOrganisationRole resolve(Long userId, Long organisationId) {
    return (UserOrganisationRole)
        this.repository
            .findById(new UserOrganisationRoleId(userId, organisationId))
            .orElseThrow(() -> ApiException.notFound());
  }

  static UserOrganisationRoleView view(UserOrganisationRole p) {
    return new UserOrganisationRoleView(
        p.getUserId(),
        p.getOrganisationId(),
        p.getRoleId(),
        p.getCreatorId(),
        p.getCreatedAt(),
        p.getUpdatedAt());
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
