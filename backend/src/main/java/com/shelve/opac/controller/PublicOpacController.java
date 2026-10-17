package com.shelve.opac.controller;

import com.shelve.common.Json;
import com.shelve.exception.ApiException;
import com.shelve.opac.entity.OpacPage;
import com.shelve.opac.entity.OpacPageVersion;
import com.shelve.opac.repository.OpacPageRepository;
import com.shelve.opac.repository.OpacPageVersionRepository;
import com.shelve.opac.service.OpacMapper;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/**
 * Rendu public du constructeur OPAC — R05 : l'API n'expose que la STRUCTURE
 * de la version active (page + version + blocs) ; le rendu vit côté React.
 * Accès public (permitAll dans SecurityConfig).
 */
@RestController
@RequestMapping("/api/public/opac")
public class PublicOpacController {
  private final OpacPageRepository pages;
  private final OpacPageVersionRepository versions;
  private final OpacMapper mapper;

  public PublicOpacController(
      OpacPageRepository pages, OpacPageVersionRepository versions, OpacMapper mapper) {
    this.pages = pages;
    this.versions = versions;
    this.mapper = mapper;
  }

  /** GET /api/public/opac/home */
  @GetMapping("/home")
  public Map<String, Object> home() {
    OpacPage page = this.pages.findBySlug("home").orElse(null);
    if (page == null) {
      return Json.of("data", null);
    }

    OpacPageVersion version = this.versions.findFirstByOpacPageIdAndIsActiveTrue(page.getId()).orElse(null);

    LinkedHashMap<String, Object> data = new LinkedHashMap<String, Object>();
    data.put("page", this.mapper.page(page, this.versions.findAllByOpacPageId(page.getId()).size()));
    data.put("version", version != null ? this.mapper.version(version, false) : null);
    data.put("blocks", version != null ? this.mapper.blocksOf(version.getId()) : List.of());
    return Json.of("data", data);
  }

  /** GET /api/public/opac/pages/{slug} */
  @GetMapping("/pages/{slug}")
  public Map<String, Object> page(@PathVariable String slug) {
    OpacPage page = this.pages.findBySlug(slug).orElseThrow(ApiException::notFound);
    if (!"published".equals(page.getStatus())) {
      throw ApiException.notFound();
    }
    OpacPageVersion version = this.versions.findFirstByOpacPageIdAndIsActiveTrue(page.getId()).orElse(null);

    LinkedHashMap<String, Object> data = new LinkedHashMap<String, Object>();
    data.put("page", this.mapper.page(page, this.versions.findAllByOpacPageId(page.getId()).size()));
    data.put("version", version != null ? this.mapper.version(version, false) : null);
    data.put("blocks", version != null ? this.mapper.blocksOf(version.getId()) : List.of());
    return Json.of("data", data);
  }
}
