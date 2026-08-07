package com.shelve.records.controller;

import com.fasterxml.jackson.databind.ObjectMapper;
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
import com.shelve.records.entity.Record;
import com.shelve.records.entity.RecordLevel;
import com.shelve.records.repository.RecordLevelRepository;
import com.shelve.records.repository.RecordRepository;
import com.shelve.records.entity.RecordStatus;
import com.shelve.records.repository.RecordStatusRepository;
import com.shelve.records.repository.RecordTypeRepository;
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
@RequestMapping(value = {"/api/v1/records"})
public class RecordController {
  private static final List<String> FILTERABLE =
      List.of(
          "id",
          "code",
          "name",
          "type_id",
          "level_id",
          "status_id",
          "activity_id",
          "parent_id",
          "organisation_id",
          "creator_id",
          "access_level",
          "confidentiality_id",
          "access_limit_id",
          "start_date",
          "end_date",
          "date_exact",
          "date_format",
          "is_current_version",
          "unavailable",
          "is_essential",
          "created_at",
          "updated_at");
  private static final List<String> SORTABLE =
      List.of(
          "id",
          "code",
          "name",
          "type_id",
          "level_id",
          "status_id",
          "activity_id",
          "parent_id",
          "organisation_id",
          "creator_id",
          "access_level",
          "start_date",
          "end_date",
          "date_exact",
          "date_format",
          "created_at",
          "updated_at");
  private static final List<String> INCLUDABLE =
      List.of(
          "type", "level", "status", "activity", "organisation", "creator", "parent", "children");
  private final RecordRepository recordRepository;
  private final RecordTypeRepository typeRepository;
  private final RecordLevelRepository levelRepository;
  private final RecordStatusRepository statusRepository;
  private final ObjectMapper objectMapper;

  public RecordController(
      RecordRepository recordRepository,
      RecordTypeRepository typeRepository,
      RecordLevelRepository levelRepository,
      RecordStatusRepository statusRepository,
      ObjectMapper objectMapper) {
    this.recordRepository = recordRepository;
    this.typeRepository = typeRepository;
    this.levelRepository = levelRepository;
    this.statusRepository = statusRepository;
    this.objectMapper = objectMapper;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, INCLUDABLE);
    Specification spec =
        this.scope(auth)
            .and(
                (Specification & Serializable)
                    (root, q, cb) ->
                        cb.equal((Expression) root.get("isCurrentVersion"), (Object) true))
            .and(Filters.of(qp.getFilters(), Record.class));
    return Paging.page(
        this.recordRepository, spec, qp, SORTABLE, "updatedAt", request, this::mapper);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_view");
    Record record = this.findInScope(id, auth);
    return Json.of("data", this.mapper(record));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_create");
    String name = RecordController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 191, "name");
    v.validate();
    Record record = new Record();
    record.setName(name);
    record.setCode(RecordController.str(body.get("code")));
    record.setDescription(RecordController.str(body.get("description")));
    record.setTypeId(RecordController.longOf(body.get("type_id")));
    record.setLevelId(RecordController.longOf(body.get("level_id")));
    record.setStatusId(RecordController.longOf(body.get("status_id")));
    record.setActivityId(RecordController.longOf(body.get("activity_id")));
    record.setParentId(RecordController.longOf(body.get("parent_id")));
    record.setAssignedTo(RecordController.longOf(body.get("assigned_to")));
    record.setConfidentialityId(RecordController.longOf(body.get("confidentiality_id")));
    record.setAccessLimitId(RecordController.longOf(body.get("access_limit_id")));
    record.setAccessLevel(RecordController.str(body.get("access_level")));
    if (body.containsKey("requires_approval")) {
      record.setRequiresApproval(RecordController.bool(body.get("requires_approval")));
    }
    record.setStartDate(RecordController.dateOf(body.get("start_date")));
    record.setEndDate(RecordController.dateOf(body.get("end_date")));
    record.setDateExact(RecordController.dateOf(body.get("date_exact")));
    record.setDateFormat(RecordController.str(body.get("date_format")));
    record.setOpeningDate(RecordController.dateOf(body.get("opening_date")));
    record.setClosingDate(RecordController.dateOf(body.get("closing_date")));
    if (body.containsKey("metadata")) {
      record.setMetadata(this.toJson(body.get("metadata")));
    }
    if (record.getLevelId() == null) {
      record.setLevelId(ensureLevel());
    }
    if (record.getStatusId() == null) {
      record.setStatusId(ensureStatus());
    }
    if (record.getAccessLevel() == null || record.getAccessLevel().isBlank()) {
      record.setAccessLevel("internal");
    }
    record.setOrganisationId(auth.user().getCurrentOrganisationId());
    record.setCreatorId(auth.user().getId());
    record.setVersionNumber(1);
    record.setIsCurrentVersion(true);
    if (record.getCode() == null || record.getCode().isBlank()) {
      if (record.getTypeId() != null) {
        record.setCode(
            this.typeRepository
                .findById(record.getTypeId())
                .map(
                    t ->
                        (t.getCodePrefix() != null ? t.getCodePrefix() : t.getCode())
                            + this.recordRepository.count())
                .orElse(null));
      }
      if (record.getCode() == null) {
        throw ValidationException.single(
            "code", "Le code est requis lorsque aucun type n'est fourni.");
      }
    }
    this.recordRepository.save(record);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/records/" + record.getId()}))
        .body(Json.of("data", this.mapper(record)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_update");
    Record record = this.findInScope(id, auth);
    if (body.containsKey("name")) {
      record.setName(RecordController.str(body.get("name")));
    }
    if (body.containsKey("code")) {
      record.setCode(RecordController.str(body.get("code")));
    }
    if (body.containsKey("description")) {
      record.setDescription(RecordController.str(body.get("description")));
    }
    if (body.containsKey("type_id")) {
      record.setTypeId(RecordController.longOf(body.get("type_id")));
    }
    if (body.containsKey("level_id")) {
      record.setLevelId(RecordController.longOf(body.get("level_id")));
    }
    if (body.containsKey("status_id")) {
      record.setStatusId(RecordController.longOf(body.get("status_id")));
    }
    if (body.containsKey("activity_id")) {
      record.setActivityId(RecordController.longOf(body.get("activity_id")));
    }
    if (body.containsKey("parent_id")) {
      record.setParentId(
          body.get("parent_id") == null ? null : RecordController.longOf(body.get("parent_id")));
    }
    if (body.containsKey("assigned_to")) {
      record.setAssignedTo(RecordController.longOf(body.get("assigned_to")));
    }
    if (body.containsKey("access_level")) {
      record.setAccessLevel(RecordController.str(body.get("access_level")));
    }
    if (body.containsKey("requires_approval")) {
      record.setRequiresApproval(RecordController.bool(body.get("requires_approval")));
    }
    if (body.containsKey("confidentiality_id")) {
      record.setConfidentialityId(RecordController.longOf(body.get("confidentiality_id")));
    }
    if (body.containsKey("access_limit_id")) {
      record.setAccessLimitId(RecordController.longOf(body.get("access_limit_id")));
    }
    if (body.containsKey("start_date")) {
      record.setStartDate(RecordController.dateOf(body.get("start_date")));
    }
    if (body.containsKey("end_date")) {
      record.setEndDate(RecordController.dateOf(body.get("end_date")));
    }
    if (body.containsKey("date_exact")) {
      record.setDateExact(RecordController.dateOf(body.get("date_exact")));
    }
    if (body.containsKey("date_format")) {
      record.setDateFormat(RecordController.str(body.get("date_format")));
    }
    if (body.containsKey("opening_date")) {
      record.setOpeningDate(RecordController.dateOf(body.get("opening_date")));
    }
    if (body.containsKey("closing_date")) {
      record.setClosingDate(RecordController.dateOf(body.get("closing_date")));
    }
    if (body.containsKey("metadata")) {
      record.setMetadata(this.toJson(body.get("metadata")));
    }
    this.recordRepository.save(record);
    return Json.of("data", this.mapper(record));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_delete");
    Record record = this.findInScope(id, auth);
    this.recordRepository.delete(record);
    return ResponseEntity.noContent().build();
  }

  @PatchMapping(value = {"/{id}/status"})
  public Map<String, Object> status(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_update");
    Record record = this.findInScope(id, auth);
    Long statusId = RecordController.longOf(body.get("status_id"));
    if (statusId == null) {
      throw ValidationException.single("status_id", "The status id field is required.");
    }
    record.setStatusId(statusId);
    this.recordRepository.save(record);
    return Json.of("data", this.mapper(record));
  }

  private Specification<Record> scope(AuthenticatedUser auth) {
    Long orgId = auth.user().getCurrentOrganisationId();
    return (root, query, cb) -> cb.equal(root.get("organisationId"), orgId);
  }

  private Record findInScope(Long id, AuthenticatedUser auth) {
    Specification<Record> spec =
        scope(auth).and((root, q, cb) -> cb.equal(root.get("id"), id));
    return recordRepository.findAll(spec).stream()
        .findFirst()
        .orElseThrow(() -> ApiException.notFound());
  }

  /** Retourne le niveau existant, ou crée le niveau par défaut si la table est vide. */
  private Long ensureLevel() {
    return levelRepository.findAll().stream().findFirst()
        .map(RecordLevel::getId)
        .orElseGet(() -> {
          RecordLevel level = new RecordLevel();
          level.setName("Dossier");
          level.setDescription("Niveau par défaut");
          levelRepository.save(level);
          return level.getId();
        });
  }

  /** Retourne le statut existant, ou crée le statut par défaut si la table est vide. */
  private Long ensureStatus() {
    return statusRepository.findAll().stream().findFirst()
        .map(RecordStatus::getId)
        .orElseGet(() -> {
          RecordStatus status = new RecordStatus();
          status.setName("Brouillon");
          status.setDescription("Statut par défaut");
          statusRepository.save(status);
          return status.getId();
        });
  }

  private Map<String, Object> mapper(Record r) {
    boolean isContainer = r.getType() != null && r.getType().isContainer();
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("code", r.getCode());
    map.put("name", r.getName());
    map.put("description", r.getDescription());
    map.put("opening_date", r.getOpeningDate() != null ? r.getOpeningDate().toString() : null);
    map.put("closing_date", r.getClosingDate() != null ? r.getClosingDate().toString() : null);
    map.put("old_record_number", r.getOldRecordNumber());
    map.put("unavailable", r.getUnavailable() != null && r.getUnavailable() != false);
    map.put("annual_opening", r.getAnnualOpening() != null && r.getAnnualOpening() != false);
    map.put("is_essential", r.getIsEssential() != null && r.getIsEssential() != false);
    map.put("loaned_to", r.getLoanedTo());
    map.put("loaned_at", Json.timestamp(r.getLoanedAt()));
    map.put("loan_planned_return_at", Json.timestamp(r.getLoanPlannedReturnAt()));
    map.put("loan_actual_return_at", Json.timestamp(r.getLoanActualReturnAt()));
    map.put(
        "modified_after_loan",
        r.getModifiedAfterLoan() != null && r.getModifiedAfterLoan() != false);
    map.put("confidentiality_id", r.getConfidentialityId());
    map.put("access_limit_id", r.getAccessLimitId());
    map.put(
        "publication_date",
        r.getPublicationDate() != null ? r.getPublicationDate().toString() : null);
    map.put("location_before_add", r.getLocationBeforeAdd());
    map.put("type_id", r.getTypeId());
    map.put("level_id", r.getLevelId());
    map.put("status_id", r.getStatusId());
    map.put("activity_id", r.getActivityId());
    map.put("parent_id", r.getParentId());
    map.put("organisation_id", r.getOrganisationId());
    map.put("creator_id", r.getCreatorId());
    map.put("assigned_to", r.getAssignedTo());
    map.put("access_level", r.getAccessLevel());
    map.put(
        "requires_approval", r.getRequiresApproval() != null && r.getRequiresApproval() != false);
    map.put("approved_by", r.getApprovedBy());
    map.put("approved_at", Json.timestamp(r.getApprovedAt()));
    map.put("metadata", this.parseJson(r.getMetadata()));
    map.put("start_date", r.getStartDate() != null ? r.getStartDate().toString() : null);
    map.put("end_date", r.getEndDate() != null ? r.getEndDate().toString() : null);
    map.put("date_exact", r.getDateExact() != null ? r.getDateExact().toString() : null);
    map.put("date_format", r.getDateFormat());
    map.put("version_number", r.getVersionNumber());
    map.put(
        "is_current_version", r.getIsCurrentVersion() != null && r.getIsCurrentVersion() != false);
    map.put("is_container", isContainer);
    map.put("is_root", r.isRoot());
    map.put("created_at", Json.timestamp(r.getCreatedAt()));
    map.put("updated_at", Json.timestamp(r.getUpdatedAt()));
    return map;
  }

  private Object parseJson(String json) {
    if (json == null || json.isBlank()) {
      return null;
    }
    try {
      return this.objectMapper.readValue(json, Object.class);
    } catch (Exception e) {
      return null;
    }
  }

  private String toJson(Object value) {
    try {
      return this.objectMapper.writeValueAsString(value);
    } catch (Exception e) {
      return null;
    }
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
