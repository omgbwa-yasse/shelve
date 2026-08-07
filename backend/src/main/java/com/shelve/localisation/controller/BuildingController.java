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
import com.shelve.localisation.entity.Building;
import com.shelve.localisation.repository.BuildingRepository;
import com.shelve.localisation.dto.BuildingView;
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
@RequestMapping(value = {"/api/v1/buildings"})
public class BuildingController {
  private static final List<String> FILTERABLE =
      List.of("id", "name", "visibility", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("floors", "creator");
  private final BuildingRepository repository;

  public BuildingController(BuildingRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "building_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    return Paging.page(
        this.repository,
        Filters.of(qp.getFilters(), Building.class),
        qp,
        SORTABLE,
        "id",
        request,
        BuildingController::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "building_view");
    Building building =
        (Building) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    return Json.of("data", BuildingController.view(building));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "building_create");
    String name = BuildingController.str(body.get("name"));
    String visibility = BuildingController.str(body.get("visibility"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 100, "name")
            .require("visibility", visibility, "The visibility field is required.");
    if (visibility != null && !List.of("public", "private", "inherit").contains(visibility)) {
      v.add("visibility", "The selected visibility is invalid.");
    }
    v.validate();
    Building building = new Building();
    building.setName(name);
    building.setDescription(BuildingController.str(body.get("description")));
    building.setVisibility(visibility);
    building.setCreatorId(auth.user().getId());
    this.repository.save(building);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/buildings/" + building.getId()}))
        .body(Json.of("data", BuildingController.view(building)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "building_update");
    Building building =
        (Building) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("name")) {
      building.setName(BuildingController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      building.setDescription(BuildingController.str(body.get("description")));
    }
    if (body.containsKey("visibility")) {
      building.setVisibility(BuildingController.str(body.get("visibility")));
    }
    this.repository.save(building);
    return Json.of("data", BuildingController.view(building));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "building_delete");
    Building building =
        (Building) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    this.repository.delete(building);
    return ResponseEntity.noContent().build();
  }

  static BuildingView view(Building b) {
    return new BuildingView(
        b.getId(),
        b.getName(),
        b.getDescription(),
        b.getVisibility(),
        b.isPublic(),
        b.isPrivate(),
        b.inheritsVisibility(),
        b.getCreatorId(),
        b.getCreatedAt(),
        b.getUpdatedAt());
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }
}
