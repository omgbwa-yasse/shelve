package com.shelve.ai.controller;

import com.shelve.ai.entity.AiRoutine;
import com.shelve.ai.repository.AiRoutineRepository;
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
@RequestMapping(value = {"/api/v1/ai/routines"})
public class AiRoutineController extends GenericCrudController<AiRoutine> {
  private final AiRoutineRepository repo;

  public AiRoutineController(AiRoutineRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<AiRoutine> entityClass() {
    return AiRoutine.class;
  }

  @Override
  protected JpaSpecificationExecutor<AiRoutine> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "ai_routine";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "organisation_id",
        "created_by",
        "name",
        "description",
        "prompt_id",
        "skill_id",
        "schedule_type",
        "is_enabled",
        "last_status",
        "last_output");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected AiRoutine newEntity() {
    return new AiRoutine();
  }

  @Override
  protected String location(AiRoutine e) {
    return "/api/v1/ai/routines/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (AiRoutineController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, AiRoutine e) {
    AuthenticatedUser auth = CurrentUser.get();
    e.setOrganisationId(auth.user().getCurrentOrganisationId());
    e.setCreatedBy(auth.user().getId());
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(AiRoutineController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(AiRoutineController.longOf(body.get("created_by")));
    }
    if (body.containsKey("name")) {
      e.setName(AiRoutineController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(AiRoutineController.str(body.get("description")));
    }
    if (body.containsKey("prompt_id")) {
      e.setPromptId(AiRoutineController.longOf(body.get("prompt_id")));
    }
    if (body.containsKey("skill_id")) {
      e.setSkillId(AiRoutineController.longOf(body.get("skill_id")));
    }
    if (body.containsKey("schedule_type")) {
      e.setScheduleType(AiRoutineController.str(body.get("schedule_type")));
    }
    if (body.containsKey("is_enabled")) {
      e.setIsEnabled(AiRoutineController.bool(body.get("is_enabled")));
    }
    if (body.containsKey("last_status")) {
      e.setLastStatus(AiRoutineController.str(body.get("last_status")));
    }
    if (body.containsKey("last_output")) {
      e.setLastOutput(AiRoutineController.str(body.get("last_output")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, AiRoutine e) {
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(AiRoutineController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(AiRoutineController.longOf(body.get("created_by")));
    }
    if (body.containsKey("name")) {
      e.setName(AiRoutineController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(AiRoutineController.str(body.get("description")));
    }
    if (body.containsKey("prompt_id")) {
      e.setPromptId(AiRoutineController.longOf(body.get("prompt_id")));
    }
    if (body.containsKey("skill_id")) {
      e.setSkillId(AiRoutineController.longOf(body.get("skill_id")));
    }
    if (body.containsKey("schedule_type")) {
      e.setScheduleType(AiRoutineController.str(body.get("schedule_type")));
    }
    if (body.containsKey("is_enabled")) {
      e.setIsEnabled(AiRoutineController.bool(body.get("is_enabled")));
    }
    if (body.containsKey("last_status")) {
      e.setLastStatus(AiRoutineController.str(body.get("last_status")));
    }
    if (body.containsKey("last_output")) {
      e.setLastOutput(AiRoutineController.str(body.get("last_output")));
    }
  }

  @Override
  protected Map<String, Object> mapper(AiRoutine e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("organisation_id", e.getOrganisationId());
    map.put("created_by", e.getCreatedBy());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("prompt_id", e.getPromptId());
    map.put("skill_id", e.getSkillId());
    map.put("schedule_type", e.getScheduleType());
    map.put("is_enabled", e.getIsEnabled() != null && e.getIsEnabled() != false);
    map.put("last_status", e.getLastStatus());
    map.put("last_output", e.getLastOutput());
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
