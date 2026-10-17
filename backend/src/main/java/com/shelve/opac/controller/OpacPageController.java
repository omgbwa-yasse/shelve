package com.shelve.opac.controller;

import com.shelve.common.Json;
import com.shelve.common.Validator;
import com.shelve.exception.ApiException;
import com.shelve.opac.entity.OpacBlock;
import com.shelve.opac.entity.OpacPage;
import com.shelve.opac.entity.OpacPageVersion;
import com.shelve.opac.repository.OpacBlockRepository;
import com.shelve.opac.repository.OpacPageRepository;
import com.shelve.opac.repository.OpacPageVersionRepository;
import com.shelve.opac.service.OpacMapper;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/** Pages OPAC — CRUD + bootstrap de la page `home` (table `opac_pages`). */
@RestController
@RequestMapping("/api/v1/opac/pages")
public class OpacPageController {
  private final OpacPageRepository pages;
  private final OpacPageVersionRepository versions;
  private final OpacBlockRepository blocks;
  private final OpacMapper mapper;

  public OpacPageController(
      OpacPageRepository pages,
      OpacPageVersionRepository versions,
      OpacBlockRepository blocks,
      OpacMapper mapper) {
    this.pages = pages;
    this.versions = versions;
    this.blocks = blocks;
    this.mapper = mapper;
  }

  // ============================ HELPERS ============================

  private boolean inScope(OpacPage page, Long orgId) {
    return page.getOrganisationId() == null
        || (orgId != null && page.getOrganisationId().equals(orgId));
  }

  private OpacPage findInScope(Long id, Long orgId) {
    OpacPage page = this.pages.findById(id).orElseThrow(ApiException::notFound);
    if (!inScope(page, orgId)) {
      throw ApiException.notFound();
    }
    return page;
  }

  private Map<String, Object> pageMapper(OpacPage p) {
    return this.mapper.page(p, this.versions.findAllByOpacPageId(p.getId()).size());
  }

  // ============================ ENDPOINTS ============================

  /** GET /api/v1/opac/pages — liste (créée la page `home` si vide). */
  @GetMapping
  public Map<String, Object> index() {
    AuthenticatedUser auth = CurrentUser.get();
    Long orgId = auth.user().getCurrentOrganisationId();
    this.ensureDefaultHome(orgId);

    List<Map<String, Object>> out = new ArrayList<Map<String, Object>>();
    for (OpacPage page : this.pages.findAllByOrderByIsDefaultDescTitleAsc()) {
      if (inScope(page, orgId)) {
        out.add(pageMapper(page));
      }
    }
    return Json.of("data", out);
  }

  /** GET /api/v1/opac/pages/{id} — détail : page + versions. */
  @GetMapping("/{id}")
  public Map<String, Object> show(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    Long orgId = auth.user().getCurrentOrganisationId();
    OpacPage page = this.findInScope(id, orgId);

    List<Map<String, Object>> versionList = new ArrayList<Map<String, Object>>();
    for (OpacPageVersion v : this.versions.findAllByOpacPageIdOrderByIdDesc(page.getId())) {
      versionList.add(this.mapper.version(v, false));
    }

    LinkedHashMap<String, Object> data = new LinkedHashMap<String, Object>();
    data.put("page", pageMapper(page));
    data.put("versions", versionList);
    return Json.of("data", data);
  }

  /** POST /api/v1/opac/pages — crée la page + sa première version. */
  @PostMapping
  public ResponseEntity<Map<String, Object>> store(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Long orgId = auth.user().getCurrentOrganisationId();

    String slug = OpacPageController.str(body.get("slug"));
    String title = OpacPageController.str(body.get("title"));

    Validator v = Validator.begin();
    v.require("slug", slug, "The slug field is required.");
    v.require("title", title, "The title field is required.");
    v.unique("slug", slug, slug != null && this.pages.findBySlug(slug).isPresent(), "opac_pages", "slug");
    v.validate();

    OpacPage page = new OpacPage();
    page.setOrganisationId(orgId);
    page.setSlug(slug);
    page.setTitle(title);
    page.setDescription(OpacPageController.str(body.get("description")));
    page.setStatus("draft");
    page.setIsDefault(OpacPageController.bool(body.get("is_default"), false));
    page.setCreatedBy(auth.user().getId());
    this.pages.save(page);

    OpacPageVersion version = new OpacPageVersion();
    version.setOpacPageId(page.getId());
    version.setName("Version 1");
    version.setStatus("draft");
    version.setIsActive(false);
    version.setCreatedBy(auth.user().getId());
    this.versions.save(version);

    return ResponseEntity.status(201)
        .header("Location", "/api/v1/opac/pages/" + page.getId())
        .body(Json.of("data", pageMapper(page)));
  }

  /** PATCH /api/v1/opac/pages/{id} — met à jour la page. */
  @PatchMapping("/{id}")
  public Map<String, Object> update(@PathVariable Long id, @RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Long orgId = auth.user().getCurrentOrganisationId();
    OpacPage page = this.findInScope(id, orgId);

    String slug = body.containsKey("slug") ? OpacPageController.str(body.get("slug")) : page.getSlug();
    String title = body.containsKey("title") ? OpacPageController.str(body.get("title")) : page.getTitle();

    Validator v = Validator.begin();
    v.require("slug", slug, "The slug field is required.");
    v.require("title", title, "The title field is required.");
    if (slug != null) {
      this.pages.findBySlug(slug).ifPresent(existing -> {
        if (!existing.getId().equals(page.getId())) {
          v.add("slug", "The slug has already been taken.");
        }
      });
    }
    v.validate();

    if (body.containsKey("slug")) {
      page.setSlug(slug);
    }
    if (body.containsKey("title")) {
      page.setTitle(title);
    }
    if (body.containsKey("description")) {
      page.setDescription(OpacPageController.str(body.get("description")));
    }
    if (body.containsKey("is_default")) {
      page.setIsDefault(OpacPageController.bool(body.get("is_default"), page.getIsDefault()));
    }
    page.setUpdatedBy(auth.user().getId());
    this.pages.save(page);

    return Json.of("data", pageMapper(page));
  }

  /** DELETE /api/v1/opac/pages/{id}. */
  @DeleteMapping("/{id}")
  public ResponseEntity<Void> destroy(@PathVariable Long id) {
    AuthenticatedUser auth = CurrentUser.get();
    OpacPage page = this.findInScope(id, auth.user().getCurrentOrganisationId());
    this.pages.delete(page);
    return ResponseEntity.noContent().build();
  }

  // ============================ SEED ============================

  private void ensureDefaultHome(Long orgId) {
    if (this.pages.count() > 0) {
      return;
    }
    OpacPage home = new OpacPage();
    home.setOrganisationId(orgId);
    home.setSlug("home");
    home.setTitle("Accueil");
    home.setDescription("Page d'accueil du portail public.");
    home.setStatus("draft");
    home.setIsDefault(true);
    this.pages.save(home);

    OpacPageVersion version = new OpacPageVersion();
    version.setOpacPageId(home.getId());
    version.setName("Version 1");
    version.setStatus("draft");
    version.setIsActive(false);
    this.versions.save(version);

    String[][] raw = {
      {"header", "menu", "{\"title\":\"Navigation\",\"show_sub\":true,\"variant\":\"horizontal\"}"},
      {"hero", "search", "{\"title\":\"Rechercher\",\"placeholder\":\"Rechercher un document…\",\"variant\":\"simple\"}"},
      {
        "main",
        "news",
        "{\"title\":\"Actualités\",\"limit\":3,\"cols\":3,\"featured_only\":false,\"variant\":\"grid\"}"
      },
      {
        "main",
        "events",
        "{\"title\":\"Événements\",\"limit\":4,\"cols\":2,\"filter\":\"upcoming\",\"variant\":\"upcoming\"}"
      },
      {
        "sidebar",
        "records",
        "{\"title\":\"Derniers documents\",\"limit\":3,\"cols\":1,\"sort\":\"created_at\",\"has_digital_copy\":false,\"variant\":\"list\"}"
      },
      {
        "footer",
        "links",
        "{\"title\":\"Liens utiles\",\"links\":\"Recherche | /opac/search\\nCatalogue | /opac/records\",\"variant\":\"list\"}"
      },
    };

    String[] responsive = {
      null,
      null,
      "{\"tablet\":{\"cols\":2},\"mobile\":{\"cols\":1,\"limit\":2}}",
      "{\"mobile\":{\"cols\":1,\"limit\":2}}",
      "{\"mobile\":{\"hidden\":true}}",
      null
    };

    for (int i = 0; i < raw.length; i++) {
      OpacBlock block = new OpacBlock();
      block.setOpacPageVersionId(version.getId());
      block.setZone(raw[i][0]);
      block.setModuleKey(raw[i][1]);
      block.setSettings(raw[i][2]);
      block.setResponsive(responsive[i]);
      block.setOrder(i);
      this.blocks.save(block);
    }
  }

  // ============================ STATIC ============================

  static String str(Object value) {
    return value != null ? String.valueOf(value) : null;
  }

  static Boolean bool(Object value, Boolean fallback) {
    if (value == null) {
      return fallback;
    }
    if (value instanceof Boolean) {
      return (Boolean) value;
    }
    return Boolean.parseBoolean(String.valueOf(value));
  }
}
