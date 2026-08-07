package com.shelve.mails.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.mails.entity.MailAction;
import com.shelve.mails.repository.MailActionRepository;
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
@RequestMapping(value = {"/api/v1/mail-actions"})
public class MailActionController extends GenericCrudController<MailAction> {
  private final MailActionRepository repo;

  public MailActionController(MailActionRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<MailAction> entityClass() {
    return MailAction.class;
  }

  @Override
  protected JpaSpecificationExecutor<MailAction> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "mail_action";
  }

  @Override
  protected List<String> filterable() {
    return List.of("name", "duration", "to_return", "description");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected MailAction newEntity() {
    return new MailAction();
  }

  @Override
  protected String location(MailAction e) {
    return "/api/v1/mail-actions/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (MailActionController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    if (MailActionController.str(body.get("duration")) == null) {
      v.add("duration", "The duration field is required.");
    }
    if (MailActionController.str(body.get("name")) != null
        && this.repository()
                .findAll(
                    (Specification & Serializable)
                        (root, q, cb) ->
                            cb.equal(
                                (Expression) root.get("name"),
                                (Object) MailActionController.str(body.get("name"))))
                .size()
            > 0) {
      v.add("name", "The name has already been taken.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, MailAction e) {
    if (body.containsKey("name")) {
      e.setName(MailActionController.str(body.get("name")));
    }
    if (body.containsKey("duration")) {
      e.setDuration(MailActionController.intOf(body.get("duration")));
    }
    if (body.containsKey("to_return")) {
      e.setToReturn(MailActionController.bool(body.get("to_return")));
    }
    if (body.containsKey("description")) {
      e.setDescription(MailActionController.str(body.get("description")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, MailAction e) {
    if (body.containsKey("name")) {
      e.setName(MailActionController.str(body.get("name")));
    }
    if (body.containsKey("duration")) {
      e.setDuration(MailActionController.intOf(body.get("duration")));
    }
    if (body.containsKey("to_return")) {
      e.setToReturn(MailActionController.bool(body.get("to_return")));
    }
    if (body.containsKey("description")) {
      e.setDescription(MailActionController.str(body.get("description")));
    }
  }

  @Override
  protected Map<String, Object> mapper(MailAction e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("name", e.getName());
    map.put("duration", e.getDuration());
    map.put("to_return", e.getToReturn() != null && e.getToReturn() != false);
    map.put("description", e.getDescription());
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
