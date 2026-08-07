package com.shelve.ai.controller;

import com.shelve.ai.entity.AiTemplate;
import com.shelve.ai.repository.AiTemplateRepository;
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
@RequestMapping(value = {"/api/v1/ai-templates"})
public class AiTemplateController extends GenericCrudController<AiTemplate> {
  private final AiTemplateRepository repo;

  public AiTemplateController(AiTemplateRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<AiTemplate> entityClass() {
    return AiTemplate.class;
  }

  @Override
  protected JpaSpecificationExecutor<AiTemplate> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "ai_template";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "name",
        "category",
        "file_name",
        "file_path",
        "mime_type",
        "size",
        "description",
        "created_by");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected AiTemplate newEntity() {
    return new AiTemplate();
  }

  @Override
  protected String location(AiTemplate e) {
    return "/api/v1/ai-templates/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (AiTemplateController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, AiTemplate e) {
    e.setCreatedBy(CurrentUser.get().user().getId());
    if (body.containsKey("name")) {
      e.setName(AiTemplateController.str(body.get("name")));
    }
    if (body.containsKey("category")) {
      e.setCategory(AiTemplateController.str(body.get("category")));
    }
    if (body.containsKey("file_name")) {
      e.setFileName(AiTemplateController.str(body.get("file_name")));
    }
    if (body.containsKey("file_path")) {
      e.setFilePath(AiTemplateController.str(body.get("file_path")));
    }
    if (body.containsKey("mime_type")) {
      e.setMimeType(AiTemplateController.str(body.get("mime_type")));
    }
    if (body.containsKey("size")) {
      e.setSize(AiTemplateController.longOf(body.get("size")));
    }
    if (body.containsKey("description")) {
      e.setDescription(AiTemplateController.str(body.get("description")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(AiTemplateController.longOf(body.get("created_by")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, AiTemplate e) {
    if (body.containsKey("name")) {
      e.setName(AiTemplateController.str(body.get("name")));
    }
    if (body.containsKey("category")) {
      e.setCategory(AiTemplateController.str(body.get("category")));
    }
    if (body.containsKey("file_name")) {
      e.setFileName(AiTemplateController.str(body.get("file_name")));
    }
    if (body.containsKey("file_path")) {
      e.setFilePath(AiTemplateController.str(body.get("file_path")));
    }
    if (body.containsKey("mime_type")) {
      e.setMimeType(AiTemplateController.str(body.get("mime_type")));
    }
    if (body.containsKey("size")) {
      e.setSize(AiTemplateController.longOf(body.get("size")));
    }
    if (body.containsKey("description")) {
      e.setDescription(AiTemplateController.str(body.get("description")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(AiTemplateController.longOf(body.get("created_by")));
    }
  }

  @Override
  protected Map<String, Object> mapper(AiTemplate e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("name", e.getName());
    map.put("category", e.getCategory());
    map.put("file_name", e.getFileName());
    map.put("file_path", e.getFilePath());
    map.put("mime_type", e.getMimeType());
    map.put("size", e.getSize());
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
