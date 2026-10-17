package com.shelve.seed;

import com.shelve.common.Json;
import com.shelve.exception.ValidationException;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/**
 * Endpoints d'installation — consommés par la page web `/install`.
 * Accessibles sans authentification (voir SecurityConfig).
 *
 * GET  /api/v1/installation → liste des modules cochables (obligatoires + facultatifs).
 * POST /api/v1/installation → exécute l'installation (nom org + modules sélectionnés).
 */
@RestController
@RequestMapping(value = {"/api/v1/installation"})
public class InstallationController {
  private final InstallationService service;
  private final ModuleManager moduleManager;

  public InstallationController(InstallationService service, ModuleManager moduleManager) {
    this.service = service;
    this.moduleManager = moduleManager;
  }

  @GetMapping
  public Map<String, Object> index() {
    Map<String, Object> map = new LinkedHashMap<String, Object>();
    map.put("options", this.service.moduleOptions());
    map.put("required_modules", InstallationService.REQUIRED_MODULES);
    map.put("installed", this.moduleManager.isInstalled());
    return map;
  }

  @PostMapping
  public Map<String, Object> run(@RequestBody Map<String, Object> body) {
    String organisationName =
        body.get("organisation_name") != null ? String.valueOf(body.get("organisation_name")) : null;
    if (organisationName == null || organisationName.isBlank()) {
      throw ValidationException.single(
          "organisation_name", "The organisation name field is required.");
    }
    @SuppressWarnings("unchecked")
    List<String> modules =
        body.get("modules") instanceof List<?> list
            ? list.stream().map(String::valueOf).toList()
            : List.of();
    boolean force = Boolean.parseBoolean(String.valueOf(body.get("force")));
    return Json.of("data", this.service.run(organisationName, modules, force));
  }
}
