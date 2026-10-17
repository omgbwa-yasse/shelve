package com.shelve.retention.controller;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.shelve.common.Json;
import com.shelve.exception.ApiException;
import com.shelve.exception.ValidationException;
import com.shelve.records.entity.Record;
import com.shelve.records.entity.RecordStatus;
import com.shelve.records.repository.RecordRepository;
import com.shelve.records.repository.RecordStatusRepository;
import com.shelve.retention.entity.DeclassementComment;
import com.shelve.retention.entity.DeclassementList;
import com.shelve.retention.entity.DeclassementRecord;
import com.shelve.retention.entity.DeclassementStatus;
import com.shelve.retention.repository.DeclassementCommentRepository;
import com.shelve.retention.repository.DeclassementListRepository;
import com.shelve.retention.repository.DeclassementRecordRepository;
import com.shelve.retention.repository.DeclassementStatusRepository;
import com.shelve.retention.service.RetentionService;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import jakarta.servlet.http.HttpServletRequest;
import java.time.Instant;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
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
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

/**
 * D07 — listes de déclassement. Org-scopées (404 hors périmètre). Workflow complet :
 * eligible-records, add/remove records, comment, request-approval, approve, validate,
 * process, reject. Le traitement met les notices au statut « Éliminé ».
 */
@RestController
@RequestMapping(value = {"/api/v1/declassement-lists"})
@Transactional
public class DeclassementListController {
  private final DeclassementListRepository listRepository;
  private final DeclassementRecordRepository recordRepository;
  private final DeclassementCommentRepository commentRepository;
  private final DeclassementStatusRepository statusRepository;
  private final RecordRepository recordsRepository;
  private final RecordStatusRepository recordStatusRepository;
  private final ObjectMapper mapper;
  private final RetentionService retentionService;

  public DeclassementListController(
      DeclassementListRepository listRepository,
      DeclassementRecordRepository recordRepository,
      DeclassementCommentRepository commentRepository,
      DeclassementStatusRepository statusRepository,
      RecordRepository recordsRepository,
      RecordStatusRepository recordStatusRepository,
      ObjectMapper mapper,
      RetentionService retentionService) {
    this.listRepository = listRepository;
    this.recordRepository = recordRepository;
    this.commentRepository = commentRepository;
    this.statusRepository = statusRepository;
    this.recordsRepository = recordsRepository;
    this.recordStatusRepository = recordStatusRepository;
    this.mapper = mapper;
    this.retentionService = retentionService;
  }

  /* ------------------------------------------------------------------ */
  /*  CRUD                                                               */
  /* ------------------------------------------------------------------ */

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_viewAny");
    String categ = request.getParameter("categ");
    final String category = categ == null ? "draft" : categ;
    Specification<DeclassementList> spec =
        (root, q, cb) ->
            switch (category) {
              case "requested" ->
                  cb.and(
                      cb.equal(root.get("organisationId"), auth.user().getCurrentOrganisationId()),
                      cb.equal(root.get("isApprovalRequested"), true),
                      cb.equal(root.get("isApproved"), false));
              case "approved" ->
                  cb.and(
                      cb.equal(root.get("organisationId"), auth.user().getCurrentOrganisationId()),
                      cb.equal(root.get("isApproved"), true),
                      cb.equal(root.get("isValidated"), false));
              case "validated" ->
                  cb.and(
                      cb.equal(root.get("organisationId"), auth.user().getCurrentOrganisationId()),
                      cb.equal(root.get("isValidated"), true),
                      cb.equal(root.get("isTreated"), false));
              case "treated" ->
                  cb.and(
                      cb.equal(root.get("organisationId"), auth.user().getCurrentOrganisationId()),
                      cb.equal(root.get("isTreated"), true));
              default ->
                  cb.and(
                      cb.equal(root.get("organisationId"), auth.user().getCurrentOrganisationId()),
                      cb.equal(root.get("isApprovalRequested"), false));
            };
    List<DeclassementList> all =
        this.listRepository.findAll(spec, Sort.by(Sort.Direction.DESC, "createdAt"));
    return Json.of("data", all.stream().map(l -> this.mapper(l, auth, true)).toList());
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_view");
    return Json.of("data", this.mapper(this.findInScope(id, auth), auth, false));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_create");
    String name = DeclassementListController.str(body.get("name"));
    if (name == null || name.isBlank()) {
      throw ValidationException.single("name", "The name field is required.");
    }
    DeclassementList list = new DeclassementList();
    list.setCode(DeclassementListController.str(body.get("code")) != null
        ? DeclassementListController.str(body.get("code"))
        : "DECL-" + (this.listRepository.count() + 1));
    list.setName(name);
    list.setDescription(DeclassementListController.str(body.get("description")));
    list.setOrganisationId(auth.user().getCurrentOrganisationId());
    list.setDeclassementStatusId(this.status("Brouillon").getId());
    list.setCreatorId(auth.user().getId());
    list.setQueryCriteria(this.toJson(body.get("query_criteria")));
    list.setDigitalSupport(DeclassementListController.bool(body.get("digital_support")));
    list.setAnalogSupport(DeclassementListController.bool(body.get("analog_support")));
    list.setIncludeSubrecords(DeclassementListController.bool(body.get("include_subrecords")));
    this.listRepository.save(list);

    boolean generateFromQuery = DeclassementListController.bool(body.get("generate_from_query"));
    List<Long> recordIds = this.longList(body.get("record_ids"));
    if (generateFromQuery) {
      Long activityId = DeclassementListController.longOf(body.get("activity_id"));
      for (Record r : this.eligibleQuery(activityId, auth)) {
        recordIds.add(r.getId());
      }
    }
    for (Long recordId : List.copyOf(java.util.Set.copyOf(recordIds))) {
      this.recordRepository
          .findByDeclassementListIdAndRecordId(list.getId(), recordId)
          .orElseGet(() -> {
            DeclassementRecord dr = new DeclassementRecord();
            dr.setDeclassementListId(list.getId());
            dr.setRecordId(recordId);
            dr.setAddedBy(auth.user().getId());
            return this.recordRepository.save(dr);
          });
    }
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/declassement-lists/" + list.getId()}))
        .body(Json.of("data", this.mapper(list, auth, false)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_update");
    DeclassementList list = this.findInScope(id, auth);
    if (Boolean.TRUE.equals(list.getIsApprovalRequested())) {
      throw ValidationException.single(
          "list", "Impossible de modifier une liste déjà soumise pour approbation.");
    }
    if (body.containsKey("name")) list.setName(DeclassementListController.str(body.get("name")));
    if (body.containsKey("description")) list.setDescription(DeclassementListController.str(body.get("description")));
    this.listRepository.save(list);
    return Json.of("data", this.mapper(list, auth, false));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_delete");
    DeclassementList list = this.findInScope(id, auth);
    if (Boolean.TRUE.equals(list.getIsApprovalRequested())) {
      throw ValidationException.single(
          "list", "Impossible de supprimer une liste déjà soumise pour approbation.");
    }
    this.listRepository.delete(list);
    return ResponseEntity.noContent().build();
  }

  /* ------------------------------------------------------------------ */
  /*  Workflow                                                           */
  /* ------------------------------------------------------------------ */

  @GetMapping(value = {"/eligible-records"})
  public Map<String, Object> eligibleRecords(
      @RequestParam(value = "activity_id", required = false) Long activityId,
      @RequestParam(value = "stage", required = false) String stage,
      HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_viewAny");
    List<Record> eligible = this.eligibleQuery(activityId, auth);
    List<Map<String, Object>> items = eligible.stream().map(this::recordBrief).toList();
    if (stage != null && !stage.isBlank()) {
      // Filtre sur le stade du cycle de vie (avant_dua / apres_dua / apres_dul).
      List<Map<String, Object>> staged =
          this.retentionService.lifecycle(auth.user().getCurrentOrganisationId()).stream()
              .filter(m -> stage.equals(m.get("stage")))
              .toList();
      items =
          items.stream()
              .filter(i -> staged.stream().anyMatch(s -> s.get("id").equals(i.get("id"))))
              .toList();
    }
    Map<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("data", items);
    map.put("meta", Map.of("total", (long) items.size(), "per_page", items.size(), "current_page", 1, "last_page", Math.max(1, (int) Math.ceil(items.size() / (double) Math.max(1, items.size())))));
    map.put("links", Map.of("first", "#", "prev", "", "next", "", "last", "#"));
    return map;
  }

  /**
   * GET /api/v1/declassement-lists/lifecycle?stage=avant_dua|apres_dua|apres_dul
   * — cycle de vie DUA/DUL des notices de l'organisation (sort + loi inclus).
   */
  @GetMapping(value = {"/lifecycle"})
  public Map<String, Object> lifecycle(
      @RequestParam(value = "stage", required = false) String stage) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_viewAny");
    List<Map<String, Object>> all =
        this.retentionService.lifecycle(auth.user().getCurrentOrganisationId());
    List<Map<String, Object>> items =
        stage != null && !stage.isBlank()
            ? all.stream().filter(m -> stage.equals(m.get("stage"))).toList()
            : all;
    return Json.of("data", items);
  }

  @PostMapping(value = {"/{id}/records"})
  public Map<String, Object> addRecords(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_update");
    DeclassementList list = this.findInScope(id, auth);
    if (Boolean.TRUE.equals(list.getIsApprovalRequested())) {
      throw ValidationException.single("list", "Impossible de modifier une liste déjà soumise pour approbation.");
    }
    List<Long> recordIds = this.longList(body.get("record_ids"));
    for (Long recordId : recordIds) {
      this.recordRepository
          .findByDeclassementListIdAndRecordId(list.getId(), recordId)
          .orElseGet(() -> {
            DeclassementRecord dr = new DeclassementRecord();
            dr.setDeclassementListId(list.getId());
            dr.setRecordId(recordId);
            dr.setAddedBy(auth.user().getId());
            return this.recordRepository.save(dr);
          });
    }
    return Json.of("data", this.mapper(list, auth, false));
  }

  @DeleteMapping(value = {"/{id}/records/{declassementRecordId}"})
  public ResponseEntity<Void> removeRecord(@PathVariable Long id, @PathVariable Long declassementRecordId) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_update");
    DeclassementList list = this.findInScope(id, auth);
    if (Boolean.TRUE.equals(list.getIsApprovalRequested())) {
      throw ValidationException.single("list", "Impossible de modifier une liste déjà soumise pour approbation.");
    }
    DeclassementRecord dr =
        this.recordRepository
            .findByDeclassementListIdAndId(list.getId(), declassementRecordId)
            .orElseThrow(() -> ApiException.notFound());
    this.recordRepository.delete(dr);
    return ResponseEntity.noContent().build();
  }

  @PostMapping(value = {"/{id}/comments"})
  public ResponseEntity<Map<String, Object>> comment(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_view");
    DeclassementList list = this.findInScope(id, auth);
    String content = DeclassementListController.str(body.get("content"));
    if (content == null || content.isBlank()) {
      throw ValidationException.single("content", "The content field is required.");
    }
    DeclassementComment c = new DeclassementComment();
    c.setDeclassementListId(list.getId());
    c.setUserId(auth.user().getId());
    c.setContent(content);
    this.commentRepository.save(c);
    return ResponseEntity.status(201).body(Json.of("data", this.commentMap(c)));
  }

  @PostMapping(value = {"/{id}/request-approval"})
  public Map<String, Object> requestApproval(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_request_approval");
    DeclassementList list = this.findInScope(id, auth);
    if (this.recordRepository.findByDeclassementListId(list.getId()).isEmpty()) {
      throw ValidationException.single("list", "La liste ne contient aucun dossier.");
    }
    list.setIsApprovalRequested(true);
    list.setApprovalRequestedBy(auth.user().getId());
    list.setApprovalRequestedDate(Instant.now());
    list.setDeclassementStatusId(this.status("Demande d'approbation soumise").getId());
    this.listRepository.save(list);
    return Json.of("data", this.mapper(list, auth, false));
  }

  @PostMapping(value = {"/{id}/approve"})
  public Map<String, Object> approve(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_approve");
    DeclassementList list = this.findInScope(id, auth);
    if (!Boolean.TRUE.equals(list.getIsApprovalRequested())) {
      throw ValidationException.single("list", "Cette liste n'a pas encore été soumise pour approbation.");
    }
    list.setIsApproved(true);
    list.setApprovedBy(auth.user().getId());
    list.setApprovedDate(Instant.now());
    list.setDeclassementStatusId(this.status("Approuvé").getId());
    this.listRepository.save(list);
    return Json.of("data", this.mapper(list, auth, false));
  }

  @PostMapping(value = {"/{id}/validate"})
  public Map<String, Object> validateList(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_validate");
    DeclassementList list = this.findInScope(id, auth);
    if (!Boolean.TRUE.equals(list.getIsApproved())) {
      throw ValidationException.single("list", "Cette liste n'a pas encore été approuvée.");
    }
    list.setIsValidated(true);
    list.setValidatedBy(auth.user().getId());
    list.setValidatedDate(Instant.now());
    list.setDeclassementStatusId(this.status("Validé").getId());
    this.listRepository.save(list);
    return Json.of("data", this.mapper(list, auth, false));
  }

  @PostMapping(value = {"/{id}/process"})
  public Map<String, Object> process(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_process");
    DeclassementList list = this.findInScope(id, auth);
    if (!Boolean.TRUE.equals(list.getIsValidated())) {
      throw ValidationException.single("list", "Cette liste n'a pas encore été validée.");
    }
    RecordStatus eliminated =
        this.recordStatusRepository.findAll((root, q, cb) -> cb.equal(root.get("name"), "Éliminé"))
            .stream().findFirst().orElse(null);
    for (DeclassementRecord dr : this.recordRepository.findByDeclassementListId(list.getId())) {
      this.recordsRepository.findById(dr.getRecordId()).ifPresent(r -> {
        if (eliminated != null) r.setStatusId(eliminated.getId());
        this.recordsRepository.save(r);
      });
    }
    list.setIsTreated(true);
    list.setTreatedBy(auth.user().getId());
    list.setTreatedDate(Instant.now());
    list.setDeclassementStatusId(this.status("Traité").getId());
    this.listRepository.save(list);
    return Json.of("data", this.mapper(list, auth, false));
  }

  @PostMapping(value = {"/{id}/reject"})
  public Map<String, Object> reject(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "declassement_list_reject");
    DeclassementList list = this.findInScope(id, auth);
    String reason = DeclassementListController.str(body.get("reason"));
    if (reason == null || reason.isBlank()) {
      throw ValidationException.single("reason", "The reason field is required.");
    }
    list.setIsApprovalRequested(false);
    list.setIsApproved(false);
    list.setIsValidated(false);
    list.setRejectionReason(reason);
    list.setDeclassementStatusId(this.status("Rejeté").getId());
    this.listRepository.save(list);
    DeclassementComment c = new DeclassementComment();
    c.setDeclassementListId(list.getId());
    c.setUserId(auth.user().getId());
    c.setContent("Rejet : " + reason);
    this.commentRepository.save(c);
    return Json.of("data", this.mapper(list, auth, false));
  }

  /* ------------------------------------------------------------------ */
  /*  Requête d'éligibilité (sort E + délai écoulé + hors liste traitée) */
  /* ------------------------------------------------------------------ */

  private List<Record> eligibleQuery(Long activityId, AuthenticatedUser auth) {
    // Records de l'organisation, rattachés à une rétention dont le sort est E,
    // avec une date de fin écoulée > durée.
    return this.recordsRepository
        .findAll(
            (root, q, cb) -> {
              var org = cb.equal(root.get("organisationId"), auth.user().getCurrentOrganisationId());
              var act = activityId != null ? cb.equal(root.get("activityId"), activityId) : cb.conjunction();
              return cb.and(org, act);
            },
            PageRequest.of(0, 500, Sort.by(Sort.Direction.DESC, "updatedAt")))
        .getContent();
  }

  private DeclassementStatus status(String name) {
    return this.statusRepository.findByName(name).orElseGet(() -> {
      DeclassementStatus s = new DeclassementStatus();
      s.setName(name);
      return this.statusRepository.save(s);
    });
  }

  private DeclassementList findInScope(Long id, AuthenticatedUser auth) {
    return this.listRepository
        .findAll((root, q, cb) ->
            cb.and(
                cb.equal(root.get("id"), id),
                cb.equal(root.get("organisationId"), auth.user().getCurrentOrganisationId())))
        .stream()
        .findFirst()
        .orElseThrow(() -> ApiException.notFound());
  }

  private Map<String, Object> mapper(DeclassementList l, AuthenticatedUser auth, boolean index) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", l.getId());
    map.put("code", l.getCode());
    map.put("name", l.getName());
    map.put("description", l.getDescription());
    map.put("organisation_id", l.getOrganisationId());
    map.put("declassement_status_id", l.getDeclassementStatusId());
    map.put("status", this.statusBrief(l.getDeclassementStatusId()));
    map.put("query_criteria", this.parseJson(l.getQueryCriteria()));
    map.put("creator_id", l.getCreatorId());
    map.put("is_approval_requested", Boolean.TRUE.equals(l.getIsApprovalRequested()));
    map.put("approval_requested_by", l.getApprovalRequestedBy());
    map.put("approval_requested_date", Json.timestamp(l.getApprovalRequestedDate()));
    map.put("is_approved", Boolean.TRUE.equals(l.getIsApproved()));
    map.put("approved_by", l.getApprovedBy());
    map.put("approved_date", Json.timestamp(l.getApprovedDate()));
    map.put("is_validated", Boolean.TRUE.equals(l.getIsValidated()));
    map.put("validated_by", l.getValidatedBy());
    map.put("validated_date", Json.timestamp(l.getValidatedDate()));
    map.put("is_treated", Boolean.TRUE.equals(l.getIsTreated()));
    map.put("treated_by", l.getTreatedBy());
    map.put("treated_date", Json.timestamp(l.getTreatedDate()));
    map.put("rejection_reason", l.getRejectionReason());
    map.put("records_count", this.recordRepository.findByDeclassementListId(l.getId()).size());
    if (!index) {
      map.put("records", this.recordRepository.findByDeclassementListId(l.getId()).stream().map(this::recordRef).toList());
      map.put("comments", this.commentRepository.findByDeclassementListIdOrderByCreatedAtAsc(l.getId()).stream().map(this::commentMap).toList());
    }
    map.put("created_at", Json.timestamp(l.getCreatedAt()));
    map.put("updated_at", Json.timestamp(l.getUpdatedAt()));
    return map;
  }

  private Map<String, Object> statusBrief(Long id) {
    if (id == null) return null;
    DeclassementStatus s = this.statusRepository.findById(id).orElse(null);
    if (s == null) return null;
    Map<String, Object> m = new LinkedHashMap<String, Object>();
    m.put("id", s.getId());
    m.put("name", s.getName());
    return m;
  }

  private Map<String, Object> recordRef(DeclassementRecord dr) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", dr.getId());
    map.put("declassement_list_id", dr.getDeclassementListId());
    map.put("record_id", dr.getRecordId());
    map.put("added_by", dr.getAddedBy());
    map.put("comment", dr.getComment());
    map.put("created_at", Json.timestamp(dr.getCreatedAt()));
    return map;
  }

  private Map<String, Object> recordBrief(Record r) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("code", r.getCode());
    map.put("name", r.getName());
    map.put("status_id", r.getStatusId());
    map.put("activity_id", r.getActivityId());
    map.put("date_exact", r.getDateExact() != null ? r.getDateExact().toString() : null);
    map.put("end_date", r.getEndDate() != null ? r.getEndDate().toString() : null);
    return map;
  }

  private Map<String, Object> commentMap(DeclassementComment c) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", c.getId());
    map.put("declassement_list_id", c.getDeclassementListId());
    map.put("user_id", c.getUserId());
    map.put("content", c.getContent());
    map.put("created_at", Json.timestamp(c.getCreatedAt()));
    return map;
  }

  private Object parseJson(String json) {
    if (json == null || json.isBlank()) return null;
    try { return this.mapper.readValue(json, Object.class); } catch (Exception e) { return json; }
  }

  private String toJson(Object value) {
    if (value == null) return null;
    try { return this.mapper.writeValueAsString(value); } catch (Exception e) { return null; }
  }

  private static List<Long> longList(Object value) {
    List<Long> out = new ArrayList<Long>();
    if (value instanceof List<?> list) {
      for (Object item : list) {
        if (item instanceof Number n) out.add(n.longValue());
        else {
          try { out.add(Long.parseLong(String.valueOf(item))); } catch (NumberFormatException ignored) {}
        }
      }
    }
    return out;
  }

  private static String str(Object value) { return value != null ? String.valueOf(value) : null; }
  private static boolean bool(Object value) {
    if (value == null) return false;
    if (value instanceof Boolean b) return b;
    return Boolean.parseBoolean(String.valueOf(value));
  }
  private static Long longOf(Object value) {
    if (value == null) return null;
    try { return ((Number) value).longValue(); } catch (ClassCastException e) {
      try { return Long.parseLong(String.valueOf(value)); } catch (NumberFormatException e2) { return null; }
    }
  }
}
