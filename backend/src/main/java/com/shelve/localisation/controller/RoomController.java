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
import com.shelve.localisation.service.OrgScope;
import com.shelve.localisation.entity.OrganisationRoom;
import com.shelve.localisation.entity.OrganisationRoomId;
import com.shelve.localisation.repository.OrganisationRoomRepository;
import com.shelve.localisation.entity.Room;
import com.shelve.localisation.repository.RoomRepository;
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
@RequestMapping(value = {"/api/v1/rooms"})
public class RoomController {
  private static final List<String> FILTERABLE =
      List.of("id", "code", "name", "visibility", "type", "floor_id", "created_at", "updated_at");
  private static final List<String> SORTABLE = FILTERABLE;
  private static final List<String> INCLUDABLE = List.of("floor", "shelves", "organisations");
  private final RoomRepository repository;
  private final OrganisationRoomRepository orgRoomRepository;
  private final EntityManager em;

  public RoomController(
      RoomRepository repository, OrganisationRoomRepository orgRoomRepository, EntityManager em) {
    this.repository = repository;
    this.orgRoomRepository = orgRoomRepository;
    this.em = em;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "room_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    Specification<Room> orgScope =
        OrgScope.roomsInOrganisation(auth.user().getCurrentOrganisationId());
    Specification spec = orgScope.and(Filters.of(qp.getFilters(), Room.class));
    return Paging.page(this.repository, spec, qp, SORTABLE, "id", request, this::view);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "room_view");
    Room room = this.findInOrganisation(id, auth);
    return Json.of("data", this.view(room));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "room_create");
    String code = RoomController.str(body.get("code"));
    String name = RoomController.str(body.get("name"));
    String visibility = RoomController.str(body.get("visibility"));
    String type = RoomController.str(body.get("type"));
    Validator v =
        Validator.begin()
            .require("code", code, "The code field is required.")
            .max("code", code, 10, "code")
            .require("name", name, "The name field is required.")
            .max("name", name, 100, "name")
            .require("visibility", visibility, "The visibility field is required.")
            .require("type", type, "The type field is required.");
    if (visibility != null && !List.of("public", "private", "inherit").contains(visibility)) {
      v.add("visibility", "The selected visibility is invalid.");
    }
    if (type != null && !List.of("archives", "producer").contains(type)) {
      v.add("type", "The selected type is invalid.");
    }
    if (RoomController.parseId(body.get("floor_id")) == null) {
      v.add("floor_id", "The floor id field is required.");
    }
    v.validate();
    Room room = new Room();
    room.setCode(code);
    room.setName(name);
    room.setDescription(RoomController.str(body.get("description")));
    room.setVisibility(visibility);
    room.setType(type);
    room.setFloorId(RoomController.parseId(body.get("floor_id")));
    room.setCreatorId(auth.user().getId());
    this.repository.save(room);
    OrganisationRoom pivot = new OrganisationRoom();
    pivot.setId(new OrganisationRoomId(room.getId(), auth.user().getCurrentOrganisationId()));
    this.orgRoomRepository.save(pivot);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/rooms/" + room.getId()}))
        .body(Json.of("data", this.view(room)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "room_update");
    Room room = this.findInOrganisation(id, auth);
    if (body.containsKey("code")) {
      room.setCode(RoomController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      room.setName(RoomController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      room.setDescription(RoomController.str(body.get("description")));
    }
    if (body.containsKey("visibility")) {
      room.setVisibility(RoomController.str(body.get("visibility")));
    }
    if (body.containsKey("type")) {
      room.setType(RoomController.str(body.get("type")));
    }
    if (body.containsKey("floor_id")) {
      room.setFloorId(RoomController.parseId(body.get("floor_id")));
    }
    this.repository.save(room);
    return Json.of("data", this.view(room));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "room_delete");
    Room room = this.findInOrganisation(id, auth);
    this.repository.delete(room);
    return ResponseEntity.noContent().build();
  }

  private Room findInOrganisation(Long id, AuthenticatedUser auth) {
    return (Room)
        this.repository
            .findAll(
                OrgScope.roomsInOrganisation(auth.user().getCurrentOrganisationId())
                    .and((root, q, cb) -> cb.equal(root.get("id"), id)))
            .stream()
            .findFirst()
            .orElseThrow(() -> ApiException.notFound());
  }

  private Map<String, Object> view(Room r) {
    long shelvesCount =
        (Long)
            this.em
                .createQuery("select count(s) from Shelf s where s.roomId = :id", Long.class)
                .setParameter("id", (Object) r.getId())
                .getSingleResult();
    String effectiveVisibility = this.computeEffectiveVisibility(r);
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("code", r.getCode());
    map.put("name", r.getName());
    map.put("description", r.getDescription());
    map.put("visibility", r.getVisibility());
    map.put("effective_visibility", effectiveVisibility);
    map.put("is_visible", "public".equals(r.getVisibility()));
    map.put("type", r.getType());
    map.put("floor_id", r.getFloorId());
    map.put("shelves_count", shelvesCount);
    map.put("creator_id", r.getCreatorId());
    map.put("created_at", Json.timestamp(r.getCreatedAt()));
    map.put("updated_at", Json.timestamp(r.getUpdatedAt()));
    return map;
  }

  private String computeEffectiveVisibility(Room r) {
    String buildingVisibility;
    Long buildingId;
    if ("inherit".equals(r.getVisibility())
        && (buildingId =
                (Long)
                    this.em
                        .createQuery(
                            "select f.buildingId from Floor f where f.id = :id", Long.class)
                        .setParameter("id", (Object) r.getFloorId())
                        .getResultStream()
                        .findFirst()
                        .orElse(null))
            != null
        && (buildingVisibility =
                (String)
                    this.em
                        .createQuery(
                            "select b.visibility from Building b where b.id = :id", String.class)
                        .setParameter("id", (Object) buildingId)
                        .getResultStream()
                        .findFirst()
                        .orElse(null))
            != null) {
      return buildingVisibility;
    }
    return r.getVisibility();
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
