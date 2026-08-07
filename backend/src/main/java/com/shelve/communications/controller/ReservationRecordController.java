package com.shelve.communications.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.communications.entity.Reservation;
import com.shelve.communications.entity.ReservationRecord;
import com.shelve.communications.repository.ReservationRecordRepository;
import com.shelve.communications.repository.ReservationRepository;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.time.LocalDate;
import java.time.format.DateTimeParseException;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
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
@RequestMapping(value = {"/api/v1/reservations/{reservationId}/records"})
public class ReservationRecordController {
  private final ReservationRecordRepository repository;
  private final ReservationRepository reservationRepository;

  public ReservationRecordController(
      ReservationRecordRepository repository, ReservationRepository reservationRepository) {
    this.repository = repository;
    this.reservationRepository = reservationRepository;
  }

  @GetMapping
  public Map<String, Object> index(@PathVariable Long reservationId, HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reservation_record_viewAny");
    this.resolve(reservationId, auth);
    List<Map<String, Object>> items =
        this.repository
            .findAll((Specification<ReservationRecord>) (root, q, cb) -> cb.equal(root.get("reservationId"), reservationId))
            .stream()
            .map(this::mapper)
            .toList();
    return Json.of("data", items);
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(
      @PathVariable Long reservationId, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reservation_record_create");
    this.resolve(reservationId, auth);
    ReservationRecord record = new ReservationRecord();
    record.setReservationId(reservationId);
    record.setRecordId(ReservationRecordController.longOf(body.get("record_id")));
    if (body.containsKey("is_original")) {
      record.setIsOriginal(ReservationRecordController.bool(body.get("is_original")));
    }
    record.setReservationDate(
        body.get("reservation_date") != null
            ? ReservationRecordController.dateOf(body.get("reservation_date"))
            : LocalDate.now());
    record.setOperatorId(auth.user().getId());
    if (body.containsKey("communication_id")) {
      record.setCommunicationId(ReservationRecordController.longOf(body.get("communication_id")));
    }
    this.repository.save(record);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header(
                    "Location",
                    new String[] {
                      "/api/v1/reservations/" + reservationId + "/records/" + record.getId()
                    }))
        .body(Json.of("data", this.mapper(record)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(
      @PathVariable Long reservationId,
      @PathVariable Long id,
      @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reservation_record_update");
    this.resolve(reservationId, auth);
    ReservationRecord record = this.find(reservationId, id);
    if (body.containsKey("record_id")) {
      record.setRecordId(ReservationRecordController.longOf(body.get("record_id")));
    }
    if (body.containsKey("is_original")) {
      record.setIsOriginal(ReservationRecordController.bool(body.get("is_original")));
    }
    if (body.containsKey("reservation_date")) {
      record.setReservationDate(ReservationRecordController.dateOf(body.get("reservation_date")));
    }
    if (body.containsKey("communication_id")) {
      record.setCommunicationId(ReservationRecordController.longOf(body.get("communication_id")));
    }
    this.repository.save(record);
    return Json.of("data", this.mapper(record));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long reservationId, @PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "reservation_record_delete");
    this.resolve(reservationId, auth);
    ReservationRecord record = this.find(reservationId, id);
    this.repository.delete(record);
    return ResponseEntity.noContent().build();
  }

  private ReservationRecord find(Long reservationId, Long id) {
    return (ReservationRecord)
        this.repository
            .findAll((Specification<ReservationRecord>) (root, q, cb) -> cb.and(
                            (Expression)
                                cb.equal(
                                    (Expression) root.get("reservationId"), (Object) reservationId),
                            (Expression) cb.equal((Expression) root.get("id"), (Object) id))).stream().findFirst()
            .orElseThrow(() -> ApiException.notFound());
  }

  private Reservation resolve(Long id, AuthenticatedUser auth) {
    Long orgId = auth.user().getCurrentOrganisationId();
    return (Reservation)
        this.reservationRepository
            .findAll((Specification<Reservation>) (root, q, cb) -> cb.and(
                            (Expression) cb.equal((Expression) root.get("id"), (Object) id),
                            (Expression)
                                cb.or(
                                    (Expression)
                                        cb.equal(
                                            (Expression) root.get("operatorOrganisationId"),
                                            (Object) orgId),
                                    (Expression)
                                        cb.equal(
                                            (Expression) root.get("userOrganisationId"),
                                            (Object) orgId)))).stream().findFirst()
            .orElseThrow(() -> ApiException.notFound());
  }

  private Map<String, Object> mapper(ReservationRecord r) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("reservation_id", r.getReservationId());
    map.put("record_id", r.getRecordId());
    map.put("is_original", r.getIsOriginal() != null && r.getIsOriginal() != false);
    map.put(
        "reservation_date",
        r.getReservationDate() != null ? r.getReservationDate().toString() : null);
    map.put("operator_id", r.getOperatorId());
    map.put("communication_id", r.getCommunicationId());
    map.put("created_at", Json.timestamp(r.getCreatedAt()));
    map.put("updated_at", Json.timestamp(r.getUpdatedAt()));
    return map;
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  private static Boolean bool(Object value) {
    Boolean b;
    return value == null
        ? null
        : (value instanceof Boolean
            ? (b = (Boolean) value)
            : Boolean.valueOf(Boolean.parseBoolean(String.valueOf(value))));
  }

  private static Long longOf(Object value) {
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

  private static LocalDate dateOf(Object value) {
    if (value == null) {
      return null;
    }
    try {
      return LocalDate.parse(String.valueOf(value));
    } catch (DateTimeParseException e) {
      return null;
    }
  }
}
