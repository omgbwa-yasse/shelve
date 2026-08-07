package com.shelve.ai.controller;

import com.shelve.ai.entity.AiSkill;
import com.shelve.ai.repository.AiSkillRepository;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.security.CurrentUser;
import com.shelve.common.GenericCrudController;
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
@RequestMapping(value = {"/api/v1/ai-skills"})
public class AiSkillController extends GenericCrudController<AiSkill> {
  private final AiSkillRepository repo;

  public AiSkillController(AiSkillRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<AiSkill> entityClass() {
    return AiSkill.class;
  }

  @Override
  protected JpaSpecificationExecutor<AiSkill> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "ai_skill";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "slug", "name", "description", "version", "location", "folder", "enabled", "installed_by");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected AiSkill newEntity() {
    return new AiSkill();
  }

  @Override
  protected String location(AiSkill e) {
    return "/api/v1/ai-skills/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (AiSkillController.str(body.get("slug")) == null) {
      v.add("slug", "The slug field is required.");
    }
    if (AiSkillController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    if (AiSkillController.str(body.get("slug")) != null
        && this.repository()
                .findAll(
                    (Specification & Serializable)
                        (root, q, cb) ->
                            cb.equal(
                                (Expression) root.get("slug"),
                                (Object) AiSkillController.str(body.get("slug"))))
                .size()
            > 0) {
      v.add("slug", "The slug has already been taken.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, AiSkill e) {
    e.setInstalledBy(CurrentUser.get().user().getId());
    if (body.containsKey("slug")) {
      e.setSlug(AiSkillController.str(body.get("slug")));
    }
    if (body.containsKey("name")) {
      e.setName(AiSkillController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(AiSkillController.str(body.get("description")));
    }
    if (body.containsKey("version")) {
      e.setVersion(AiSkillController.str(body.get("version")));
    }
    if (body.containsKey("location")) {
      e.setLocation(AiSkillController.str(body.get("location")));
    }
    if (body.containsKey("folder")) {
      e.setFolder(AiSkillController.str(body.get("folder")));
    }
    if (body.containsKey("enabled")) {
      e.setEnabled(AiSkillController.bool(body.get("enabled")));
    }
    if (body.containsKey("installed_by")) {
      e.setInstalledBy(AiSkillController.longOf(body.get("installed_by")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, AiSkill e) {
    if (body.containsKey("slug")) {
      e.setSlug(AiSkillController.str(body.get("slug")));
    }
    if (body.containsKey("name")) {
      e.setName(AiSkillController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(AiSkillController.str(body.get("description")));
    }
    if (body.containsKey("version")) {
      e.setVersion(AiSkillController.str(body.get("version")));
    }
    if (body.containsKey("location")) {
      e.setLocation(AiSkillController.str(body.get("location")));
    }
    if (body.containsKey("folder")) {
      e.setFolder(AiSkillController.str(body.get("folder")));
    }
    if (body.containsKey("enabled")) {
      e.setEnabled(AiSkillController.bool(body.get("enabled")));
    }
    if (body.containsKey("installed_by")) {
      e.setInstalledBy(AiSkillController.longOf(body.get("installed_by")));
    }
  }

  @Override
  protected Map<String, Object> mapper(AiSkill e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("slug", e.getSlug());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("version", e.getVersion());
    map.put("location", e.getLocation());
    map.put("folder", e.getFolder());
    map.put("enabled", e.getEnabled() != null && e.getEnabled() != false);
    map.put("installed_by", e.getInstalledBy());
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
