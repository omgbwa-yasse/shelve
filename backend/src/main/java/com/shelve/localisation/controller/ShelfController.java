package com.shelve.localisation.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.exception.ValidationException;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.localisation.service.OrgScope;
import com.shelve.localisation.repository.RoomRepository;
import com.shelve.localisation.entity.Shelf;
import com.shelve.localisation.repository.ShelfRepository;
import jakarta.persistence.EntityManager;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.util.LinkedHashMap;
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
@RequestMapping(value = {"/api/v1/shelves"})
public class ShelfController {
  private static final List<String> FILTERABLE =
      List.of("id", "code", "room_id", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("room", "containers", "creator");
  private final ShelfRepository repository;
  private final RoomRepository roomRepository;
  private final EntityManager em;

  public ShelfController(
      ShelfRepository repository, RoomRepository roomRepository, EntityManager em) {
    this.repository = repository;
    this.roomRepository = roomRepository;
    this.em = em;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "shelf_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    Specification<Shelf> orgScope =
        OrgScope.shelvesInOrganisation(auth.user().getCurrentOrganisationId());
    Specification spec = orgScope.and(Filters.of(qp.getFilters(), Shelf.class));
    return Paging.page(this.repository, spec, qp, SORTABLE, "id", request, this::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "shelf_view");
    Shelf shelf = this.findInOrganisation(id, auth);
    return Json.of("data", this.view(shelf));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "shelf_create");
    String code = ShelfController.str(body.get("code"));
    Long roomId = ShelfController.parseId(body.get("room_id"));
    Validator v =
        Validator.begin()
            .require("code", code, "The code field is required.")
            .max("code", code, 30, "code");
    if (roomId == null) {
      v.add("room_id", "The room id field is required.");
    }
    v.validate();
    if (roomId != null
        && !this.roomInOrganisation(roomId, auth.user().getCurrentOrganisationId())) {
      throw ValidationException.single(
          "room_id", "La salle n'appartient pas \u00e0 votre organisation.");
    }
    Shelf shelf = new Shelf();
    shelf.setCode(code);
    shelf.setObservation(ShelfController.str(body.get("observation")));
    shelf.setFace(ShelfController.doubleOf(body.get("face")));
    shelf.setEar(ShelfController.doubleOf(body.get("ear")));
    shelf.setShelf(ShelfController.doubleOf(body.get("shelf")));
    shelf.setShelfLength(ShelfController.doubleOf(body.get("shelf_length")));
    shelf.setRoomId(roomId);
    shelf.setCreatorId(auth.user().getId());
    this.repository.save(shelf);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/shelves/" + shelf.getId()}))
        .body(Json.of("data", this.view(shelf)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "shelf_update");
    Shelf shelf = this.findInOrganisation(id, auth);
    if (body.containsKey("code")) {
      shelf.setCode(ShelfController.str(body.get("code")));
    }
    if (body.containsKey("observation")) {
      shelf.setObservation(ShelfController.str(body.get("observation")));
    }
    if (body.containsKey("face")) {
      shelf.setFace(ShelfController.doubleOf(body.get("face")));
    }
    if (body.containsKey("ear")) {
      shelf.setEar(ShelfController.doubleOf(body.get("ear")));
    }
    if (body.containsKey("shelf")) {
      shelf.setShelf(ShelfController.doubleOf(body.get("shelf")));
    }
    if (body.containsKey("shelf_length")) {
      shelf.setShelfLength(ShelfController.doubleOf(body.get("shelf_length")));
    }
    if (body.containsKey("room_id")) {
      shelf.setRoomId(ShelfController.parseId(body.get("room_id")));
    }
    this.repository.save(shelf);
    return Json.of("data", this.view(shelf));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "shelf_delete");
    Shelf shelf = this.findInOrganisation(id, auth);
    this.repository.delete(shelf);
    return ResponseEntity.noContent().build();
  }

  private Shelf findInOrganisation(Long id, AuthenticatedUser auth) {
    return (Shelf)
        this.repository
            .findAll(
                OrgScope.shelvesInOrganisation(auth.user().getCurrentOrganisationId())
                    .and((root, q, cb) -> cb.equal(root.get("id"), id)))
            .stream()
            .findFirst()
            .orElseThrow(() -> ApiException.notFound());
  }

  private boolean roomInOrganisation(Long roomId, Long organisationId) {
    return this.roomRepository
        .findAll(
            OrgScope.roomsInOrganisation(organisationId)
                .and(
                    (Specification & Serializable)
                        (root, q, cb) -> cb.equal((Expression) root.get("id"), (Object) roomId)))
        .stream()
        .findFirst()
        .isPresent();
  }

  private Map<String, Object> view(Shelf s) {
    double totalCapacity =
        (s.getFace() != null ? s.getFace() : 0.0)
            * (s.getEar() != null ? s.getEar() : 0.0)
            * (s.getShelf() != null ? s.getShelf() : 0.0);
    long occupied =
        (Long)
            this.em
                .createQuery("select count(c) from Container c where c.shelveId = :id", Long.class)
                .setParameter("id", (Object) s.getId())
                .getSingleResult();
    long available = (long) Math.max(0.0, totalCapacity - (double) occupied);
    double occupancyPct =
        totalCapacity > 0.0
            ? (double) Math.round((double) occupied / totalCapacity * 100.0 * 10.0) / 10.0
            : 0.0;
    double volumetry =
        (s.getFace() != null ? s.getFace() : 0.0)
            * (s.getEar() != null ? s.getEar() : 0.0)
            * (s.getShelf() != null ? s.getShelf() : 0.0)
            * (s.getShelfLength() != null ? s.getShelfLength() : 0.0)
            / 100.0;
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", s.getId());
    map.put("code", s.getCode());
    map.put("observation", s.getObservation());
    map.put("face", s.getFace());
    map.put("ear", s.getEar());
    map.put("shelf", s.getShelf());
    map.put("shelf_length", s.getShelfLength());
    map.put("room_id", s.getRoomId());
    map.put("creator_id", s.getCreatorId());
    map.put("total_capacity", totalCapacity);
    map.put("occupied_spots", occupied);
    map.put("available_spots", available);
    map.put("occupancy_percentage", occupancyPct);
    map.put("volumetry_ml", volumetry);
    map.put("created_at", Json.timestamp(s.getCreatedAt()));
    map.put("updated_at", Json.timestamp(s.getUpdatedAt()));
    return map;
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
