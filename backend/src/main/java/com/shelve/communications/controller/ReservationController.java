package com.shelve.communications.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
import com.shelve.communications.entity.Reservation;
import com.shelve.communications.repository.ReservationRepository;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.time.LocalDate;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping(value = {"/api/v1/reservations"})
public class ReservationController extends GenericCrudController<Reservation> {
  private final ReservationRepository reservationRepository;

  public ReservationController(ReservationRepository reservationRepository) {
    this.reservationRepository = reservationRepository;
  }

  @Override
  protected Class<Reservation> entityClass() {
    return Reservation.class;
  }

  @Override
  protected JpaSpecificationExecutor<Reservation> repository() {
    return this.reservationRepository;
  }

  @Override
  protected String resource() {
    return "reservation";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "id",
        "code",
        "name",
        "operator_organisation_id",
        "user_id",
        "user_organisation_id",
        "status",
        "communication_id",
        "created_at",
        "updated_at");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected Reservation newEntity() {
    return new Reservation();
  }

  @Override
  protected String location(Reservation entity) {
    return "/api/v1/reservations/" + entity.getId();
  }

  @Override
  protected Specification<Reservation> scope(AuthenticatedUser auth) {
    Long orgId = auth.user().getCurrentOrganisationId();
    return (Specification & Serializable)
        (root, query, cb) ->
            cb.or(
                (Expression)
                    cb.equal((Expression) root.get("operatorOrganisationId"), (Object) orgId),
                (Expression) cb.equal((Expression) root.get("userOrganisationId"), (Object) orgId));
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    String name = ReservationController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 200, "name");
    if (ReservationController.longOf(body.get("user_id")) == null) {
      v.add("user_id", "The user id field is required.");
    }
    if (ReservationController.longOf(body.get("user_organisation_id")) == null) {
      v.add("user_organisation_id", "The user organisation id field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Reservation r) {
    AuthenticatedUser auth = CurrentUser.get();
    r.setCode(this.generateCode("R"));
    r.setName(ReservationController.str(body.get("name")));
    r.setContent(ReservationController.str(body.get("content")));
    r.setUserId(ReservationController.longOf(body.get("user_id")));
    r.setUserOrganisationId(ReservationController.longOf(body.get("user_organisation_id")));
    r.setStatus(ReservationController.str(body.get("status")));
    r.setCommunicationId(ReservationController.longOf(body.get("communication_id")));
    r.setOperatorId(auth.user().getId());
    r.setOperatorOrganisationId(auth.user().getCurrentOrganisationId());
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Reservation r) {
    if (body.containsKey("name")) {
      r.setName(ReservationController.str(body.get("name")));
    }
    if (body.containsKey("content")) {
      r.setContent(ReservationController.str(body.get("content")));
    }
    if (body.containsKey("user_id")) {
      r.setUserId(ReservationController.longOf(body.get("user_id")));
    }
    if (body.containsKey("user_organisation_id")) {
      r.setUserOrganisationId(ReservationController.longOf(body.get("user_organisation_id")));
    }
    if (body.containsKey("status")) {
      r.setStatus(ReservationController.str(body.get("status")));
    }
    if (body.containsKey("communication_id")) {
      r.setCommunicationId(ReservationController.longOf(body.get("communication_id")));
    }
    if (body.containsKey("return_date")) {
      r.setReturnDate(ReservationController.dateOf(body.get("return_date")));
    }
    if (body.containsKey("return_effective")) {
      r.setReturnEffective(ReservationController.dateOf(body.get("return_effective")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Reservation r) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("code", r.getCode());
    map.put("name", r.getName());
    map.put("content", r.getContent());
    map.put("operator_id", r.getOperatorId());
    map.put("operator_organisation_id", r.getOperatorOrganisationId());
    map.put("user_id", r.getUserId());
    map.put("user_organisation_id", r.getUserOrganisationId());
    map.put("status", r.getStatus());
    map.put("communication_id", r.getCommunicationId());
    map.put("return_date", r.getReturnDate() != null ? r.getReturnDate().toString() : null);
    map.put(
        "return_effective",
        r.getReturnEffective() != null ? r.getReturnEffective().toString() : null);
    map.put("created_at", Json.timestamp(r.getCreatedAt()));
    map.put("updated_at", Json.timestamp(r.getUpdatedAt()));
    return map;
  }

  private String generateCode(String prefix) {
    String year = String.valueOf(LocalDate.now().getYear());
    long count = this.reservationRepository.count();
    return prefix + year + String.format("%04d", count + 1L);
  }

  @Override
  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    return super.index(request);
  }

  @Override
  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    return super.show(id);
  }

  @Override
  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    return super.store(body);
  }

  @Override
  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    return super.update(id, body);
  }

  @Override
  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    return super.destroy(id);
  }
}
