package com.shelve.mails.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
import com.shelve.mails.entity.MailContainer;
import com.shelve.mails.repository.MailContainerRepository;
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
@RequestMapping(value = {"/api/v1/mail-containers"})
public class MailContainerController extends GenericCrudController<MailContainer> {
  private final MailContainerRepository repo;

  public MailContainerController(MailContainerRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<MailContainer> entityClass() {
    return MailContainer.class;
  }

  @Override
  protected JpaSpecificationExecutor<MailContainer> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "mail_container";
  }

  @Override
  protected List<String> filterable() {
    return List.of("code", "name", "created_by", "creator_organisation_id", "property_id");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected MailContainer newEntity() {
    return new MailContainer();
  }

  @Override
  protected String location(MailContainer e) {
    return "/api/v1/mail-containers/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (MailContainerController.str(body.get("code")) == null) {
      v.add("code", "The code field is required.");
    }
    if (MailContainerController.str(body.get("code")) != null
        && this.repository()
                .findAll(
                    (Specification & Serializable)
                        (root, q, cb) ->
                            cb.equal(
                                (Expression) root.get("code"),
                                (Object) MailContainerController.str(body.get("code"))))
                .size()
            > 0) {
      v.add("code", "The code has already been taken.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, MailContainer e) {
    AuthenticatedUser auth = CurrentUser.get();
    e.setCreatedBy(auth.user().getId());
    e.setCreatorOrganisationId(auth.user().getCurrentOrganisationId());
    if (body.containsKey("code")) {
      e.setCode(MailContainerController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(MailContainerController.str(body.get("name")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(MailContainerController.longOf(body.get("created_by")));
    }
    if (body.containsKey("creator_organisation_id")) {
      e.setCreatorOrganisationId(
          MailContainerController.longOf(body.get("creator_organisation_id")));
    }
    if (body.containsKey("property_id")) {
      e.setPropertyId(MailContainerController.longOf(body.get("property_id")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, MailContainer e) {
    if (body.containsKey("code")) {
      e.setCode(MailContainerController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(MailContainerController.str(body.get("name")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(MailContainerController.longOf(body.get("created_by")));
    }
    if (body.containsKey("creator_organisation_id")) {
      e.setCreatorOrganisationId(
          MailContainerController.longOf(body.get("creator_organisation_id")));
    }
    if (body.containsKey("property_id")) {
      e.setPropertyId(MailContainerController.longOf(body.get("property_id")));
    }
  }

  @Override
  protected Map<String, Object> mapper(MailContainer e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("code", e.getCode());
    map.put("name", e.getName());
    map.put("created_by", e.getCreatedBy());
    map.put("creator_organisation_id", e.getCreatorOrganisationId());
    map.put("property_id", e.getPropertyId());
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
