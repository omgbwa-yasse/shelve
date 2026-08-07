package com.shelve.collaboration.controller;

import com.shelve.collaboration.entity.Workplace;
import com.shelve.collaboration.repository.WorkplaceRepository;
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
@RequestMapping(value = {"/api/v1/workplaces"})
public class WorkplaceController extends GenericCrudController<Workplace> {
  private final WorkplaceRepository repo;

  public WorkplaceController(WorkplaceRepository repo) {
    this.repo = repo;
  }

  @Override
  protected Class<Workplace> entityClass() {
    return Workplace.class;
  }

  @Override
  protected JpaSpecificationExecutor<Workplace> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "workplace";
  }

  @Override
  protected List<String> filterable() {
    return List.of(
        "code",
        "name",
        "description",
        "icon",
        "color",
        "settings",
        "is_public",
        "allow_external_sharing",
        "status",
        "start_date",
        "end_date",
        "organisation_id",
        "owner_id",
        "created_by");
  }

  @Override
  protected List<String> sortable() {
    return this.filterable();
  }

  @Override
  protected Workplace newEntity() {
    return new Workplace();
  }

  @Override
  protected String location(Workplace e) {
    return "/api/v1/workplaces/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (WorkplaceController.str(body.get("code")) == null) {
      v.add("code", "The code field is required.");
    }
    if (WorkplaceController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, Workplace e) {
    AuthenticatedUser auth = CurrentUser.get();
    e.setOrganisationId(auth.user().getCurrentOrganisationId());
    e.setOwnerId(auth.user().getId());
    e.setCreatedBy(auth.user().getId());
    if (body.containsKey("code")) {
      e.setCode(WorkplaceController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(WorkplaceController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(WorkplaceController.str(body.get("description")));
    }
    if (body.containsKey("icon")) {
      e.setIcon(WorkplaceController.str(body.get("icon")));
    }
    if (body.containsKey("color")) {
      e.setColor(WorkplaceController.str(body.get("color")));
    }
    if (body.containsKey("settings")) {
      e.setSettings(WorkplaceController.str(body.get("settings")));
    }
    if (body.containsKey("is_public")) {
      e.setIsPublic(WorkplaceController.bool(body.get("is_public")));
    }
    if (body.containsKey("allow_external_sharing")) {
      e.setAllowExternalSharing(WorkplaceController.bool(body.get("allow_external_sharing")));
    }
    if (body.containsKey("status")) {
      e.setStatus(WorkplaceController.str(body.get("status")));
    }
    if (body.containsKey("start_date")) {
      e.setStartDate(WorkplaceController.dateOf(body.get("start_date")));
    }
    if (body.containsKey("end_date")) {
      e.setEndDate(WorkplaceController.dateOf(body.get("end_date")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(WorkplaceController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("owner_id")) {
      e.setOwnerId(WorkplaceController.longOf(body.get("owner_id")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(WorkplaceController.longOf(body.get("created_by")));
    }
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, Workplace e) {
    if (body.containsKey("code")) {
      e.setCode(WorkplaceController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(WorkplaceController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(WorkplaceController.str(body.get("description")));
    }
    if (body.containsKey("icon")) {
      e.setIcon(WorkplaceController.str(body.get("icon")));
    }
    if (body.containsKey("color")) {
      e.setColor(WorkplaceController.str(body.get("color")));
    }
    if (body.containsKey("settings")) {
      e.setSettings(WorkplaceController.str(body.get("settings")));
    }
    if (body.containsKey("is_public")) {
      e.setIsPublic(WorkplaceController.bool(body.get("is_public")));
    }
    if (body.containsKey("allow_external_sharing")) {
      e.setAllowExternalSharing(WorkplaceController.bool(body.get("allow_external_sharing")));
    }
    if (body.containsKey("status")) {
      e.setStatus(WorkplaceController.str(body.get("status")));
    }
    if (body.containsKey("start_date")) {
      e.setStartDate(WorkplaceController.dateOf(body.get("start_date")));
    }
    if (body.containsKey("end_date")) {
      e.setEndDate(WorkplaceController.dateOf(body.get("end_date")));
    }
    if (body.containsKey("organisation_id")) {
      e.setOrganisationId(WorkplaceController.longOf(body.get("organisation_id")));
    }
    if (body.containsKey("owner_id")) {
      e.setOwnerId(WorkplaceController.longOf(body.get("owner_id")));
    }
    if (body.containsKey("created_by")) {
      e.setCreatedBy(WorkplaceController.longOf(body.get("created_by")));
    }
  }

  @Override
  protected Map<String, Object> mapper(Workplace e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("code", e.getCode());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("icon", e.getIcon());
    map.put("color", e.getColor());
    map.put("settings", e.getSettings());
    map.put("is_public", e.getIsPublic() != null && e.getIsPublic() != false);
    map.put(
        "allow_external_sharing",
        e.getAllowExternalSharing() != null && e.getAllowExternalSharing() != false);
    map.put("status", e.getStatus());
    map.put("start_date", e.getStartDate() != null ? e.getStartDate().toString() : null);
    map.put("end_date", e.getEndDate() != null ? e.getEndDate().toString() : null);
    map.put("organisation_id", e.getOrganisationId());
    map.put("owner_id", e.getOwnerId());
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
