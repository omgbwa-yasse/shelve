package com.shelve.localisation.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.localisation.entity.ContainerStatus;
import com.shelve.localisation.repository.ContainerStatusRepository;
import com.shelve.localisation.dto.ContainerStatusView;
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
@RequestMapping(value = {"/api/v1/container-statuses"})
public class ContainerStatusController {
  private static final List<String> FILTERABLE = List.of("id", "name", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("creator");
  private final ContainerStatusRepository repository;

  public ContainerStatusController(ContainerStatusRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_status_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    return Paging.page(
        this.repository,
        Filters.of(qp.getFilters(), ContainerStatus.class),
        qp,
        SORTABLE,
        "id",
        request,
        ContainerStatusController::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_status_view");
    ContainerStatus status =
        (ContainerStatus) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    return Json.of("data", ContainerStatusController.view(status));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_status_create");
    String name = ContainerStatusController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 50, "name");
    v.validate();
    ContainerStatus status = new ContainerStatus();
    status.setName(name);
    status.setDescription(ContainerStatusController.str(body.get("description")));
    status.setCreatorId(auth.user().getId());
    this.repository.save(status);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/container-statuses/" + status.getId()}))
        .body(Json.of("data", ContainerStatusController.view(status)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_status_update");
    ContainerStatus status =
        (ContainerStatus) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("name")) {
      status.setName(ContainerStatusController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      status.setDescription(ContainerStatusController.str(body.get("description")));
    }
    this.repository.save(status);
    return Json.of("data", ContainerStatusController.view(status));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_status_delete");
    ContainerStatus status =
        (ContainerStatus) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    this.repository.delete(status);
    return ResponseEntity.noContent().build();
  }

  static ContainerStatusView view(ContainerStatus s) {
    return new ContainerStatusView(
        s.getId(),
        s.getName(),
        s.getDescription(),
        s.getCreatorId(),
        s.getCreatedAt(),
        s.getUpdatedAt());
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }
}
