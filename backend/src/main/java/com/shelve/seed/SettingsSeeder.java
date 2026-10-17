package com.shelve.seed;

import com.shelve.settings.entity.Setting;
import com.shelve.settings.entity.SettingCategory;
import com.shelve.settings.repository.SettingCategoryRepository;
import com.shelve.settings.repository.SettingRepository;
import java.util.List;
import java.util.function.Function;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.stereotype.Component;
import org.springframework.transaction.annotation.Transactional;

/**
 * Paramètres applicatifs — transposition de `Settings/SettingSeeder.php`
 * (catégories + paramètres, idempotent).
 */
@Component
public class SettingsSeeder {
  private static final Logger log = LoggerFactory.getLogger(SettingsSeeder.class);

  private final SettingCategoryRepository categoryRepository;
  private final SettingRepository settingRepository;

  public SettingsSeeder(
      SettingCategoryRepository categoryRepository, SettingRepository settingRepository) {
    this.categoryRepository = categoryRepository;
    this.settingRepository = settingRepository;
  }

  @Transactional
  public void run() {
    SettingCategory general = this.category("Général", "Paramètres généraux de l'application", null);
    SettingCategory security = this.category("Sécurité", "Paramètres de sécurité et d'authentification", null);
    SettingCategory notifications = this.category("Notifications", "Paramètres de notifications et d'alertes", null);
    SettingCategory ai = this.category("Intelligence Artificielle", "Paramètres des services d'IA et des providers", null);
    SettingCategory emailNotif = this.category("Email", "Paramètres de notifications par email", notifications);
    SettingCategory aiProviders = this.category("Providers", "Configuration des providers d'IA", ai);
    SettingCategory aiModels = this.category("Modèles", "Configuration des modèles d'IA", ai);

    this.setting("app_name", general, "string", "\"Shelves\"", "Nom de l'application", true, "{\"max_length\":100}");
    this.setting("organisation_name", general, "string", "\"Mon organisation\"", "Nom de l'organisation (défini lors de l'installation)", true, "{\"max_length\":200}");
    this.setting("installation_completed", general, "boolean", "false", "Indique si l'installation initiale a été réalisée", true, null);
    this.setting("enabled_modules", general, "array", "[]", "Modules actifs (clés des modules installés)", true, null);
    this.setting("maintenance_mode", general, "boolean", "false", "Mode maintenance activé", true, null);
    this.setting("session_timeout", security, "integer", "3600", "Délai d'expiration de session (en secondes)", true, "{\"min\":300,\"max\":86400}");
    this.setting("password_min_length", security, "integer", "8", "Longueur minimale des mots de passe", true, "{\"min\":6,\"max\":50}");
    this.setting("email_notifications_enabled", emailNotif, "boolean", "true", "Activer les notifications par email", false, null);
    this.setting("email_frequency", emailNotif, "string", "\"daily\"", "Fréquence des notifications email", false, "{\"options\":[\"immediate\",\"daily\",\"weekly\",\"never\"]}");
    this.setting("ai_default_provider", ai, "string", "\"ollama\"", "Provider d'IA par défaut", true, "{\"options\":[\"ollama\",\"openai\",\"gemini\",\"claude\",\"openrouter\",\"lmstudio\",\"custom\"]}");
    this.setting("ai_default_model", ai, "string", "\"gemma4:e2b\"", "Modèle d'IA par défaut", true, null);
    this.setting("ai_max_chars_per_file", ai, "integer", "200", "Nombre maximum de caractères à conserver par fichier avant envoi à l'IA", false, "{\"min\":200,\"max\":100000,\"step\":500}");
    this.setting("ai_max_total_chars", ai, "integer", "40000", "Budget global maximum de caractères envoyés à l'IA", false, "{\"min\":5000,\"max\":200000,\"step\":1000}");
    this.setting("ai_request_timeout", ai, "integer", "120", "Timeout des requêtes IA (en secondes)", true, "{\"min\":30,\"max\":300}");
    this.setting("ollama_base_url", aiProviders, "string", "\"http://127.0.0.1:11434/v1\"", "URL de base pour Ollama (endpoint OpenAI-compatible)", true, null);
    this.setting("ollama_enabled", aiProviders, "boolean", "true", "Activer le provider Ollama", true, null);
    this.setting("lmstudio_base_url", aiProviders, "string", "\"http://127.0.0.1:1234/v1\"", "URL de base pour LM Studio", true, null);
    this.setting("lmstudio_enabled", aiProviders, "boolean", "false", "Activer le provider LM Studio", true, null);
    this.setting("openai_enabled", aiProviders, "boolean", "false", "Activer le provider OpenAI", true, null);
    this.setting("openai_api_key", aiProviders, "string", "\"\"", "Clé API pour OpenAI", true, null);
    this.setting("model_summary", aiModels, "string", "\"gemma4:e2b\"", "Modèle pour la génération de résumés", true, null);
    this.setting("model_keywords", aiModels, "string", "\"gemma4:e2b\"", "Modèle pour l'extraction de mots-clés", true, null);
    this.setting("model_analysis", aiModels, "string", "\"gemma4:e2b\"", "Modèle pour l'analyse de texte", true, null);

    log.info("Paramètres applicatifs : vérifiés (catégories + clés par défaut)");
  }

  private SettingCategory category(String name, String description, SettingCategory parent) {
    return this.categoryRepository
        .findAll((root, q, cb) -> cb.equal(root.get("name"), name))
        .stream()
        .findFirst()
        .orElseGet(() -> {
          SettingCategory c = new SettingCategory();
          c.setName(name);
          c.setDescription(description);
          c.setParentId(parent != null ? parent.getId() : null);
          return this.categoryRepository.save(c);
        });
  }

  private void setting(
      String name,
      SettingCategory category,
      String type,
      String defaultValue,
      String description,
      boolean isSystem,
      String constraints) {
    // updateOrCreate : met à jour la valeur par défaut si le paramètre existe déjà,
    // afin que les corrections (modèles, base URLs) soient rejouées au redémarrage.
    Setting s =
        this.settingRepository
            .findAll((root, q, cb) -> cb.equal(root.get("name"), name))
            .stream()
            .findFirst()
            .orElseGet(Setting::new);
    s.setName(name);
    s.setCategoryId(category.getId());
    s.setType(type);
    s.setDefaultValue(defaultValue);
    s.setDescription(description);
    s.setIsSystem(isSystem);
    s.setConstraints(constraints);
    this.settingRepository.save(s);
  }
}
