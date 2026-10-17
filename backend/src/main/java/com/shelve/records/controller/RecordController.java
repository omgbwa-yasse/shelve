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
import com.shelve.records.repository.RecordRepository;
import com.shelve.records.repository.RecordTypeRepository;
import com.shelve.records.service.RecordIncludes;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.time.Instant;
import java.time.LocalDate;
import java.time.format.DateTimeParseException;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import org.springframework.dao.DataIntegrityViolationException;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.http.ResponseEntity;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.transaction.annotation.Transactional;

@RestController
@RequestMapping(value = {"/api/v1"})
@Transactional
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
          "created_at",
          "updated_at");
  private static final List<String> ACCESS_LEVELS =
      List.of("public", "internal", "restricted", "confidential", "secret");
  /** Statuts qu'une notice soumise à approbation ne peut atteindre sans validation. */
  private static final List<String> APPROVAL_GATED_STATUSES = List.of("Publié", "Archivé");
  private static final int CODE_MAX = 30;
  private static final int NAME_MAX = 191;
  private static final Pattern TRAILING_DIGITS = Pattern.compile("(\\d+)$");

  private final RecordRepository recordRepository;
  private final RecordTypeRepository typeRepository;
  private final ObjectMapper objectMapper;
  private final com.shelve.referentials.service.MetadataFieldService metadataFieldService;
  private final com.shelve.organisation.repository.UserRoleRepository userRoleRepository;
  private final RecordIncludes includes;
  private final JdbcTemplate jdbc;

  public RecordController(
      RecordRepository recordRepository,
      RecordTypeRepository typeRepository,
      ObjectMapper objectMapper,
      com.shelve.referentials.service.MetadataFieldService metadataFieldService,
      com.shelve.organisation.repository.UserRoleRepository userRoleRepository,
      RecordIncludes includes,
      JdbcTemplate jdbc) {
    this.recordRepository = recordRepository;
    this.typeRepository = typeRepository;
    this.objectMapper = objectMapper;
    this.metadataFieldService = metadataFieldService;
    this.userRoleRepository = userRoleRepository;
    this.includes = includes;
    this.jdbc = jdbc;
  }

  /* ------------------------------------------------------------------ */
  /*  CRUD                                                               */
  /* ------------------------------------------------------------------ */

  @GetMapping(value = {"/records"})
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, RecordIncludes.INCLUDABLE);
    Specification spec =
        this.scope(auth)
            .and(
                (Specification & Serializable)
                    (root, q, cb) ->
                        cb.equal((Expression) root.get("isCurrentVersion"), (Object) true))
            .and(Filters.of(qp.getFilters(), Record.class));
    return this.withIncludes(
        Paging.page(this.recordRepository, spec, qp, SORTABLE, "updatedAt", request, this::mapper),
        qp);
  }

  @GetMapping(value = {"/records/{id}"})
  public Map<String, Object> show(@PathVariable Long id, HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_view");
    QueryParams qp = QueryParams.parse(request);
    Record record = this.findInScope(id, auth);
    Map<String, Object> view = this.mapper(record);
    this.includes.apply(List.of(view), this.allowedIncludes(qp));
    return Json.of("data", view);
  }

  /**
   * GET /api/v1/records/{id}/metadata-fields — champs visibles de la notice avec
   * les valeurs courantes (y compris métadonnées copiées/calculées résolues et
   * sécurisation par rôles).
   */
  @GetMapping(value = {"/records/{id}/metadata-fields"})
  public Map<String, Object> metadataFields(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_view");
    Record record = this.findInScope(id, auth);
    java.util.Set<String> roles =
        auth.isSuperAdmin()
            ? java.util.Set.of("superadmin")
            : java.util.Set.copyOf(this.userRoleRepository.findRoleNamesByUserId(auth.user().getId()));
    @SuppressWarnings("unchecked")
    Map<String, Object> values =
        record.getMetadata() == null || record.getMetadata().isBlank()
            ? Map.of()
            : (Map<String, Object>) this.parseJson(record.getMetadata());
    java.util.Map<String, Object> context = new java.util.LinkedHashMap<String, Object>(values);
    // Références copiées : codes et libellés résolus (et non les identifiants).
    context.put("parent_code", this.lookup("select code from records where id = ?", record.getParentId()));
    context.put(
        "creator_name",
        this.lookup(
            "select trim(concat(coalesce(name, ''), ' ', coalesce(surname, ''))) from users where id = ?",
            record.getCreatorId()));
    context.put("type_code", this.lookup("select code from record_types where id = ?", record.getTypeId()));
    Long typeId = record.getTypeId();
    if (typeId == null) {
      return Json.of("data", java.util.List.of());
    }
    return Json.of("data", this.metadataFieldService.fieldsForType(typeId, roles, context));
  }

  @PostMapping(value = {"/records"})
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_create");
    Record record = this.create(body, auth);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/records/" + record.getId()}))
        .body(Json.of("data", this.mapper(record)));
  }

  @PatchMapping(value = {"/records/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_update");
    Record record = this.findInScope(id, auth);
    this.fill(record, body, auth, false);
    this.recordRepository.save(record);
    return Json.of("data", this.mapper(record));
  }

  /** DELETE /api/v1/records/{id} — envoi à la corbeille (soft delete). */
  @DeleteMapping(value = {"/records/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_delete");
    Record record = this.findInScope(id, auth);
    this.recordRepository.delete(record);
    return ResponseEntity.noContent().build();
  }

  /* ------------------------------------------------------------------ */
  /*  Statut et approbation                                              */
  /* ------------------------------------------------------------------ */

  @PatchMapping(value = {"/records/{id}/status"})
  public Map<String, Object> status(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_update");
    Record record = this.findInScope(id, auth);
    Long statusId = RecordController.longOf("status_id", body.get("status_id"));
    if (statusId == null) {
      throw ValidationException.single("status_id", "The status id field is required.");
    }
    this.assertExists("status_id", "record_statuses", statusId);
    this.assertApprovalAllows(record, statusId);
    record.setStatusId(statusId);
    this.recordRepository.save(record);
    return Json.of("data", this.mapper(record));
  }

  /** POST /api/v1/records/{id}/approve — valide une notice soumise à approbation. */
  @PostMapping(value = {"/records/{id}/approve"})
  public Map<String, Object> approve(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_update");
    Record record = this.findInScope(id, auth);
    if (auth.user().getId().equals(record.getCreatorId()) && !auth.isSuperAdmin()) {
      throw ApiException.conflict("Le créateur d'une notice ne peut pas l'approuver lui-même.");
    }
    record.setApprovedBy(auth.user().getId());
    record.setApprovedAt(Instant.now());
    this.recordRepository.save(record);
    return Json.of("data", this.mapper(record));
  }

  /* ------------------------------------------------------------------ */
  /*  Sous-notices                                                       */
  /* ------------------------------------------------------------------ */

  @GetMapping(value = {"/records/{id}/children"})
  public Map<String, Object> children(@PathVariable Long id, HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_view");
    Record parent = this.findInScope(id, auth);
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, RecordIncludes.INCLUDABLE);
    Specification<Record> spec =
        this.scope(auth)
            .and((root, q, cb) -> cb.equal(root.get("parentId"), parent.getId()))
            .and(Filters.of(qp.getFilters(), Record.class));
    return this.withIncludes(
        Paging.page(this.recordRepository, spec, qp, SORTABLE, "code", request, this::mapper), qp);
  }

  @PostMapping(value = {"/records/{id}/children"})
  public ResponseEntity<Map<String, Object>> storeChild(
      @PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_create");
    Record parent = this.findInScope(id, auth);
    Map<String, Object> payload = new LinkedHashMap<>(body);
    payload.put("parent_id", parent.getId());
    // Hérite par défaut du classement du parent.
    payload.putIfAbsent("activity_id", parent.getActivityId());
    payload.putIfAbsent("type_id", parent.getTypeId());
    Record child = this.create(payload, auth);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/records/" + child.getId()}))
        .body(Json.of("data", this.mapper(child)));
  }

  @DeleteMapping(value = {"/records/{id}/children/{childId}"})
  public ResponseEntity<Void> destroyChild(@PathVariable Long id, @PathVariable Long childId) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_delete");
    Record child = this.findInScope(childId, auth);
    if (!id.equals(child.getParentId())) {
      throw ApiException.notFound();
    }
    this.recordRepository.delete(child);
    return ResponseEntity.noContent().build();
  }

  /* ------------------------------------------------------------------ */
  /*  Corbeille                                                          */
  /* ------------------------------------------------------------------ */

  /** GET /api/v1/records-trash — notices supprimées de l'organisation courante. */
  @GetMapping(value = {"/records-trash"})
  public Map<String, Object> trash(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_viewAny");
    QueryParams qp = QueryParams.parse(request);
    Long orgId = auth.user().getCurrentOrganisationId();
    int page = (int) qp.getPageNumber();
    int size = (int) qp.getPageSize();
    Long total =
        this.jdbc.queryForObject(
            "select count(*) from records where deleted_at is not null and organisation_id = ?",
            Long.class,
            orgId);
    List<Map<String, Object>> rows =
        new ArrayList<>(
            this.jdbc.queryForList(
                "select id, code, name, type_id, parent_id, deleted_at from records"
                    + " where deleted_at is not null and organisation_id = ?"
                    + " order by deleted_at desc limit ? offset ?",
                orgId,
                size,
                (page - 1) * size));
    List<Map<String, Object>> views = rows.stream().map(r -> (Map<String, Object>) new LinkedHashMap<>(r)).toList();
    this.includes.apply(views, List.of("type"));
    return Paging.envelope(views, total == null ? 0 : total, page, size, request);
  }

  @PostMapping(value = {"/records/{id}/restore"})
  public Map<String, Object> restore(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_delete");
    int updated =
        this.jdbc.update(
            "update records set deleted_at = null, updated_at = now()"
                + " where id = ? and organisation_id = ? and deleted_at is not null",
            id,
            auth.user().getCurrentOrganisationId());
    if (updated == 0) {
      throw ApiException.notFound();
    }
    return Json.of("data", this.mapper(this.findInScope(id, auth)));
  }

  /** DELETE /api/v1/records/{id}/force — suppression définitive d'une notice en corbeille. */
  @DeleteMapping(value = {"/records/{id}/force"})
  public ResponseEntity<Void> forceDelete(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_force_delete");
    Long orgId = auth.user().getCurrentOrganisationId();
    Long children =
        this.jdbc.queryForObject("select count(*) from records where parent_id = ?", Long.class, id);
    if (children != null && children > 0) {
      throw ApiException.conflict(
          "Cette notice a encore des sous-notices : supprimez-les ou déplacez-les d'abord.");
    }
    try {
      int deleted =
          this.jdbc.update(
              "delete from records where id = ? and organisation_id = ? and deleted_at is not null",
              id,
              orgId);
      if (deleted == 0) {
        throw ApiException.notFound();
      }
    } catch (DataIntegrityViolationException e) {
      throw ApiException.conflict(
          "Cette notice est encore référencée (communications, bordereaux…) et ne peut pas être supprimée définitivement.");
    }
    return ResponseEntity.noContent().build();
  }

  /* ------------------------------------------------------------------ */
  /*  Création / validation                                              */
  /* ------------------------------------------------------------------ */

  private Record create(Map<String, Object> body, AuthenticatedUser auth) {
    Record record = new Record();
    this.fill(record, body, auth, true);
    if (record.getLevelId() == null) {
      record.setLevelId(this.defaultId("record_levels", "Dossier"));
    }
    if (record.getStatusId() == null) {
      record.setStatusId(this.defaultId("record_statuses", "Brouillon"));
    }
    if (record.getAccessLevel() == null || record.getAccessLevel().isBlank()) {
      record.setAccessLevel("internal");
    }
    record.setOrganisationId(auth.user().getCurrentOrganisationId());
    record.setCreatorId(auth.user().getId());
    record.setVersionNumber(1);
    record.setIsCurrentVersion(true);
    if (record.getCode() == null || record.getCode().isBlank()) {
      String prefix =
          record.getTypeId() == null
              ? null
              : this.typeRepository
                  .findById(record.getTypeId())
                  .map(t -> t.getCodePrefix() != null ? t.getCodePrefix() : t.getCode())
                  .orElse(null);
      if (prefix == null) {
        throw ValidationException.single(
            "code", "Le code est requis lorsque aucun type n'est fourni.");
      }
      record.setCode(this.nextCode(prefix));
    }
    this.recordRepository.save(record);
    return record;
  }

  /**
   * Applique le corps de la requête à la notice, en validant chaque champ : une
   * valeur mal formée ou une référence inexistante produit une erreur 422 au lieu
   * d'être silencieusement ignorée.
   */
  private void fill(Record record, Map<String, Object> body, AuthenticatedUser auth, boolean creating) {
    Map<String, List<String>> errors = new LinkedHashMap<>();
    Validator v = Validator.begin();

    if (creating || body.containsKey("name")) {
      String name = RecordController.str(body.get("name"));
      v.require("name", name, "The name field is required.").max("name", name, NAME_MAX, "name");
      record.setName(name);
    }
    if (body.containsKey("code")) {
      String code = RecordController.str(body.get("code"));
      if (code != null && !code.isBlank()) {
        code = code.trim();
        v.max("code", code, CODE_MAX, "code");
        if (!code.equals(record.getCode()) && this.codeTaken(code)) {
          errors.put("code", List.of("Ce code est déjà utilisé par une autre notice (y compris en corbeille)."));
        }
        record.setCode(code);
      } else if (!creating) {
        errors.put("code", List.of("Le code ne peut pas être vidé."));
      }
    }
    if (body.containsKey("description")) {
      record.setDescription(RecordController.str(body.get("description")));
    }
    if (body.containsKey("type_id")) {
      record.setTypeId(this.reference(errors, body, "type_id", "record_types"));
    }
    if (body.containsKey("level_id")) {
      record.setLevelId(this.reference(errors, body, "level_id", "record_levels"));
    }
    if (body.containsKey("status_id")) {
      Long statusId = this.reference(errors, body, "status_id", "record_statuses");
      if (statusId != null && !creating) {
        this.assertApprovalAllows(record, statusId);
      }
      record.setStatusId(statusId);
    }
    if (body.containsKey("activity_id")) {
      record.setActivityId(this.reference(errors, body, "activity_id", "activities"));
    }
    if (body.containsKey("assigned_to")) {
      record.setAssignedTo(this.reference(errors, body, "assigned_to", "users"));
    }
    if (body.containsKey("confidentiality_id")) {
      record.setConfidentialityId(
          this.reference(errors, body, "confidentiality_id", "record_confidentialities"));
    }
    if (body.containsKey("access_limit_id")) {
      record.setAccessLimitId(this.reference(errors, body, "access_limit_id", "communicabilities"));
    }
    if (body.containsKey("parent_id")) {
      record.setParentId(this.parentOf(errors, record, body.get("parent_id"), auth));
    }
    if (body.containsKey("access_level")) {
      String level = RecordController.str(body.get("access_level"));
      if (level != null && !level.isBlank() && !ACCESS_LEVELS.contains(level)) {
        errors.put("access_level", List.of("Niveau d'accès inconnu : " + level + "."));
      }
      record.setAccessLevel(level);
    }
    if (body.containsKey("requires_approval")) {
      record.setRequiresApproval(RecordController.bool(body.get("requires_approval")));
    }
    if (body.containsKey("start_date")) {
      record.setStartDate(RecordController.dateOf(errors, "start_date", body.get("start_date")));
    }
    if (body.containsKey("end_date")) {
      record.setEndDate(RecordController.dateOf(errors, "end_date", body.get("end_date")));
    }
    if (body.containsKey("date_exact")) {
      record.setDateExact(RecordController.dateOf(errors, "date_exact", body.get("date_exact")));
    }
    if (record.getStartDate() != null
        && record.getEndDate() != null
        && record.getEndDate().isBefore(record.getStartDate())) {
      errors.put("end_date", List.of("La date de fin doit être postérieure à la date de début."));
    }
    if (body.containsKey("metadata")) {
      record.setMetadata(this.toJson(body.get("metadata")));
    }

    try {
      v.validate();
    } catch (ValidationException e) {
      if (errors.isEmpty()) {
        throw e;
      }
      errors.putAll(e.getErrors());
    }
    if (!errors.isEmpty()) {
      throw ValidationException.of(errors);
    }
  }

  private Long reference(Map<String, List<String>> errors, Map<String, Object> body, String field, String table) {
    Object raw = body.get(field);
    if (raw == null || (raw instanceof String s && s.isBlank())) {
      return null;
    }
    Long id = RecordController.parseLong(raw);
    if (id == null) {
      errors.put(field, List.of("La valeur de " + field + " doit être un identifiant numérique."));
      return null;
    }
    if (!this.exists(table, id)) {
      errors.put(field, List.of("La référence " + field + " = " + id + " n'existe pas."));
      return null;
    }
    return id;
  }

  /** Valide le parent : même organisation, pas la notice elle-même ni un descendant. */
  private Long parentOf(Map<String, List<String>> errors, Record record, Object raw, AuthenticatedUser auth) {
    if (raw == null || (raw instanceof String s && s.isBlank())) {
      return null;
    }
    Long parentId = RecordController.parseLong(raw);
    if (parentId == null) {
      errors.put("parent_id", List.of("La valeur de parent_id doit être un identifiant numérique."));
      return null;
    }
    Long parentOrg =
        this.jdbc.query(
            "select organisation_id from records where id = ? and deleted_at is null",
            rs -> rs.next() ? rs.getLong(1) : null,
            parentId);
    if (parentOrg == null || !parentOrg.equals(auth.user().getCurrentOrganisationId())) {
      errors.put("parent_id", List.of("La notice parente n'existe pas dans votre organisation."));
      return null;
    }
    if (record.getId() != null) {
      // Remonte les ancêtres du parent proposé : la notice ne doit pas y figurer.
      Long cursor = parentId;
      for (int depth = 0; cursor != null && depth < 100; depth++) {
        if (cursor.equals(record.getId())) {
          errors.put("parent_id", List.of("Une notice ne peut pas être rattachée à elle-même ou à l'une de ses sous-notices."));
          return null;
        }
        cursor = this.jdbc.query("select parent_id from records where id = ?",
            rs -> rs.next() ? (Long) rs.getObject(1, Long.class) : null, cursor);
      }
    }
    return parentId;
  }

  private void assertApprovalAllows(Record record, Long statusId) {
    if (!Boolean.TRUE.equals(record.getRequiresApproval()) || record.getApprovedBy() != null) {
      return;
    }
    String statusName = (String) this.lookup("select name from record_statuses where id = ?", statusId);
    if (statusName != null && APPROVAL_GATED_STATUSES.contains(statusName)) {
      throw ValidationException.single(
          "status_id",
          "Cette notice doit être approuvée avant de passer au statut « " + statusName + " ».");
    }
  }

  private void assertExists(String field, String table, Long id) {
    if (!this.exists(table, id)) {
      throw ValidationException.single(field, "La référence " + field + " = " + id + " n'existe pas.");
    }
  }

  private boolean exists(String table, Long id) {
    Long n = this.jdbc.queryForObject("select count(*) from " + table + " where id = ?", Long.class, id);
    return n != null && n > 0;
  }

  /** Vérifie l'unicité sur toute la table, corbeille comprise (contrainte SQL). */
  private boolean codeTaken(String code) {
    Long n = this.jdbc.queryForObject("select count(*) from records where code = ?", Long.class, code);
    return n != null && n > 0;
  }

  /**
   * Prochain code libre pour un préfixe : max(suffixe numérique) + 1, calculé sur
   * toute la table (corbeille comprise), puis vérifié pour éviter toute collision.
   */
  private String nextCode(String prefix) {
    long max = 0;
    for (String code :
        this.jdbc.queryForList("select code from records where code like ?", String.class, prefix + "%")) {
      Matcher m = TRAILING_DIGITS.matcher(code.substring(prefix.length()));
      if (m.matches()) {
        try {
          max = Math.max(max, Long.parseLong(m.group(1)));
        } catch (NumberFormatException ignored) {
          // suffixe trop long : ignoré
        }
      }
    }
    for (long n = max + 1; ; n++) {
      String candidate = prefix + n;
      if (candidate.length() > CODE_MAX) {
        throw ValidationException.single("code", "Impossible de générer un code : préfixe trop long.");
      }
      if (!this.codeTaken(candidate)) {
        return candidate;
      }
    }
  }

  /** Valeur par défaut : la ligne nommée `preferred`, sinon la première de la table. */
  private Long defaultId(String table, String preferred) {
    List<Long> ids =
        this.jdbc.queryForList(
            "select id from " + table + " order by (name = ?) desc, id limit 1", Long.class, preferred);
    if (!ids.isEmpty()) {
      return ids.get(0);
    }
    this.jdbc.update(
        "insert into " + table + " (name, description, created_at, updated_at) values (?, ?, now(), now())",
        preferred,
        "Valeur par défaut");
    return this.jdbc.queryForObject("select max(id) from " + table, Long.class);
  }

  private Object lookup(String sql, Long id) {
    if (id == null) {
      return null;
    }
    List<Object> out = this.jdbc.queryForList(sql, Object.class, id);
    return out.isEmpty() ? null : out.get(0);
  }

  /* ------------------------------------------------------------------ */
  /*  Portée, includes, sérialisation                                    */
  /* ------------------------------------------------------------------ */

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

  private List<String> allowedIncludes(QueryParams qp) {
    return qp.getIncludes().stream().filter(RecordIncludes.INCLUDABLE::contains).toList();
  }

  @SuppressWarnings("unchecked")
  private Map<String, Object> withIncludes(Map<String, Object> envelope, QueryParams qp) {
    Object data = envelope.get("data");
    if (data instanceof List<?> list) {
      this.includes.apply((List<Map<String, Object>>) list, this.allowedIncludes(qp));
    }
    return envelope;
  }

  private Map<String, Object> mapper(Record r) {
    boolean isContainer = r.getType() != null && r.getType().isContainer();
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", r.getId());
    map.put("code", r.getCode());
    map.put("name", r.getName());
    map.put("description", r.getDescription());
    map.put("old_record_number", r.getOldRecordNumber());
    map.put("unavailable", r.getUnavailable() != null && r.getUnavailable() != false);
    map.put("annual_opening", r.getAnnualOpening() != null && r.getAnnualOpening() != false);
    map.put("is_essential", r.getIsEssential() != null && r.getIsEssential() != false);
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
      throw ValidationException.single("metadata", "Les métadonnées ne sont pas sérialisables en JSON.");
    }
  }

  private static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  private static Boolean bool(Object value) {
    return value == null
        ? null
        : (value instanceof Boolean b ? b : Boolean.valueOf(Boolean.parseBoolean(String.valueOf(value))));
  }

  private static Long parseLong(Object value) {
    if (value instanceof Number n) {
      return n.longValue();
    }
    try {
      return Long.parseLong(String.valueOf(value).trim());
    } catch (NumberFormatException e) {
      return null;
    }
  }

  private static Long longOf(String field, Object value) {
    if (value == null) {
      return null;
    }
    Long id = parseLong(value);
    if (id == null) {
      throw ValidationException.single(field, "La valeur de " + field + " doit être un identifiant numérique.");
    }
    return id;
  }

  private static LocalDate dateOf(Map<String, List<String>> errors, String field, Object value) {
    if (value == null || String.valueOf(value).isBlank()) {
      return null;
    }
    try {
      return LocalDate.parse(String.valueOf(value));
    } catch (DateTimeParseException e) {
      errors.put(field, List.of("La date " + field + " doit être au format AAAA-MM-JJ."));
      return null;
    }
  }
}
