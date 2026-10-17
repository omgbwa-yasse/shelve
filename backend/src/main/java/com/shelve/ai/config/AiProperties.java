package com.shelve.ai.config;

import java.util.LinkedHashMap;
import java.util.Map;
import org.springframework.boot.context.properties.ConfigurationProperties;
import org.springframework.stereotype.Component;

/**
 * Configuration des moteurs LLM — `app.ai`.
 *
 * - `app.ai.default-provider` : nom du provider utilisé par défaut.
 * - `app.ai.timeout-seconds` : timeout d'appel (tous providers).
 * - `app.ai.providers.<name>` : un provider nommé. Chaque provider peut être :
 *   - `type`      : `openai` (API compatible OpenAI : Ollama, OpenRouter, LMStudio,
 *                   OpenAI, Mistral, Grok…) | `anthropic` (Claude) | `gemini` ;
 *   - `base-url`  : racine de l'API (ex. `http://127.0.0.1:11434/v1`) ;
 *   - `api-key`   : clé d'API (vide pour Ollama/LMStudio locaux) ;
 *   - `model`     : modèle (ex. `qwen3.5:2b`, `gpt-4o-mini`, `claude-3-5-sonnet`).
 *
 * Les valeurs peuvent être surchargées par variables d'environnement, ex. :
 * `APP_AI_PROVIDERS_OPENAI_APIKEY=sk-...`. Chacune des valeurs peut aussi être
 * lue depuis la table `settings` (clés `ai_*`) pour une configuration pilotée
 * par un administrateur — voir `AiProviderRegistry`.
 */
@Component
@ConfigurationProperties(prefix = "app.ai")
public class AiProperties {
  private String defaultProvider = "ollama";
  private int timeoutSeconds = 120;
  private Map<String, AiProviderConfig> providers = new LinkedHashMap<String, AiProviderConfig>();

  public String getDefaultProvider() {
    return this.defaultProvider;
  }

  public void setDefaultProvider(String v) {
    this.defaultProvider = v;
  }

  public int getTimeoutSeconds() {
    return this.timeoutSeconds;
  }

  public void setTimeoutSeconds(int v) {
    this.timeoutSeconds = v;
  }

  public Map<String, AiProviderConfig> getProviders() {
    return this.providers;
  }

  public void setProviders(Map<String, AiProviderConfig> v) {
    this.providers = v;
  }
}
