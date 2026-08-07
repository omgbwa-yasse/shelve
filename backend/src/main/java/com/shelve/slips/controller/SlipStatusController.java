package com.shelve.slips.controller;

import com.shelve.exception.ApiException;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.slips.entity.SlipStatus;
import com.shelve.slips.repository.SlipStatusRepository;
import jakarta.persistence.EntityManager;
import jakarta.servlet.http.HttpServletRequest;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.repository.JpaSpecificationExecutor;
import org.springframework.http.HttpStatus;
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
@RequestMapping(value = {"/api/v1/slip-statuses"})
public class SlipStatusController extends GenericCrudController<SlipStatus> {
  private final SlipStatusRepository statusRepository;
  private final EntityManager em;

  public SlipStatusController(SlipStatusRepository statusRepository, EntityManager em) {
    this.statusRepository = statusRepository;
    this.em = em;
  }

  @Override
  protected Class<SlipStatus> entityClass() {
    return SlipStatus.class;
  }

  @Override
  protected JpaSpecificationExecutor<SlipStatus> repository() {
    return this.statusRepository;
  }

  @Override
  protected String resource() {
    return "slip_status";
  }

  @Override
  protected List<String> filterable() {
    return List.of("id", "name", "created_at", "updated_at");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected SlipStatus newEntity() {
    return new SlipStatus();
  }

  @Override
  protected String location(SlipStatus entity) {
    return "/api/v1/slip-statuses/" + entity.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    String name = SlipStatusController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 50, "name");
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, SlipStatus entity) {
    entity.setName(SlipStatusController.str(body.get("name")));
    entity.setDescription(SlipStatusController.str(body.get("description")));
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, SlipStatus entity) {
    if (body.containsKey("name")) {
      entity.setName(SlipStatusController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      entity.setDescription(SlipStatusController.str(body.get("description")));
    }
  }

  @Override
  protected Map<String, Object> mapper(SlipStatus s) {
    long slipsCount =
        s.getId() == null
            ? 0L
            : (Long)
                this.em
                    .createQuery(
                        "select count(x) from Slip x where x.slipStatusId = :id", Long.class)
                    .setParameter("id", (Object) s.getId())
                    .getSingleResult();
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", s.getId());
    map.put("name", s.getName());
    map.put("description", s.getDescription());
    map.put("slips_count", slipsCount);
    map.put("created_at", Json.timestamp(s.getCreatedAt()));
    map.put("updated_at", Json.timestamp(s.getUpdatedAt()));
    return map;
  }

  @Override
  protected void beforeDelete(SlipStatus status) {
    long used =
        (Long)
            this.em
                .createQuery("select count(x) from Slip x where x.slipStatusId = :id", Long.class)
                .setParameter("id", (Object) status.getId())
                .getSingleResult();
    if (used > 0L) {
      throw new ApiException(
          HttpStatus.CONFLICT, "Ce statut est affect\u00e9 \u00e0 un ou plusieurs bordereaux.");
    }
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

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> delete(@PathVariable Long id) {
    return super.destroy(id);
  }
}
