package com.shelve.opac.controller;

import com.shelve.common.Json;
import com.shelve.exception.ApiException;
import com.shelve.exception.ValidationException;
import com.shelve.opac.entity.OpacBlock;
import com.shelve.opac.entity.OpacPage;
import com.shelve.opac.entity.OpacPageVersion;
import com.shelve.opac.repository.OpacBlockRepository;
import com.shelve.opac.repository.OpacPageRepository;
import com.shelve.opac.repository.OpacPageVersionRepository;
import com.shelve.opac.service.OpacJson;
import com.shelve.opac.service.OpacMapper;
import com.shelve.opac.service.OpacModuleRegistry;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import java.time.Instant;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.transaction.annotation.Transactional;

/** Versions d'une page OPAC — cycle de vie (brouillon → publication, branches). */
@RestController
@RequestMapping("/api/v1/opac")
public class OpacVersionController {
  private final OpacPageRepository pages;
  private final OpacPageVersionRepository versions;
  private final OpacBlockRepository blocks;
  private final OpacJson json;
  private final OpacMapper mapper;

  public OpacVersionController(
      OpacPageRepository pages,
      OpacPageVersionRepository versions,
      OpacBlockRepository blocks,
      OpacJson json,
      OpacMapper mapper) {
    this.pages = pages;
    this.versions = versions;
    this.blocks = blocks;
    this.json = json;
    this.mapper = mapper;
  }

  // ============================ HELPERS ============================

  private OpacPage pageOf(OpacPageVersion version, Long orgId) {
    OpacPage page =
        this.pages.findById(version.getOpacPageId()).orElseThrow(ApiException::notFound);
    checkPageScope(page, orgId);
    return page;
  }

  private void checkPageScope(OpacPage page, Long orgId) {
    if (page.getOrganisationId() != null
        && (orgId == null || !page.getOrganisationId().equals(orgId))) {
      throw ApiException.notFound();
    }
  }

  private OpacPageVersion findVersion(Long id, Long orgId) {
    OpacPageVersion version =
        this.versions.findById(id).orElseThrow(ApiException::notFound);
    this.pageOf(version, orgId);
    return version;
  }

  private void requireDraft(OpacPageVersion version) {
    if (!version.isEditable()) {
      throw ApiException.conflict("Cette version est publiée et donc immuable.");
    }
  }

  private void copyBlocks(OpacPageVersion from, OpacPageVersion to) {
    for (OpacBlock source : this.blocks.findAllByOpacPageVersionIdOrderByZoneAscOrderAsc(from.getId())) {
      OpacBlock block = new OpacBlock();
      block.setOpacPageVersionId(to.getId());
      block.setZone(source.getZone());
      block.setModuleKey(source.getModuleKey());
      block.setSettings(source.getSettings());
      block.setResponsive(source.getResponsive());
      block.setOrder(source.getOrder());
      this.blocks.save(block);
    }
  }

  private void deactivateOthers(Long pageId, Long exceptId) {
    for (OpacPageVersion v : this.versions.findAllByOpacPageId(pageId)) {
      if (!v.getId().equals(exceptId) && Boolean.TRUE.equals(v.getIsActive())) {
        v.setIsActive(false);
        this.versions.save(v);
      }
    }
  }

  private OpacPageVersion activate(OpacPageVersion version, String description) {
    version.setStatus("published");
    version.setIsActive(true);
    version.setPublishedAt(version.getPublishedAt() != null ? version.getPublishedAt() : Instant.now());
    if (description != null) {
      version.setChangeDescription(description);
    }
    this.versions.save(version);
    this.deactivateOthers(version.getOpacPageId(), version.getId());

    OpacPage page = this.pages.findById(version.getOpacPageId()).orElseThrow(ApiException::notFound);
    page.setStatus("published");
    this.pages.save(page);

    return this.versions.findById(version.getId()).orElseThrow(ApiException::notFound);
  }

  @SuppressWarnings("unchecked")
  private void validateBlocks(List<?> rawBlocks) {
    List<String> zones = OpacModuleRegistry.zoneKeys();
    LinkedHashMap<String, List<String>> errors = new LinkedHashMap<String, List<String>>();

    int index = 0;
    for (Object raw : rawBlocks) {
      Map<String, Object> block =
          raw instanceof Map ? (Map<String, Object>) raw : new LinkedHashMap<String, Object>();
      try {
        OpacModuleRegistry.normalize(block, zones);
      } catch (ValidationException e) {
        for (Map.Entry<String, List<String>> entry : e.getErrors().entrySet()) {
          for (String message : entry.getValue()) {
            errors
                .computeIfAbsent("blocks." + index + "." + entry.getKey(), k -> new ArrayList<String>())
                .add(message);
          }
        }
      }
      index++;
    }

    if (!errors.isEmpty()) {
      throw ValidationException.of(errors);
    }
  }

  private void persistBlocks(Long versionId, List<?> rawBlocks) {
    List<String> zones = OpacModuleRegistry.zoneKeys();

    this.blocks.deleteByOpacPageVersionId(versionId);

    int index = 0;
    for (Object raw : rawBlocks) {
      Map<String, Object> block =
          raw instanceof Map ? (Map<String, Object>) raw : new LinkedHashMap<String, Object>();
      Map<String, Object> normalized = OpacModuleRegistry.normalize(block, zones);
      OpacBlock entity = new OpacBlock();
      entity.setOpacPageVersionId(versionId);
      entity.setZone((String) normalized.get("zone"));
      entity.setModuleKey((String) normalized.get("module_key"));
      entity.setSettings(this.json.write(normalized.get("settings")));
      entity.setResponsive(this.json.write(normalized.get("responsive")));
      entity.setOrder(index);
      this.blocks.save(entity);
      index++;
    }
  }

  // ============================ ENDPOINTS ============================

  /** GET /api/v1/opac/pages/{pageId}/versions */
  @GetMapping("/pages/{pageId}/versions")
  public Map<String, Object> index(@PathVariable Long pageId) {
    AuthenticatedUser auth = CurrentUser.get();
    Long orgId = auth.user().getCurrentOrganisationId();
    OpacPage page = this.pages.findById(pageId).orElseThrow(ApiException::notFound);
    this.checkPageScope(page, orgId);

    List<Map<String, Object>> out = new ArrayList<Map<String, Object>>();
    for (OpacPageVersion v : this.versions.findAllByOpacPageIdOrderByIdDesc(pageId)) {
      Map<String, Object> m = this.mapper.version(v, false);
      m.put("blocks_count", this.blocks.findAllByOpacPageVersionIdOrderByZoneAscOrderAsc(v.getId()).size());
      out.add(m);
    }
    return Json.of("data", out);
  }

  /** POST /api/v1/opac/pages/{pageId}/versions — nouvelle version (brouillon). */
  @PostMapping("/pages/{pageId}/versions")
  public ResponseEntity<Map<String, Object>> store(
      @PathVariable Long pageId, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Long orgId = auth.user().getCurrentOrganisationId();
    OpacPage page = this.pages.findById(pageId).orElseThrow(ApiException::notFound);
    this.checkPageScope(page, orgId);

    if (OpacPageController.str(body.get("name")) == null) {
      throw ValidationException.single("name", "The name field is required.");
    }

    OpacPageVersion source = null;
    Object sourceId = body.get("source_version_id");
    if (sourceId != null) {
      source = this.findVersion(toLong(sourceId), orgId);
      if (!source.getOpacPageId().equals(pageId)) {
        throw ApiException.notFound();
      }
    } else {
      source = this.versions.findFirstByOpacPageIdAndIsActiveTrue(pageId).orElse(null);
    }

    OpacPageVersion version = new OpacPageVersion();
    version.setOpacPageId(pageId);
    version.setName(OpacPageController.str(body.get("name")));
    version.setStatus("draft");
    version.setIsActive(false);
    version.setChangeDescription(OpacPageController.str(body.get("change_description")));
    version.setCreatedBy(auth.user().getId());
    this.versions.save(version);

    if (source != null) {
      this.copyBlocks(source, version);
    }

    Map<String, Object> data = this.mapper.version(version, true);
    return ResponseEntity.status(201)
        .header("Location", "/api/v1/opac/versions/" + version.getId())
        .body(Json.of("data", data));
  }

  /** GET /api/v1/opac/versions/{id} — version + blocs. */
  @GetMapping("/versions/{id}")
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    OpacPageVersion version = this.findVersion(id, auth.user().getCurrentOrganisationId());
    return Json.of("data", this.mapper.version(version, true));
  }

  /** PUT /api/v1/opac/versions/{id} — remplace les blocs (brouillon uniquement). */
  @PutMapping("/versions/{id}")
  @Transactional
  public Map<String, Object> update(
      @PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    OpacPageVersion version = this.findVersion(id, auth.user().getCurrentOrganisationId());

    if (body.containsKey("blocks") && body.get("blocks") instanceof List) {
      List<?> rawBlocks = (List<?>) body.get("blocks");
      // Ordre Laravel : la validation (422) précède le contrôle d'immuabilité (409).
      this.validateBlocks(rawBlocks);
      this.requireDraft(version);
    }

    if (body.containsKey("name")) {
      version.setName(OpacPageController.str(body.get("name")));
    }
    if (body.containsKey("change_description")) {
      version.setChangeDescription(OpacPageController.str(body.get("change_description")));
    }
    if (body.containsKey("blocks") && body.get("blocks") instanceof List) {
      this.persistBlocks(version.getId(), (List<?>) body.get("blocks"));
    }
    this.versions.save(version);

    return Json.of("data", this.mapper.version(this.versions.findById(id).orElseThrow(ApiException::notFound), true));
  }

  /** DELETE /api/v1/opac/versions/{id} — jamais la version active. */
  @DeleteMapping("/versions/{id}")
  @Transactional
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    OpacPageVersion version = this.findVersion(id, auth.user().getCurrentOrganisationId());
    if (Boolean.TRUE.equals(version.getIsActive())) {
      throw ApiException.conflict("La version active ne peut pas être supprimée.");
    }
    this.versions.delete(version);
    return ResponseEntity.noContent().build();
  }

  /** POST /api/v1/opac/versions/{id}/activate — publier. */
  @PostMapping("/versions/{id}/activate")
  public Map<String, Object> activate(@PathVariable Long id, @RequestBody(required = false) Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    OpacPageVersion version = this.findVersion(id, auth.user().getCurrentOrganisationId());
    String description = body != null ? OpacPageController.str(body.get("change_description")) : null;
    OpacPageVersion active = this.activate(version, description);
    return Json.of("data", this.mapper.version(active, true));
  }

  /** POST /api/v1/opac/versions/{id}/restore — re-publier une version existante. */
  @PostMapping("/versions/{id}/restore")
  public Map<String, Object> restore(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    OpacPageVersion version = this.findVersion(id, auth.user().getCurrentOrganisationId());
    OpacPageVersion active =
        this.activate(version, "Restauration de la version « " + version.getName() + " »");
    return Json.of("data", this.mapper.version(active, true));
  }

  /** POST /api/v1/opac/versions/{id}/duplicate — nouvelle branche de travail. */
  @PostMapping("/versions/{id}/duplicate")
  public ResponseEntity<Map<String, Object>> duplicate(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    OpacPageVersion version = this.findVersion(id, auth.user().getCurrentOrganisationId());

    OpacPageVersion draft = new OpacPageVersion();
    draft.setOpacPageId(version.getOpacPageId());
    draft.setName("Copie de « " + version.getName() + " »");
    draft.setStatus("draft");
    draft.setIsActive(false);
    draft.setCreatedBy(auth.user().getId());
    this.versions.save(draft);
    this.copyBlocks(version, draft);

    return ResponseEntity.status(201)
        .header("Location", "/api/v1/opac/versions/" + draft.getId())
        .body(Json.of("data", this.mapper.version(draft, true)));
  }

  /** GET /api/v1/opac/versions/{id}/diff?with={id} — comparaison des blocs. */
  @GetMapping("/versions/{id}/diff")
  public Map<String, Object> diff(@PathVariable Long id, @RequestParam(value = "with", required = false) Long withId) {
    AuthenticatedUser auth = CurrentUser.get();
    Long orgId = auth.user().getCurrentOrganisationId();
    OpacPageVersion left = this.findVersion(id, orgId);

    OpacPageVersion right;
    if (withId != null) {
      right = this.findVersion(withId, orgId);
    } else {
      right = this.versions.findFirstByOpacPageIdAndIsActiveTrue(left.getOpacPageId()).orElse(null);
    }

    if (right == null) {
      LinkedHashMap<String, Object> empty = new LinkedHashMap<String, Object>();
      empty.put("added", List.of());
      empty.put("removed", List.of());
      empty.put("modified", List.of());
      return Json.of("data", empty);
    }

    Map<String, OpacBlock> leftMap = keyed(this.blocks.findAllByOpacPageVersionIdOrderByZoneAscOrderAsc(left.getId()));
    Map<String, OpacBlock> rightMap = keyed(this.blocks.findAllByOpacPageVersionIdOrderByZoneAscOrderAsc(right.getId()));

    List<Map<String, Object>> added = new ArrayList<Map<String, Object>>();
    List<Map<String, Object>> removed = new ArrayList<Map<String, Object>>();
    List<Map<String, Object>> modified = new ArrayList<Map<String, Object>>();

    for (Map.Entry<String, OpacBlock> e : rightMap.entrySet()) {
      if (!leftMap.containsKey(e.getKey())) {
        added.add(this.mapper.block(e.getValue()));
      }
    }
    for (Map.Entry<String, OpacBlock> e : leftMap.entrySet()) {
      if (!rightMap.containsKey(e.getKey())) {
        removed.add(this.mapper.block(e.getValue()));
      }
    }
    for (Map.Entry<String, OpacBlock> e : leftMap.entrySet()) {
      OpacBlock other = rightMap.get(e.getKey());
      if (other != null) {
        String ls = e.getValue().getSettings() + "|" + e.getValue().getResponsive();
        String rs = other.getSettings() + "|" + other.getResponsive();
        if (!ls.equals(rs)) {
          LinkedHashMap<String, Object> m = new LinkedHashMap<String, Object>();
          m.put("block", e.getKey());
          m.put("left", this.json.readMap(e.getValue().getSettings()));
          m.put("right", this.json.readMap(other.getSettings()));
          modified.add(m);
        }
      }
    }

    LinkedHashMap<String, Object> data = new LinkedHashMap<String, Object>();
    data.put("with_version_id", right.getId());
    data.put("added", added);
    data.put("removed", removed);
    data.put("modified", modified);
    return Json.of("data", data);
  }

  private Map<String, OpacBlock> keyed(List<OpacBlock> list) {
    LinkedHashMap<String, OpacBlock> map = new LinkedHashMap<String, OpacBlock>();
    for (OpacBlock b : list) {
      map.put(b.getZone() + ":" + b.getModuleKey() + ":" + b.getOrder(), b);
    }
    return map;
  }

  private static Long toLong(Object value) {
    if (value == null) {
      return null;
    }
    try {
      return ((Number) value).longValue();
    } catch (ClassCastException e) {
      try {
        return Long.parseLong(String.valueOf(value));
      } catch (NumberFormatException e2) {
        return null;
      }
    }
  }
}
