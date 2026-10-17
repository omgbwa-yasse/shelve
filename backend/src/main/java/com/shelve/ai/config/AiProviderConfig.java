package com.shelve.ai.config;

/**
 * Configuration d'un provider LLM. Les clés viennent de `app.ai.providers.<name>`
 * et peuvent être surchargées par la table `settings` (voir AiProviderRegistry).
 */
public class AiProviderConfig {
  public static final String TYPE_OPENAI = "openai";
  public static final String TYPE_ANTHROPIC = "anthropic";
  public static final String TYPE_GEMINI = "gemini";

  private String type = TYPE_OPENAI;
  private String baseUrl;
  private String apiKey = "";
  private String model;

  public String getType() {
    return this.type;
  }

  public void setType(String v) {
    this.type = v;
  }

  public String getBaseUrl() {
    return this.baseUrl;
  }

  public void setBaseUrl(String v) {
    this.baseUrl = v;
  }

  public String getApiKey() {
    return this.apiKey;
  }

  public void setApiKey(String v) {
    this.apiKey = v;
  }

  public String getModel() {
    return this.model;
  }

  public void setModel(String v) {
    this.model = v;
  }
}
