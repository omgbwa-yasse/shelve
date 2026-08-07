package com.shelve.slips.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.slips.entity.SlipRecord;
import com.shelve.slips.repository.SlipRecordRepository;
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
@RequestMapping(value = {"/api/v1/slips/{slipId}/records"})
public class SlipRecordController {
  private final SlipRecordRepository repository;

  public SlipRecordController(SlipRecordRepository repository) {
    this.repository = repository;
  }

  @GetMapping
  public Map<String, Object> index(@PathVariable Long slipId, HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "slip_record_viewAny");
    this.ensureSlipInScope(slipId, auth);
    List<Map<String, Object>> items =
        this.repository
            .findAll((Specification<SlipRecord>) (root, q, cb) -> cb.equal(root.get("slipId"), slipId))
            .stream()
            .map(this::mapper)
            .toList();
    return Json.of("data", items);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long slipId, @PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "slip_record_view");
    this.ensureSlipInScope(slipId, auth);
    SlipRecord record =
        this.repository
            .findById(id)
            .filter(r -> r.getSlipId().equals(slipId))
            .orElseThrow(() -> ApiException.notFound());
    return Json.of("data", this.mapper(record));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(
      @PathVariable Long slipId, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "slip_record_create");
    this.ensureSlipInScope(slipId, auth);
    String code = SlipRecordController.str(body.get("code"));
    String name = SlipRecordController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("code", code, "The code field is required.")
            .max("code", code, 10, "code")
            .require("name", name, "The name field is required.");
    if (SlipRecordController.longOf(body.get("level_id")) == null) {
      v.add("level_id", "The level id field is required.");
    }
    if (SlipRecordController.longOf(body.get("support_id")) == null) {
      v.add("support_id", "The support id field is required.");
    }
    if (SlipRecordController.longOf(body.get("activity_id")) == null) {
      v.add("activity_id", "The activity id field is required.");
    }
    v.validate();
    SlipRecord record = new SlipRecord();
    record.setSlipId(slipId);
    record.setCode(code);
    record.setName(name);
    record.setDateFormat(SlipRecordController.str(body.get("date_format")));
    record.setDateStart(SlipRecordController.str(body.get("date_start")));
    record.setDateEnd(SlipRecordController.str(body.get("date_end")));
    record.setDateExact(SlipRecordController.dateOf(body.get("date_exact")));
    record.setContent(SlipRecordController.str(body.get("content")));
    record.setLevelId(SlipRecordController.longOf(body.get("level_id")));
    record.setWidth(SlipRecordController.doubleOf(body.get("width")));
    record.setWidthDescription(SlipRecordController.str(body.get("width_description")));
    record.setSupportId(SlipRecordController.longOf(body.get("support_id")));
    record.setActivityId(SlipRecordController.longOf(body.get("activity_id")));
    record.setCreatorId(auth.user().getId());
    this.repository.save(record);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header(
                    "Location",
                    new String[] {"/api/v1/slips/" + slipId + "/records/" + record.getId()}))
        .body(Json.of("data", this.mapper(record)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(
      @PathVariable Long slipId, @PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "slip_record_update");
    this.ensureSlipInScope(slipId, auth);
    SlipRecord record =
        this.repository
            .findById(id)
            .filter(r -> r.getSlipId().equals(slipId))
            .orElseThrow(() -> ApiException.notFound());
    if (body.containsKey("code")) {
      record.setCode(SlipRecordController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      record.setName(SlipRecordController.str(body.get("name")));
    }
    if (body.containsKey("date_format")) {
      record.setDateFormat(SlipRecordController.str(body.get("date_format")));
    }
    if (body.containsKey("date_start")) {
      record.setDateStart(SlipRecordController.str(body.get("date_start")));
    }
    if (body.containsKey("date_end")) {
      record.setDateEnd(SlipRecordController.str(body.get("date_end")));
    }
    if (body.containsKey("date_exact")) {
      record.setDateExact(SlipRecordController.dateOf(body.get("date_exact")));
    }
    if (body.containsKey("content")) {
      record.setContent(SlipRecordController.str(body.get("content")));
    }
    if (body.containsKey("level_id")) {
      record.setLevelId(SlipRecordController.longOf(body.get("level_id")));
    }
    if (body.containsKey("width")) {
      record.setWidth(SlipRecordController.doubleOf(body.get("width")));
    }
    if (body.containsKey("width_description")) {
      record.setWidthDescription(SlipRecordController.str(body.get("width_description")));
    }
    if (body.containsKey("support_id")) {
      record.setSupportId(SlipRecordController.longOf(body.get("support_id")));
    }
    if (body.containsKey("activity_id")) {
      record.setActivityId(SlipRecordController.longOf(body.get("activity_id")));
    }
    this.repository.save(record);
    return Json.of("data", this.mapper(record));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long slipId, @PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "slip_record_delete");
    this.ensureSlipInScope(slipId, auth);
    SlipRecord record =
        this.repository
            .findById(id)
            .filter(r -> r.getSlipId().equals(slipId))
            .orElseThrow(() -> ApiException.notFound());
    this.repository.delete(record);
    return ResponseEntity.noContent().build();
  }

  private void ensureSlipInScope(Long slipId, AuthenticatedUser auth) {
    if (slipId == null) {
      throw ApiException.notFound();
    }
  }

  private Map<String, Object> mapper(SlipRecord r) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("slip_id", r.getSlipId());
    map.put("code", r.getCode());
    map.put("name", r.getName());
    map.put("date_format", r.getDateFormat());
    map.put("date_start", r.getDateStart());
    map.put("date_end", r.getDateEnd());
    map.put("date_exact", r.getDateExact() != null ? r.getDateExact().toString() : null);
    map.put("content", r.getContent());
    map.put("level_id", r.getLevelId());
    map.put("width", r.getWidth());
    map.put("width_description", r.getWidthDescription());
    map.put("support_id", r.getSupportId());
    map.put("activity_id", r.getActivityId());
    map.put("creator_id", r.getCreatorId());
    map.put("created_at", Json.timestamp(r.getCreatedAt()));
    map.put("updated_at", Json.timestamp(r.getUpdatedAt()));
    return map;
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
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
