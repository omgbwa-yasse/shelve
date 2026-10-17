package com.shelve.records.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.common.GenericCrudController;
import com.shelve.records.entity.RecordType;
import com.shelve.records.repository.RecordTypeRepository;
import com.shelve.referentials.service.MetadataFieldService;
import com.shelve.organisation.repository.UserRoleRepository;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import jakarta.persistence.criteria.Expression;
import jakarta.servlet.http.HttpServletRequest;
import java.io.Serializable;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;
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
@RequestMapping(value = {"/api/v1/record-types"})
public class RecordTypeController extends GenericCrudController<RecordType> {
  private final RecordTypeRepository repo;
  private final MetadataFieldService metadataFieldService;
  private final UserRoleRepository userRoleRepository;

  public RecordTypeController(
      RecordTypeRepository repo,
      MetadataFieldService metadataFieldService,
      UserRoleRepository userRoleRepository) {
    this.repo = repo;
    this.metadataFieldService = metadataFieldService;
    this.userRoleRepository = userRoleRepository;
  }

  @Override
  protected Class<RecordType> entityClass() {
    return RecordType.class;
  }

  @Override
  protected JpaSpecificationExecutor<RecordType> repository() {
    return this.repo;
  }

  @Override
  protected String resource() {
    return "record_type";
  }

  @Override
  protected List<String> filterable() {
    return List.of("code", "name", "description", "is_container", "reference_list_id", "is_active");
  }

  @Override
  protected List<String> sortable() {
    return List.of("code", "name", "id", "is_container", "display_order");
  }

  @Override
  protected RecordType newEntity() {
    return new RecordType();
  }

  @Override
  protected String location(RecordType e) {
    return "/api/v1/record-types/" + e.getId();
  }

  @Override
  protected void validateCreate(Map<String, Object> body) {
    Validator v = Validator.begin();
    if (RecordTypeController.str(body.get("code")) == null) {
      v.add("code", "The code field is required.");
    }
    if (RecordTypeController.str(body.get("name")) == null) {
      v.add("name", "The name field is required.");
    }
    if (RecordTypeController.str(body.get("code")) != null
        && this.repository()
                .findAll(
                    (Specification & Serializable)
                        (root, q, cb) ->
                            cb.equal(
                                (Expression) root.get("code"),
                                (Object) RecordTypeController.str(body.get("code"))))
                .size()
            > 0) {
      v.add("code", "The code has already been taken.");
    }
    v.validate();
  }

  @Override
  protected void applyCreate(Map<String, Object> body, RecordType e) {
    if (body.containsKey("code")) {
      e.setCode(RecordTypeController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(RecordTypeController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(RecordTypeController.str(body.get("description")));
    }
    if (body.containsKey("is_container")) {
      e.setIsContainer(RecordTypeController.bool(body.get("is_container")));
    }
    if (body.containsKey("reference_list_id")) {
      e.setReferenceListId(RecordTypeController.longOf(body.get("reference_list_id")));
    }
    if (body.containsKey("code_prefix")) {
      e.setCodePrefix(RecordTypeController.str(body.get("code_prefix")));
    }
    if (body.containsKey("code_pattern")) {
      e.setCodePattern(RecordTypeController.str(body.get("code_pattern")));
    }
    if (body.containsKey("is_active")) {
      e.setIsActive(RecordTypeController.bool(body.get("is_active")));
    }
    if (body.containsKey("display_order")) {
      e.setDisplayOrder(RecordTypeController.intOf(body.get("display_order")));
    }
    if (body.containsKey("requires_versioning")) {
      e.setRequiresVersioning(RecordTypeController.bool(body.get("requires_versioning")));
    }
    if (body.containsKey("requires_approval")) {
      e.setRequiresApproval(RecordTypeController.bool(body.get("requires_approval")));
    }
    if (body.containsKey("default_access_level")) {
      e.setDefaultAccessLevel(RecordTypeController.str(body.get("default_access_level")));
    }
    e.setCreatedBy(com.shelve.security.CurrentUser.get().user().getId());
  }

  @Override
  protected void applyUpdate(Map<String, Object> body, RecordType e) {
    if (body.containsKey("code")) {
      e.setCode(RecordTypeController.str(body.get("code")));
    }
    if (body.containsKey("name")) {
      e.setName(RecordTypeController.str(body.get("name")));
    }
    if (body.containsKey("description")) {
      e.setDescription(RecordTypeController.str(body.get("description")));
    }
    if (body.containsKey("is_container")) {
      e.setIsContainer(RecordTypeController.bool(body.get("is_container")));
    }
    if (body.containsKey("reference_list_id")) {
      e.setReferenceListId(RecordTypeController.longOf(body.get("reference_list_id")));
    }
    if (body.containsKey("code_prefix")) {
      e.setCodePrefix(RecordTypeController.str(body.get("code_prefix")));
    }
    if (body.containsKey("code_pattern")) {
      e.setCodePattern(RecordTypeController.str(body.get("code_pattern")));
    }
    if (body.containsKey("is_active")) {
      e.setIsActive(RecordTypeController.bool(body.get("is_active")));
    }
    if (body.containsKey("display_order")) {
      e.setDisplayOrder(RecordTypeController.intOf(body.get("display_order")));
    }
    if (body.containsKey("requires_versioning")) {
      e.setRequiresVersioning(RecordTypeController.bool(body.get("requires_versioning")));
    }
    if (body.containsKey("requires_approval")) {
      e.setRequiresApproval(RecordTypeController.bool(body.get("requires_approval")));
    }
    if (body.containsKey("default_access_level")) {
      e.setDefaultAccessLevel(RecordTypeController.str(body.get("default_access_level")));
    }
    e.setUpdatedBy(com.shelve.security.CurrentUser.get().user().getId());
  }

  @Override
  protected Map<String, Object> mapper(RecordType e) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", e.getId());
    map.put("code", e.getCode());
    map.put("name", e.getName());
    map.put("description", e.getDescription());
    map.put("is_container", e.isContainer());
    map.put("reference_list_id", e.getReferenceListId());
    map.put("code_prefix", e.getCodePrefix());
    map.put("code_pattern", e.getCodePattern());
    map.put("is_active", e.getIsActive() != null && e.getIsActive() != false);
    map.put("display_order", e.getDisplayOrder());
    map.put("requires_versioning", e.getRequiresVersioning() != null && e.getRequiresVersioning() != false);
    map.put("requires_approval", e.getRequiresApproval() != null && e.getRequiresApproval() != false);
    map.put("default_access_level", e.getDefaultAccessLevel());
    map.put("created_by", e.getCreatedBy());
    map.put("updated_by", e.getUpdatedBy());
    map.put("created_at", Json.timestamp(e.getCreatedAt()));
    map.put("updated_at", Json.timestamp(e.getUpdatedAt()));
    return map;
  }

  /**
   * GET /api/v1/record-types/{id}/metadata-fields — champs visibles de la
   * typologie (profil + définition, métadonnées copiées/calculées résolues,
   * sécurisation par rôles appliquée). Consommé par le formulaire de notice.
   */
  @GetMapping(value = {"/{id}/metadata-fields"})
  public Map<String, Object> metadataFields(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "record_type_view");
    this.repo.findById(id).orElseThrow(() -> com.shelve.exception.ApiException.notFound());
    Set<String> roles =
        auth.isSuperAdmin()
            ? Set.of("superadmin")
            : Set.copyOf(this.userRoleRepository.findRoleNamesByUserId(auth.user().getId()));
    return Json.of("data", this.metadataFieldService.fieldsForType(id, roles, Map.of()));
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
