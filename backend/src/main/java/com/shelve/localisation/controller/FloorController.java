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
import com.shelve.localisation.entity.Floor;
import com.shelve.localisation.repository.FloorRepository;
import com.shelve.localisation.dto.FloorView;
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
@RequestMapping(value = {"/api/v1/floors"})
public class FloorController {
  private static final List<String> FILTERABLE =
      List.of("id", "name", "building_id", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("building", "rooms", "creator");
  private final FloorRepository repository;

  public FloorController(FloorRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "floor_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    return Paging.page(
        this.repository,
        Filters.of(qp.getFilters(), Floor.class),
        qp,
        SORTABLE,
        "id",
        request,
        FloorController::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "floor_view");
    Floor floor = (Floor) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    return Json.of("data", FloorController.view(floor));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "floor_create");
    String name = FloorController.str(body.get("name"));
    Long buildingId = FloorController.parseId(body.get("building_id"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 100, "name");
    if (buildingId == null) {
      v.add("building_id", "The building id field is required.");
    }
    v.validate();
    Floor floor = new Floor();
    floor.setName(name);
    floor.setDescription(FloorController.str(body.get("description")));
    floor.setBuildingId(buildingId);
    floor.setCreatorId(auth.user().getId());
    this.repository.save(floor);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/floors/" + floor.getId()}))
        .body(Json.of("data", FloorController.view(floor)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "floor_update");
    Floor floor = (Floor) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("name")) {
      floor.setName(FloorController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      floor.setDescription(FloorController.str(body.get("description")));
    }
    if (body.containsKey("building_id")) {
      floor.setBuildingId(FloorController.parseId(body.get("building_id")));
    }
    this.repository.save(floor);
    return Json.of("data", FloorController.view(floor));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "floor_delete");
    Floor floor = (Floor) this.repository.findById(id).orElseThrow(() -> ApiException.notFound());
    this.repository.delete(floor);
    return ResponseEntity.noContent().build();
  }

  static FloorView view(Floor f) {
    return new FloorView(
        f.getId(),
        f.getName(),
        f.getDescription(),
        f.getBuildingId(),
        f.getCreatorId(),
        f.getCreatedAt(),
        f.getUpdatedAt());
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
