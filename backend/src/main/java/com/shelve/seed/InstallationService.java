package com.shelve.seed;

import com.shelve.settings.entity.Setting;
import com.shelve.settings.repository.SettingRepository;
import com.fasterxml.jackson.databind.ObjectMapper;
import java.util.ArrayList;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

/**
 * Service d'installation — orchestré par la page web `/install`.
 *
 * Étapes : nom d'organisation, choix des modules (4 obligatoires, 6 facultatifs),
 * création du superadmin rattaché aux bureaux, exécution des seeders sélectionnés.
 *
 * Chaque module pilote les seeders qui lui sont liés (voir {@link #MODULE_SEEDERS}).
 * Les modules obligatoires (records, transfers, tools, settings) sont toujours
 * inclus. Idempotent : chaque seeder ne crée que ce qui manque.
 */
@Service
public class InstallationService {
  private static final Logger log = LoggerFactory.getLogger(InstallationService.class);

  /** Modules obligatoires — toujours inclus, jamais décochables. */
  public static final List<String> REQUIRED_MODULES =
      List.of("records", "transfers", "tools", "settings");

  /** Correspondance module → seeders à exécuter. */
  private static final Map<String, List<String>> MODULE_SEEDERS =
      Map.ofEntries(
          Map.entry("records", List.of("referentials")),
          Map.entry("transfers", List.of("referentials")),
          Map.entry("tools", List.of("referentials")),
          Map.entry("settings", List.of("settings")),
          Map.entry("workplace", List.of("workplace")),
          Map.entry("mail", List.of("mail")),
          Map.entry("ai", List.of("ai")));

  private final SettingRepository settingRepository;
  private final ObjectMapper mapper;
  private final ModuleManager moduleManager;
  private final ReferentialsSeeder referentialsSeeder;
  private final SettingsSeeder settingsSeeder;
  private final OrganisationSeeder organisationSeeder;
  private final AdminSeeder adminSeeder;
  private final WorkplaceModuleSeeder workplaceModuleSeeder;
  private final MailModuleSeeder mailModuleSeeder;
  private final AiModuleSeeder aiModuleSeeder;
  private final ConstellioAlignmentSeeder constellioAlignmentSeeder;

  public InstallationService(
      SettingRepository settingRepository,
      ObjectMapper mapper,
      ModuleManager moduleManager,
      ReferentialsSeeder referentialsSeeder,
      SettingsSeeder settingsSeeder,
      OrganisationSeeder organisationSeeder,
      AdminSeeder adminSeeder,
      WorkplaceModuleSeeder workplaceModuleSeeder,
      MailModuleSeeder mailModuleSeeder,
      AiModuleSeeder aiModuleSeeder,
      ConstellioAlignmentSeeder constellioAlignmentSeeder) {
    this.settingRepository = settingRepository;
    this.mapper = mapper;
    this.moduleManager = moduleManager;
    this.referentialsSeeder = referentialsSeeder;
    this.settingsSeeder = settingsSeeder;
    this.organisationSeeder = organisationSeeder;
    this.adminSeeder = adminSeeder;
    this.workplaceModuleSeeder = workplaceModuleSeeder;
    this.mailModuleSeeder = mailModuleSeeder;
    this.aiModuleSeeder = aiModuleSeeder;
    this.constellioAlignmentSeeder = constellioAlignmentSeeder;
  }

  /** Liste des modules proposés à la sélection (avec drapeau obligatoire). */
  public List<Map<String, Object>> moduleOptions() {
    return ModuleCatalog.ALL.stream()
        .map(
            m -> {
              Map<String, Object> map = new LinkedHashMap<String, Object>();
              map.put("key", m.key());
              map.put("label", m.label());
              map.put("description", m.description());
              map.put("required", m.required());
              return map;
            })
        .toList();
  }

  /**
   * Exécute l'installation. Refuse si déjà installée, sauf `force=true`.
   *
   * @param organisationName nom de l'organisation (écrit dans `settings.organisation_name`)
   * @param modules clés des modules cochés (les obligatoires sont forcés)
   * @param force relance même si l'installation est déjà marquée terminée
   */
  @Transactional
  public Map<String, Object> run(String organisationName, List<String> modules, boolean force) {
    if (this.moduleManager.isInstalled() && !force) {
      throw com.shelve.exception.ApiException.conflict(
          "L'installation a déjà été réalisée. Connectez-vous en tant que superadmin pour gérer les modules depuis les paramètres.");
    }
    List<String> selected = new ArrayList<String>();
    if (modules != null) {
      selected.addAll(modules);
    }
    // Les modules obligatoires sont toujours inclus.
    for (String required : REQUIRED_MODULES) {
      if (!selected.contains(required)) selected.add(required);
    }

    Map<String, Object> executed = new LinkedHashMap<String, Object>();
    if (organisationName != null && !organisationName.isBlank()) {
      this.setOrganisationName(organisationName);
      executed.put("organisation_name", organisationName);
    }

    // Les seeders déduits des modules cochés.
    List<String> seeders = this.resolveSeeders(selected);
    if (seeders.contains("referentials")) {
      this.referentialsSeeder.run();
      executed.put("referentials", "ok");
    }
    if (seeders.contains("settings")) {
      this.settingsSeeder.run();
      executed.put("settings", "ok");
    }
    if (seeders.contains("mail")) {
      this.mailModuleSeeder.run();
      executed.put("mail", "ok");
    }
    if (seeders.contains("ai")) {
      this.aiModuleSeeder.run();
      executed.put("ai", "ok");
    }
    if (seeders.contains("workplace")) {
      this.workplaceModuleSeeder.run();
      executed.put("workplace", "ok");
    }

    // Comptes : structure organisationnelle + superadmin, puis compte admin.
    this.organisationSeeder.run();
    executed.put("organisations", "ok");
    this.adminSeeder.run();
    executed.put("admin", "ok");

    // Alignement Constellio : domaines de valeurs, types et métadonnées.
    this.constellioAlignmentSeeder.run();
    executed.put("constellio", "ok");

    // Verrouille l'installation et enregistre les modules actifs.
    this.moduleManager.update(selected);
    this.moduleManager.markInstalled();

    log.info("Installation exécutée (org={}, modules={})", organisationName, selected);
    Map<String, Object> result = new LinkedHashMap<String, Object>();
    result.put("executed", executed);
    result.put("organisation_name", organisationName);
    result.put("modules", selected);
    result.put("seeders", seeders);
    return result;
  }

  private List<String> resolveSeeders(List<String> modules) {
    LinkedHashMap<String, Boolean> seeders = new LinkedHashMap<String, Boolean>();
    for (String module : modules) {
      for (String seeder : MODULE_SEEDERS.getOrDefault(module, List.of())) {
        seeders.put(seeder, Boolean.TRUE);
      }
    }
    return new ArrayList<String>(seeders.keySet());
  }

  private void setOrganisationName(String name) {
    Setting s =
        this.settingRepository
            .findAll((root, q, cb) -> cb.equal(root.get("name"), "organisation_name"))
            .stream()
            .findFirst()
            .orElse(null);
    if (s == null) return;
    try {
      s.setValue(this.mapper.writeValueAsString(name));
      this.settingRepository.save(s);
    } catch (Exception e) {
      log.warn("Impossible d'écrire organisation_name={}", name, e);
    }
  }
}
