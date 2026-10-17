package com.shelve.seed;

import com.fasterxml.jackson.core.type.TypeReference;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.shelve.settings.entity.Setting;
import com.shelve.settings.repository.SettingRepository;
import java.util.ArrayList;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Set;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/**
 * Gestion des modules actifs — persiste la liste des clés de modules dans le
 * paramètre `settings.enabled_modules` (JSON array). Consommé par le superadmin
 * depuis les paramètres, et par l'installation pour verrouiller les choix.
 */
@Service
public class ModuleManager {
  private static final Logger log = LoggerFactory.getLogger(ModuleManager.class);

  public static final String SETTING_NAME = "enabled_modules";
  public static final String INSTALLATION_DONE = "installation_completed";

  private final SettingRepository settingRepository;
  private final ObjectMapper mapper;

  public ModuleManager(SettingRepository settingRepository, ObjectMapper mapper) {
    this.settingRepository = settingRepository;
    this.mapper = mapper;
  }

  /** Clés des modules actuellement actifs (défaut : tous si jamais configuré). */
  @Transactional(readOnly = true)
  public Set<String> enabled() {
    Object raw = this.value(SETTING_NAME);
    if (raw == null) {
      // Jamais configuré → tous les modules sont actifs.
      return ModuleCatalog.ALL.stream().map(ModuleCatalog.Module::key).collect(java.util.stream.Collectors.toCollection(LinkedHashSet::new));
    }
    try {
      List<String> list = this.mapper.convertValue(raw, new TypeReference<List<String>>() {});
      return new LinkedHashSet<String>(list == null ? List.of() : list);
    } catch (Exception e) {
      log.warn("enabled_modules illisible ({}) — tous actifs.", raw, e);
      return ModuleCatalog.ALL.stream().map(ModuleCatalog.Module::key).collect(java.util.stream.Collectors.toCollection(LinkedHashSet::new));
    }
  }

  /** Remplace la liste des modules actifs. Les obligatoires sont toujours forcés. */
  @Transactional
  public Set<String> update(List<String> keys) {
    Set<String> active = new LinkedHashSet<String>(keys == null ? List.of() : keys);
    for (String required : InstallationService.REQUIRED_MODULES) {
      active.add(required);
    }
    this.setValue(SETTING_NAME, new ArrayList<String>(active));
    return active;
  }

  /** True si l'installation initiale a été réalisée. */
  @Transactional(readOnly = true)
  public boolean isInstalled() {
    Object raw = this.value(INSTALLATION_DONE);
    if (raw == null) return false;
    if (raw instanceof Boolean b) return b;
    return Boolean.parseBoolean(String.valueOf(raw));
  }

  /** Marque l'installation comme terminée. */
  @Transactional
  public void markInstalled() {
    this.setValue(INSTALLATION_DONE, Boolean.TRUE);
  }

  private Object value(String name) {
    return this.settingRepository
        .findAll((root, q, cb) -> cb.equal(root.get("name"), name))
        .stream()
        .findFirst()
        .map(s -> {
          String raw = s.getValue() != null && !s.getValue().isBlank() ? s.getValue() : s.getDefaultValue();
          if (raw == null || raw.isBlank()) return null;
          try {
            return this.mapper.readValue(raw, Object.class);
          } catch (Exception e) {
            return raw;
          }
        })
        .orElse(null);
  }

  private void setValue(String name, Object value) {
    Setting s =
        this.settingRepository
            .findAll((root, q, cb) -> cb.equal(root.get("name"), name))
            .stream()
            .findFirst()
            .orElse(null);
    if (s == null) return;
    try {
      s.setValue(this.mapper.writeValueAsString(value));
      this.settingRepository.save(s);
    } catch (Exception e) {
      log.warn("Impossible d'écrire {}={}", name, value, e);
    }
  }
}
