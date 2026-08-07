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
import com.shelve.localisation.entity.ContainerProperty;
import com.shelve.localisation.repository.ContainerPropertyRepository;
import com.shelve.localisation.dto.ContainerPropertyView;
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
@RequestMapping(value = {"/api/v1/container-properties"})
public class ContainerPropertyController {
  private static final List<String> FILTERABLE = List.of("id", "name", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("creator");
  private final ContainerPropertyRepository repository;

  public ContainerPropertyController(ContainerPropertyRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_property_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    return Paging.page(
        this.repository,
        Filters.of(qp.getFilters(), ContainerProperty.class),
        qp,
        SORTABLE,
        "id",
        request,
        ContainerPropertyController::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_property_view");
    ContainerProperty property =
        (ContainerProperty) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    return Json.of("data", ContainerPropertyController.view(property));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_property_create");
    String name = ContainerPropertyController.str(body.get("name"));
    Double width = ContainerPropertyController.doubleOf(body.get("width"));
    Double length = ContainerPropertyController.doubleOf(body.get("length"));
    Double depth = ContainerPropertyController.doubleOf(body.get("depth"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 100, "name");
    if (width == null) {
      v.add("width", "The width field is required.");
    }
    if (length == null) {
      v.add("length", "The length field is required.");
    }
    if (depth == null) {
      v.add("depth", "The depth field is required.");
    }
    v.validate();
    ContainerProperty property = new ContainerProperty();
    property.setName(name);
    property.setWidth(width);
    property.setLength(length);
    property.setDepth(depth);
    property.setCreatorId(auth.user().getId());
    this.repository.save(property);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header(
                    "Location", new String[] {"/api/v1/container-properties/" + property.getId()}))
        .body(Json.of("data", ContainerPropertyController.view(property)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_property_update");
    ContainerProperty property =
        (ContainerProperty) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("name")) {
      property.setName(ContainerPropertyController.str(body.get("name")));
    }
    if (body.containsKey("width")) {
      property.setWidth(ContainerPropertyController.doubleOf(body.get("width")));
    }
    if (body.containsKey("length")) {
      property.setLength(ContainerPropertyController.doubleOf(body.get("length")));
    }
    if (body.containsKey("depth")) {
      property.setDepth(ContainerPropertyController.doubleOf(body.get("depth")));
    }
    this.repository.save(property);
    return Json.of("data", ContainerPropertyController.view(property));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "container_property_delete");
    ContainerProperty property =
        (ContainerProperty) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    this.repository.delete(property);
    return ResponseEntity.noContent().build();
  }

  static ContainerPropertyView view(ContainerProperty p) {
    return new ContainerPropertyView(
        p.getId(),
        p.getName(),
        p.getWidth(),
        p.getLength(),
        p.getDepth(),
        p.getCreatorId(),
        p.getCreatedAt(),
        p.getUpdatedAt());
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  private static Double doubleOf(Object value) {
    if (value == null) {
      return null;
    }
    if (value instanceof Number) {
      Number n = (Number) value;
      return n.doubleValue();
    }
    return Double.parseDouble(String.valueOf(value));
  }
}
