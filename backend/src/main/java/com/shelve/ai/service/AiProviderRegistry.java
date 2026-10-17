package com.shelve.ai.service;

import com.shelve.ai.config.AiProperties;
import com.shelve.ai.config.AiProviderConfig;
import com.shelve.exception.ApiException;
import com.shelve.settings.service.SettingReader;
import java.util.Set;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;

/**
 * Registre des providers LLM configurables.
 *
 * Ordre de résolution de chaque valeur (du plus prioritaire au moins) :
 *  1. table `settings` (clés `ai_default_provider`, `ai_default_model`,
 *     `<provider>_base_url`, `<provider>_api_key`, `<provider>_enabled`) — les
 *     valeurs sont créées par le seeder (SettingsSeeder) et modifiables par un
 *     administrateur depuis le backend, sans recompiler ;
 *  2. variables d'environnement `APP_AI_PROVIDERS_<NAME>_*` ;
 *  3. `app.ai` (application.yml).
 *
 * Un provider désactivé (`<provider>_enabled=false`) n'est jamais utilisé.
 */
@Service
public class AiProviderRegistry {
  private final AiProperties props;
  private final SettingReader settings;

  public AiProviderRegistry(AiProperties props, SettingReader settings) {
    this.props = props;
    this.settings = settings;
  }

  /** Noms des providers déclarés dans la configuration. */
  public Set<String> providerNames() {
    return this.props.getProviders().keySet();
  }

  /** Provider par défaut (défaut = `ai_default_provider` puis `app.ai.default-provider`). */
  public AiProviderConfig resolveDefault() {
    String defaultName =
        this.settings.stringValue("ai_default_provider", this.props.getDefaultProvider());
    return this.resolve(defaultName);
  }

  /** Résout un provider par nom (fallback sur le défaut si inconnu). */
  public AiProviderConfig resolve(String providerName) {
    String name =
        providerName != null && !providerName.isBlank()
            ? providerName
            : this.settings.stringValue("ai_default_provider", this.props.getDefaultProvider());

    // Provider désactivé via `<name>_enabled=false` → repli sur le défaut.
    if (!this.isEnabled(name)) {
      name = this.settings.stringValue("ai_default_provider", this.props.getDefaultProvider());
    }

    AiProviderConfig base = this.props.getProviders().get(name);
    if (base == null) {
      base = this.props.getProviders().values().stream().findFirst().orElse(null);
    }
    if (base == null) {
      throw new ApiException(
          HttpStatus.SERVICE_UNAVAILABLE,
          "Aucun provider LLM configuré. Renseignez `app.ai.providers` dans la configuration.");
    }

    AiProviderConfig effective = new AiProviderConfig();
    effective.setType(base.getType());

    // Base URL : settings `<name>_base_url` > env > app.ai.
    String envBase = System.getenv("APP_AI_PROVIDERS_" + this.envKey(name) + "_BASEURL");
    effective.setBaseUrl(
        this.firstNonBlank(
            this.settings.stringValue(name + "_base_url", null),
            envBase,
            base.getBaseUrl()));

    // Clé API : settings `<name>_api_key` > env > app.ai.
    String envApi = System.getenv("APP_AI_PROVIDERS_" + this.envKey(name) + "_APIKEY");
    effective.setApiKey(
        this.firstNonBlank(
            this.settings.stringValue(name + "_api_key", null),
            envApi,
            base.getApiKey()));

    // Modèle : `ai_default_model` (provider par défaut) ou `<name>_model` > env > app.ai.
    String envModel = System.getenv("APP_AI_PROVIDERS_" + this.envKey(name) + "_MODEL");
    String settingModel = this.settings.stringValue(name + "_model", null);
    if (settingModel == null && this.isDefaultName(name)) {
      settingModel = this.settings.stringValue("ai_default_model", null);
    }
    effective.setModel(this.firstNonBlank(settingModel, envModel, base.getModel()));

    return effective;
  }

  /** Un provider est actif si aucun `enabled=false` n'est déclaré pour lui. */
  private boolean isEnabled(String name) {
    Object enabled = this.settings.effective(name + "_enabled");
    if (enabled == null) return true;
    if (enabled instanceof Boolean b) return b;
    return Boolean.parseBoolean(String.valueOf(enabled));
  }

  private boolean isDefaultName(String name) {
    String defaultName =
        this.settings.stringValue("ai_default_provider", this.props.getDefaultProvider());
    return name.equals(defaultName);
  }

  private String envKey(String name) {
    return name.toUpperCase().replace("-", "_");
  }

  private String firstNonBlank(String... values) {
    for (String v : values) {
      if (v != null && !v.isBlank()) return v;
    }
    return null;
  }
}
