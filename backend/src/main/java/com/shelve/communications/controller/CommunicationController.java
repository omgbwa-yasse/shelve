package com.shelve.communications.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
import com.shelve.communications.entity.Communication;
import com.shelve.communications.repository.CommunicationRepository;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.time.LocalDate;
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
@RequestMapping(value = {"/api/v1/communications"})
public class CommunicationController extends GenericCrudController<Communication> {
  private final CommunicationRepository communicationRepository;

  public CommunicationController(CommunicationRepository communicationRepository) {
    this.communicationRepository = communicationRepository;
  }

  @Override
  protected Class<Communication> entityClass() {
    return Communication.class;
  }

  @Override
  protected JpaSpecificationExecutor<Communication> repository() {
    return this.communicationRepository;
  }

  @Override
  protected String resource() {
    return "communication";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "id",
        "code",
        "name",
        "operator_organisation_id",
        "user_id",
        "user_organisation_id",
        "return_date",
        "return_effective",
        "status",
        "created_at",
        "updated_at");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected Communication newEntity() {
    return new Communication();
  }

  @Override
  protected String location(Communication entity) {
    return "/api/v1/communications/" + entity.getId();
  }

  @Override
  protected Specification<Communication> scope(AuthenticatedUser auth) {
    Long orgId = auth.user().getCurrentOrganisationId();
    return (Specification & Serializable)
        (root, query, cb) ->
            cb.or(
                (Expression)
                    cb.equal((Expression) root.get("operatorOrganisationId"), (Object) orgId),
                (Expression) cb.equal((Expression) root.get("userOrganisationId"), (Object) orgId));
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    String name = CommunicationController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("name", name, "The name field is required.")
            .max("name", name, 200, "name");
    if (CommunicationController.longOf(body.get("user_id")) == null) {
      v.add("user_id", "The user id field is required.");
    }
    if (CommunicationController.longOf(body.get("user_organisation_id")) == null) {
      v.add("user_organisation_id", "The user organisation id field is required.");
    }
    if (body.get("return_date") == null) {
      v.add("return_date", "The return date field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Communication c) {
    AuthenticatedUser auth = CurrentUser.get();
    c.setCode(this.generateCode("C"));
    c.setName(CommunicationController.str(body.get("name")));
    c.setContent(CommunicationController.str(body.get("content")));
    c.setUserId(CommunicationController.longOf(body.get("user_id")));
    c.setUserOrganisationId(CommunicationController.longOf(body.get("user_organisation_id")));
    c.setReturnDate(CommunicationController.dateOf(body.get("return_date")));
    c.setStatus(CommunicationController.str(body.get("status")));
    c.setOperatorId(auth.user().getId());
    c.setOperatorOrganisationId(auth.user().getCurrentOrganisationId());
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Communication c) {
    if (body.containsKey("name")) {
      c.setName(CommunicationController.str(body.get("name")));
    }
    if (body.containsKey("content")) {
      c.setContent(CommunicationController.str(body.get("content")));
    }
    if (body.containsKey("user_id")) {
      c.setUserId(CommunicationController.longOf(body.get("user_id")));
    }
    if (body.containsKey("user_organisation_id")) {
      c.setUserOrganisationId(CommunicationController.longOf(body.get("user_organisation_id")));
    }
    if (body.containsKey("return_date")) {
      c.setReturnDate(CommunicationController.dateOf(body.get("return_date")));
    }
    if (body.containsKey("return_effective")) {
      c.setReturnEffective(CommunicationController.dateOf(body.get("return_effective")));
    }
    if (body.containsKey("status")) {
      c.setStatus(CommunicationController.str(body.get("status")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Communication c) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", c.getId());
    map.put("code", c.getCode());
    map.put("name", c.getName());
    map.put("content", c.getContent());
    map.put("operator_id", c.getOperatorId());
    map.put("operator_organisation_id", c.getOperatorOrganisationId());
    map.put("user_id", c.getUserId());
    map.put("user_organisation_id", c.getUserOrganisationId());
    map.put("return_date", c.getReturnDate() != null ? c.getReturnDate().toString() : null);
    map.put(
        "return_effective",
        c.getReturnEffective() != null ? c.getReturnEffective().toString() : null);
    map.put("status", c.getStatus());
    map.put("created_at", Json.timestamp(c.getCreatedAt()));
    map.put("updated_at", Json.timestamp(c.getUpdatedAt()));
    return map;
  }

  private String generateCode(String prefix) {
    String year = String.valueOf(LocalDate.now().getYear());
    long count = this.communicationRepository.count();
    return prefix + year + String.format("%04d", count + 1L);
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
