package com.shelve.ai.controller;

import com.shelve.ai.entity.AiConversation;
import com.shelve.ai.repository.AiConversationRepository;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.AuthenticatedUser;
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
@RequestMapping(value = {"/api/v1/ai/conversations"})
public class AiConversationController extends GenericCrudController<AiConversation> {
  private final AiConversationRepository repo;

  public AiConversationController(AiConversationRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<AiConversation> entityClass() {
    return AiConversation.class;
  }

  @Override
  protected JpaSpecificationExecutor<AiConversation> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "ai_conversation";
  }

  @Override
  protected List<String> filterable() {
    return List.of("organisation_id", "user_id", "title", "context", "archived_at", "mode");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected AiConversation newEntity() {
    return new AiConversation();
  }

  @Override
  protected String location(AiConversation e) {
    return "/api/v1/ai/conversations/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (AiConversationController.str(body.get("title")) == null) {
      v.add("title", "The title field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, AiConversation e) {
    AuthenticatedUser auth = CurrentUser.get();
    e.setOrganisationId(auth.user().getCurrentOrganisationId());
    e.setUserId(auth.user().getId());
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(AiConversationController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("user_id")) {
      e.setUserId(AiConversationController.longOf(body.get("user_id")));
    }
    if (body.containsKey("title")) {
      e.setTitle(AiConversationController.str(body.get("title")));
    }
    if (body.containsKey("context")) {
      e.setContext(AiConversationController.str(body.get("context")));
    }
    if (body.containsKey("archived_at")) {
      e.setArchivedAt(AiConversationController.instantOf(body.get("archived_at")));
    }
    if (body.containsKey("mode")) {
      e.setMode(AiConversationController.str(body.get("mode")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, AiConversation e) {
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(AiConversationController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("user_id")) {
      e.setUserId(AiConversationController.longOf(body.get("user_id")));
    }
    if (body.containsKey("title")) {
      e.setTitle(AiConversationController.str(body.get("title")));
    }
    if (body.containsKey("context")) {
      e.setContext(AiConversationController.str(body.get("context")));
    }
    if (body.containsKey("archived_at")) {
      e.setArchivedAt(AiConversationController.instantOf(body.get("archived_at")));
    }
    if (body.containsKey("mode")) {
      e.setMode(AiConversationController.str(body.get("mode")));
    }
  }

  @Override
  protected Map<String, Object> mapper(AiConversation e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("organisation_id", e.getOrganisationId());
    map.put("user_id", e.getUserId());
    map.put("title", e.getTitle());
    map.put("context", e.getContext());
    map.put("archived_at", Json.timestamp(e.getArchivedAt()));
    map.put("mode", e.getMode());
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
