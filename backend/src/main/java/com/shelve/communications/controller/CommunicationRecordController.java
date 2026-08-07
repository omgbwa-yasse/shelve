package com.shelve.communications.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.communications.entity.Communication;
import com.shelve.communications.entity.CommunicationRecord;
import com.shelve.communications.repository.CommunicationRecordRepository;
import com.shelve.communications.repository.CommunicationRepository;
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
@RequestMapping(value = {"/api/v1/communications/{communicationId}/records"})
public class CommunicationRecordController {
  private final CommunicationRecordRepository repository;
  private final CommunicationRepository communicationRepository;

  public CommunicationRecordController(
      CommunicationRecordRepository repository, CommunicationRepository communicationRepository) {
    this.repository = repository;
    this.communicationRepository = communicationRepository;
  }

  @GetMapping
  public Map<String, Object> index(@PathVariable Long communicationId, HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "communication_record_viewAny");
    this.resolve(communicationId, auth);
    List<Map<String, Object>> items =
        this.repository
            .findAll(
                (Specification<CommunicationRecord>)
                    (root, q, cb) -> cb.equal(root.get("communicationId"), communicationId))
            .stream()
            .map(this::mapper)
            .toList();
    return Json.of("data", items);
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(
      @PathVariable Long communicationId, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "communication_record_create");
    this.resolve(communicationId, auth);
    CommunicationRecord record = new CommunicationRecord();
    record.setCommunicationId(communicationId);
    record.setRecordId(CommunicationRecordController.longOf(body.get("record_id")));
    record.setContent(CommunicationRecordController.str(body.get("content")));
    if (body.containsKey("is_original")) {
      record.setIsOriginal(CommunicationRecordController.bool(body.get("is_original")));
    }
    record.setReturnDate(
        body.get("return_date") != null
            ? CommunicationRecordController.dateOf(body.get("return_date"))
            : LocalDate.now().plusDays(14L));
    if (body.containsKey("return_effective")) {
      record.setReturnEffective(CommunicationRecordController.dateOf(body.get("return_effective")));
    }
    record.setOperatorId(auth.user().getId());
    this.repository.save(record);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header(
                    "Location",
                    new String[] {
                      "/api/v1/communications/" + communicationId + "/records/" + record.getId()
                    }))
        .body(Json.of("data", this.mapper(record)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(
      @PathVariable Long communicationId,
      @PathVariable Long id,
      @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "communication_record_update");
    this.resolve(communicationId, auth);
    CommunicationRecord record = this.find(communicationId, id);
    if (body.containsKey("record_id")) {
      record.setRecordId(CommunicationRecordController.longOf(body.get("record_id")));
    }
    if (body.containsKey("content")) {
      record.setContent(CommunicationRecordController.str(body.get("content")));
    }
    if (body.containsKey("is_original")) {
      record.setIsOriginal(CommunicationRecordController.bool(body.get("is_original")));
    }
    if (body.containsKey("return_date")) {
      record.setReturnDate(CommunicationRecordController.dateOf(body.get("return_date")));
    }
    if (body.containsKey("return_effective")) {
      record.setReturnEffective(CommunicationRecordController.dateOf(body.get("return_effective")));
    }
    this.repository.save(record);
    return Json.of("data", this.mapper(record));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long communicationId, @PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "communication_record_delete");
    this.resolve(communicationId, auth);
    CommunicationRecord record = this.find(communicationId, id);
    this.repository.delete(record);
    return ResponseEntity.noContent().build();
  }

  private CommunicationRecord find(Long communicationId, Long id) {
    return this.repository
        .findAll(
            (Specification<CommunicationRecord>)
                (root, q, cb) ->
                    cb.and(
                        cb.equal(root.get("communicationId"), communicationId),
                        cb.equal(root.get("id"), id)))
        .stream()
        .findFirst()
        .orElseThrow(() -> ApiException.notFound());
  }

  private Communication resolve(Long id, AuthenticatedUser auth) {
    Long orgId = auth.user().getCurrentOrganisationId();
    return (Communication)
        this.communicationRepository
            .findAll(
                (Specification<Communication>)
                    (root, q, cb) ->
                        cb.and(
                            cb.equal(root.get("id"), id),
                            cb.or(
                                cb.equal(root.get("operatorOrganisationId"), orgId),
                                cb.equal(root.get("userOrganisationId"), orgId))))
            .stream()
            .findFirst()
            .orElseThrow(() -> ApiException.notFound());
  }

  private Map<String, Object> mapper(CommunicationRecord r) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("communication_id", r.getCommunicationId());
    map.put("record_id", r.getRecordId());
    map.put("content", r.getContent());
    map.put("is_original", r.getIsOriginal() != null && r.getIsOriginal() != false);
    map.put("return_date", r.getReturnDate() != null ? r.getReturnDate().toString() : null);
    map.put(
        "return_effective",
        r.getReturnEffective() != null ? r.getReturnEffective().toString() : null);
    map.put("operator_id", r.getOperatorId());
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
