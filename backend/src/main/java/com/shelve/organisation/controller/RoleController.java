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
import com.shelve.organisation.entity.Role;
import com.shelve.organisation.repository.RoleRepository;
import com.shelve.organisation.dto.RoleView;
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
@RequestMapping(value = {"/api/v1/roles"})
public class RoleController {
  private static final List<String> FILTERABLE =
      List.of("id", "name", "guard_name", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("permissions", "users");
  private final RoleRepository repository;

  public RoleController(RoleRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "role_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    return Paging.page(
        this.repository,
        Filters.of(qp.getFilters(), Role.class),
        qp,
        SORTABLE,
        "id",
        request,
        RoleController::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "role_view");
    Role role = (Role) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    return Json.of("data", RoleController.view(role));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "role_create");
    String name = RoleController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 191, "name");
    v.validate();
    Role role = new Role();
    role.setName(name);
    role.setDescription(RoleController.str(body.get("description")));
    if (body.containsKey("guard_name")) {
      role.setGuardName(RoleController.str(body.get("guard_name")));
    }
    this.repository.save(role);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/roles/" + role.getId()}))
        .body(Json.of("data", RoleController.view(role)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "role_update");
    Role role = (Role) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("name")) {
      role.setName(RoleController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      role.setDescription(RoleController.str(body.get("description")));
    }
    if (body.containsKey("guard_name")) {
      role.setGuardName(RoleController.str(body.get("guard_name")));
    }
    this.repository.save(role);
    return Json.of("data", RoleController.view(role));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "role_delete");
    Role role = (Role) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    this.repository.delete(role);
    return ResponseEntity.noContent().build();
  }

  static RoleView view(Role r) {
    return new RoleView(
        r.getId(),
        r.getName(),
        r.getGuardName(),
        r.getDescription(),
        r.getCreatedAt(),
        r.getUpdatedAt());
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }
}
