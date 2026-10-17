package com.shelve.referentials.controller;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.exception.ApiException;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import com.shelve.common.Filters;
import com.shelve.common.Paging;
import com.shelve.common.QueryParams;
import com.shelve.referentials.entity.MetadataDefinition;
import com.shelve.referentials.repository.MetadataDefinitionRepository;
import jakarta.servlet.http.HttpServletRequest;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.data.jpa.domain.Specification;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/**
 * CRUD des définitions de métadonnées (`metadata_definitions`) — aligné Constellio.
 * Expose copie, calcul, sécurité et options de comportement des métadonnées.
 */
@RestController
@RequestMapping(value = {"/api/v1/metadata-definitions"})
public class MetadataDefinitionController {
  private static final List<String> FILTERABLE =
      List.of("id", "name", "code", "data_type", "is_system", "active", "reference_list_id");
  private static final List<String> SORTABLE =
      List.of("id", "name", "code", "data_type", "sort_order", "created_at", "updated_at");

  private final MetadataDefinitionRepository repo;
  private final ObjectMapper mapper;

  public MetadataDefinitionController(MetadataDefinitionRepository repo, ObjectMapper mapper) {
    this.repo = repo;
    this.mapper = mapper;
  }

  @GetMapping
  public Map<String, Object> index(HttpServletRequest request) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "metadata_definition_viewAny");
    QueryParams qp = QueryParams.parse(request);
    qp.validate(FILTERABLE, SORTABLE, List.of());
    Specification<MetadataDefinition> spec = Filters.of(qp.getFilters(), MetadataDefinition.class);
    return Paging.page(this.repo, spec, qp, SORTABLE, "id", request, this::mapper);
  }

  @GetMapping(value = {"/{id}"})
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "metadata_definition_view");
    MetadataDefinition def = this.find(id);
    return Json.of("data", this.mapper(def));
  }

  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "metadata_definition_create");
    String code = MetadataDefinitionController.str(body.get("code"));
    String name = MetadataDefinitionController.str(body.get("name"));
    Validator v =
        Validator.begin()
            .require("code", code, "The code field is required.")
            .unique("code", code, code != null && this.repo.findByCode(code).isPresent(), "metadata_definitions", "code")
            .require("name", name, "The name field is required.");
    v.validate();
    MetadataDefinition def = new MetadataDefinition();
    this.apply(body, def, auth);
    this.repo.save(def);
    return ((ResponseEntity.BodyBuilder)
            ResponseEntity.status((int) 201)
                .header("Location", new String[] {"/api/v1/metadata-definitions/" + def.getId()}))
        .body(Json.of("data", this.mapper(def)));
  }

  @PatchMapping(value = {"/{id}"})
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "metadata_definition_update");
    MetadataDefinition def = this.find(id);
    this.apply(body, def, auth);
    this.repo.save(def);
    return Json.of("data", this.mapper(def));
  }

  @DeleteMapping(value = {"/{id}"})
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "metadata_definition_delete");
    this.repo.delete(this.find(id));
    return ResponseEntity.noContent().build();
  }

  /* ------------------------------------------------------------------ */
  /*  Mapper                                                             */
  /* ------------------------------------------------------------------ */

  Map<String, Object> mapper(MetadataDefinition def) {
    LinkedHashMap<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("id", def.getId());
    map.put("name", def.getName());
    map.put("code", def.getCode());
    map.put("description", def.getDescription());
    map.put("data_type", def.getDataType());
    map.put("validation_rules", this.parseJson(def.getValidationRules()));
    map.put("options", this.parseJson(def.getOptions()));
    map.put("reference_list_id", def.getReferenceListId());
    map.put("searchable", def.getSearchable() != null && def.getSearchable() != false);
    map.put("sortable", def.getSortable() != null && def.getSortable() != false);
    map.put("highlightable", def.getHighlightable() != null && def.getHighlightable() != false);
    map.put("autocomplete", def.getAutocomplete() != null && def.getAutocomplete() != false);
    map.put("unique", def.getUnique() != null && def.getUnique() != false);
    map.put("input_mask", def.getInputMask());
    map.put("max_length", def.getMaxLength());
    map.put("copy_source_type", def.getCopySourceType());
    map.put("copy_source_field", def.getCopySourceField());
    map.put("computed_template", def.getComputedTemplate());
    map.put("active", def.getActive() != null && def.getActive() != false);
    map.put("is_system", def.getIsSystem() != null && def.getIsSystem() != false);
    map.put("sort_order", def.getSortOrder());
    map.put("created_by", def.getCreatedBy());
    map.put("updated_by", def.getUpdatedBy());
    map.put("created_at", Json.timestamp(def.getCreatedAt()));
    map.put("updated_at", Json.timestamp(def.getUpdatedAt()));
    return map;
  }

  private void apply(Map<String, Object> body, MetadataDefinition def, AuthenticatedUser auth) {
    if (body.containsKey("name")) def.setName(MetadataDefinitionController.str(body.get("name")));
    if (body.containsKey("code")) def.setCode(MetadataDefinitionController.str(body.get("code")));
    if (body.containsKey("description")) def.setDescription(MetadataDefinitionController.str(body.get("description")));
    if (body.containsKey("data_type")) def.setDataType(MetadataDefinitionController.str(body.get("data_type")));
    if (body.containsKey("validation_rules")) def.setValidationRules(this.toJson(body.get("validation_rules")));
    if (body.containsKey("options")) def.setOptions(this.toJson(body.get("options")));
    if (body.containsKey("reference_list_id")) def.setReferenceListId(MetadataDefinitionController.longOf(body.get("reference_list_id")));
    if (body.containsKey("searchable")) def.setSearchable(MetadataDefinitionController.bool(body.get("searchable")));
    if (body.containsKey("sortable")) def.setSortable(MetadataDefinitionController.bool(body.get("sortable")));
    if (body.containsKey("highlightable")) def.setHighlightable(MetadataDefinitionController.bool(body.get("highlightable")));
    if (body.containsKey("autocomplete")) def.setAutocomplete(MetadataDefinitionController.bool(body.get("autocomplete")));
    if (body.containsKey("unique")) def.setUnique(MetadataDefinitionController.bool(body.get("unique")));
    if (body.containsKey("input_mask")) def.setInputMask(MetadataDefinitionController.str(body.get("input_mask")));
    if (body.containsKey("max_length")) def.setMaxLength(MetadataDefinitionController.intOf(body.get("max_length")));
    if (body.containsKey("copy_source_type")) def.setCopySourceType(MetadataDefinitionController.str(body.get("copy_source_type")));
    if (body.containsKey("copy_source_field")) def.setCopySourceField(MetadataDefinitionController.str(body.get("copy_source_field")));
    if (body.containsKey("computed_template")) def.setComputedTemplate(MetadataDefinitionController.str(body.get("computed_template")));
    if (body.containsKey("active")) def.setActive(MetadataDefinitionController.bool(body.get("active")));
    if (body.containsKey("is_system")) def.setIsSystem(MetadataDefinitionController.bool(body.get("is_system")));
    if (body.containsKey("sort_order")) def.setSortOrder(MetadataDefinitionController.intOf(body.get("sort_order")));
    def.setUpdatedBy(auth.user().getId());
    if (def.getCreatedBy() == null) def.setCreatedBy(auth.user().getId());
  }

  private MetadataDefinition find(Long id) {
    return this.repo.findById(id).orElseThrow(() -> ApiException.notFound());
  }

  private Object parseJson(String json) {
    if (json == null || json.isBlank()) return null;
    try {
      return this.mapper.readValue(json, Object.class);
    } catch (Exception e) {
      return json;
    }
  }

  private String toJson(Object value) {
    if (value == null) return null;
    try {
      return this.mapper.writeValueAsString(value);
    } catch (Exception e) {
      return String.valueOf(value);
    }
  }

  private static String str(Object value) { return value != null ? String.valueOf(value) : null; }
  private static boolean bool(Object value) {
    if (value == null) return false;
    if (value instanceof Boolean b) return b;
    return Boolean.parseBoolean(String.valueOf(value));
  }
  private static Long longOf(Object value) {
    if (value == null) return null;
    try { return ((Number) value).longValue(); } catch (ClassCastException e) {
      try { return Long.parseLong(String.valueOf(value)); } catch (NumberFormatException e2) { return null; }
    }
  }
  private static Integer intOf(Object value) {
    if (value == null) return null;
    if (value instanceof Number n) return n.intValue();
    try { return Integer.parseInt(String.valueOf(value)); } catch (NumberFormatException e) { return null; }
  }
}
