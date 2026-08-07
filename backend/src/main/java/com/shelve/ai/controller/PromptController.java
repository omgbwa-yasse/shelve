package com.shelve.ai.controller;

import com.shelve.ai.entity.Prompt;
import com.shelve.ai.repository.PromptRepository;
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
@RequestMapping(value = {"/api/v1/prompts"})
public class PromptController extends GenericCrudController<Prompt> {
  private final PromptRepository repo;

  public PromptController(PromptRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<Prompt> entityClass() {
    return Prompt.class;
  }

  @Override
  protected JpaSpecificationExecutor<Prompt> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "prompt";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "title", "content", "is_system", "organisation_id", "user_id", "prompt_category_id");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected Prompt newEntity() {
    return new Prompt();
  }

  @Override
  protected String location(Prompt e) {
    return "/api/v1/prompts/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (PromptController.str(body.get("content")) == null) {
      v.add("content", "The content field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Prompt e) {
    AuthenticatedUser auth = CurrentUser.get();
    e.setOrganisationId(auth.user().getCurrentOrganisationId());
    e.setUserId(auth.user().getId());
    if (body.containsKey("title")) {
      e.setTitle(PromptController.str(body.get("title")));
    }
    if (body.containsKey("content")) {
      e.setContent(PromptController.str(body.get("content")));
    }
    if (body.containsKey("is_system")) {
      e.setIsSystem(PromptController.bool(body.get("is_system")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(PromptController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("user_id")) {
      e.setUserId(PromptController.longOf(body.get("user_id")));
    }
    if (body.containsKey("prompt_category_id")) {
      e.setPromptCategoryId(PromptController.longOf(body.get("prompt_category_id")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Prompt e) {
    if (body.containsKey("title")) {
      e.setTitle(PromptController.str(body.get("title")));
    }
    if (body.containsKey("content")) {
      e.setContent(PromptController.str(body.get("content")));
    }
    if (body.containsKey("is_system")) {
      e.setIsSystem(PromptController.bool(body.get("is_system")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(PromptController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("user_id")) {
      e.setUserId(PromptController.longOf(body.get("user_id")));
    }
    if (body.containsKey("prompt_category_id")) {
      e.setPromptCategoryId(PromptController.longOf(body.get("prompt_category_id")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Prompt e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("title", e.getTitle());
    map.put("content", e.getContent());
    map.put("is_system", e.getIsSystem() != null && e.getIsSystem() != false);
    map.put("organisation_id", e.getOrganisationId());
    map.put("user_id", e.getUserId());
    map.put("prompt_category_id", e.getPromptCategoryId());
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
