package com.shelve.slips.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
import com.shelve.slips.entity.Slip;
import com.shelve.slips.repository.SlipRepository;
import com.shelve.slips.entity.SlipStatus;
import com.shelve.slips.repository.SlipStatusRepository;
import jakarta.persistence.EntityManager;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
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
@RequestMapping(value = {"/api/v1/slips"})
public class SlipController extends GenericCrudController<Slip> {
  private final SlipRepository slipRepository;
  private final SlipStatusRepository slipStatusRepository;
  private final EntityManager em;

  public SlipController(
      SlipRepository slipRepository, SlipStatusRepository slipStatusRepository, EntityManager em) {
    this.slipRepository = slipRepository;
    this.slipStatusRepository = slipStatusRepository;
    this.em = em;
  }

  @Override
  protected Class<Slip> entityClass() {
    return Slip.class;
  }

  @Override
  protected JpaSpecificationExecutor<Slip> repository() {
    return this.slipRepository;
  }

  @Override
  protected String resource() {
    return "slip";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "id",
        "code",
        "name",
        "slip_status_id",
        "is_received",
        "is_approved",
        "is_integrated",
        "created_at",
        "updated_at");
  }

  @Override
  protected List<String> sortable() {
    return List.of(
        "id",
        "code",
        "name",
        "slip_status_id",
        "is_received",
        "is_approved",
        "is_integrated",
        "created_at",
        "updated_at");
  }

  @Override
  protected String defaultSort() {
    return "id";
  }

  @Override
  protected Specification<Slip> scope(AuthenticatedUser auth) {
    Long orgId = auth.user().getCurrentOrganisationId();
    return (Specification & Serializable)
        (root, query, cb) ->
            cb.or(
                (Expression)
                    cb.equal((Expression) root.get("officerOrganisationId"), (Object) orgId),
                (Expression) cb.equal((Expression) root.get("userOrganisationId"), (Object) orgId));
  }

  @Override
  protected Slip newEntity() {
    return new Slip();
  }

  @Override
  protected String location(Slip entity) {
    return "/api/v1/slips/" + entity.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    String code = SlipController.str(body.get("code"));
    String name = SlipController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("code", code, "The code field is required.")
            .max("code", code, 20, "code")
            .require("name", name, "The name field is required.")
            .max("name", name, 200, "name");
    if (SlipController.longOf(body.get("user_organisation_id")) == null) {
      v.add("user_organisation_id", "The user organisation id field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Slip slip) {
    AuthenticatedUser auth = CurrentUser.get();
    slip.setCode(SlipController.str(body.get("code")));
    slip.setName(SlipController.str(body.get("name")));
    slip.setDescription(SlipController.str(body.get("description")));
    slip.setOfficerId(auth.user().getId());
    slip.setOfficerOrganisationId(auth.user().getCurrentOrganisationId());
    slip.setUserOrganisationId(SlipController.longOf(body.get("user_organisation_id")));
    slip.setUserId(SlipController.longOf(body.get("user_id")));
    Long statusId =
        this.slipStatusRepository.findAll().stream()
            .filter(s -> "Projects".equals(s.getName()))
            .findFirst()
            .map(SlipStatus::getId)
            .orElse(1L);
    slip.setSlipStatusId(statusId);
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Slip slip) {
    if (body.containsKey("code")) {
      slip.setCode(SlipController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      slip.setName(SlipController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      slip.setDescription(SlipController.str(body.get("description")));
    }
    if (body.containsKey("user_organisation_id")) {
      slip.setUserOrganisationId(SlipController.longOf(body.get("user_organisation_id")));
    }
    if (body.containsKey("user_id")) {
      slip.setUserId(SlipController.longOf(body.get("user_id")));
    }
    if (body.containsKey("slip_status_id")) {
      slip.setSlipStatusId(SlipController.longOf(body.get("slip_status_id")));
    }
    if (body.containsKey("is_received")) {
      slip.setIsReceived(SlipController.bool(body.get("is_received")));
    }
    if (body.containsKey("received_date")) {
      slip.setReceivedDate(SlipController.instantOf(body.get("received_date")));
    }
    if (body.containsKey("is_approved")) {
      slip.setIsApproved(SlipController.bool(body.get("is_approved")));
    }
    if (body.containsKey("approved_date")) {
      slip.setApprovedDate(SlipController.instantOf(body.get("approved_date")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Slip s) {
    long recordsCount = s.getId() == null ? 0L : this.countRecords(s.getId());
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", s.getId());
    map.put("code", s.getCode());
    map.put("name", s.getName());
    map.put("description", s.getDescription());
    map.put("officer_organisation_id", s.getOfficerOrganisationId());
    map.put("officer_id", s.getOfficerId());
    map.put("user_organisation_id", s.getUserOrganisationId());
    map.put("user_id", s.getUserId());
    map.put("slip_status_id", s.getSlipStatusId());
    map.put("is_received", s.getIsReceived() != null && s.getIsReceived() != false);
    map.put("received_date", Json.timestamp(s.getReceivedDate()));
    map.put("received_by", s.getReceivedBy());
    map.put("is_approved", s.getIsApproved() != null && s.getIsApproved() != false);
    map.put("approved_date", Json.timestamp(s.getApprovedDate()));
    map.put("approved_by", s.getApprovedBy());
    map.put("is_integrated", s.getIsIntegrated() != null && s.getIsIntegrated() != false);
    map.put("integrated_date", Json.timestamp(s.getIntegratedDate()));
    map.put("integrated_by", s.getIntegratedBy());
    map.put("records_count", recordsCount);
    map.put("created_at", Json.timestamp(s.getCreatedAt()));
    map.put("updated_at", Json.timestamp(s.getUpdatedAt()));
    return map;
  }

  private long countRecords(Long slipId) {
    return (Long)
        this.em
            .createQuery("select count(sr) from SlipRecord sr where sr.slipId = :id", Long.class)
            .setParameter("id", (Object) slipId)
            .getSingleResult();
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
