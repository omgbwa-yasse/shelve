package com.shelve.projects.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
import com.shelve.projects.entity.Kpi;
import com.shelve.projects.repository.KpiRepository;
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
@RequestMapping(value = {"/api/v1/kpis"})
public class KpiController extends GenericCrudController<Kpi> {
  private final KpiRepository repo;

  public KpiController(KpiRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<Kpi> entityClass() {
    return Kpi.class;
  }

  @Override
  protected JpaSpecificationExecutor<Kpi> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "kpi";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "code",
        "name",
        "description",
        "unit",
        "target_value",
        "direction",
        "frequency",
        "task_id",
        "owner_id",
        "organisation_id",
        "created_by",
        "updated_by");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected Kpi newEntity() {
    return new Kpi();
  }

  @Override
  protected String location(Kpi e) {
    return "/api/v1/kpis/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (KpiController.str(body.get("code")) == null) {
      v.add("code", "The code field is required.");
    }
    if (KpiController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    if (KpiController.str(body.get("code")) != null
        && this.repository()
                .findAll(
                    (Specification & Serializable)
                        (root, q, cb) ->
                            cb.equal(
                                (Expression) root.get("code"),
                                (Object) KpiController.str(body.get("code"))))
                .size()
            > 0) {
      v.add("code", "The code has already been taken.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Kpi e) {
    AuthenticatedUser auth = CurrentUser.get();
    e.setOrganisationId(auth.user().getCurrentOrganisationId());
    e.setCreatedBy(auth.user().getId());
    e.setAttachableType(KpiController.str(body.get("attachable_type")));
    e.setAttachableId(KpiController.longOf(body.get("attachable_id")));
    if (body.containsKey("code")) {
      e.setCode(KpiController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(KpiController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(KpiController.str(body.get("description")));
    }
    if (body.containsKey("unit")) {
      e.setUnit(KpiController.str(body.get("unit")));
    }
    if (body.containsKey("target_value")) {
      e.setTargetValue(KpiController.str(body.get("target_value")));
    }
    if (body.containsKey("direction")) {
      e.setDirection(KpiController.str(body.get("direction")));
    }
    if (body.containsKey("frequency")) {
      e.setFrequency(KpiController.str(body.get("frequency")));
    }
    if (body.containsKey("task_id")) {
      e.setTaskId(KpiController.longOf(body.get("task_id")));
    }
    if (body.containsKey("owner_id")) {
      e.setOwnerId(KpiController.longOf(body.get("owner_id")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(KpiController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(KpiController.longOf(body.get("created_by")));
    }
    if (body.containsKey("updated_by")) {
      e.setUpdatedBy(KpiController.longOf(body.get("updated_by")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Kpi e) {
    if (body.containsKey("code")) {
      e.setCode(KpiController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(KpiController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(KpiController.str(body.get("description")));
    }
    if (body.containsKey("unit")) {
      e.setUnit(KpiController.str(body.get("unit")));
    }
    if (body.containsKey("target_value")) {
      e.setTargetValue(KpiController.str(body.get("target_value")));
    }
    if (body.containsKey("direction")) {
      e.setDirection(KpiController.str(body.get("direction")));
    }
    if (body.containsKey("frequency")) {
      e.setFrequency(KpiController.str(body.get("frequency")));
    }
    if (body.containsKey("task_id")) {
      e.setTaskId(KpiController.longOf(body.get("task_id")));
    }
    if (body.containsKey("owner_id")) {
      e.setOwnerId(KpiController.longOf(body.get("owner_id")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(KpiController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(KpiController.longOf(body.get("created_by")));
    }
    if (body.containsKey("updated_by")) {
      e.setUpdatedBy(KpiController.longOf(body.get("updated_by")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Kpi e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("code", e.getCode());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("unit", e.getUnit());
    map.put("target_value", e.getTargetValue());
    map.put("direction", e.getDirection());
    map.put("frequency", e.getFrequency());
    map.put("task_id", e.getTaskId());
    map.put("owner_id", e.getOwnerId());
    map.put("organisation_id", e.getOrganisationId());
    map.put("created_by", e.getCreatedBy());
    map.put("updated_by", e.getUpdatedBy());
    map.put("created_at", Json.timestamp(e.getCreatedAt()));
    map.put("updated_at", Json.timestamp(e.getUpdatedAt()));
    return map;
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
