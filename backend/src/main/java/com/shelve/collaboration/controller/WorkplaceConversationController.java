package com.shelve.collaboration.controller;

import com.shelve.collaboration.entity.WorkplaceConversation;
import com.shelve.collaboration.repository.WorkplaceConversationRepository;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
import jakarta.servlet.http.HttpServletRequest;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
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
@RequestMapping(value = {"/api/v1/workplace-conversations"})
public class WorkplaceConversationController extends GenericCrudController<WorkplaceConversation> {
  private final WorkplaceConversationRepository repo;

  public WorkplaceConversationController(WorkplaceConversationRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<WorkplaceConversation> entityClass() {
    return WorkplaceConversation.class;
  }

  @Override
  protected JpaSpecificationExecutor<WorkplaceConversation> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "workplace_conversation";
  }

  @Override
  protected List<String> filterable() {
    return List.of("workplace_id", "type", "name", "description", "created_by");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected WorkplaceConversation newEntity() {
    return new WorkplaceConversation();
  }

  @Override
  protected String location(WorkplaceConversation e) {
    return "/api/v1/workplace-conversations/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (WorkplaceConversationController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, WorkplaceConversation e) {
    e.setCreatedBy(CurrentUser.get().user().getId());
    if (body.containsKey("workplace_id")) {
      e.setWorkplaceId(WorkplaceConversationController.longOf(body.get("workplace_id")));
    }
    if (body.containsKey("type")) {
      e.setType(WorkplaceConversationController.str(body.get("type")));
    }
    if (body.containsKey("name")) {
      e.setName(WorkplaceConversationController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(WorkplaceConversationController.str(body.get("description")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(WorkplaceConversationController.longOf(body.get("created_by")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, WorkplaceConversation e) {
    if (body.containsKey("workplace_id")) {
      e.setWorkplaceId(WorkplaceConversationController.longOf(body.get("workplace_id")));
    }
    if (body.containsKey("type")) {
      e.setType(WorkplaceConversationController.str(body.get("type")));
    }
    if (body.containsKey("name")) {
      e.setName(WorkplaceConversationController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(WorkplaceConversationController.str(body.get("description")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(WorkplaceConversationController.longOf(body.get("created_by")));
    }
  }

  @Override
  protected Map<String, Object> mapper(WorkplaceConversation e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("workplace_id", e.getWorkplaceId());
    map.put("type", e.getType());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("created_by", e.getCreatedBy());
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
