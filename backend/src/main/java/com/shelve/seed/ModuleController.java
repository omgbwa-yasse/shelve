package com.shelve.seed;

import com.shelve.common.Json;
import com.shelve.security.AuthenticatedUser;
import com.shelve.security.CurrentUser;
import com.shelve.security.Policy;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

/**
 * Gestion des modules actifs — réservé au superadmin (permission `setting_update`).
 *
 * GET /api/v1/settings/modules → liste des modules avec état actif/inactif.
 * PUT /api/v1/settings/modules → active/désactive les modules (obligatoires forcés).
 */
@RestController
@RequestMapping(value = {"/api/v1/settings/modules"})
public class ModuleController {
  private final ModuleManager moduleManager;
  private final InstallationService installationService;

  public ModuleController(ModuleManager moduleManager, InstallationService installationService) {
    this.moduleManager = moduleManager;
    this.installationService = installationService;
  }

  @GetMapping
  public Map<String, Object> index() {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "setting_update");
    Set<String> enabled = this.moduleManager.enabled();
    return Json.of(
        "data",
        this.installationService.moduleOptions().stream()
            .map(
                m -> {
                  Map<String, Object> copy = new LinkedHashMap<String, Object>(m);
                  copy.put("enabled", enabled.contains(m.get("key")));
                  return copy;
                })
            .toList());
  }

  @PutMapping
  public Map<String, Object> update(@RequestBody Map<String, Object> body) {
    AuthenticatedUser auth = CurrentUser.get();
    Policy.check(auth, "setting_update");
    @SuppressWarnings("unchecked")
    List<String> keys =
        body.get("modules") instanceof List<?> list
            ? list.stream().map(String::valueOf).toList()
            : List.of();
    Set<String> enabled = this.moduleManager.update(keys);
    return Json.of("data", enabled);
  }
}
